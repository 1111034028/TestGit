<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$username = $_SESSION["username"];
$sql_role = "SELECT role FROM students WHERE username = '$username'";
$user_role_data = mysqli_fetch_assoc(mysqli_query($link, $sql_role));
if ($user_role_data['role'] !== 'admin') {
    die("Unauthorized access.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id == 0) {
    header("Location: admin_contact.php");
    exit;
}

// 處理 POST 請求 (回覆 / 結單 / 刪除)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        mysqli_query($link, "DELETE FROM contact_replies WHERE message_id = $id");
        mysqli_query($link, "DELETE FROM contact_messages WHERE id = $id");
        
        // 新功能：若是清單已完全清空，自動重設 ID 計數器為 1
        $count_res = mysqli_query($link, "SELECT COUNT(*) as cnt FROM contact_messages");
        $count_row = mysqli_fetch_assoc($count_res);
        if ($count_row['cnt'] == 0) {
            mysqli_query($link, "ALTER TABLE contact_messages AUTO_INCREMENT = 1");
            mysqli_query($link, "ALTER TABLE contact_replies AUTO_INCREMENT = 1");
        }
        
        if (isset($_POST['ajax'])) { echo "SUCCESS"; exit; }
        header("Location: admin_contact.php");
        exit;
    }
    
    if ($action === 'close') {
        mysqli_query($link, "UPDATE contact_messages SET status = 'closed' WHERE id = $id");
        if (isset($_POST['ajax'])) { echo "SUCCESS"; exit; }
        header("Location: admin_chat.php?id=$id");
        exit;
    }

    if (isset($_POST['content']) && !empty(trim($_POST['content']))) {
        // 管理員回覆
        $content = mysqli_real_escape_string($link, $_POST['content']);
        $sql = "INSERT INTO contact_replies (message_id, sender_role, content) 
                VALUES ($id, 'admin', '$content')";
        mysqli_query($link, $sql);
        if (isset($_POST['ajax'])) { echo "SUCCESS"; exit; }
        header("Location: admin_chat.php?id=$id");
        exit;
    }
}

// 只有當狀態為 'new' 時才更新為 'read' (處理中)
// 只有當狀態為 'new' 時才更新為 'read' (處理中)
$status_updated = false;
if ($id > 0) {
    if (mysqli_query($link, "UPDATE contact_messages SET status = 'read' WHERE id = $id AND status = 'new'")) {
        if (mysqli_affected_rows($link) > 0) {
            $status_updated = true;
        }
    }
}

$msg = get_contact_message($link, $id);
if (!$msg) {
    if (isset($_GET['ajax_body'])) { echo json_encode(['status' => 'deleted']); exit; }
    header("Location: admin_contact.php"); exit;
}

$res_replies = get_message_replies($link, $id);

// AJAX 輸出 JSON
if (isset($_GET['ajax_body'])) {
    session_write_close(); // Release session lock for concurrent polling
    $html = '<div class="msg-row admin">
                <div style="display: flex; flex-direction: column; align-items: flex-start;">
                    <div class="bubble secondary">' . nl2br(htmlspecialchars((string)($msg['message'] ?? ''))) . '</div>
                    <div class="meta">' . htmlspecialchars((string)($msg['name'] ?? '用戶')) . ' • ' . $msg['created_at'] . '</div>
                </div>
            </div>';
            
    while ($row = mysqli_fetch_assoc($res_replies)) {
        $is_admin = ($row['sender_role'] === 'admin');
        $row_class = $is_admin ? 'user' : 'admin'; 
        $bubble_class = $is_admin ? 'primary' : 'secondary';
        $align = $is_admin ? 'flex-end' : 'flex-start';
        $sender_name = $is_admin ? '我 (管理員)' : htmlspecialchars($msg['name']);
        
        $html .= '<div class="msg-row ' . ($is_admin ? 'user' : 'admin') . '">
                    <div style="display: flex; flex-direction: column; align-items: ' . $align . ';">
                        <div class="bubble ' . $bubble_class . '">' . nl2br(htmlspecialchars((string)($row['content'] ?? ''))) . '</div>
                        <div class="meta">' . $sender_name . ' • ' . $row['created_at'] . '</div>
                    </div>
                </div>';
    }
    
    echo json_encode(['status' => $msg['status'], 'html' => $html]);
    exit;
}

$page_title = "客服管理 - 通訊";
$extra_css = '<link rel="stylesheet" href="css/chat.css">';
require_once("inc/header.php");
require_once("inc/modal.php");
?>
    <div class="chat-container">
        <div class="chat-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="admin_contact.php" class="btn-secondary" style="padding: 5px 15px;">< 返回</a>
                <div>
                    <h3 style="margin:0; font-size: 1.1rem;">客服對話</h3>
                    <div style="font-size: 0.9rem; color: var(--accent-color); font-weight: bold; margin-top: 2px;">【<?php echo htmlspecialchars($msg['category'] ?? '一般'); ?>】</div>
                    <div style="font-size: 0.85rem; color: #aaa;"><?php echo htmlspecialchars($msg['name'] ?? '未知用戶'); ?> (<?php echo htmlspecialchars($msg['email'] ?? ''); ?>)</div>
                </div>
            </div>
            <div id="admin-actions-area" style="display: flex; gap: 10px;">
                <?php if ($msg['status'] !== 'closed'): ?>
                    <button type="button" id="btn-close-ticket" onclick="confirmAction('close', '確定要將此案件結單嗎？')" class="btn-primary" style="padding: 5px 15px; font-size: 0.85rem;">✔ 結單</button>
                <?php endif; ?>
                <button type="button" onclick="confirmAction('delete', '確定要徹底刪除此對話嗎？將包含所有回覆記錄。')" class="btn-secondary" style="border-color: #ff4757; color: #ff4757; padding: 5px 15px; font-size: 0.85rem;">🗑 刪除</button>
            </div>
        </div>

        <div class="chat-body" id="chat-box">
            <!-- Initial content pre-rendered for instant visibility -->
            <div class="msg-row admin">
                <div style="display: flex; flex-direction: column; align-items: flex-start;">
                    <div class="bubble secondary"><?php echo nl2br(htmlspecialchars((string)($msg['message'] ?? ''))); ?></div>
                    <div class="meta"><?php echo htmlspecialchars((string)($msg['name'] ?? '用戶')); ?> • <?php echo $msg['created_at']; ?></div>
                </div>
            </div>
            <?php 
            mysqli_data_seek($res_replies, 0); // Reset pointer
            while ($row = mysqli_fetch_assoc($res_replies)): 
                $is_admin = ($row['sender_role'] === 'admin');
                $row_class = $is_admin ? 'user' : 'admin'; 
                $bubble_class = $is_admin ? 'primary' : 'secondary';
                $align = $is_admin ? 'flex-end' : 'flex-start';
                $sender_name = $is_admin ? '我 (管理員)' : htmlspecialchars($msg['name'] ?? '用戶');
            ?>
                <div class="msg-row <?php echo ($is_admin ? 'user' : 'admin'); ?>">
                    <div style="display: flex; flex-direction: column; align-items: <?php echo $align; ?>;">
                        <div class="bubble <?php echo $bubble_class; ?>"><?php echo nl2br(htmlspecialchars((string)($row['content'] ?? ''))); ?></div>
                        <div class="meta"><?php echo $sender_name; ?> • <?php echo $row['created_at']; ?></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="chat-footer" id="chat-footer-area">
            <?php if ($msg['status'] !== 'closed'): ?>
                <form method="post" class="reply-form" onsubmit="return handleChatSubmit(event, this)">
                    <textarea name="content" class="reply-input" placeholder="輸入回覆訊息..." required></textarea>
                    <button type="submit" class="btn-primary" style="padding: 0 25px; border-radius: 20px;">傳送回覆</button>
                </form>
            <?php else: ?>
                <div class="closed-notice" style="text-align: center; color: #777; padding: 10px;">此案件已結單。</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/chat.js"></script>
    <script src="js/chat_init.js"></script>
    <script>
        initAdminChat(<?php echo $id; ?>, <?php echo (isset($status_updated) && $status_updated) ? 'true' : 'false'; ?>);
    </script>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
