<?php
require_once("inc/auth_guard.php");
require_once("../DB/DB_open.php");

$user_id = $_SESSION['sno'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: my_messages.php");
    exit;
}

// 取得主要訊息並驗證擁有權
$user_id = mysqli_real_escape_string($link, $user_id);
$sql_main = "SELECT * FROM contact_messages WHERE id = $id AND user_id = '$user_id'";
$res_main = mysqli_query($link, $sql_main);
$msg = mysqli_fetch_assoc($res_main);

if (!$msg) {
    die("Message not found or access denied.");
}

// 處理回覆
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['content']) && !empty(trim($_POST['content']))) {
        // 檢查是否已結單
        $check_status = mysqli_fetch_assoc(mysqli_query($link, "SELECT status FROM contact_messages WHERE id=$id"));
        if ($check_status['status'] === 'closed') {
           if (isset($_POST['ajax'])) { echo "locked"; exit; }
           echo "<script>alert('此案件已結單，無法回覆。'); location.href='my_messages_view.php?id=$id';</script>";
           exit;
        }

        $content = mysqli_real_escape_string($link, $_POST['content']);
        // 如果狀態是 'read'，則變回 'new' (管理員未讀)。
        $sql_reply = "INSERT INTO contact_replies (message_id, sender_role, content) VALUES ($id, 'user', '$content')";
        if (mysqli_query($link, $sql_reply)) {
            mysqli_query($link, "UPDATE contact_messages SET status = 'new' WHERE id = $id");
        }
        
        if (isset($_POST['ajax'])) {
            echo "success";
            exit;
        }
        
        header("Location: my_messages_view.php?id=$id");
        exit;
    }
}

// 取得回覆
$sql_replies = "SELECT * FROM contact_replies WHERE message_id = $id ORDER BY created_at ASC";
$res_replies = mysqli_query($link, $sql_replies);

if (!$res_replies) {
    die("Error fetching replies: " . mysqli_error($link));
}

//  AJAX: 僅輸出對話內容邏輯
if (isset($_GET['ajax_body'])) {
    // 原始訊息 (使用者 - 右側)
    echo '<div class="msg-row user">
            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                <div class="bubble">
                    <div style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 3px;">我的提問</div>
                    '.nl2br(htmlspecialchars($msg['message'])).'
                </div>
                <div class="meta">'.$msg['created_at'].'</div>
            </div>
        </div>';
    
    // 回覆列表
    while($row = mysqli_fetch_assoc($res_replies)) {
        $row_class = $row['sender_role']; 
        echo '<div class="msg-row '.$row_class.'">
                <div style="display: flex; flex-direction: column; align-items: '.($row_class == 'user' ? 'flex-end' : 'flex-start').';">
                    <div class="bubble">
                        '.nl2br(htmlspecialchars($row['content'])).'
                    </div>
                    <div class="meta">'.($row_class=='admin' ? '客服人員' : '我').' • '.$row['created_at'].'</div>
                </div>
            </div>';
    }
    exit; // 如果是 AJAX 則停止
}
?>
<?php
$page_title = "對話記錄 - 客服中心";
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
        
        /* 對話氣泡 */
        .msg-row { display: flex; margin-bottom: 10px; }
        .msg-row.user { justify-content: flex-end; }  /* 使用者現在在右側 */
        .msg-row.admin { justify-content: flex-start; } /* 管理員在左側 */
        
        .bubble {
            max-width: 70%; padding: 12px 16px; border-radius: 18px; 
            font-size: 0.95rem; line-height: 1.4; position: relative;
        }
        .msg-row.user .bubble {
            background: var(--accent-color); color: black; border-bottom-right-radius: 4px;
        }
        .msg-row.admin .bubble {
            background: #3e3e3e; color: white; border-bottom-left-radius: 4px;
        }
        
        .meta { font-size: 0.75rem; margin-top: 5px; opacity: 0.7; }
        .msg-row.user .meta { text-align: right; }
        .msg-row.admin .meta { text-align: left; }

        /* 表單 */
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
                <a href="my_messages.php" class="btn-secondary" style="padding: 5px 15px;">&lt; 返回</a>
                <div>
                    <div style="font-weight: bold; font-size: 1.1rem;"><?php echo htmlspecialchars($msg['category']); ?></div>
                    <div style="font-size: 0.85rem; color: #aaa;">案件編號 #<?php echo $msg['id']; ?></div>
                </div>
            </div>
            <div class="btn-secondary" style="border:none; cursor:default; background:none;">
                <?php 
                    if($msg['status'] == 'new') echo '已送出';
                    elseif($msg['status'] == 'read') echo '處理中';
                    else echo '已完成';
                ?>
            </div>
        </div>
        
        <!-- Body -->
        <div class="chat-body" id="chat-box">
            <!-- 原始訊息 (使用者 - 右側) -->
            <div class="msg-row user">
                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                    <div class="bubble">
                        <div style="font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 3px;">我的提問</div>
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    <div class="meta"><?php echo $msg['created_at']; ?></div>
                </div>
            </div>
            
            <!-- 回覆列表 -->
            <?php while($row = mysqli_fetch_assoc($res_replies)): 
                $row_class = $row['sender_role']; 
            ?>
                <div class="msg-row <?php echo $row_class; ?>">
                    <div style="display: flex; flex-direction: column; align-items: <?php echo ($row_class == 'user') ? 'flex-end' : 'flex-start'; ?>;">
                        <div class="bubble">
                            <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        </div>
                        <div class="meta"><?php echo ($row_class=='admin' ? '客服人員' : '我'); ?> • <?php echo $row['created_at']; ?></div>
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
            fetch('my_messages_view.php?id=<?php echo $id; ?>&ajax_body=1')
                .then(response => response.text())
                .then(html => {
                    chatBox.innerHTML = html;
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        // 每 10 秒自動刷新
        setInterval(refreshChat, 10000);

        // Enter 送出，Shift+Enter 換行
        const replyInput = document.getElementById('replyInput');
        const replyForm = document.getElementById('replyForm');
        
        if (replyInput && replyForm) {
            // 處理送出功能
            function submitMessage(e) {
                if(e) e.preventDefault();
                if (replyInput.value.trim() === '') return;

                const formData = new FormData(replyForm);
                formData.append('ajax', '1');

                fetch('my_messages_view.php?id=<?php echo $id; ?>', {
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
