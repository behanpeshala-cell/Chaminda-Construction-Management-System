<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'fetch';
$userId = current_user_id();

if ($action === 'fetch') {
    try {
        // Fetch notifications intended for this user or all users (user_id IS NULL)
        $stmt = $pdo->prepare("
            SELECT n.*, u.full_name AS sender_name 
            FROM notifications n
            LEFT JOIN users u ON n.created_by = u.user_id
            WHERE n.user_id = ? OR n.user_id IS NULL
            ORDER BY n.created_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unreadStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notifications 
            WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
        ");
        $unreadStmt->execute([$userId]);
        $unreadCount = (int)$unreadStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'mark_read') {
    try {
        $notifId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($notifId > 0) {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND (user_id = ? OR user_id IS NULL)");
            $stmt->execute([$notifId, $userId]);
        } else {
            // Mark all as read
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? OR user_id IS NULL");
            $stmt->execute([$userId]);
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
