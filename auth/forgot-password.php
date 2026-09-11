
<?php
// auth/forgot-password.php

$apiUrl = "../api/auth/forgot-password.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Spotify Clone</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-xl">

            <!-- Heading -->
            <div class="text-center mb-7">

                <h1 class="text-2xl font-bold mb-2">
                    Forgot Password?
                </h1>

                <p class="text-gray-400 text-sm">
                    Enter your email and we'll send you a password reset link.
                </p>

            </div>

            <!-- Message -->
            <div
                id="message"
                class="hidden mb-5 p-3 rounded-lg text-sm"
            ></div>

            <!-- Form -->
            <form id="forgotForm">

                <div class="mb-5">

                    <label class="block text-sm text-gray-300 mb-2">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 outline-none focus:border-green-500"
                    >

                </div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full bg-green-500 hover:bg-green-400 text-black font-semibold py-3 rounded-lg transition"
                >
                    Send Reset Link
                </button>

            </form>

            <!-- Back to login -->
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

const form = document.getElementById("forgotForm");
const message = document.getElementById("message");
const submitBtn = document.getElementById("submitBtn");

form.addEventListener("submit", async function(event) {

    event.preventDefault();

    const email = document.getElementById("email").value.trim();

    // Basic validation
    if (!email) {

        showMessage("Please enter your email address.", "error");
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = "Sending...";

    try {

        const response = await fetch("<?php echo $apiUrl; ?>", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                email: email
            })

        });

        const data = await response.json();

        if (data.success) {

            showMessage(
                data.message || "Password reset link has been sent to your email.",
                "success"
            );

            form.reset();

        } else {

            showMessage(
                data.message || "Unable to process your request.",
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
        submitBtn.textContent = "Send Reset Link";
    }

});


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
