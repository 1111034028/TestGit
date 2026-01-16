<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if (!is_admin($link, $_SESSION["username"])) {
    die("Unauthorized access.");
}

// 處理操作 (結單 / 刪除)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $page = $_GET['page'] ?? 1;

    if ($action === 'close') {
        db_update($link, 'contact_messages', ['status' => 'closed'], "id = $id");
    } elseif ($action === 'delete') {
        mysqli_query($link, "DELETE FROM contact_replies WHERE message_id = $id");
        mysqli_query($link, "DELETE FROM contact_messages WHERE id = $id");
        
        // 若清空則重置 ID
        $cnt = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as c FROM contact_messages"))['c'];
        if ($cnt == 0) {
            mysqli_query($link, "ALTER TABLE contact_messages AUTO_INCREMENT = 1");
            mysqli_query($link, "ALTER TABLE contact_replies AUTO_INCREMENT = 1");
        }
    }
    header("Location: admin_contact.php?page=$page");
    exit;
}

// 分頁設定
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$total_records = get_total_count($link, 'contact_messages');
$total_pages = ceil($total_records / $limit);

$sql_msg = "SELECT * FROM contact_messages 
            ORDER BY FIELD(status, 'new', 'read', 'closed'), created_at DESC 
            LIMIT $offset, $limit";
$result_msg = mysqli_query($link, $sql_msg);

function render_contact_table($result_msg, $page, $total_pages) {
?>
    <?php if ($result_msg && mysqli_num_rows($result_msg) > 0) : ?>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th width="50">ID</th>
                    <th width="90">狀態</th>
                    <th width="180">姓名/Email</th>
                    <th width="100">類別</th>
                    <th>內容摘要</th>
                    <th width="140">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_msg)) : 
                    $status_map = ['new' => ['未讀', '#ff7675'], 'read' => ['處理中', '#74b9ff'], 'closed' => ['已結單', '#b2bec3']];
                    $s = $status_map[$row['status']] ?? ['未知', '#fff'];
                ?>
                    <tr onclick="location.href='admin_chat.php?id=<?php echo $row['id']; ?>'" style="cursor: pointer;">
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <span class="status-badge" style="background: <?php echo $s[1]; ?>; color: #000;"><?php echo $s[0]; ?></span>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($row['name']); ?></div>
                            <div style="font-size: 0.8em; opacity: 0.6;"><?php echo htmlspecialchars($row['email']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            <div style="max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <span style="color: #eee;"><?php echo htmlspecialchars($row['message']); ?></span>
                            </div>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div style="display:flex; gap:8px;">
                                <?php if ($row['status'] !== 'closed'): ?>
                                    <a href="admin_contact.php?action=close&id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" 
                                       class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border: 1px solid #00b894; color: #00b894; background: transparent; border-radius: 50px; white-space: nowrap; font-weight: 600; text-decoration: none; transition: all 0.2s;"
                                       onmouseover="this.style.background='rgba(0, 184, 148, 0.1)'" onmouseout="this.style.background='transparent'"
                                       onclick="confirmLink(event, this.href, '確認結單', '確定將此案件標記為已結單？', false)">✔ 結單</a>
                                <?php endif; ?>
                                <a href="admin_contact.php?action=delete&id=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" 
                                   class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border: 1px solid #ff4757; color: #ff4757; background: transparent; border-radius: 50px; white-space: nowrap; font-weight: 600; text-decoration: none; transition: all 0.2s;"
                                   onmouseover="this.style.background='rgba(255, 71, 87, 0.1)'" onmouseout="this.style.background='transparent'"
                                   onclick="confirmLink(event, this.href, '確認刪除', '確定徹底刪除此記錄？此操作無法復原。', true)">🗑 刪除</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1) : ?>
            <div class="pagination-container">
                <a href="#" onclick="loadList(1); return false;" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&laquo;</a>
                <a href="#" onclick="loadList(<?php echo $page-1; ?>); return false;" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&lsaquo;</a>
                <span class="page-info"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
                <a href="#" onclick="loadList(<?php echo $page+1; ?>); return false;" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&rsaquo;</a>
                <a href="#" onclick="loadList(<?php echo $total_pages; ?>); return false;" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&raquo;</a>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div style="padding: 40px; text-align: center; color: #888;">目前沒有新的客服訊息。</div>
    <?php endif; ?>
<?php 
}

// AJAX: 僅返回表格部分
if (isset($_GET['ajax_list'])) { 
    session_write_close();
    render_contact_table($result_msg, $page, $total_pages);
    exit; 
} 

$page_title = "客服管理 - 音樂串流平台";
$extra_css = '
    <link rel="stylesheet" href="css/chat.css">
    <style>
        .admin-header {
            background: #282828; padding: 20px; border-radius: 8px; margin-bottom: 20px;
            border-left: 5px solid var(--accent-color); display: flex;
            justify-content: space-between; align-items: center;
        }
    </style>';
require_once("inc/header.php");
?>
    <div id="content-container" style="margin: 30px auto;">
        <div class="admin-header">
            <h2 style="margin: 0;">客服訊息管理</h2>
            <div style="display: flex; gap: 10px;">
                <a href="admin.php" class="btn-secondary">歌曲管理</a>
                <a href="admin_contact.php" class="btn-primary">客服訊息</a>
                <a href="admin_users.php" class="btn-secondary">使用者管理</a>
            </div>
        </div>
        
        <div id="list-container">
            <!-- Content loaded via AJAX or include -->
            <?php render_contact_table($result_msg, $page, $total_pages); ?>
        </div>
    </div>
    
    <script>
        function loadList(page) {
            const container = document.getElementById('list-container');
            fetch(`admin_contact.php?ajax_list=1&page=${page}`)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                });
        }

        // Auto refresh list every 3 seconds (slowed down to avoid button flicker while hovering)
        setInterval(() => {
            const pageInfo = document.querySelector('.page-info');
            const activePage = pageInfo ? pageInfo.innerText.split(' / ')[0] : 1;
            // Only refresh if user is not hovering over the table? 
            // For now, keep it simple. If refreshing is annoying, can increase interval or check hover.
            // Actually, admin might want real-time. 1s is fine if DOM diffing, but we replace HTML.
            // Let's keep 1s but if interaction issues arise, user will tell.
            loadList(activePage);
        }, 2000);

        // Instant sync via global helper
        if (window.onChatSync) {
            window.onChatSync(() => {
                const pageInfo = document.querySelector('.page-info');
                loadList(pageInfo ? pageInfo.innerText.split(' / ')[0] : 1);
            });
        }
    </script>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
