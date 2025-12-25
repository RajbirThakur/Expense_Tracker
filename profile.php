<?php
// PHP SETUP
session_start();

// Check if the user is logged in. If not, redirect them to the login page.
if(!isset($_SESSION['loggedIn'])){
    header("location: login.php");
    exit(); 
}

// ----------------------------------------------------------------------
// 1. DATABASE CONNECTION
// ----------------------------------------------------------------------
include 'dbconnect.php'; 

// ----------------------------------------------------------------------
// 2. USER DATA AND AUTHENTICATION (SECURE FETCH)
// ----------------------------------------------------------------------
// The user ID must come from the session for security.
$user_id = $_SESSION['userID'];
$user_name = $_SESSION['username'];
$user_email = $_SESSION['email'];

$MailUpdation = false;
$PasswordUpdation = false;
$AccountDeletion = false;
$PassNotMatching = false;
$WrongPass = false;

if($_SERVER['REQUEST_METHOD'] == "POST"){

    // Handling the mail updation form
    if($_POST['UpdateDetails'] == 'UpdateMail'){

        $name = $_POST['username'];
        $email = $_POST['email'];
        $sql = "UPDATE `users` SET `Username` = '$name', `Email` = '$email' WHERE `id` = '$user_id'";
    
        $res = mysqli_query($conn, $sql);
        if($res){
            $MailUpdation = true;
        }
    }

    // Handling the password updation form
    if($_POST['UpdateDetails'] == 'UpdatePassword'){

        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        $sql = "SELECT * FROM `users` WHERE `Email`='$user_email'";
        $res = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($res)){
            $passVerification = password_verify($currentPassword, $row['Password']);
            if($passVerification){

                if($newPassword == $confirmPassword){

                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $sql1 = "UPDATE `users` SET `Password` = '$hash' WHERE `id` = '$user_id'";
    
                    $res1 = mysqli_query($conn, $sql1);
                    if($res1){
                        $PasswordUpdation = true;
                    }

                }
                else{
                    // echo "new and confirm password is not same";
                    $PassNotMatching = true;
                }

            }
            else{
                // echo "Wrong password";
                $WrongPass = true;
            }
        }

    }

    // Handling the Account Deletion form
    if($_POST['UpdateDetails'] == 'DeleteAccount'){

        $sqlD = "DELETE FROM `users` WHERE `Email` = '$user_email'";
        $resD = mysqli_query($conn, $sqlD);

        if($resD){
            $AccountDeletion = true;
            session_unset();
            session_destroy();
            header("location: login.php");
            exit;
        }

    }

}


// Fetch full user details from the database using a prepared statement
$user_details = [];
$sql_user = "SELECT id, Username, Email, date FROM `users` WHERE `id` = ?";

if (isset($conn) && $stmt = $conn->prepare($sql_user)) {
    // Assuming 'id' is an integer type (i)
    $stmt->bind_param("i", $user_id); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user_details = $result->fetch_assoc();
        
        // Overwrite defaults with fetched data
        $user_name = htmlspecialchars($user_details['Username']);
        $user_email = htmlspecialchars($user_details['Email']);
        $member_since = htmlspecialchars(substr($user_details['date'], 0, 10)); // Use 'date' column from users table
    } else {
        $member_since = 'Unknown';
    }
    $stmt->close();
    mysqli_close($conn); // Close connection after fetching data
} else {
    $member_since = 'N/A (DB Error)';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Profile</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
    /* Custom styles for consistency and aesthetics */
    :root {
        --primary-color: #16A34A;
        /* Tailwind green-600 */
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f6f8;
    }

    /* Standard Sidebar/Nav styles */
    .sidebar {
        transition: transform 0.3s ease-in-out;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    }

    .sidebar-item-active {
        background-color: var(--primary-color);
        color: white !important;
        font-weight: 600;
    }

    .sidebar-item-active svg {
        color: white !important;
    }

    .sidebar-item:hover:not(.sidebar-item-active) {
        background-color: #E5E7EB;
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Input focus style matching green theme */
    .form-input:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.5) !important;
    }
    </style>
</head>

<body class="flex min-h-screen">

    <button id="menu-toggle" class="fixed top-4 left-4 z-50 p-2 bg-white rounded-lg shadow-md lg:hidden">
        <i data-lucide="menu" class="w-6 h-6 text-gray-700"></i>
    </button>

    <?php include 'nav.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-grow p-6 lg:p-10">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 pb-2 border-b-2 border-green-500/50">
                User Profile & Settings
            </h1>

            <!-- Profile Overview Card -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl mb-10">
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-8">

                    <!-- Avatar Area -->
                    <div class="flex-shrink-0 text-center">
                        <div
                            class="w-24 h-24 mx-auto bg-green-100 rounded-full flex items-center justify-center border-4 border-green-300">
                            <i data-lucide="user" class="w-16 h-16 text-green-600"></i>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Member Since: <?php echo $member_since; ?></p>
                    </div>

                    <!-- Details -->
                    <div class="flex-grow space-y-2 text-center md:text-left">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Account Information</p>
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center justify-center md:justify-start">
                            <i data-lucide="at-sign" class="w-5 h-5 mr-2 text-green-600"></i> <?php echo $user_name; ?>
                        </h2>
                        <p class="text-md text-gray-600 flex items-center justify-center md:justify-start">
                            <i data-lucide="mail" class="w-4 h-4 mr-2 text-gray-500"></i> <?php echo $user_email; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Settings Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Card 1: Update Username/Email -->
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <div class="flex items-center space-x-3 mb-4 border-b pb-2">
                        <i data-lucide="pencil" class="w-6 h-6 text-blue-500"></i>
                        <h3 class="text-xl font-semibold text-gray-800">Edit Profile</h3>
                    </div>

                    <form action="profile.php" method="POST" class="space-y-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo $user_name; ?>"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-lg form-input" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo $user_email; ?>"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-lg form-input" required>
                        </div>
                        <input type="hidden" name="UpdateDetails" value="UpdateMail">
                        <button type="submit"
                            class="w-full py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition duration-150 mt-2">
                            Save Profile Changes
                        </button>
                    </form>
                </div>

                <!-- Card 2: Change Password -->
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <div class="flex items-center space-x-3 mb-4 border-b pb-2">
                        <i data-lucide="lock" class="w-6 h-6 text-red-500"></i>
                        <h3 class="text-xl font-semibold text-gray-800">Change Password</h3>
                    </div>

                    <form action="profile.php" method="POST" class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current
                                Password</label>
                            <input type="password" id="current_password" name="current_password"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-lg form-input" required>
                        </div>
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">New
                                Password</label>
                            <input type="password" id="new_password" name="new_password"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-lg form-input" required>
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New
                                Password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-lg form-input" required>
                        </div>
                        <input type="hidden" name="UpdateDetails" value="UpdatePassword">
                        <button type="submit"
                            class="w-full py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition duration-150 mt-2">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Optional: Delete Account Button -->
            <form action="profile.php" method="POST"
                onsubmit="return confirmDeleteAccount()">
                <div
                    class="mt-10 p-6 bg-red-50 border border-red-200 rounded-2xl shadow-md flex justify-between items-center">
                    <p class="font-medium text-red-700">Danger Zone: Delete Account</p>
                    <input type="hidden" name="UpdateDetails" value="DeleteAccount">
                    <button
                        class="py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition duration-150">
                        Delete Account
                    </button>
                </div>
            </form>

            <!-- Hidden message box for alerts (instead of alert()) -->
            <div id="message-box" class="fixed top-6 left-1/2 -translate-x-1/2 w-96 
            bg-green-100 border border-green-400 text-green-700 
            px-4 py-3 rounded-xl shadow-xl hidden z-50" role="alert">
                <p id="message-text" class="font-medium text-center"></p>
            </div>
        </div>
    </main>

    <script>
    // Initialize Lucide icons
    lucide.createIcons();

    // --- Mobile Sidebar Toggle Logic ---
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.createElement('div');
    backdrop.id = 'sidebar-backdrop';
    backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden';
    document.body.appendChild(backdrop);

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden');
    });
    backdrop.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    });

    function confirmDeleteAccount() {
        // Using window.confirm() for simplicity, replace with a custom modal for production.
        const confirmation = window.confirm(`Are you sure you want to delete your account ?`);

        // NOTE: In a real-world Canvas app, replace window.confirm() with a custom modal UI.
        if (confirmation) {
            // Submit the form
            return true;
        } else {
            // Prevent form submission
            return false;
        }
    }
    </script>
    <script>
    function showToast(message, type) {
        const box = document.getElementById('message-box');
        const text = document.getElementById('message-text');

        text.textContent = message;

        box.classList.remove('hidden');
        box.classList.add('opacity-0', '-translate-y-4');

        box.className = `fixed top-6 left-1/2 -translate-x-1/2 w-96 px-4 py-3 
                         rounded-xl shadow-xl z-50
                         transition-all duration-300 ease-in-out
                         ${type === 'success'
                            ? 'bg-green-100 border-green-400 text-green-700'
                            : 'bg-red-100 border-red-400 text-red-700'}`;

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

    <?php if ($MailUpdation): ?>
    showToast("Mail updated successfully!", 'success');
    <?php endif; ?>

    <?php if ($PasswordUpdation): ?>
    showToast("Password updated successfully!", 'success');
    <?php endif; ?>

    <?php if ($AccountDeletion): ?>
    showToast("Account Deleted successfully!", 'success');
    <?php endif; ?>

    <?php if ($PassNotMatching): ?>
    showToast("Your Passwords do not match!", 'danger');
    <?php endif; ?>

    <?php if ($WrongPass): ?>
    showToast("Wrong password entered!", 'danger');
    <?php endif; ?>
    </script>
</body>

</html>