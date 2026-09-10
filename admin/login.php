
<?php

session_start();

require_once "../config/database.php";

$error = "";

/* Check login */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Email and password are required.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                id,
                name,
                email,
                password,
                profile_image,
                role,
                status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $error = "Database query error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $email);

            if (!$stmt->execute()) {

                $error = "Query execution error: " . $stmt->error;

            } else {

                $result = $stmt->get_result();

                if ($result->num_rows === 0) {

                    $error = "USER NOT FOUND: " . htmlspecialchars($email);

                } else {

                    $user = $result->fetch_assoc();

                    if ($user["role"] !== "admin") {

                        $error = "ROLE ERROR: " . htmlspecialchars($user["role"]);

                    } elseif ($user["status"] !== "active") {

                        $error = "STATUS ERROR: " . htmlspecialchars($user["status"]);

                    } elseif (!password_verify($password, $user["password"])) {

                        $error = "PASSWORD VERIFY FAILED";

                    } else {

                        session_regenerate_id(true);

                        $_SESSION["admin_id"] = (int) $user["id"];
                        $_SESSION["admin_name"] = $user["name"];
                        $_SESSION["admin_email"] = $user["email"];
                        $_SESSION["admin_image"] = $user["profile_image"] ?? null;

                        header("Location: dashboard.php");
                        exit;
                    }
                }
            }

            $stmt->close();
        }
    }
}

?>