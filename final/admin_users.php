<?php
session_start();
if (!isset($_SESSION["login_session"]) || $_SESSION["login_session"] !== true) {
    header("Location: login.php");
    exit;
}

require_once("../DB/DB_open.php");

// Check Admin Access
// Since we might not have re-logged in, we check DB or Session. 
// Ideally Session should be updated on login. 
// For safety, let's query DB for current user's role.
$username = $_SESSION["username"];
$sql_role = "SELECT role FROM students WHERE username = '$username'";
$res_role = mysqli_query($link, $sql_role);
$user_role_data = mysqli_fetch_assoc($res_role);
$current_role = $user_role_data['role'] ?? 'user';

if ($current_role !== 'admin') {
    die("Access Denied: You are not an admin.");
}

// Handle Promotion/Demotion
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($link, $_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'promote') {
        $sql_update = "UPDATE students SET role = 'admin' WHERE sno = '$target_id'";
    } elseif ($action === 'revoke') {
        // Prevent self-revoke? Optional.
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

// Fetch all users
$sql_users = "SELECT * FROM students ORDER BY sno ASC";
$result_users = mysqli_query($link, $sql_users);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8" />
    <title>使用者管理 - 管理員後台</title>
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
            <h2 style="margin: 0;">使用者權限管理</h2>
            <div style="display: flex; gap: 10px;">
                <a href="admin.php" class="btn-secondary">歌曲管理</a>
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
                            <a href="admin_users.php?action=promote&id=<?php echo $row['sno']; ?>" class="btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">設為管理員</a>
                        <?php } else { ?>
                            <a href="admin_users.php?action=revoke&id=<?php echo $row['sno']; ?>" class="btn-secondary" style="padding: 5px 10px; font-size: 0.9rem;" onclick="return confirm('確定取消其管理員權限？')">取消權限</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
