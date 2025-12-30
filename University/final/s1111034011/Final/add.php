<?php
include 'DB_open.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $image = $_POST['image'];
    $description = $_POST['description'];

    $name = mysqli_real_escape_string($link, $name);
    $type = mysqli_real_escape_string($link, $type);
    $image = mysqli_real_escape_string($link, $image);
    $description = mysqli_real_escape_string($link, $description);

    $sql = "INSERT INTO animals (name, type, image, description) VALUES ('$name', '$type', '$image', '$description')";
    if (mysqli_query($link, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "新增失敗：" . mysqli_error($link);
    }
}

include 'DB_close.php'; 
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>新增患者</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <h1>新增患者</h1>
        <form method="POST" action="">
            <label>患者姓名：</label><input type="text" name="name" required><br>
            <label>科別：</label><input type="text" name="type" required><br>
            <label>大頭照 URL：</label><input type="text" name="image"><br>
            <label>症狀敘述：</label><textarea name="description" required></textarea><br>
            <button type="submit">新增</button>
        </form>
        <a href="index.php" class="return-home">返回首頁</a>
    </div>
</body>

</html>
