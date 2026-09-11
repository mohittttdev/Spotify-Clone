
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Spotify Clone</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="min-h-screen bg-black text-white flex items-center justify-center">


    <div class="w-full max-w-md px-6">


        <!-- Logo -->

        <div class="text-center mb-8">

            <h1 class="text-3xl font-bold">
                Spotify Clone
            </h1>

            <p class="text-gray-400 mt-2">
                Login to your account
            </p>

        </div>


        <!-- Login Card -->

        <div class="bg-zinc-900 rounded-2xl p-8 shadow-xl">


            <form id="loginForm">


                <!-- Email -->

                <div class="mb-5">

                    <label
                        for="email"
                        class="block text-sm font-medium mb-2"
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 outline-none focus:border-white"
                    >

                </div>


                <!-- Password -->

                <div class="mb-4">

                    <label
                        for="password"
                        class="block text-sm font-medium mb-2"
                    >
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 outline-none focus:border-white"
                    >

                </div>


                <!-- Forgot Password -->

                <div class="text-right mb-6">

                    <a
                        href="forgot-password.php"
                        class="text-sm text-gray-400 hover:text-white"
                    >
                        Forgot password?
                    </a>

                </div>


                <!-- Message -->

                <div
                    id="message"
                    class="hidden text-sm mb-4 p-3 rounded-lg"
                ></div>


                <!-- Login Button -->

                <button
                    type="submit"
                    id="loginButton"
                    class="w-full bg-white text-black font-semibold py-3 rounded-full hover:bg-gray-200 transition disabled:opacity-50"
                >
                    Login
                </button>


            </form>


            <!-- Register -->

            <div class="text-center mt-6 text-sm text-gray-400">

                Don't have an account?

                <a
                    href="register.php"
                    class="text-white font-semibold hover:underline"
                >
                    Sign up
                </a>

            </div>


        </div>


    </div>


<script>

const loginForm = document.getElementById("loginForm");

const loginButton = document.getElementById("loginButton");

const message = document.getElementById("message");



loginForm.addEventListener("submit", async function (event) {

    event.preventDefault();


    const email =
        document.getElementById("email").value.trim();

    const password =
        document.getElementById("password").value;


    if (!email || !password) {

        showMessage(
            "Email and password are required.",
            "error"
        );

        return;

    }


    loginButton.disabled = true;

    loginButton.innerText = "Logging in...";


    try {


        const response = await fetch(
            "../api/auth/login.php",
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


        const data = await response.json();


        console.log("Login response:", data);


        // ----------------------------------------
        // Login successful
        // ----------------------------------------

        if (data.success === true) {


            /*
             * Save JWT token
             */

            localStorage.setItem(
                "spotify_token",
                data.token
            );


            /*
             * Save user information
             */

            localStorage.setItem(
                "spotify_user",
                JSON.stringify(data.user)
            );


            /*
             * Save expiry time
             */

            if (data.expires_in) {

                const expiryTime =
                    Date.now() + (data.expires_in * 1000);

                localStorage.setItem(
                    "spotify_token_expiry",
                    expiryTime
                );

            }


            showMessage(
                data.message || "Login successful.",
                "success"
            );


            /*
             * Redirect to Spotify home
             */

            setTimeout(function () {

                window.location.href = "../index.php";

            }, 700);


        } else {


            showMessage(
                data.message || "Invalid email or password.",
                "error"
            );

        }


    } catch (error) {


        console.error(
            "Login Error:",
            error
        );


        showMessage(
            "Unable to connect to the server.",
            "error"
        );


    } finally {


        loginButton.disabled = false;

        loginButton.innerText = "Login";


    }

});



function showMessage(text, type) {


    message.className =
        "text-sm mb-4 p-3 rounded-lg";


    message.classList.remove("hidden");


    message.innerText = text;


    if (type === "success") {

        message.classList.add(
            "bg-green-900",
            "text-green-200"
        );

    } else {

        message.classList.add(
            "bg-red-900",
            "text-red-200"
        );

    }

}

</script>


</body>

</html>
