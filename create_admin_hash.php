<?php
/**
 * Run this once from the command line (php create_admin_hash.php) to generate
 * a fresh bcrypt hash for the default Administrator account, then paste the
 * output into database/ccms.sql (or run the UPDATE statement it prints)
 * before importing the schema — or after, against a live database.
 *
 * Default login this script assumes:
 *   Username: admin
 *   Password: Admin@123   <-- CHANGE THIS after first login via Users > Edit
 */
$password = 'Admin@123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Generated bcrypt hash for password '$password':\n\n";
echo $hash . "\n\n";
echo "If you already imported ccms.sql, run this SQL to fix the seeded admin account:\n\n";
echo "UPDATE users SET password_hash = '" . $hash . "' WHERE username = 'admin';\n";
