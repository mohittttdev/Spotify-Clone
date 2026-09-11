
<?php
// auth/reset-password.php

$apiUrl = "../api/auth/reset-password.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password - Spotify Clone</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-xl">

            <!-- Heading -->
            <div class="text-center mb-7">

                <h1 class="text-2xl font-bold mb-2">
                    Reset Password
                </h1>

                <p class="text-gray-400 text-sm">
                    Enter your new password below.
                </p>

            </div>

            <!-- Message -->
            <div
                id="message"
                class="hidden mb-5 p-3 rounded-lg text-sm"
            ></div>

            <!-- Form -->
            <form id="resetForm">

                <!-- New Password -->
                <div class="mb-5">

                    <label class="block text-sm text-gray-300 mb-2">
                        New Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter new password"
                        minlength="6"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 outline-none focus:border-green-500"
                    >

                </div>

                <!-- Confirm Password -->
                <div class="mb-6">

                    <label class="block text-sm text-gray-300 mb-2">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirm_password"
                        placeholder="Confirm new password"
                        minlength="6"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 outline-none focus:border-green-500"
                    >

                </div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full bg-green-500 hover:bg-green-400 text-black font-semibold py-3 rounded-lg transition"
                >
                    Reset Password
                </button>

            </form>

            <!-- Login -->
            <div class="text-center mt-6">

                <a
                    href="login.php"
                    class="text-green-400 hover:text-green-300 text-sm"
                >
                    ← Back to Login
                </a>

            </div>

        </div>

    </div>


<script>

const form = document.getElementById("resetForm");
const message = document.getElementById("message");
const submitBtn = document.getElementById("submitBtn");

// Get token from URL
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get("token");

// Check token
if (!token) {

    showMessage(
        "Invalid or missing reset token.",
        "error"
    );

    submitBtn.disabled = true;
    submitBtn.classList.add("opacity-50", "cursor-not-allowed");
}


// Form submit
form.addEventListener("submit", async function(event) {

    event.preventDefault();

    if (!token) {
        return;
    }

    const password = document.getElementById("password").value;
    const confirmPassword =
        document.getElementById("confirmPassword").value;

    // Password validation
    if (password.length < 6) {

        showMessage(
            "Password must be at least 6 characters.",
            "error"
        );

        return;
    }

    // Confirm password
    if (password !== confirmPassword) {

        showMessage(
            "Passwords do not match.",
            "error"
        );

        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = "Resetting...";

    try {

        const response = await fetch("<?php echo $apiUrl; ?>", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                token: token,
                password: password,
                confirm_password: confirmPassword
            })

        });

        const data = await response.json();

        if (data.success) {

            showMessage(
                data.message || "Password reset successfully.",
                "success"
            );

            form.reset();

            // Redirect to login
            setTimeout(() => {

                window.location.href = "login.php";

            }, 1500);

        } else {

            showMessage(
                data.message || "Unable to reset password.",
                "error"
            );

        }

    } catch (error) {

        console.error(error);

        showMessage(
            "Something went wrong. Please try again.",
            "error"
        );

    } finally {

        submitBtn.disabled = false;
        submitBtn.textContent = "Reset Password";
    }

});


// Show message
function showMessage(text, type) {

    message.textContent = text;

    message.classList.remove(
        "hidden",
        "bg-green-900",
        "text-green-300",
        "bg-red-900",
        "text-red-300"
    );

    if (type === "success") {

        message.classList.add(
            "bg-green-900",
            "text-green-300"
        );

    } else {

        message.classList.add(
            "bg-red-900",
            "text-red-300"
        );
    }
}

</script>

</body>
</html>