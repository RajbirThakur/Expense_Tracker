<?php
        include "dbconnect.php";

        $InternalProblem = false;
        $ExternalProblem = false;
        $message = "";

        if($_SERVER['REQUEST_METHOD'] == "POST"){
            $email = $_POST['email'];
            $password = $_POST['password'];

            $sql = "SELECT * FROM `users` WHERE `Email`='$email'";
            $res = mysqli_query($conn, $sql);
            $rowsAffected = mysqli_num_rows($res);

            if($rowsAffected == 1){
                while($row = mysqli_fetch_assoc($res)){
                    $passVerification = password_verify($password, $row['Password']);
                    if($passVerification){
                        session_start();
                        $_SESSION['loggedIn'] = true;
                        $_SESSION['username'] = $row['Username'];
                        $_SESSION['email'] = $row['Email'];
                        $_SESSION['userID'] = $row['id'];
                        header('location: index.php');
                        exit();
                    }
                    else{
                        // echo "Wrong password";
                        $InternalProblem = true;
                        $message = "Wrong password, Please try again!";
                    }
                }
            }
            else{
                // echo "No such user exists";
                $InternalProblem = true;
                $message = "No such user exists, please Sign Up first !!";
            }
        }
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker Login</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Load Lucide Icons for aesthetic enhancement -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    /* Custom styles for the visual half */
    .visual-side {
        background: linear-gradient(135deg, #0f4c75 0%, #3282b8 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow: hidden;
        position: relative;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .visual-side {
            display: none;
            /* Hide the image side on mobile for better usability */
        }
    }

    /* Adjusting Bootstrap's default form control rounding to match Tailwind's aesthetic */
    .form-control,
    .btn {
        border-radius: 0.5rem !important;
        /* Tailwind's rounded-lg */
    }

    .login-card {
        max-width: 400px;
    }
    </style>
</head>

<body>

    <!-- Main Container: Full viewport height, using Bootstrap grid and flex utilities -->
    <div class="container-fluid vh-100 d-flex justify-content-center align-items-center p-0">
        <div class="row w-100 h-100 m-0 shadow-2xl overflow-hidden">

            <!-- Left Half: Themed Visual (Hidden on Mobile) -->
            <div class="col-md-6 visual-side">
                <div class="p-5">
                    <!-- Icon placeholder using Lucide Icons -->
                    <i data-lucide="line-chart" class="w-20 h-20 mb-4 text-white mx-auto"></i>
                    <h1 class="text-4xl font-bold mb-3">Track Your Riches</h1>
                    <p class="text-xl opacity-80">
                        "Financial freedom starts here. Visualize your savings and manage your flow."
                    </p>
                    <div class="mt-8 text-sm opacity-70">
                        Securely Powered by BudgetBuddy
                    </div>
                </div>
            </div>

            <!-- Right Half: Login Form -->
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-gray-50">
                <div class="login-card p-4 p-md-5 w-full bg-white rounded-xl shadow-lg border border-gray-200">
                    <h2 class="text-3xl font-semibold text-gray-800 mb-2">Welcome Back!</h2>
                    <p class="text-gray-500 mb-6">Sign in to access your dashboard.</p>

                    <form action="login.php" method="POST">
                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label text-sm font-medium text-gray-700">Email
                                address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-gray-100 border-gray-300"><i data-lucide="mail"
                                        class="w-4 h-4 text-gray-500"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="name@example.com" required>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-5">
                            <label for="password" class="form-label text-sm font-medium text-gray-700">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-gray-100 border-gray-300"><i data-lucide="lock"
                                        class="w-4 h-4 text-gray-500"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="••••••••" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="btn btn-primary w-full py-2.5 shadow-md hover:shadow-lg transition duration-200">
                            Secure Login
                        </button>

                        <p class="text-center text-sm text-gray-500 mt-4">
                            Don't have an account? <a href="signup.php"
                                class="text-blue-600 hover:text-blue-700 font-medium">Sign Up</a>
                        </p>

                    </form>
                </div>
            </div>
            <!-- Hidden message box for alerts (instead of alert()) -->
            <div id="message-box" class="fixed top-6 left-1/2 -translate-x-1/2 w-96 
            bg-green-100 border border-green-400 text-green-700 
            px-4 py-3 rounded-xl shadow-xl hidden z-50" role="alert">
                <p id="message-text" class="font-medium text-center"></p>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS (required for some components, though not strictly needed here) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
    // Initialize Lucide Icons
    lucide.createIcons();
    </script>

    <script>
    function showToast(message, type = 'danger') {
        const box = document.getElementById('message-box');
        const text = document.getElementById('message-text');

        text.textContent = message;

        box.classList.remove('hidden');
        box.classList.add('opacity-0', '-translate-y-4');

        box.className = `fixed top-6 left-1/2 -translate-x-1/2 w-96 px-4 py-3 
                         rounded-xl shadow-xl z-50
                         transition-all duration-300 ease-in-out
                         ${type === 'danger'
                            ? 'bg-red-100 border-red-400 text-red-700'
                            : 'bg-green-100 border-green-400 text-green-700'}`;

        // Animate in
        setTimeout(() => {
            box.classList.remove('opacity-0', '-translate-y-4');
            box.classList.add('opacity-100', 'translate-y-0');
        }, 50);

        // Animate out
        setTimeout(() => {
            box.classList.remove('opacity-100');
            box.classList.add('opacity-0', '-translate-y-4');

            setTimeout(() => {
                box.classList.add('hidden');
            }, 300);
        }, 3000);
    }

    <?php if ($InternalProblem): ?>
    showToast("<?php echo $message; ?>");
    <?php endif; ?>

    <?php if ($ExternalProblem): ?>
    showToast("<?php echo $message; ?>");
    <?php endif; ?>
    </script>
</body>

</html>