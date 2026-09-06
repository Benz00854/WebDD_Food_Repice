<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
$food = ['name_th' => '', 'category' => '', 'image' => ''];
$recipes = [];

// กรณี "แก้ไข" -> ดึงข้อมูลเดิมมาเติมลงในฟอร์ม
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM foods WHERE id = ?");
    $stmt->execute([$id]);
    $food = $stmt->fetch();
    
    if (!$food) {
        header("Location: index.php");
        exit;
    }

    $stmtR = $pdo->prepare("SELECT * FROM recipes WHERE food_id = ?");
    $stmtR->execute([$id]);
    $recipes = $stmtR->fetchAll();
}

// เมื่อผู้ใช้กดปุ่ม "บันทึกข้อมูล"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_th = $_POST['name_th'];
    $category = $_POST['category'];
    $imageName = $food['image']; // ใช้รูปเดิมเป็นค่าเริ่มต้น

    // จัดการการอัปโหลดไฟล์รูปภาพ
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // อนุญาตให้อัปโหลดเฉพาะนามสกุลรูปภาพ
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // ตั้งชื่อไฟล์ใหม่ด้วย microtime ป้องกันชื่อซ้ำกัน
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            
            if(!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                // ถ้ามีรูปเก่าอยู่แล้ว ให้ลบทิ้งเพื่อประหยัดพื้นที่เซิร์ฟเวอร์
                if (!empty($food['image']) && file_exists($uploadFileDir . $food['image'])) {
                    unlink($uploadFileDir . $food['image']);
                }
                $imageName = $newFileName;
            }
        }
    }

    if ($id) {
        // --- กรณีแก้ไข (Update) ---
        $stmt = $pdo->prepare("UPDATE foods SET name_th = ?, category = ?, image = ? WHERE id = ?");
        $stmt->execute([$name_th, $category, $imageName, $id]);

        // ลบวัตถุดิบเดิมออกแล้วบันทึกใหม่ยกเซ็ต
        $pdo->prepare("DELETE FROM recipes WHERE food_id = ?")->execute([$id]);
        $food_id = $id;
    } else {
        // --- กรณีเพิ่มใหม่ (Create) ---
        $stmt = $pdo->prepare("INSERT INTO foods (name_th, category, image) VALUES (?, ?, ?)");
        $stmt->execute([$name_th, $category, $imageName]);
        $food_id = $pdo->lastInsertId();
    }

    // บันทึกรายการวัตถุดิบ (Recipes List)
    if (isset($_POST['recipes']) && is_array($_POST['recipes'])) {
        $stmtRecipe = $pdo->prepare("INSERT INTO recipes (food_id, recipe_name, quantity, unit_name) VALUES (?, ?, ?, ?)");
        foreach ($_POST['recipes'] as $r) {
            if (!empty($r['recipe_name']) && !empty($r['quantity'])) {
                $stmtRecipe->execute([$food_id, $r['recipe_name'], $r['quantity'], $r['unit_name']]);
            }
        }
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'แก้ไข' : 'เพิ่ม' ?>เมนูอาหาร</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5" style="max-width: 750px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white p-3">
                <h4 class="mb-0"><?= $id ? '✏️ แก้ไขข้อมูลเมนูอาหาร' : '➕ เพิ่มข้อมูลเมนูอาหารใหม่' ?></h4>
            </div>
            <div class="card-body p-4">
                <!-- เพิ่ม enctype="multipart/form-data" เพื่อให้ฟอร์มรองรับการอัปโหลดไฟล์ -->
                <form action="" method="POST" enctype="multipart/form-data">
                    
                    <!-- ส่วนที่ 1: ข้อมูลอาหารหลัก -->
                    <h5 class="text-primary border-bottom pb-2 mb-3">ข้อมูลอาหาร</h5>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">ชื่ออาหาร (ภาษาไทย) <span class="text-danger">*</span></label>
                        <input type="text" name="name_th" class="form-control" value="<?= htmlspecialchars($food['name_th']) ?>" required placeholder="เช่น ผัดไทยกุ้งสด">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">หมวดหมู่ <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <option value="อาหารคาว" <?= $food['category'] == 'อาหารคาว' ? 'selected' : '' ?>>อาหารคาว</option>
                            <option value="อาหารหวาน" <?= $food['category'] == 'อาหารหวาน' ? 'selected' : '' ?>>อาหารหวาน</option>
                            <option value="เครื่องดื่ม" <?= $food['category'] == 'เครื่องดื่ม' ? 'selected' : '' ?>>เครื่องดื่ม</option>
                        </select>
                    </div>

                    <!-- ส่วนอัปโหลดรูปภาพ -->
                    <div class="mb-4">
                        <label class="form-label font-weight-bold">รูปภาพอาหาร</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg, image/png, image/webp" id="imageInput" onchange="previewImage(event)">
                        <div class="form-text">รองรับไฟล์ภาพนามสกุล: JPG, JPEG, PNG, WEBP</div>
                        
                        <!-- ช่องแสดงตัวอย่างรูปภาพ (Image Preview) -->
                        <div class="mt-3">
                            <?php if (!empty($food['image']) && file_exists('uploads/' . $food['image'])): ?>
                                <img id="preview" src="uploads/<?= htmlspecialchars($food['image']) ?>" alt="ตัวอย่างรูปภาพ" class="rounded shadow-sm" style="max-height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <img id="preview" src="#" alt="ตัวอย่างรูปภาพ" class="rounded shadow-sm d-none" style="max-height: 150px; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ส่วนที่ 2: ข้อมูลวัตถุดิบ/สูตรอาหาร -->
                    <h5 class="text-primary border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                        สูตรและวัตถุดิบ (Recipe)
                        <button type="button" class="btn btn-sm btn-outline-success" id="add-recipe-btn">+ เพิ่มแถววัตถุดิบ</button>
                    </h5>

                    <div id="recipe-container">
                        <?php if (empty($recipes)): ?>
                            <!-- แถวเริ่มต้นกรณีเพิ่มข้อมูลใหม่ -->
                            <div class="row g-2 mb-2 recipe-row align-items-center">
                                <div class="col-5">
                                    <input type="text" name="recipes[0][recipe_name]" class="form-control" placeholder="ชื่อวัตถุดิบ เช่น เส้นเล็ก">
                                </div>
                                <div class="col-3">
                                    <input type="number" step="0.01" name="recipes[0][quantity]" class="form-control" placeholder="ปริมาณ เช่น 150">
                                </div>
                                <div class="col-3">
                                    <input type="text" name="recipes[0][unit_name]" class="form-control" placeholder="หน่วย เช่น กรัม">
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.recipe-row').remove();">✕</button>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- แสดงวัตถุดิบเดิมกรณีแก้ไข -->
                            <?php foreach ($recipes as $index => $r): ?>
                                <div class="row g-2 mb-2 recipe-row align-items-center">
                                    <div class="col-5">
                                        <input type="text" name="recipes[<?= $index ?>][recipe_name]" class="form-control" value="<?= htmlspecialchars($r['recipe_name']) ?>" required>
                                    </div>
                                    <div class="col-3">
                                        <input type="number" step="0.01" name="recipes[<?= $index ?>][quantity]" class="form-control" value="<?= $r['quantity'] ?>" required>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" name="recipes[<?= $index ?>][unit_name]" class="form-control" value="<?= htmlspecialchars($r['unit_name']) ?>" required>
                                    </div>
                                    <div class="col-1 text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.recipe-row').remove();">✕</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- ปุ่มควบคุม -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-success">💾 บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript สำหรับ Preview รูปภาพ และเพิ่มแถววัตถุดิบแบบ Dynamic -->
    <script>
        // ฟังก์ชันแสดงตัวอย่างรูปภาพก่อนอัปโหลด (HCI: Feedback & Visibility)
        function previewImage(event) {
            const reader = new FileReader();
            const preview = document.getElementById('preview');
            reader.onload = function() {
                preview.src = reader.result;
                preview.classList.remove('d-none');
            }
            if(event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        let recipeIndex = <?= max(count($recipes), 1) ?>;
        
        document.getElementById('add-recipe-btn').addEventListener('click', function() {
            const container = document.getElementById('recipe-container');
            const newRow = document.createElement('div');
            newRow.className = 'row g-2 mb-2 recipe-row align-items-center';
            newRow.innerHTML = `
                <div class="col-5">
                    <input type="text" name="recipes[${recipeIndex}][recipe_name]" class="form-control" placeholder="ชื่อวัตถุดิบ" required>
                </div>
                <div class="col-3">
                    <input type="number" step="0.01" name="recipes[${recipeIndex}][quantity]" class="form-control" placeholder="ปริมาณ" required>
                </div>
                <div class="col-3">
                    <input type="text" name="recipes[${recipeIndex}][unit_name]" class="form-control" placeholder="หน่วย" required>
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.recipe-row').remove();">✕</button>
                </div>
            `;
            container.appendChild(newRow);
            recipeIndex++;
        });
    </script>
</body>
</html>