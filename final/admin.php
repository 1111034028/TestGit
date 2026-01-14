<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if (!is_admin($link, $_SESSION["username"])) {
    die("您無權存取此頁面");
}

// 刪除邏輯
if (isset($_GET['delete_id'])) {
    $song_id = intval($_GET['delete_id']);
    // 先取得檔案路徑
    $res = mysqli_query($link, "SELECT file_path FROM songs WHERE id = $song_id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (file_exists("music/" . $row['file_path'])) unlink("music/" . $row['file_path']);
        db_delete($link, 'songs', "id = $song_id");
        header("Location: admin.php"); 
        exit;
    }
}

// 分頁與資料取得
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_records = get_total_count($link, 'songs');
$total_pages = ceil($total_records / $limit);

$result_all = db_get_paginated($link, 'songs', $page, $limit, "upload_date DESC");

$extra_css = '
    <style>
        .admin-header {
            background: #282828; padding: 20px; border-radius: 8px; margin-bottom: 20px;
            border-left: 5px solid var(--accent-color); display: flex;
            justify-content: space-between; align-items: center;
        }
    </style>';
$page_title = "後台管理 - 音樂串流平台";
require_once("inc/header.php"); 
?>
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
                <?php while ($row = mysqli_fetch_assoc($result_all)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['artist']); ?></td>
                        <td><?php echo $row['uploader_id']; ?></td>
                        <td><?php echo $row['play_count']; ?></td>
                        <td>
                            <a href="song_edit.php?id=<?php echo $row['id']; ?>&from=admin" class="btn-secondary" style="margin-right: 5px; font-size: 0.9rem; padding: 6px 15px;">編輯</a>
                            <a href="admin.php?delete_id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn-secondary" style="border-color: #d63031; color: #d63031; font-size: 0.9rem; padding: 6px 15px;" onclick="confirmLink(event, this.href, '確認刪除', '警告：管理員刪除將無法復原，確定執行？', true)">強制刪除</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <a href="admin.php?page=1" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&laquo;</a>
            <a href="admin.php?page=<?php echo $page-1; ?>" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&lsaquo;</a>
            <span class="page-info"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
            <a href="admin.php?page=<?php echo $page+1; ?>" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&rsaquo;</a>
            <a href="admin.php?page=<?php echo $total_pages; ?>" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&raquo;</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="js/manage_notifications.js"></script>
    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
