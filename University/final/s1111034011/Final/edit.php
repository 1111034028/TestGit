<?php
require_once 'DB_open.php'; 

$id = $_GET['id'];
$id = mysqli_real_escape_string($link, $id);

$sql = "SELECT * FROM animals WHERE id = $id";
$result = mysqli_query($link, $sql);

$animal = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $image = $_POST['image'];
    $description = $_POST['description'];

    $name = mysqli_real_escape_string($link, $name);
    $type = mysqli_real_escape_string($link, $type);
    $image = mysqli_real_escape_string($link, $image);
    $description = mysqli_real_escape_string($link, $description);

    $sql = "UPDATE animals SET name = '$name', type = '$type', image = '$image', description = '$description' WHERE id = $id";

    if (mysqli_query($link, $sql)) {
        header("Location: index.php");
        exit;
    } 
}

require_once 'DB_close.php'; 
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>編輯患者資訊</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <h1>編輯患者資訊</h1>
        <form method="POST" action="">
            <label>患者姓名：</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($animal['name']); ?>" required><br>
            <label>科別：</label>
            <input type="text" name="type" value="<?php echo htmlspecialchars($animal['type']); ?>" required><br>
            <label>大頭照 URL：</label>
            <input type="text" name="image" value="<?php echo htmlspecialchars($animal['image']); ?>"><br>
            <label>症狀敘述：</label>
            <textarea name="description" required><?php echo htmlspecialchars($animal['description']); ?></textarea><br>
            <button type="submit">更新</button>
        </form>
        <a href="index.php">返回首頁</a>
    </div>
</body>

</html>
