<?php
include 'DB_open.php'; // 開啟資料庫連線
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM animals WHERE name LIKE '%$search%' OR type LIKE '%$search%'";
$result = mysqli_query($link, $sql); // 使用 $link 作為資料庫連線
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>動物看診紀錄</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php
    session_start();
    if ($_SESSION["login_session"] != true)
        header("Location: login.php");
    ?>
    <div class="welcome-message">
        歡迎 <?php echo htmlspecialchars($_SESSION["username"]); ?> 進入網站!
    </div>

    <div class="container">
        <div class="logo-container">
            <img src="img/final_logo.png" alt="動物介紹網站">
        </div>

        <form method="GET" action="">
            <input type="text" name="search" placeholder="搜尋患者" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">搜尋</button>
        </form>
        <a href="add.php">新增患者</a>
        <table>
            <tr>
                <th>患者姓名</th>
                <th>科別</th>
                <th>大頭照</th>
                <th>症狀敘述</th>
                <th>操作</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($row['image']); ?>" alt="圖片" width="100"></td>
                    <td class="description"><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <a href="prescribe.php?id=<?php echo $row['id']; ?>" class="prescribe">配藥</a>
                        <a href="edit.php?id=<?php echo $row['id']; ?>">編輯</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="delete"
                            onclick="return confirm('確認刪除？');">刪除</a>
                        
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <?php if ($search): ?>
            <div class="back-home">
                <a href="index.php" class="button">回首頁</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'DB_close.php'; // 關閉資料庫連線 ?>
</body>

</html>
