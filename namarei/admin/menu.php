<?php
include '../includes/config.php';
include '../includes/auth.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: menu.php');
    exit;
}

// Fetch all items with category names
$stmt = $pdo->query("SELECT m.*, c.name as category_name FROM menu_items m JOIN categories c ON m.category_id = c.id ORDER BY c.name, m.name");
$items = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Menu - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>Menu Items</h1>
        <a href="menu_add.php" class="btn">Add New Item</a>
        <table>
            <thead>
                <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Available</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><?php if ($item['image']): ?><img src="../uploads/<?= htmlspecialchars($item['image']) ?>" width="50"><?php endif; ?></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['category_name']) ?></td>
                <td>KES <?= number_format($item['price'], 2) ?></td>
                <td><?= $item['is_available'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="menu_edit.php?id=<?= $item['id'] ?>">Edit</a> |
                    <a href="menu.php?delete=<?= $item['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>