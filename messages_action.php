<?php
// AJAX endpoint for messaging (list conversations, get messages, send message)
$return_to = null;
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'DBConn.php';
// Default to JSON responses; but if a `return_to` is provided we will perform server redirects when appropriate.
$user_id = $_SESSION['user_id'] ?? null;
if (isset($_REQUEST['return_to'])) {
    $return_to = $_REQUEST['return_to'];
}
header('Content-Type: application/json; charset=utf-8');
// If not authenticated and a return_to was provided, redirect to login so user can authenticate and return.
if (!$user_id) {
    if ($return_to) {
        $safe = $return_to;
        if (stripos($safe, 'http://') === 0 || stripos($safe, 'https://') === 0) { $safe = '/'; }
        header('Location: login.php?redirect=' . urlencode($safe));
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}
$action = $_REQUEST['action'] ?? '';

if ($action === 'list_conversations') {
    $stmt = $conn->prepare(
        "SELECT conv.partner_id, u.name, u.surname, m.message_text, m.sent_at, m.is_read
         FROM (
            SELECT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id, MAX(sent_at) AS last_sent
            FROM tblMessages
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY partner_id
         ) AS conv
         JOIN tblMessages m ON ((m.sender_id = ? AND m.receiver_id = conv.partner_id) OR (m.sender_id = conv.partner_id AND m.receiver_id = ?)) AND m.sent_at = conv.last_sent
         JOIN tblUser u ON u.user_id = conv.partner_id
         ORDER BY m.sent_at DESC"
    );
    $stmt->bind_param('iiiii', $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode(['success' => true, 'conversations' => $rows]);
    exit;
}

if ($action === 'get_messages') {
    $partner = (int) ($_GET['partner_id'] ?? 0);
    if (!$partner) { echo json_encode(['success'=>false,'error'=>'missing partner']); exit; }
    $stmt = $conn->prepare(
        "SELECT m.*, s.username AS sender_username, r.username AS receiver_username
         FROM tblMessages m
         LEFT JOIN tblUser s ON s.user_id = m.sender_id
         LEFT JOIN tblUser r ON r.user_id = m.receiver_id
         WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.sent_at ASC"
    );
    $stmt->bind_param('iiii', $user_id, $partner, $partner, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $msgs = [];
    while ($m = $res->fetch_assoc()) { $msgs[] = $m; }
    // mark messages sent to current user as read
    $upd = $conn->prepare("UPDATE tblMessages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $upd->bind_param('ii', $partner, $user_id);
    $upd->execute();
    echo json_encode(['success'=>true,'messages'=>$msgs]);
    exit;
}

if ($action === 'send_message') {
    $partner = (int) ($_POST['partner_id'] ?? 0);
    $text = trim($_POST['message_text'] ?? '');
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    if (!$partner || $text === '') { echo json_encode(['success'=>false,'error'=>'invalid input']); exit; }
    // If no product ID was supplied (or it's 0), insert without product_id to avoid binding NULL as integer
    if (!empty($product_id)) {
        $stmt = $conn->prepare("INSERT INTO tblMessages (sender_id, receiver_id, product_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiis', $user_id, $partner, $product_id, $text);
    } else {
        $stmt = $conn->prepare("INSERT INTO tblMessages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $user_id, $partner, $text);
    }
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $row = $conn->query("SELECT * FROM tblMessages WHERE message_id = $id")->fetch_assoc();
        // If called from a form with a return target, perform a server-side redirect back to that URL.
        if (!empty($_POST['return_to'])) {
            $safe = $_POST['return_to'];
            if (stripos($safe, 'http://') === 0 || stripos($safe, 'https://') === 0) { $safe = '/'; }
            header('Location: ' . $safe);
            exit;
        }
        echo json_encode(['success'=>true,'message'=>$row]);
    } else {
        echo json_encode(['success'=>false,'error'=>$conn->error]);
    }
    exit;
}

echo json_encode(['success'=>false,'error'=>'unknown action']);
