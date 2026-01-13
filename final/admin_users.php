<?php
require_once("inc/auth_guard.php");

require_once("../DB/DB_open.php");

// 檢查管理員權限
// 由於可能未重新登入，我們檢查資料庫或 Session。
// 理想情況下 Session 應在登入時更新。
// 為安全起見，查詢資料庫確認當前使用者角色。
$username = $_SESSION["username"];
$sql_role = "SELECT role FROM students WHERE username = '$username'";
$res_role = mysqli_query($link, $sql_role);
$user_role_data = mysqli_fetch_assoc($res_role);
$current_role = $user_role_data['role'] ?? 'user';

if ($current_role !== 'admin') {
    die("Access Denied: You are not an admin.");
}

// 處理晉升/降級
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($link, $_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'promote') {
        $sql_update = "UPDATE students SET role = 'admin' WHERE sno = '$target_id'";
    } elseif ($action === 'revoke') {
        // 防止自我撤銷權限？可選。
        if ($target_id === $_SESSION['sno']) {
            echo "<script>alert('不能取消自己的管理員權限');</script>";
        } else {
            $sql_update = "UPDATE students SET role = 'user' WHERE sno = '$target_id'";
        }
    }
    
    if (isset($sql_update)) {
        mysqli_query($link, $sql_update);
    }
}

// 取得所有使用者
// 分頁邏輯
$limit = 10; // 每頁顯示筆數
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 計算總使用者數
$sql_count = "SELECT COUNT(*) as total FROM students";
$res_count = mysqli_query($link, $sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

// 使用 LIMIT 取得使用者
$sql_users = "SELECT * FROM students ORDER BY sno ASC LIMIT $offset, $limit";
$result_users = mysqli_query($link, $sql_users);
?>
<?php
$page_title = "使用者管理 - 管理員後台";
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
    </style>';
require_once("inc/header.php");
?>
    <!-- Nav removed for App Shell integration -->

    <div id="content-container" style="margin: 30px auto;">
        <div class="admin-header">
            <h2 style="margin: 0;">使用者權限管理</h2>
            <div style="display: flex; gap: 10px;">
                <a href="admin.php" class="btn-secondary">歌曲管理</a>
                <a href="admin_contact.php" class="btn-secondary">客服訊息</a>
                <a href="admin_users.php" class="btn-primary">使用者管理</a>
            </div>
        </div>
        
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>學號 (ID)</th>
                    <th>姓名</th>
                    <th>帳號</th>
                    <th>目前角色</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_users)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sno']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <?php 
                        if ($row['role'] === 'admin') echo "<span style='color: #d63031; font-weight: bold;'>管理員</span>";
                        else echo "一般使用者";
                        ?>
                    </td>
                    <td>
                        <?php if ($row['role'] === 'user') { ?>
                            <a href="admin_users.php?action=promote&id=<?php echo $row['sno']; ?>&page=<?php echo $page; ?>" class="btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">設為管理員</a>
                        <?php } else { ?>
                            <a href="admin_users.php?action=revoke&id=<?php echo $row['sno']; ?>&page=<?php echo $page; ?>" class="btn-secondary" style="padding: 5px 10px; font-size: 0.9rem;" onclick="return confirm('確定取消其管理員權限？')">取消權限</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="margin-top: 20px; text-align: center;">
            <?php if ($page > 1): ?>
                <a href="admin_users.php?page=1" class="btn-secondary" style="padding: 5px 10px;">&laquo; 第一頁</a>
                <a href="admin_users.php?page=<?php echo $page - 1; ?>" class="btn-secondary" style="padding: 5px 10px;">&lt; 上一頁</a>
            <?php endif; ?>

            <span style="margin: 0 10px; color: #aaa;">第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁</span>

            <?php if ($page < $total_pages): ?>
                <a href="admin_users.php?page=<?php echo $page + 1; ?>" class="btn-secondary" style="padding: 5px 10px;">下一頁 &gt;</a>
                <a href="admin_users.php?page=<?php echo $total_pages; ?>" class="btn-secondary" style="padding: 5px 10px;">最末頁 &raquo;</a>
            <?php endif; ?>
        </div>
        </table>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
