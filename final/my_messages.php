<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

$user_id = mysqli_real_escape_string($link, $_SESSION['sno']);
// 分頁邏輯
$limit = 5; // 使用者卡片的限制較小
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 計數
$sql_count = "SELECT COUNT(*) as total FROM contact_messages WHERE user_id = '$user_id'";
$res_count = mysqli_query($link, $sql_count);
$row = mysqli_fetch_assoc($res_count);
$total_records = $row['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM contact_messages WHERE user_id = '$user_id' 
        ORDER BY FIELD(status, 'new', 'read', 'closed'), created_at DESC 
        LIMIT $offset, $limit";
$result = mysqli_query($link, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($link));
}
?>
<?php if (!isset($_GET['ajax_list'])): ?>
<?php
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
        .status-read { background: #74b9ff; color: black; } /* 藍色表示處理中 */
        .status-closed { background: #b2bec3; color: black; } /* 灰色表示已完成 */
    </style>';
require_once("inc/header.php");
?>
    <!-- 為 App Shell 移除導覽列 -->

    <div id="content-container" style="margin: 40px auto;">
        <h2 style="text-align: center; margin-bottom: 30px;">我的客服記錄</h2>
        
        <div id="list-container">
<?php endif; ?>
        
        <div class="msg-list">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="msg-card" onclick="location.href='my_messages_view.php?id=<?php echo $row['id']; ?>'">
                        <div class="msg-header">
                            <span><?php echo $row['created_at']; ?></span>
                            <?php 
                                $s_class = ''; $s_text = '';
                                if($row['status'] == 'new') { $s_class='status-new'; $s_text='已送出'; }
                                elseif($row['status'] == 'read') { $s_class='status-read'; $s_text='處理中'; }
                                else { $s_class='status-closed'; $s_text='已完成'; }
                            ?>
                            <span class="msg-status <?php echo $s_class; ?>">
                                <?php echo $s_text; ?>
                            </span>
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

        <!-- 分頁 -->
        <div style="margin-top: 20px; text-align: center;">
            <?php if ($total_pages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="my_messages.php?page=1" class="btn-secondary" style="padding: 5px 10px;">&laquo; 第一頁</a>
                    <a href="my_messages.php?page=<?php echo $page - 1; ?>" class="btn-secondary" style="padding: 5px 10px;">&lt; 上一頁</a>
                <?php endif; ?>

                <span style="margin: 0 10px; color: #aaa;">第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁</span>

                <?php if ($page < $total_pages): ?>
                    <a href="my_messages.php?page=<?php echo $page + 1; ?>" class="btn-secondary" style="padding: 5px 10px;">下一頁 &gt;</a>
                    <a href="my_messages.php?page=<?php echo $total_pages; ?>" class="btn-secondary" style="padding: 5px 10px;">最末頁 &raquo;</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
<?php if (!isset($_GET['ajax_list'])): ?>
        </div> <!-- End #list-container -->
    </div>

    <script>
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && link.href.includes('page=') && link.href.includes('my_messages.php')) {
                e.preventDefault();
                loadList(link.href);
            }
        });

        function refreshList() {
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
                })
                .catch(err => console.error(err));
        }
    </script>

    <?php include "foot.html"; ?>
</body>
</html>
<?php endif; ?>
<?php require_once("../DB/DB_close.php"); ?>
