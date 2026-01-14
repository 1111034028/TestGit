<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
require_once("../DB/db_helper.php");

$user_id = $_SESSION['sno'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: my_messages.php");
    exit;
}

// 處理回覆
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['content']) && !empty(trim($_POST['content']))) {
        // 檢查是否已結單
        $check_sql = "SELECT status FROM contact_messages WHERE id=$id AND user_id='$user_id'";
        $check_row = mysqli_fetch_assoc(mysqli_query($link, $check_sql));
        
        if ($check_row && $check_row['status'] !== 'closed') {
            $content = mysqli_real_escape_string($link, $_POST['content']);
            $sql = "INSERT INTO contact_replies (message_id, sender_role, content) 
                    VALUES ($id, 'user', '$content')";
            mysqli_query($link, $sql);
        }
        
        if (isset($_POST['ajax'])) { echo "SUCCESS"; exit; }

        header("Location: my_messages_view.php?id=$id");
        exit;
    }
}

$msg = get_contact_message($link, $id, $user_id);

if (!$msg) {
    if (isset($_GET['ajax_body'])) {
        echo json_encode(['status' => 'deleted']); 
        exit;
    }
    header("Location: my_messages.php");
    exit;
}

$res_replies = get_message_replies($link, $id);

// AJAX 輸出 JSON
if (isset($_GET['ajax_body'])) {
    session_write_close();
    $html = '<div class="msg-row user">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <div class="bubble primary">' . nl2br(htmlspecialchars((string)($msg['message'] ?? ''))) . '</div>
                    <div class="meta">我 • ' . $msg['created_at'] . '</div>
                </div>
            </div>';
            
    while ($row = mysqli_fetch_assoc($res_replies)) {
        $is_admin = ($row['sender_role'] === 'admin');
        $row_class = $is_admin ? 'admin' : 'user';
        $bubble_class = $is_admin ? 'secondary' : 'primary';
        $align = $is_admin ? 'flex-start' : 'flex-end';
        $sender_name = $is_admin ? '客服人員' : '我';
        
        $html .= '<div class="msg-row ' . $row_class . '">
                    <div style="display: flex; flex-direction: column; align-items: ' . $align . ';">
                        <div class="bubble ' . $bubble_class . '">' . nl2br(htmlspecialchars((string)($row['content'] ?? ''))) . '</div>
                        <div class="meta">' . $sender_name . ' • ' . $row['created_at'] . '</div>
                    </div>
                </div>';
    }
    
    echo json_encode(['status' => $msg['status'], 'html' => $html]);
    exit;
}

$page_title = "對話記錄 - 客服中心";
$extra_css = '<link rel="stylesheet" href="css/chat.css">';
require_once("inc/header.php");
?>
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="my_messages.php" class="btn-secondary" style="padding: 5px 15px;">< 返回</a>
                <div>
                    <h3 style="margin:0; font-size: 1.1rem;"><?php echo htmlspecialchars($msg['subject']); ?></h3>
                    <div style="font-size: 0.85rem; color: #aaa;"><?php echo htmlspecialchars($msg['category']); ?></div>
                </div>
            </div>
            <div>
                <?php
                $status_map = ['new' => ['未讀', '#ff7675'], 'read' => ['處理中', '#74b9ff'], 'closed' => ['已結單', '#b2bec3']];
                $s = $status_map[$msg['status']] ?? ['未知', '#fff'];
                ?>
                <span id="status-badge-ui" class="status-badge" style="background: <?php echo $s[1]; ?>; color: #000;"><?php echo $s[0]; ?></span>
            </div>
        </div>
        
        <!-- Body -->
        <div class="chat-body" id="chat-box">
            <!-- Initial content pre-rendered for instant visibility -->
            <div class="msg-row user">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <div class="bubble primary"><?php echo nl2br(htmlspecialchars((string)($msg['message'] ?? ''))); ?></div>
                    <div class="meta">我 • <?php echo $msg['created_at']; ?></div>
                </div>
            </div>
            <?php 
            mysqli_data_seek($res_replies, 0); // Reset pointer
            while ($row = mysqli_fetch_assoc($res_replies)): 
                $is_admin = ($row['sender_role'] === 'admin');
                $row_class = $is_admin ? 'admin' : 'user';
                $bubble_class = $is_admin ? 'secondary' : 'primary';
                $align = $is_admin ? 'flex-start' : 'flex-end';
                $sender_name = $is_admin ? '客服人員' : '我';
            ?>
                <div class="msg-row <?php echo $row_class; ?>">
                    <div style="display: flex; flex-direction: column; align-items: <?php echo $align; ?>;">
                        <div class="bubble <?php echo $bubble_class; ?>"><?php echo nl2br(htmlspecialchars((string)($row['content'] ?? ''))); ?></div>
                        <div class="meta"><?php echo $sender_name; ?> • <?php echo $row['created_at']; ?></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Footer -->
        <div class="chat-footer" id="chat-footer-area">
            <?php if ($msg['status'] !== 'closed'): ?>
            <form method="post" class="reply-form" id="replyForm" onsubmit="return handleChatSubmit(event, this, () => {/* Handled by poll */})">
                <textarea name="content" class="reply-input" placeholder="輸入回覆..." required></textarea>
                <button type="submit" class="btn-primary" style="padding: 0 25px; border-radius: 20px;">傳送</button>
            </form>
            <?php else: ?>
                <div class="closed-notice" style="text-align: center; color: #777; padding: 10px;">此案件已結單，無法傳送訊息。</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/chat.js"></script>
    <script src="js/chat_init.js"></script>
    <script>
        initUserChat(<?php echo $id; ?>);
    </script>
    <?php include "inc/modal.php"; ?>
    <?php include "foot.html"; ?>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
