<?php
header('Content-Type: application/json; charset=utf-8');

// Target email address
$recipient_email = "behanpeshala@gmail.com";

// Database connection check & table creation
$db_connected = false;
$pdo = null;
if (file_exists(__DIR__ . '/config/db.php')) {
    try {
        require_once __DIR__ . '/config/db.php';
        $db_connected = true;
        
        // Auto-create contact_messages table if missing
        $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_name VARCHAR(100) NOT NULL,
            sender_email VARCHAR(100) NOT NULL,
            sender_phone VARCHAR(30) NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            recipient_email VARCHAR(100) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Received',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;");
    } catch (Exception $e) {
        // Silently continue if DB connection fails
    }
}

// Support both form-urlencoded POST and raw JSON body
$jsonInput = json_decode(file_get_contents('php://input'), true);

$name    = trim($_POST['name'] ?? $jsonInput['name'] ?? '');
$email   = trim($_POST['email'] ?? $jsonInput['email'] ?? '');
$phone   = trim($_POST['phone'] ?? $jsonInput['phone'] ?? '');
$subject = trim($_POST['subject'] ?? $jsonInput['subject'] ?? 'New Project Inquiry');
$message = trim($_POST['message'] ?? $jsonInput['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields (Name, Email, and Message).'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address.'
    ]);
    exit;
}

// Save inquiry to Database
$db_saved = false;
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (sender_name, sender_email, sender_phone, subject, message, recipient_email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message, $recipient_email]);
        $db_saved = true;
    } catch (Exception $e) {
        // Continue if DB insert fails
    }
}

// Build HTML email
$email_subject = "CCMS Client Inquiry: " . $subject;
$email_body = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <title>" . htmlspecialchars($subject) . "</title>
</head>
<body style='font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333;'>
  <div style='max-width: 600px; background: #ffffff; margin: 0 auto; padding: 25px; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
    <h2 style='color: #10B981; margin-top: 0; border-bottom: 2px solid #10B981; padding-bottom: 12px;'>
      🏗️ Chaminda Construction - New Client Message
    </h2>
    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
      <tr>
        <td style='padding: 8px 0; font-weight: bold; width: 130px; color: #475569;'>Client Name:</td>
        <td style='padding: 8px 0; color: #0f172a;'>" . htmlspecialchars($name) . "</td>
      </tr>
      <tr>
        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Email Address:</td>
        <td style='padding: 8px 0;'><a href='mailto:" . htmlspecialchars($email) . "' style='color: #10B981; text-decoration: none; font-weight: bold;'>" . htmlspecialchars($email) . "</a></td>
      </tr>
      <tr>
        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Phone Number:</td>
        <td style='padding: 8px 0; color: #0f172a;'>" . htmlspecialchars($phone ?: 'Not provided') . "</td>
      </tr>
      <tr>
        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Subject:</td>
        <td style='padding: 8px 0; color: #0f172a;'>" . htmlspecialchars($subject) . "</td>
      </tr>
    </table>
    
    <div style='background: #f8fafc; padding: 16px; border-left: 4px solid #10B981; border-radius: 6px; margin-bottom: 20px;'>
      <h4 style='margin: 0 0 10px 0; color: #0f172a;'>Message Content:</h4>
      <p style='margin: 0; white-space: pre-wrap; line-height: 1.6; color: #334155;'>" . htmlspecialchars($message) . "</p>
    </div>

    <div style='text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;'>
      Sent directly from Chaminda Construction Website to <strong>" . htmlspecialchars($recipient_email) . "</strong>
    </div>
  </div>
</body>
</html>
";

$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: CCMS Website <no-reply@chamindaconstruction.lk>" . "\r\n";
$headers .= "Reply-To: " . $name . " <" . $email . ">" . "\r\n";

// Execute mail sending
@$mail_sent = mail($recipient_email, $email_subject, $email_body, $headers);

echo json_encode([
    'success' => true,
    'message' => 'Your message has been sent successfully to behanpeshala@gmail.com!',
    'mail_sent' => $mail_sent,
    'db_saved' => $db_saved
]);
exit;
