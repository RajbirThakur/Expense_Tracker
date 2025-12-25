<?php
// This is a minimal PHP wrapper. In a real application, this is where you would handle
// session checks, database connections, and form submissions.
// For this UI example, we just include the static HTML structure.
// You can populate the user details from session variables like this:
include "dbconnect.php";

session_start();
$user_name = $_SESSION['username'];
$user_email = $_SESSION['email'];
$user_id = $_SESSION['userID'];

// Mock data for the category radio buttons
$categories = [
    'Medicine',
    'Food',
    'Bills and Recharges',
    'Entertainment',
    'Clothings',
    'Rent',
    'Household Items',
    'Others'
];

$expense_added = false;

// Handling the form
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $category = $_POST['category'];

    $sql = "INSERT INTO `transactions` (`id`, `userID`, `amount`, `date`, `category`) VALUES (NULL, '$user_id', '$amount', '$date', '$category')";
    $res = mysqli_query($conn, $sql);

    if($res){
        $expense_added = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Add Expense</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Load Lucide Icons for aesthetic icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    /* Custom styles for better aesthetics */
    :root {
        --primary-color: #16A34A;
        /* Tailwind green-600 */
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f6f8;
        /* Light gray background */
    }

    .sidebar {
        /* Ensures smooth transition for the mobile menu */
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
        /* Light hover background */
    }

    /* Hide Scrollbar for cleaner look, but allow scrolling */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    /* Focus ring for inputs */
    input:focus,
    select:focus,
    textarea:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.5) !important;
        /* Green focus ring */
    }
    </style>
</head>

<body class="flex min-h-screen">

    <!-- Mobile Menu Toggle Button (Visible on Small Screens) -->
    <button id="menu-toggle" class="fixed top-4 left-4 z-50 p-2 bg-white rounded-lg shadow-md lg:hidden">
        <i data-lucide="menu" class="w-6 h-6 text-gray-700"></i>
    </button>

    <!-- Sidebar (Left Navigation) -->
    <?php include 'nav.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-grow p-6 lg:p-10">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 pb-2 border-b-2 border-green-500/50">
                Add Your Daily Expenses
            </h1>

            <!-- Expense Form Container -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl">
                <form action="addExpenses.php" method="POST" class="space-y-6">

                    <!-- Amount Field -->
                    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                        <label for="amount" class="text-gray-700 font-medium md:col-span-1">Enter Amount</label>
                        <div class="md:col-span-2">
                            <input type="number" id="amount" name="amount" placeholder="e.g., 160.00"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-1 focus:ring-green-500"
                                required min="0.01" step="0.01">
                        </div>
                    </div>

                    <!-- Date Field -->
                    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                        <label for="date" class="text-gray-700 font-medium md:col-span-1">Date</label>
                        <div class="md:col-span-2">
                            <input type="date" id="date" name="date"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-1 focus:ring-green-500"
                                required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Category Field (Radio Buttons) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 items-start gap-4 pt-4">
                        <label class="text-gray-700 font-medium md:col-span-1">Category</label>
                        <div class="md:col-span-2 space-y-3">
                            <?php foreach ($categories as $index => $category): ?>
                            <div class="flex items-center">
                                <input id="category-<?php echo $index; ?>" name="category" type="radio"
                                    value="<?php echo htmlspecialchars($category); ?>"
                                    class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500"
                                    <?php echo ($index === 0) ? 'checked' : ''; ?>>
                                <label for="category-<?php echo $index; ?>"
                                    class="ml-3 text-sm font-medium text-gray-700 cursor-pointer">
                                    <?php echo htmlspecialchars($category); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Description/Notes Field (Optional) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 items-start gap-4">
                        <label for="notes" class="text-gray-700 font-medium md:col-span-1">Notes (Optional)</label>
                        <div class="md:col-span-2">
                            <textarea id="notes" name="notes" rows="3"
                                placeholder="Add a short description of the expense..."
                                class="w-full p-3 border border-gray-300 rounded-xl focus:border-green-500 focus:ring-1 focus:ring-green-500"></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="md:col-span-3 pt-6 flex justify-center">
                        <button type="submit"
                            class="w-full md:w-auto px-10 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl 
                                transition duration-300 ease-in-out transform hover:scale-105 shadow-lg shadow-green-500/50">
                            Add Expense
                        </button>
                    </div>
                </form>
            </div>

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

    // Add a backdrop for mobile view
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

    // Optional: Close sidebar on link click in mobile view
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) { // Check if screen is less than lg breakpoint
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        });
    });

    // Function to display messages (in case you need to use it later instead of alert())
    // function showMessage(text, type = 'success') {
    //     const box = document.getElementById('message-box');
    //     const textElement = document.getElementById('message-text');

    //     let bgColor, borderColor, textColor;
    //     if (type === 'success') {
    //         bgColor = 'bg-green-100';
    //         borderColor = 'border-green-400';
    //         textColor = 'text-green-700';
    //     } else if (type === 'error') {
    //         bgColor = 'bg-red-100';
    //         borderColor = 'border-red-400';
    //         textColor = 'text-red-700';
    //     } else {
    //         bgColor = 'bg-blue-100';
    //         borderColor = 'border-blue-400';
    //         textColor = 'text-blue-700';
    //     }

    //     box.className = `fixed bottom-5 right-5 w-80 px-4 py-3 rounded-lg shadow-xl transition-opacity duration-300 ${bgColor} ${borderColor} ${textColor}`;
    //     textElement.textContent = text;
    //     box.classList.remove('hidden');

    //     setTimeout(() => {
    //         box.classList.add('hidden');
    //     }, 3000);
    // }

    // // Example: showMessage('Expense added successfully!', 'success');
    </script>
    <script>
    function showToast(message, type = 'success') {
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

    <?php if ($expense_added): ?>
    showToast("Expense added successfully!");
    <?php endif; ?>
    </script>


</body>

</html>