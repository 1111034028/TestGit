<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$user_id = $_SESSION['sno'];
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$total_records = get_total_count($link, 'contact_messages', "user_id = '$user_id'");
$total_pages = ceil($total_records / $limit);

$result = db_get_paginated($link, 'contact_messages', $page, $limit, 
    "FIELD(status, 'new', 'read', 'closed'), created_at DESC", 
    "user_id = '$user_id'");

if (isset($_GET['ajax_list'])) :
    session_write_close();
?>
        <div class="msg-list">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="msg-card" onclick="location.href='my_messages_view.php?id=<?php echo $row['id']; ?>'">
                        <div class="msg-header">
                            <span><?php echo $row['created_at']; ?></span>
                            <?php 
                                $status_map = ['new' => ['已送出', 'status-new'], 'read' => ['處理中', 'status-read'], 'closed' => ['已完成', 'status-closed']];
                                $s = $status_map[$row['status']] ?? ['未知', ''];
                            ?>
                            <span class="msg-status <?php echo $s[1]; ?>"><?php echo $s[0]; ?></span>
                        </div>
                        <div style="font-weight: bold; font-size: 1.1rem; margin-bottom: 5px;">
                            [<?php echo htmlspecialchars($row['category']); ?>]
                        </div>
                        <div style="color: #ddd; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($row['message']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #777;">
                    您目前沒有任何客服記錄。<br>
                    <a href="contact_us.php" class="btn-primary" style="margin-top: 20px;">聯絡客服</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <a href="my_messages.php?page=1" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&laquo;</a>
            <a href="#" onclick="loadList(<?php echo $page-1; ?>); return false;" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&lsaquo;</a>
            <span class="page-info"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
            <a href="#" onclick="loadList(<?php echo $page+1; ?>); return false;" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&rsaquo;</a>
            <a href="my_messages.php?page=<?php echo $total_pages; ?>" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&raquo;</a>
        </div>
        <?php endif; ?>
<?php 
    exit; 
endif; 

$page_title = "我的訊息 - 客服中心";
$extra_css = '<style>
        .msg-list { max-width: 800px; margin: 0 auto; }
        .msg-card {
            background: #282828; padding: 20px; border-radius: 10px; margin-bottom: 20px;
            transition: transform 0.2s; cursor: pointer; border: 1px solid #333;
        }
        .msg-card:hover { transform: translateY(-3px); border-color: var(--accent-color); }
        .msg-header { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: #aaa; }
        .msg-status { padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; }
        .status-new { background: #ff7675; color: white; }
        .status-read { background: #74b9ff; color: black; }
        .status-closed { background: #b2bec3; color: black; }
    </style>';
require_once("inc/header.php");
?>
    <div id="content-container" style="margin: 40px auto; max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="margin: 0;">我的客服記錄</h2>
            <a href="contact_us.php" class="btn-primary" style="font-size: 0.9rem; padding: 8px 20px;">
                + 發送新訊息
            </a>
        </div>
        
        <div id="list-container">
            <?php 
            $_GET['ajax_list'] = 1; 
            include "my_messages.php"; 
            ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('success') === 'contact') {
                if (window.notifyChatChange) window.notifyChatChange(); // Notify admin tabs
                showAlert('成功', '感將您的回報！客服人員將盡快聯絡您。', () => {
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            }
        });

        function loadList(page) {
            const listContainer = document.getElementById('list-container');
            fetch(`my_messages.php?ajax_list=1&page=${page}`)
                .then(response => response.text())
                .then(html => {
                    listContainer.innerHTML = html;
                })
                .catch(err => console.error(err));
        }
        
        // Auto refresh
        setInterval(() => {
            const activePage = document.querySelector('.page-info') ? document.querySelector('.page-info').innerText.split(' / ')[0] : 1;
            loadList(activePage);
        }, 1000);

        // Instant sync
        if (window.onChatSync) {
            window.onChatSync(() => {
                const info = document.querySelector('.page-info');
                loadList(info ? info.innerText.split(' / ')[0] : 1);
            });
        }
    </script>

    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
