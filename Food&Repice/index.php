<?php
require_once 'db.php';

// จัดการการลบข้อมูล (Delete) รวมถึงลบไฟล์รูปภาพออกจากโฟลเดอร์เพื่อไม่ให้เปลืองพื้นที่
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("SELECT image FROM foods WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    $foodItem = $stmt->fetch();
    
    if ($foodItem && !empty($foodItem['image'])) {
        $imagePath = 'uploads/' . $foodItem['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // ลบไฟล์รูปจริงออกจากเซิร์ฟเวอร์
        }
    }

    $stmt = $pdo->prepare("DELETE FROM foods WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: index.php");
    exit;
}

// ดึงข้อมูลอาหารทั้งหมดมาแสดงผล (Read)
$stmt = $pdo->query("SELECT * FROM foods ORDER BY id DESC");
$foods = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการข้อมูลอาหารและสูตรอาหาร</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
            <h2 class="font-weight-bold mb-0">🥗 รายการเมนูอาหารทั้งหมด</h2>
            <a href="manage.php" class="btn btn-primary shadow-sm">+ เพิ่มเมนูอาหารใหม่</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%" class="text-center">รูปภาพ</th>
                            <th style="width: 20%">ชื่ออาหาร (ไทย)</th>
                            <th style="width: 15%">หมวดหมู่</th>
                            <th style="width: 35%">วัตถุดิบและส่วนผสม (Recipe)</th>
                            <th style="width: 15%" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($foods)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">ยังไม่มีข้อมูลอาหารในระบบ</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($foods as $food): 
                                // ดึงข้อมูลวัตถุดิบของอาหารแต่ละรายการ
                                $stmtRecipe = $pdo->prepare("SELECT * FROM recipes WHERE food_id = ?");
                                $stmtRecipe->execute([$food['id']]);
                                $recipes = $stmtRecipe->fetchAll();
                            ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if (!empty($food['image']) && file_exists('uploads/' . $food['image'])): ?>
                                            <img src="uploads/<?= htmlspecialchars($food['image']) ?>" alt="รูปอาหาร" class="rounded shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-light p-2">ไม่มีรูปภาพ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($food['name_th']) ?></strong></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($food['category']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($recipes)): ?>
                                            <ul class="mb-0 ps-3">
                                                <?php foreach ($recipes as $r): ?>
                                                    <li>
                                                        <?= htmlspecialchars($r['recipe_name']) ?> 
                                                        <span class="fw-bold"><?= $r['quantity'] ?></span> 
                                                        <?= htmlspecialchars($r['unit_name']) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="text-muted small">ไม่มีข้อมูลวัตถุดิบ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="manage.php?id=<?= $food['id'] ?>" class="btn btn-sm btn-warning me-1">แก้ไข</a>
                                        <a href="index.php?delete_id=<?= $food['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบเมนูนี้?\nข้อมูลวัตถุดิบและรูปภาพจะถูกลบไปด้วย');">ลบ</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>