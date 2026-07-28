<?php
include '../includes/config.php';
include '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $image = '';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $filename);
        $image = $filename;
    }

    $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, image, is_available) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $name, $description, $price, $image, $is_available]);
    header('Location: menu.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Menu Item - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>Add Menu Item</h1>
        <form method="post" enctype="multipart/form-data">
            <label>Category:</label>
            <select name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select><br>
            <label>Name: <input type="text" name="name" required></label><br>
            <label>Description: <textarea name="description"></textarea></label><br>
            <label>Price: <input type="number" step="0.01" name="price" required></label><br>
            <label>Image: <input type="file" name="image"></label><br>
            <label>Available: <input type="checkbox" name="is_available" checked></label><br>
            <button type="submit" class="btn">Save</button>
        </form>
    </div>
</div>
</body>
</html>