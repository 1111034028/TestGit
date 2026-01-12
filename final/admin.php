<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");

// 權限檢查 - 檢查 role 是否為 admin
$username = $_SESSION["username"];
$sql_user = "SELECT sno, role FROM students WHERE username = '$username'";
$result_user = mysqli_query($link, $sql_user);
$row_user = mysqli_fetch_assoc($result_user);
$user_role = $row_user['role'] ?? 'user';

if ($user_role !== 'admin') {
    die("Access Denied: 只有管理員可以存取此頁面。");
}

// 刪除邏輯
if (isset($_GET['delete_id'])) {
    $song_id = $_GET['delete_id'];
    
    // 獲取檔案路徑以便刪除檔案
    $sql_file = "SELECT * FROM songs WHERE id = $song_id";
    $result_file = mysqli_query($link, $sql_file);
    if ($row = mysqli_fetch_assoc($result_file)) {
        if (file_exists("music/" . $row['file_path'])) unlink("music/" . $row['file_path']);
        // if (file_exists("covers/" . $row['cover_path'])) unlink("covers/" . $row['cover_path']); // DB storage now
        
        $sql_del = "DELETE FROM songs WHERE id = $song_id";
        mysqli_query($link, $sql_del);
        header("Location: admin.php"); // Refresh
    }
}

$sql_all = "SELECT * FROM songs ORDER BY upload_date DESC";
$result_all = mysqli_query($link, $sql_all);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>管理員後台 - 音樂串流平台</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/music.css">
    <style>
        .admin-header {
            background: #282828;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid var(--accent-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Nav removed for App Shell integration -->

    <div id="content-container" style="margin: 30px auto;">
        <div class="admin-header">
            <h2 style="margin: 0;">管理員控制台 (Admin Panel)</h2>
            <div style="display: flex; gap: 10px;">
                <a href="admin.php" class="btn-primary">歌曲管理</a>
                <a href="admin_users.php" class="btn-secondary">使用者管理</a>
            </div>
        </div>

        <h3>所有使用者上傳的歌曲</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>標題</th>
                    <th>歌手</th>
                    <th>上傳者ID</th>
                    <th>播放數</th>
                    <th>功能</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result_all)) {
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['artist']); ?></td>
                        <td><?php echo $row['uploader_id']; ?></td>
                        <td><?php echo $row['play_count']; ?></td>
                        <td>
                            <a href="song_edit.php?id=<?php echo $row['id']; ?>" class="btn-secondary" style="margin-right: 5px;">編輯</a>
                            <a href="admin.php?delete_id=<?php echo $row['id']; ?>" class="btn-secondary" style="border-color: #d63031; color: #d63031;" onclick="return confirm('警告：管理員刪除將無法復原，確定執行？')">強制刪除</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
