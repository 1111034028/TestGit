<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

// 檢查管理員權限
$username = $_SESSION["username"];
$sql_role = "SELECT role FROM students WHERE username = '$username'";
$user_role_data = mysqli_fetch_assoc(mysqli_query($link, $sql_role));
$current_role = $user_role_data['role'] ?? 'user';

if ($current_role !== 'admin') {
    die("您無權存取此頁面");
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
        header("Location: admin.php"); // 刷新頁面
    }
}

// 分頁邏輯
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 計算總歌曲數
$sql_count = "SELECT COUNT(*) as total FROM songs";
$res_count = mysqli_query($link, $sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

$sql_all = "SELECT * FROM songs ORDER BY upload_date DESC LIMIT $offset, $limit";
$result_all = mysqli_query($link, $sql_all);
?>
<?php 
$page_title = "後台管理 - 音樂串流平台";
require_once("inc/header.php"); 
?>
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
            <h2 style="margin: 0;">管理員控制台</h2>
            <div style="display: flex; gap: 10px;">
                <a href="admin.php" class="btn-primary">歌曲管理</a>
                <a href="admin_contact.php" class="btn-secondary">客服訊息</a>
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
                            <a href="admin.php?delete_id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn-secondary" style="border-color: #d63031; color: #d63031;" onclick="return confirm('警告：管理員刪除將無法復原，確定執行？')">強制刪除</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="margin-top: 20px; text-align: center;">
            <?php if ($page > 1): ?>
                <a href="admin.php?page=1" class="btn-secondary" style="padding: 5px 10px;">&laquo; 第一頁</a>
                <a href="admin.php?page=<?php echo $page - 1; ?>" class="btn-secondary" style="padding: 5px 10px;">&lt; 上一頁</a>
            <?php endif; ?>

            <span style="margin: 0 10px; color: #aaa;">第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁</span>

            <?php if ($page < $total_pages): ?>
                <a href="admin.php?page=<?php echo $page + 1; ?>" class="btn-secondary" style="padding: 5px 10px;">下一頁 &gt;</a>
                <a href="admin.php?page=<?php echo $total_pages; ?>" class="btn-secondary" style="padding: 5px 10px;">最末頁 &raquo;</a>
            <?php endif; ?>
        </div>
        </table>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
