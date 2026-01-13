<?php require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");

// 權限檢查
$username = $_SESSION["username"];
$sql_user = "SELECT sno, role FROM students WHERE username = '$username'";
$result_user = mysqli_query($link, $sql_user);
$row_user = mysqli_fetch_assoc($result_user);
$user_role = $row_user['role'] ?? 'user';

if ($user_role !== 'admin') {
    die("Access Denied:只有管理員可以存取此頁面。");
}

// 分頁邏輯
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 計算總訊息數
$sql_count = "SELECT COUNT(*) as total FROM contact_messages";
$res_count = mysqli_query($link, $sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

// 取得訊息並自訂排序
// 排序：未讀 (1) > 處理中 (2) > 已完成 (3)，然後按時間倒序
$sql_msg = "SELECT * FROM contact_messages 
            ORDER BY FIELD(status, 'new', 'read', 'closed'), created_at DESC 
            LIMIT $offset, $limit";
$result_msg = mysqli_query($link, $sql_msg);
?>
<?php if (!isset($_GET['ajax_list'])): ?>
<?php
$page_title = "客服訊息管理 - 音樂串流平台";
$extra_css = '<style>
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
        .msg-table td {
            vertical-align: top;
            padding: 12px;
        }
        .msg-content {
            white-space: pre-wrap;
            max-width: 400px;
        }
    </style>';
require_once("inc/header.php");
?>
    <div id="content-container" style="margin: 30px auto;">
        <div class="admin-header">
            <h2 style="margin: 0;">客服訊息中心</h2>
            <div style="display: flex; gap: 10px;">
                <a href="admin.php" class="btn-secondary">歌曲管理</a>
                <a href="admin_contact.php" class="btn-primary">客服訊息</a>
                <a href="admin_users.php" class="btn-secondary">使用者管理</a>
            </div>
        </div>

        <h3>使用者回報列表</h3>
        
        <div id="list-container">
<?php endif; ?>

        <?php if($result_msg && mysqli_num_rows($result_msg) > 0): ?>
        <table class="dashboard-table msg-table">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="8%" style="white-space: nowrap;">狀態</th>
                    <th width="15%" style="white-space: nowrap;">時間</th>
                    <th width="15%">姓名/Email</th>
                    <th width="10%">類別</th>
                    <th>內容</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_msg)): ?>
                    <tr onclick="location.href='admin_chat.php?id=<?php echo $row['id']; ?>'" style="cursor: pointer;">
                        <td><?php echo $row['id']; ?></td>
                        <td style="white-space: nowrap;">
                            <?php if ($row['status'] == 'new'): ?>
                                <span style="color: #ff7675; font-weight: bold;">● 未讀</span>
                            <?php elseif ($row['status'] == 'read'): ?>
                                <span style="color: #74b9ff; font-weight: bold;">● 處理中</span>
                            <?php else: ?>
                                <span style="color: #b2bec3; font-weight: bold;">● 已完成</span>
                            <?php endif; ?>
                        </td>

                        <td style="white-space: nowrap;">
                            <?php echo date('Y-m-d', strtotime($row['created_at'])); ?><br>
                            <span style="font-size: 0.9em; color: #888;"><?php echo date('H:i', strtotime($row['created_at'])); ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                            <span style="font-size: 0.9em; opacity: 0.7;"><?php echo htmlspecialchars($row['email']); ?></span>
                        </td>
                        <td><span class="badge" style="background: #444; padding: 2px 6px; border-radius: 4px; font-size: 0.85em;"><?php echo htmlspecialchars($row['category']); ?></span></td>
                        <td><div class="msg-content"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- 分頁 -->
        <div style="margin-top: 20px; text-align: center;">
            <?php if ($total_pages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="admin_contact.php?page=1" class="btn-secondary" style="padding: 5px 10px;">&laquo; 第一頁</a>
                    <a href="admin_contact.php?page=<?php echo $page - 1; ?>" class="btn-secondary" style="padding: 5px 10px;">&lt; 上一頁</a>
                <?php endif; ?>

                <span style="margin: 0 10px; color: #aaa;">第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁</span>

                <?php if ($page < $total_pages): ?>
                    <a href="admin_contact.php?page=<?php echo $page + 1; ?>" class="btn-secondary" style="padding: 5px 10px;">下一頁 &gt;</a>
                    <a href="admin_contact.php?page=<?php echo $total_pages; ?>" class="btn-secondary" style="padding: 5px 10px;">最末頁 &raquo;</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #888; background: #222; border-radius: 8px;">
                目前沒有新的客服訊息。
            </div>
        <?php endif; ?>
<?php if (!isset($_GET['ajax_list'])): ?>
        </div>
    </div>
    
    <script>
        // 分頁 AJAX
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && link.href.includes('page=') && !link.href.includes('delete_id')) {
                // 確保這是此頁面的分頁連結
                const url = new URL(link.href);
                if (url.pathname.endsWith('admin_contact.php')) {
                    e.preventDefault();
                    loadList(link.href);
                }
            }
        });

        function refreshList() {
             // 重新載入當前 URL 內容容器
             loadList(window.location.href);
        }

        // 每 10 秒自動刷新
        setInterval(refreshList, 10000);

        function loadList(url) {
            const listContainer = document.getElementById('list-container');
            const fetchUrl = url + (url.includes('?') ? '&' : '?') + 'ajax_list=1';
            
            fetch(fetchUrl)
                .then(response => response.text())
                .then(html => {
                    listContainer.innerHTML = html;
                    // 如果需要，自動捲動到列表頂部？對於部分刷新可能不需要。
                })
                .catch(err => console.error(err));
        }
    </script>
    
    <?php include "foot.html"; ?>
</body>
</html>
<?php endif; ?>
<?php require_once("../DB/DB_close.php"); ?>
