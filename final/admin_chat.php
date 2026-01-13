<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");
$username = $_SESSION["username"];
$sql_role = "SELECT role FROM students WHERE username = '$username'";
$user_role_data = mysqli_fetch_assoc(mysqli_query($link, $sql_role));
if (($user_role_data['role'] ?? 'user') !== 'admin') {
    die("Access Denied");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id == 0) {
    header("Location: admin_contact.php");
    exit;
}

// 處理 POST請求 (回覆 / 結單 / 刪除)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // (結單/刪除邏輯保持不變，全頁重整可接受)
        if ($_POST['action'] === 'delete') {
            mysqli_query($link, "DELETE FROM contact_replies WHERE message_id = $id");
            mysqli_query($link, "DELETE FROM contact_messages WHERE id = $id");
            echo "<script>alert('已刪除此對話記錄。'); location.href='admin_contact.php';</script>";
            exit;
        }
        elseif ($_POST['action'] === 'close') {
            mysqli_query($link, "UPDATE contact_messages SET status = 'closed' WHERE id = $id");
            header("Location: admin_chat.php?id=$id");
            exit;
        }
    } 
    elseif (isset($_POST['content']) && !empty(trim($_POST['content']))) {
        // 回覆邏輯
        $check_status = mysqli_fetch_assoc(mysqli_query($link, "SELECT status FROM contact_messages WHERE id=$id"));
        if ($check_status['status'] === 'closed') {
            if (isset($_POST['ajax'])) { echo "locked"; exit; }
            echo "<script>alert('此案件已結單，無法回覆。'); location.href='admin_chat.php?id=$id';</script>";
            exit;
        }

        $content = mysqli_real_escape_string($link, $_POST['content']);
        $sql_reply = "INSERT INTO contact_replies (message_id, sender_role, content) VALUES ($id, 'admin', '$content')";
        if(mysqli_query($link, $sql_reply)){
             // 成功
        }
        
        if (isset($_POST['ajax'])) {
            echo "success";
            exit;
        }
        
        header("Location: admin_chat.php?id=$id");
        exit;
    }
}


// 只有當狀態為 'new' 時才更新為 'read' (處理中)
mysqli_query($link, "UPDATE contact_messages SET status = 'read' WHERE id = $id AND status = 'new'");

// 取得資料
$sql_main = "SELECT * FROM contact_messages WHERE id = $id";
$res_main = mysqli_query($link, $sql_main);
$msg = mysqli_fetch_assoc($res_main);

if (!$msg) {
    die("Message not found.");
}

$sql_replies = "SELECT * FROM contact_replies WHERE message_id = $id ORDER BY created_at ASC";
$res_replies = mysqli_query($link, $sql_replies);

if (!$res_replies) {
    die("Error fetching replies: " . mysqli_error($link));
}

// AJAX: 僅輸出對話內容邏輯
if (isset($_GET['ajax_body'])) {
    // 原始訊息 (使用者)
    echo '<div class="msg-row user">
            <div style="display: flex; flex-direction: column;">
                <div class="bubble">
                    <div style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 3px;">原始詢問</div>
                    '.nl2br(htmlspecialchars($msg['message'])).'
                </div>
                <div class="meta">'.$msg['created_at'].'</div>
            </div>
        </div>';
    
    // 回覆列表
    while($row = mysqli_fetch_assoc($res_replies)) {
        $is_admin = ($row['sender_role'] === 'admin');
        $row_class = $is_admin ? 'admin' : 'user';
        echo '<div class="msg-row '.$row_class.'">
                <div style="display: flex; flex-direction: column; align-items: '.($is_admin ? 'flex-end' : 'flex-start').';">
                    <div class="bubble">
                        '.nl2br(htmlspecialchars($row['content'])).'
                    </div>
                    <div class="meta">'.$row['created_at'].'</div>
                </div>
            </div>';
    }
    exit; // 如果是 AJAX 則在此停止
}

// 狀態顯示輔助
$status_label = "";
$status_color = "";
if ($msg['status'] == 'new') {
    $status_label = "未讀"; $status_color = "#ff7675";
} elseif ($msg['status'] == 'read') {
    $status_label = "處理中"; $status_color = "#74b9ff";
} else {
    $status_label = "已完成"; $status_color = "#b2bec3";
}
$page_title = "客服對話 - 管理員後台";
$extra_css = '<style>
        body { background: #121212; color: white; }
        .chat-container {
            max-width: 800px; margin: 30px auto; background: #181818; 
            border-radius: 10px; display: flex; flex-direction: column; height: 85vh;
            border: 1px solid #333;
        }
        .chat-header {
            padding: 20px; border-bottom: 1px solid #333; display: flex; 
            justify-content: space-between; align-items: center; background: #282828;
            border-radius: 10px 10px 0 0;
        }
        .chat-body {
            flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px;
        }
        .chat-footer {
            padding: 20px; border-top: 1px solid #333; background: #282828;
            border-radius: 0 0 10px 10px;
        }
        
        /* Bubbles */
        .msg-row { display: flex; margin-bottom: 10px; }
        .msg-row.user { justify-content: flex-start; }
        .msg-row.admin { justify-content: flex-end; }
        
        .bubble {
            max-width: 70%; padding: 12px 16px; border-radius: 18px; 
            font-size: 0.95rem; line-height: 1.4; position: relative;
        }
        .msg-row.user .bubble {
            background: #3e3e3e; color: white; border-bottom-left-radius: 4px;
        }
        .msg-row.admin .bubble {
            background: var(--accent-color); color: black; border-bottom-right-radius: 4px;
        }
        
        .meta { font-size: 0.75rem; margin-top: 5px; opacity: 0.7; }
        .msg-row.user .meta { text-align: left; }
        .msg-row.admin .meta { text-align: right; }

        /* Form */
        .reply-form { display: flex; gap: 10px; }
        .reply-input {
            flex: 1; background: #333; border: 1px solid #444; color: white; 
            padding: 12px; border-radius: 20px; outline: none; resize: none; height: 45px;
        }
        .reply-input:focus { border-color: var(--accent-color); }
    </style>';
require_once("inc/header.php");
?>
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="admin_contact.php" class="btn-secondary" style="padding: 5px 15px;">&lt; 返回</a>
                <div>
                    <div style="font-weight: bold; font-size: 1.1rem;">
                        <?php echo htmlspecialchars($msg['name']); ?>
                        <span style="font-size: 0.8rem; background:<?php echo $status_color; ?>; color: #121212; padding: 2px 6px; border-radius: 4px; margin-left: 8px; vertical-align: middle;">
                            <?php echo $status_label; ?>
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: #aaa;"><?php echo htmlspecialchars($msg['category']); ?> - <?php echo htmlspecialchars($msg['email']); ?></div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <!-- Close Button (Only if not closed) -->
                <?php if ($msg['status'] !== 'closed'): ?>
                <form method="post" onsubmit="return confirm('確定將此案件標記為【已完成】嗎？');" style="margin:0;">
                    <input type="hidden" name="action" value="close">
                    <button type="submit" class="btn-secondary" style="border-color: #55efc4; color: #55efc4;">✔ 結單</button>
                </form>
                <?php endif; ?>

                <!-- Delete Button (Always available) -->
                <form method="post" onsubmit="return confirm('警示：這將會【永久刪除】此對話記錄，無法復原！確定執行？');" style="margin:0;">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn-secondary" style="border-color: #ff4757; color: #ff4757;">🗑 刪除</button>
                </form>
            </div>
        </div>
        
        <!-- Body -->
        <div class="chat-body" id="chat-box">
            <!-- 原始訊息 (使用者) -->
            <div class="msg-row user">
                <div style="display: flex; flex-direction: column;">
                    <div class="bubble">
                        <div style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 3px;">原始詢問</div>
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    <div class="meta"><?php echo $msg['created_at']; ?></div>
                </div>
            </div>
            
            <!-- 回覆列表 -->
            <?php while($row = mysqli_fetch_assoc($res_replies)): 
                $is_admin = ($row['sender_role'] === 'admin');
                $row_class = $is_admin ? 'admin' : 'user';
            ?>
                <div class="msg-row <?php echo $row_class; ?>">
                    <div style="display: flex; flex-direction: column; align-items: <?php echo $is_admin ? 'flex-end' : 'flex-start'; ?>;">
                        <div class="bubble">
                            <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        </div>
                        <div class="meta"><?php echo $row['created_at']; ?></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Footer -->
        <div class="chat-footer">
            <?php if ($msg['status'] !== 'closed'): ?>
            <form method="post" class="reply-form" id="replyForm">
                <textarea name="content" class="reply-input" id="replyInput" placeholder="輸入回覆..." required></textarea>
                <button type="submit" class="btn-primary" style="border-radius: 50%; width: 45px; height: 45px; padding: 0; display: flex; align-items: center; justify-content: center;">➤</button>
            </form>
            <?php else: ?>
                <div style="text-align: center; color: #777; padding: 10px;">此案件已結單，無法傳送訊息。</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // 自動捲動到底部
        const chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;

        function refreshChat() {
            fetch('admin_chat.php?id=<?php echo $id; ?>&ajax_body=1')
                .then(response => response.text())
                .then(html => {
                    chatBox.innerHTML = html;
                    chatBox.scrollTop = chatBox.scrollHeight; // 刷新後自動捲動
                });
        }

        // 每 10 秒自動刷新
        setInterval(refreshChat, 10000);

        // Enter 送出，Shift+Enter 換行 (使用 AJAX)
        const replyInput = document.getElementById('replyInput');
        const replyForm = document.getElementById('replyForm');
        
        if (replyInput && replyForm) {
            // 處理送出功能
            function submitMessage(e) {
                if(e) e.preventDefault();
                if (replyInput.value.trim() === '') return;

                const formData = new FormData(replyForm);
                formData.append('ajax', '1');

                fetch('admin_chat.php?id=<?php echo $id; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(status => {
                    if (status.trim() === 'success') {
                        replyInput.value = ''; // 清空輸入框
                        refreshChat(); // 刷新內容
                    } else if (status.trim() === 'locked') {
                        alert('此案件已結單，無法回覆。');
                        location.reload();
                    } else {
                        // 後備重整
                        location.reload();
                    }
                })
                .catch(err => console.error(err));
            }

            replyForm.addEventListener('submit', submitMessage);

            replyInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    submitMessage();
                }
            });
        }
    </script>
</body>
</html>
<?php require_once("../DB/DB_close.php"); ?>
