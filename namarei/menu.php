<?php
include '../includes/config.php';
include '../includes/header.php';

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

foreach ($categories as $category) {
    echo "<h2>" . htmlspecialchars($category['name']) . "</h2>";
    $stmtItems = $pdo->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1");
    $stmtItems->execute([$category['id']]);
    $items = $stmtItems->fetchAll();
    echo "<div class='menu-grid'>";
    foreach ($items as $item) {
        echo "<div class='menu-item'>";
        if ($item['image']) {
            echo "<img src='../uploads/" . htmlspecialchars($item['image']) . "' width='200'>";
        }
        echo "<h3>" . htmlspecialchars($item['name']) . "</h3>";
        echo "<p>" . htmlspecialchars($item['description']) . "</p>";
        echo "<span class='price'>KES " . number_format($item['price'], 2) . "</span>";
        echo "</div>";
    }
    echo "</div>";
}

include '../includes/footer.php';
?>