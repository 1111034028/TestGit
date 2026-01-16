<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

if (!is_admin($link, $_SESSION["username"])) {
    die("Access Denied: You are not an admin.");
}

// 處理 POST 更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $sno = mysqli_real_escape_string($link, $_POST['sno']);
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    $sql = "UPDATE students SET name='$name', password='$password' WHERE sno='$sno'";
    mysqli_query($link, $sql);
    header("Location: admin_users.php?page=" . ($_POST['page'] ?? 1));
    exit;
}

// 處理 GET 動作 (刪除/晉升/降級)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = $_GET['id'];
    $action = $_GET['action'];
    $page = $_GET['page'] ?? 1;
    
    if ($action === 'delete' && $target_id !== $_SESSION['sno']) {
        db_delete($link, 'students', "sno = '" . mysqli_real_escape_string($link, $target_id) . "'");
    } elseif ($action === 'promote') {
        db_update($link, 'students', ['role' => 'admin'], "sno = '" . mysqli_real_escape_string($link, $target_id) . "'");
    } elseif ($action === 'revoke' && $target_id !== $_SESSION['sno']) {
        db_update($link, 'students', ['role' => 'user'], "sno = '" . mysqli_real_escape_string($link, $target_id) . "'");
    }
    header("Location: admin_users.php?page=" . $page);
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
        .action-group { display:flex; gap:5px; flex-wrap:wrap; }
    </style>';
$page_title = "使用者管理 - 管理員後台";
require_once("inc/header.php");
?>
    <div id="content-container" style="margin: 30px auto;">
        <div class="admin-header">
            <h2 style="margin: 0;">使用者帳號管理</h2>
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
                    <th width="200">操作</th>
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
                        <div class="action-group">
                            <button class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem; border-radius: 50px; font-weight:600;" 
                                onclick="openEditUserModal('<?php echo $row['sno']; ?>', '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['password'], ENT_QUOTES); ?>')">
                                ✎ 編輯
                            </button>
                            
                            <?php if ($row['role'] === 'user'): ?>
                                <a href="admin_users.php?action=promote&id=<?php echo $row['sno']; ?>&page=<?php echo $page; ?>" class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem; border: 1px solid #6c5ce7; color: #6c5ce7; background: transparent; border-radius: 50px; font-weight:600;" onclick="confirmLink(event, this.href, '確認權限', '設為管理員？', false)">⬆ 升級</a>
                            <?php elseif ($row['role'] === 'admin' && $row['sno'] !== $_SESSION['sno']): ?>
                                <a href="admin_users.php?action=revoke&id=<?php echo $row['sno']; ?>&page=<?php echo $page; ?>" class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem; border-radius: 50px; font-weight:600;" onclick="confirmLink(event, this.href, '確認權限', '取消管理員？', true)">⬇ 降級</a>
                            <?php endif; ?>
                            
                            <?php if ($row['sno'] !== $_SESSION['sno']): ?>
                            <a href="admin_users.php?action=delete&id=<?php echo $row['sno']; ?>&page=<?php echo $page; ?>" class="btn-secondary" style="padding: 5px 12px; font-size: 0.85rem; border: 1px solid #ff4757; color: #ff4757; background: transparent; border-radius: 50px; font-weight:600;" onclick="confirmLink(event, this.href, '確認刪除', '確定要刪除此使用者帳號嗎？', true)">🗑 刪除</a>
                            <?php endif; ?>
                        </div>
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
    
    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="modal-overlay" style="display:none;" onclick="if(event.target===this) closeEditUserModal()">
       <div class="modal-box" style="max-width: 400px;">
           <h3 style="margin-top:0;">編輯使用者資料</h3>
           <form method="post" action="admin_users.php">
               <input type="hidden" name="action" value="update">
               <input type="hidden" name="page" value="<?php echo $page; ?>">
               <input type="hidden" name="sno" id="edit-sno-hidden">
               
               <div style="margin-bottom:15px;">
<label style="display:block; margin-bottom:5px; color:#aaa;">ID (無法修改)</label>
                   <input type="text" id="edit-sno-display" disabled style="width:100%; padding:10px; background:#333; border:1px solid #444; color:#fff; border-radius:4px; cursor:not-allowed;">
               </div>
               <div style="margin-bottom:15px;">
                   <label style="display:block; margin-bottom:5px; color:#fff;">姓名</label>
                   <input type="text" name="name" id="edit-name" required style="width:100%; padding:10px; background:#222; border:1px solid #444; color:#fff; border-radius:4px;">
               </div>
               <div style="margin-bottom:20px;">
                   <label style="display:block; margin-bottom:5px; color:#fff;">密碼</label>
                   <input type="text" name="password" id="edit-password" required style="width:100%; padding:10px; background:#222; border:1px solid #444; color:#fff; border-radius:4px;">
               </div>
               
               <div style="text-align:right; display:flex; gap:10px; justify-content:flex-end;">
                   <button type="button" class="btn-secondary" onclick="closeEditUserModal()">取消</button>
                   <button type="submit" class="btn-primary">儲存變更</button>
               </div>
           </form>
       </div>
    </div>

    <script>
        function togglePassword(cell) {
            const current = cell.innerText.trim();
            const real = cell.getAttribute('data-password');
            cell.innerText = (current === '******') ? real : '******';
        }
        
        function openEditUserModal(sno, name, password) {
            document.getElementById('edit-sno-hidden').value = sno;
            document.getElementById('edit-sno-display').value = sno;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-password').value = password;
            document.getElementById('edit-user-modal').style.display = 'flex';
        }
        
        function closeEditUserModal() {
            document.getElementById('edit-user-modal').style.display = 'none';
        }
    </script>
    
    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
