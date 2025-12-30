<?php
require_once 'DB_open.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $animal_id = mysqli_real_escape_string($link, $_POST['animal_id']);
    $medicine_name = mysqli_real_escape_string($link, $_POST['medicine_name']);
    $dosage = mysqli_real_escape_string($link, $_POST['dosage']);
    $useage = mysqli_real_escape_string($link, $_POST['useage']);
    $schedule = mysqli_real_escape_string($link, $_POST['schedule']);
    $side_effects = mysqli_real_escape_string($link, $_POST['side_effects']);

    $sql = "INSERT INTO prescriptions (animal_id, medicine_name, dosage, useage, schedule, side_effects) 
            VALUES ('$animal_id', '$medicine_name', '$dosage', '$useage', '$schedule', '$side_effects')";

    if (mysqli_query($link, $sql)) {
        header("Location: prescription_summary.php?medicine_name=" . urlencode($medicine_name) . 
               "&dosage=" . urlencode($dosage) . 
               "&useage=" . urlencode($useage) . 
               "&schedule=" . urlencode($schedule) . 
               "&side_effects=" . urlencode($side_effects));
        exit;
    }
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$animal_id = mysqli_real_escape_string($link, $_GET['id']);

$sql = "SELECT name FROM animals WHERE id = $animal_id";
$result = mysqli_query($link, $sql);

$animal = mysqli_fetch_assoc($result);

require_once 'DB_close.php'; 
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>配藥</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <h1>為患者 <?php echo htmlspecialchars($animal['name']); ?> 配藥</h1>
        <form method="POST" action="">
            <input type="hidden" name="animal_id" value="<?php echo htmlspecialchars($animal_id); ?>">
            <label>藥名稱：</label><input type="text" name="medicine_name" required><br>
            <label>劑量：</label><input type="text" name="dosage" required><br>
            <label>服用方式：</label><input type="text" name="useage" required><br>
            <label>服用時間：</label><input type="text" name="schedule" required><br>
            <label>副作用：</label><textarea name="side_effects"></textarea><br>
            <button type="submit">提交</button>
        </form>
        <a href="index.php" class="return-home">返回首頁</a>
    </div>
</body>

</html>
