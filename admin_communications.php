<?php
// File: admin_communications.php - Admin messaging interface
session_start();
include 'DBConn.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Handle sending admin reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send_reply') {
    $sender_id = $admin_id;
    $receiver_id = (int)$_POST['receiver_id'];
    $message_text = trim($_POST['message_text']);
    $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    
    if ($receiver_id && $message_text) {
        if (!empty($product_id)) {
            $stmt = $conn->prepare("INSERT INTO tblMessages (sender_id, receiver_id, product_id, message_text) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('iiis', $sender_id, $receiver_id, $product_id, $message_text);
        } else {
            $stmt = $conn->prepare("INSERT INTO tblMessages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
            $stmt->bind_param('iis', $sender_id, $receiver_id, $message_text);
        }
        $stmt->execute();
        $success = "Reply sent successfully!";
    }
}

// Get list of users who have messaged the admin (grouped by sender)
$conversations = $conn->query(
    "SELECT DISTINCT m.sender_id, u.name, u.surname, u.email, 
            MAX(m.sent_at) as last_message_time, 
            COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = $admin_id THEN 1 END) as unread_count
     FROM tblMessages m
     JOIN tblUser u ON m.sender_id = u.user_id
     WHERE m.receiver_id = $admin_id
     GROUP BY m.sender_id
     ORDER BY last_message_time DESC"
);

// Get selected conversation messages
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$conversation_messages = null;
if ($selected_user_id) {
    $stmt = $conn->prepare(
        "SELECT m.*, s.name as sender_name, r.name as receiver_name
         FROM tblMessages m
         LEFT JOIN tblUser s ON m.sender_id = s.user_id
         LEFT JOIN tblUser r ON m.receiver_id = r.user_id
         WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
         ORDER BY m.sent_at ASC"
    );
    $stmt->bind_param('iiii', $selected_user_id, $admin_id, $admin_id, $selected_user_id);
    $stmt->execute();
    $conversation_messages = $stmt->get_result();
    
    // Mark messages as read
    $upd = $conn->prepare("UPDATE tblMessages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $upd->bind_param('ii', $selected_user_id, $admin_id);
    $upd->execute();
}

// Get user details for display
$selected_user = null;
if ($selected_user_id) {
    $result = $conn->query("SELECT * FROM tblUser WHERE user_id = $selected_user_id");
    $selected_user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Communications - Pastimes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        
        .admin-header {
            background: #1a1a2e;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.5rem; font-weight: bold; color: #ff6b35; text-decoration: none; }
        .logout-btn { background: #ff6b35; padding: 0.5rem 1rem; border-radius: 5px; text-decoration: none; color: white; }
        
        .container { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 300px 1fr; min-height: calc(100vh - 60px); }
        
        .conversations-panel {
            background: white;
            border-right: 1px solid #ddd;
            overflow-y: auto;
            max-height: calc(100vh - 60px);
        }
        
        .conversations-header {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            background: #f8f9fa;
        }
        
        .conversations-header h2 {
            font-size: 1rem;
            color: #333;
            margin: 0;
        }
        
        .conversation-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .conversation-item:hover {
            background: #f5f5f5;
        }
        
        .conversation-item.active {
            background: #e3f2fd;
            border-left: 3px solid #2196f3;
        }
        
        .conversation-name {
            font-weight: 600;
            color: #333;
        }
        
        .conversation-email {
            font-size: 0.8rem;
            color: #999;
        }
        
        .conversation-time {
            font-size: 0.8rem;
            color: #999;
        }
        
        .unread-badge {
            display: inline-block;
            background: #ff6b35;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            text-align: center;
            line-height: 24px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
        
        .chat-panel {
            display: flex;
            flex-direction: column;
            background: white;
        }
        
        .chat-header {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            background: #f8f9fa;
        }
        
        .chat-header h3 {
            margin: 0 0 0.25rem 0;
            color: #333;
        }
        
        .chat-header p {
            margin: 0;
            font-size: 0.9rem;
            color: #999;
        }
        
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: #fafafa;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .message.sent {
            justify-content: flex-end;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 0.75rem;
            border-radius: 8px;
            word-wrap: break-word;
        }
        
        .message.received .message-bubble {
            background: #e0e0e0;
            color: #000;
        }
        
        .message.sent .message-bubble {
            background: #2c5f2d;
            color: white;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: #999;
            margin-top: 0.25rem;
        }
        
        .reply-form {
            padding: 1rem;
            border-top: 1px solid #ddd;
            background: white;
            display: flex;
            gap: 0.5rem;
        }
        
        .reply-form textarea {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            resize: vertical;
            min-height: 50px;
        }
        
        .reply-form button {
            padding: 0.75rem 1.5rem;
            background: #2c5f2d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            align-self: flex-end;
        }
        
        .reply-form button:hover {
            background: #1a3b1a;
        }
        
        .empty-state {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
            text-align: center;
            padding: 2rem;
        }
        
        .success {
            background: #e8f5e9;
            color: #2c5f2d;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
            .conversations-panel { display: none; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <a href="admin_dashboard.php" class="logo">🛡️ Pastimes Admin</a>
        <div>
            <a href="admin_dashboard.php" style="color:white; margin-right:1rem;">📊 Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Conversations List -->
        <div class="conversations-panel">
            <div class="conversations-header">
                <h2>💬 Messages</h2>
            </div>
            <?php if($conversations->num_rows > 0): ?>
                <?php while($conv = $conversations->fetch_assoc()): ?>
                    <a href="?user_id=<?php echo $conv['sender_id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="conversation-item <?php echo ($selected_user_id == $conv['sender_id']) ? 'active' : ''; ?>">
                            <div class="conversation-name">
                                <?php echo htmlspecialchars($conv['name'] . ' ' . $conv['surname']); ?>
                                <?php if($conv['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-email"><?php echo htmlspecialchars($conv['email']); ?></div>
                            <div class="conversation-time"><?php echo date('M d, h:i A', strtotime($conv['last_message_time'])); ?></div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding:1rem; text-align:center; color:#999;">No messages yet.</div>
            <?php endif; ?>
        </div>
        
        <!-- Chat Panel -->
        <div class="chat-panel">
            <?php if(isset($success)): ?>
                <div style="padding:1rem;">
                    <div class="success">✅ <?php echo $success; ?></div>
                </div>
            <?php endif; ?>
            
            <?php if($selected_user): ?>
                <div class="chat-header">
                    <h3><?php echo htmlspecialchars($selected_user['name'] . ' ' . $selected_user['surname']); ?></h3>
                    <p><?php echo htmlspecialchars($selected_user['email']); ?></p>
                </div>
                
                <div class="messages-container">
                    <?php if($conversation_messages && $conversation_messages->num_rows > 0): ?>
                        <?php while($msg = $conversation_messages->fetch_assoc()): ?>
                            <div class="message <?php echo ($msg['sender_id'] == $admin_id) ? 'sent' : 'received'; ?>">
                                <div>
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                    </div>
                                    <div class="message-time"><?php echo date('M d, h:i A', strtotime($msg['sent_at'])); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">Start a conversation with <?php echo htmlspecialchars($selected_user['name']); ?></div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" class="reply-form">
                    <input type="hidden" name="action" value="send_reply">
                    <input type="hidden" name="receiver_id" value="<?php echo $selected_user['user_id']; ?>">
                    <textarea name="message_text" placeholder="Type your reply..." required></textarea>
                    <button type="submit">Send Reply</button>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <div>
                        <div style="font-size:2rem; margin-bottom:1rem;">💬</div>
                        <p>Select a conversation to start messaging</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
