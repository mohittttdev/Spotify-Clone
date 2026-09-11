
<?php
// auth/register.php

$registerApiUrl = "../api/auth/register.php";
$loginApiUrl = "../api/auth/login.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Spotify Clone</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-xl">

            <!-- Heading -->
            <div class="text-center mb-7">

                <h1 class="text-2xl font-bold mb-2">
                    Create Account
                </h1>

                <p class="text-gray-400 text-sm">
                    Join Spotify Clone today
                </p>

            </div>

            <!-- Message -->
            <div
                id="message"
                class="hidden mb-5 p-3 rounded-lg text-sm"
            ></div>

            <!-- Register Form -->
            <form id="registerForm">

                <!-- Name -->
                <div class="mb-4">

                    <label class="block text-sm text-gray-300 mb-2">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your name"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 outline-none focus:border-green-500"
                    >

                </div>

                <!-- Email -->
                <div class="mb-4">

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

                <!-- Password -->
                <div class="mb-4">

                    <label class="block text-sm text-gray-300 mb-2">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
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
                        placeholder="Confirm password"
                        minlength="6"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 outline-none focus:border-green-500"
                    >

                </div>

                <!-- Button -->
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full bg-green-500 hover:bg-green-400 text-black font-semibold py-3 rounded-lg transition"
                >
                    Create Account
                </button>

            </form>

            <!-- Login -->
            <div class="text-center mt-6">

                <span class="text-gray-400 text-sm">
                    Already have an account?
                </span>

                <a
                    href="login.php"
                    class="text-green-400 hover:text-green-300 text-sm ml-1"
                >
                    Login
                </a>

            </div>

        </div>

    </div>


<script>

const form = document.getElementById("registerForm");
const message = document.getElementById("message");
const submitBtn = document.getElementById("submitBtn");

form.addEventListener("submit", async function(event) {

    event.preventDefault();

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword =
        document.getElementById("confirmPassword").value;

    // Validation
    if (!name || !email || !password || !confirmPassword) {

        showMessage(
            "Please fill all fields.",
            "error"
        );

        return;
    }

    if (password.length < 6) {

        showMessage(
            "Password must be at least 6 characters.",
            "error"
        );

        return;
    }

    if (password !== confirmPassword) {

        showMessage(
            "Passwords do not match.",
            "error"
        );

        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = "Creating Account...";

    try {

        // ==========================
        // STEP 1: REGISTER
        // ==========================

        const registerResponse = await fetch(
            "<?php echo $registerApiUrl; ?>",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    name: name,
                    email: email,
                    password: password
                })
            }
        );

        const registerData = await registerResponse.json();

        // Registration failed
        if (!registerData.success) {

            showMessage(
                registerData.message || "Registration failed.",
                "error"
            );

            submitBtn.disabled = false;
            submitBtn.textContent = "Create Account";

            return;
        }


        // ==========================
        // STEP 2: AUTO LOGIN
        // ==========================

        submitBtn.textContent = "Logging you in...";

        const loginResponse = await fetch(
            "<?php echo $loginApiUrl; ?>",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    email: email,
                    password: password
                })
            }
        );

        const loginData = await loginResponse.json();


        // Login failed
        if (!loginData.success) {

            showMessage(
                "Account created, but automatic login failed. Please login manually.",
                "error"
            );

            submitBtn.disabled = false;
            submitBtn.textContent = "Create Account";

            return;
        }


        // ==========================
        // STEP 3: SAVE LOGIN DATA
        // ==========================

        localStorage.setItem(
            "spotify_token",
            loginData.token
        );

        localStorage.setItem(
            "spotify_user",
            JSON.stringify(loginData.user)
        );

        // Token expiry
        if (loginData.expires_in) {

            const expiry =
                Date.now() + (loginData.expires_in * 1000);

            localStorage.setItem(
                "spotify_token_expiry",
                expiry
            );
        }


        // ==========================
        // STEP 4: REDIRECT HOME
        // ==========================

        showMessage(
            "Account created successfully! Redirecting...",
            "success"
        );

        setTimeout(() => {

            window.location.href = "../index.php";

        }, 500);


    } catch (error) {

        console.error("Registration error:", error);

        showMessage(
            "Something went wrong. Please try again.",
            "error"
        );

        submitBtn.disabled = false;
        submitBtn.textContent = "Create Account";
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
