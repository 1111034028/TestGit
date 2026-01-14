<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if (!is_admin($link, $_SESSION["username"])) {
    die("Access Denied: You are not an admin.");
}

// 處理晉升/降級
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'promote') {
        db_update($link, 'students', ['role' => 'admin'], "sno = '" . mysqli_real_escape_string($link, $target_id) . "'");
    } elseif ($action === 'revoke' && $target_id !== $_SESSION['sno']) {
        db_update($link, 'students', ['role' => 'user'], "sno = '" . mysqli_real_escape_string($link, $target_id) . "'");
    }
    header("Location: admin_users.php?page=" . ($_GET['page'] ?? 1));
    exit;
}

// 分頁與取得資料
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_records = get_total_count($link, 'students');
$total_pages = ceil($total_records / $limit);

$result_users = db_get_paginated($link, 'students', $page, $limit, "sno ASC");

$extra_css = '
    <style>
        .admin-header {
            background: #282828; padding: 20px; border-radius: 8px; margin-bottom: 20px;
            border-left: 5px solid var(--accent-color); display: flex;
            justify-content: space-between; align-items: center;
        }
    </style>';
$page_title = "使用者管理 - 管理員後台";
require_once("inc/header.php");
?>
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
                    <th>密碼</th>
                    <th>目前角色</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_users)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sno']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td onclick="togglePassword(this)" data-password="<?php echo htmlspecialchars($row['password']); ?>" style="font-family: monospace; color: #ff7675; cursor: pointer;" title="點擊顯示/隱藏">
                        ******
                    </td>
                    <td>
                        <?php echo ($row['role'] === 'admin') ? '<span style="color: #d63031; font-weight: bold;">管理員</span>' : '一般使用者'; ?>
                    </td>
                    <td>
                        <?php if ($row['role'] === 'user'): ?>
                            <a href="admin_users.php?action=promote&id=<?php echo $row['sno']; ?>&page=<?php echo $page; ?>" class="btn-primary" style="padding: 5px 10px; font-size: 0.9rem;" onclick="confirmLink(event, this.href, '確認權限變更', '確定將此使用者設為管理員？', false)">設為管理員</a>
                        <?php else: ?>
                            <a href="admin_users.php?action=revoke&id=<?php echo $row['sno']; ?>&page=<?php echo $page; ?>" class="btn-secondary" style="padding: 5px 10px; font-size: 0.9rem;" onclick="confirmLink(event, this.href, '確認撤銷', '確定取消其管理員權限？', true)">取消權限</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <a href="admin_users.php?page=1" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&laquo;</a>
            <a href="admin_users.php?page=<?php echo $page-1; ?>" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">&lsaquo;</a>
            <span class="page-info"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
            <a href="admin_users.php?page=<?php echo $page+1; ?>" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&rsaquo;</a>
            <a href="admin_users.php?page=<?php echo $total_pages; ?>" class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">&raquo;</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(cell) {
            const current = cell.innerText;
            const real = cell.getAttribute('data-password');
            cell.innerText = (current === '******') ? real : '******';
        }
    </script>
    
    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
