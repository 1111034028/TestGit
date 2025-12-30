<?php
// 資料庫連線設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "insect_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 接收表單資料
$insect_name = $_POST['insect_name'];
$person_id   = $_POST['person_id'];
$collect_date = $_POST['collect_date'];
$longitude   = $_POST['longitude'];
$latitude    = $_POST['latitude'];
$description = $_POST['description'];

// 判斷是否已有相同昆蟲學名
$sql_check = "SELECT * FROM insects WHERE insect_name = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $insect_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // 更新舊資料
    $sql_update = "UPDATE insects 
                   SET person_id = $person_id, collect_date = $collect_date, longitude = $longitude, latitude = $latitude, description = $description 
                   WHERE insect_name = $insect_name";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("isddss", $person_id, $collect_date, $longitude, $latitude, $description, $insect_name);
    if ($stmt_update->execute()) {
        echo "資料已更新成功！";
    } else {
        echo "更新失敗: " . $conn->error;
    }
} else {
    // 新增新資料
    $sql_insert = "INSERT INTO insects (insect_name, person_id, collect_date, longitude, latitude, description) 
                   VALUES ($insect_name, $person_id, $collect_date, $longitude, $latitude, $description)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sisdds", $insect_name, $person_id, $collect_date, $longitude, $latitude, $description);
    if ($stmt_insert->execute()) {
        echo "新資料已新增成功！";
    } else {
        echo "新增失敗: " . $conn->error;
    }
}

$conn->close();
?>
