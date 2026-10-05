<?php
require_once __DIR__ . '/../includes/auth.php';

try {
    echo "Testing Notifications Table...\n";
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link, created_by) VALUES (?,?,?,?,?,?)");
    $res = $stmt->execute([null, 'Test Notification Title', 'Test Notification Message Body', 'success', '/ccms/dashboard.php', current_user_id()]);
    echo "Insert Result: " . ($res ? "SUCCESS" : "FAILED") . "\n";
    echo "Last Insert ID: " . $pdo->lastInsertId() . "\n";

    $count = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    echo "Total Notifications in DB: " . $count . "\n";

    $all = $pdo->query("SELECT * FROM notifications ORDER BY notification_id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    print_r($all);

} catch (Exception $e) {
    echo "EXCEPTION THROWN: " . $e->getMessage() . "\n";
}
