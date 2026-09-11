
<?php
// auth/logout.php

// API logout endpoint
$apiUrl = "../api/auth/logout.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logout - Spotify Clone</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center">

    <div class="text-center">

        <div class="mb-5">
            <div class="w-12 h-12 border-4 border-gray-700 border-t-green-500 rounded-full animate-spin mx-auto"></div>
        </div>

        <h1 class="text-xl font-semibold mb-2">
            Logging out...
        </h1>

        <p id="message" class="text-gray-400 text-sm">
            Please wait
        </p>

    </div>

<script>

async function logout() {

    const token = localStorage.getItem("spotify_token");

    try {

        // Call API logout only if token exists
        if (token) {

            const response = await fetch("<?php echo $apiUrl; ?>", {
                method: "POST",
                headers: {
                    "Authorization": "Bearer " + token,
                    "Content-Type": "application/json"
                }
            });

            // API response is not required for local logout
            await response.json().catch(() => {});
        }

    } catch (error) {

        console.log("Logout API error:", error);

    } finally {

        // Clear authentication data
        localStorage.removeItem("spotify_token");
        localStorage.removeItem("spotify_user");
        localStorage.removeItem("spotify_token_expiry");

        document.getElementById("message").textContent =
            "Logout successful. Redirecting...";

        // Redirect to login
        setTimeout(() => {
            window.location.href = "login.php";
        }, 700);
    }
}

logout();

</script>

</body>
</html>
