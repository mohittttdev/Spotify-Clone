```php
<?php

require_once "../config/database.php";

$email = "admin@spotify.com";
$password = "Admin@123";

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    UPDATE users
    SET password = ?
    WHERE email = ?
");

$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {
        echo "Admin password reset successfully.";
    } else {
        echo "Admin found, but password was not changed.";
    }

} else {

    echo "Error: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>
```
