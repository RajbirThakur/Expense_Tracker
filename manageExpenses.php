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

$UpdateTransaction = false;
$DeleteTransaction = false;

// Handling the forms :
if($_SERVER['REQUEST_METHOD'] == "POST"){
    if(isset($_POST['transactionID'])){
        $amount = $_POST['amount'];
        $date = $_POST['date'];
        $category = $_POST['category'];
        $transactionID = $_POST['transactionID'];
    
        $sql = "UPDATE `transactions` SET `amount` = '$amount', `category` = '$category', `date` = '$date' WHERE `id` = '$transactionID' and `userID` = '$user_id' ";
    
        $res = mysqli_query($conn, $sql);
        if($res){
            $UpdateTransaction = true;
        }
    }
    if(isset($_POST['DeleteTransaction_id'])){
        $transactionID = $_POST['DeleteTransaction_id'];

        $sql = "DELETE FROM `transactions` WHERE `id` = '$transactionID' AND `userID` = '$user_id'";

        $res = mysqli_query($conn, $sql);
        if($res){
            $DeleteTransaction = true;
        }
    }

}

// ----------------------------------------------------------------------
// 3. DATA FETCHING (Using Prepared Statements)
// ----------------------------------------------------------------------

// SQL: Fetch all transactions for the currently logged-in user, ordered by date descending.
$sql_transactions = "
    SELECT id, amount, date, category 
    FROM `transactions` 
    WHERE `userID` = '$user_id'
    ORDER BY date DESC
";
$res = mysqli_query($conn, $sql_transactions);

$transactions = [];
$total_expenses = 0;

while($row = mysqli_fetch_assoc($res)){
    $transactions[] = $row;
    $total_expenses+=$row['amount'];
}

$categories = ['Bills & Recharges', 'Entertainment', 'Food', 'Household Items', 'Rent', 'Medicine', 'Clothings'];

// NOTE: Since we are not connected to the live environment, we will mock data
// if the database connection fails, to ensure the UI preview works.
// $mock_transactions = [
//     ['id' => 1, 'amount' => 120, 'date' => '2025-10-20 00:00:00', 'category' => 'Food'],
//     ['id' => 2, 'amount' => 450, 'date' => '2025-10-19 00:00:00', 'category' => 'Rent'],
//     ['id' => 3, 'amount' => 130, 'date' => '2025-10-18 00:00:00', 'category' => 'Bills & Recharges'],
//     ['id' => 4, 'amount' => 320, 'date' => '2025-10-17 00:00:00', 'category' => 'Entertainment'],
//     ['id' => 5, 'amount' => 180, 'date' => '2025-10-16 00:00:00', 'category' => 'Household Items'],
// ];


// // Attempt to fetch real data
// if (isset($conn) && $stmt = $conn->prepare($sql_transactions)) {
//     // Bind the user ID (i = integer type)
//     $stmt->bind_param("i", $user_id);
    
//     // Execute and get results
//     $stmt->execute();
//     $result = $stmt->get_result();

//     if ($result && $result->num_rows > 0) {
//         while ($row = $result->fetch_assoc()) {
//             $transactions[] = $row;
//             $total_expenses += $row['amount'];
//         }
//     } else {
//         // Use mock data if no real data found
//         $transactions = $mock_transactions;
//         $total_expenses = array_sum(array_column($mock_transactions, 'amount'));
//     }
//     $stmt->close();
//     mysqli_close($conn);
// } else {
//     // Fallback to mock data if connection or prepared statement setup fails
//     $transactions = $mock_transactions;
//     $total_expenses = array_sum(array_column($mock_transactions, 'amount'));
//     if (isset($conn)) mysqli_close($conn);
// }


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Manage Expenses</title>
    <!-- Load Bootstrap CSS (needed for Modal styling/functionality) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

    /* Custom table style for better horizontal scrolling on mobile */
    .table-container {
        overflow-x: auto;
    }

    /* Tailwind styles for Bootstrap lookalike button in the table */
    .btn-edit-tailwind {
        background-color: #0d6efd;
        /* Bootstrap primary blue */
        border-color: #0d6efd;
    }

    .btn-edit-tailwind:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }

    /* Ensure Bootstrap Modal form elements look nice with Inter font */
    .modal-content {
        font-family: 'Inter', sans-serif;
        border-radius: 1rem;
        /* Rounded corners */
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        /* Match input styling */
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
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 pb-2 border-b-2 border-green-500/50">
                Manage Expenses
            </h1>

            <!-- Summary Card -->
            <div class="bg-white p-6 rounded-xl shadow-lg mb-8 inline-block">
                <p class="text-lg font-medium text-gray-700">
                    Total Expenses Recorded:
                    <span
                        class="text-2xl font-bold text-red-500"><?php echo number_format($total_expenses, 2); ?></span>
                </p>
            </div>


            <!-- Expense Table Container -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xl table-container">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                #
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Expense Category
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (count($transactions) > 0): ?>
                        <?php $row_index = 1; ?>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo $row_index++; ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                <!-- Display only the date part, trimming the time from DATETIME -->
                                <?php echo htmlspecialchars(substr($transaction['date'], 0, 10)); ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-red-500">
                                <?php echo htmlspecialchars(number_format($transaction['amount'], 2)); ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo htmlspecialchars($transaction['category']); ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">


                                <!-- Edit Button with Modal Attributes -->
                                <button type="button" onclick="handleedit(<?php echo $transaction['id'] ?>)"
                                    data-bs-toggle="modal" data-bs-target="#editExpenseModal"
                                    class="btn-edit-tailwind inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white transition duration-150 mr-2">
                                    <i data-lucide="edit-3" class="w-4 h-4 mr-1"></i> Edit
                                </button>


                                <!-- Delete Button (using form for secure deletion) -->
                                <form method="POST" action="manageExpenses.php"
                                    class="inline-block"
                                    onsubmit="return confirmDelete(<?php echo ($row_index - 1); ?>);">
                                    <input type="hidden" name="DeleteTransaction_id"
                                        value="<?php echo $transaction['id'] ?>">
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 transition duration-150">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                                    </button>
                                </form>


                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-lg text-gray-500">
                                No expenses recorded yet. Start adding some!
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Hidden message box for alerts (instead of alert()) -->
            <div id="message-box" class="fixed top-6 left-1/2 -translate-x-1/2 w-96 
            bg-green-100 border border-green-400 text-green-700 
            px-4 py-3 rounded-xl shadow-xl hidden z-50" role="alert">
                <p id="message-text" class="font-medium text-center"></p>
            </div>
        </div>
    </main>

    <!-- EDIT EXPENSE MODAL (Bootstrap Style) -->
    <div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-green-600 text-white border-b-0 rounded-t-xl">
                    <h5 class="modal-title font-semibold" style="font-size: larger;" id="editExpenseModalLabel">Edit
                        Transaction<span id="modal-transaction-id"></span></h5>
                    <button type="button" class="btn-close text-white opacity-100" data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <form id="editExpenseForm" action="manageExpenses.php" method="POST">
                    <div class="modal-body space-y-4">

                        <!-- Amount Field -->
                        <div>
                            <label for="edit-amount" class="form-label font-medium text-gray-700">Amount</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="edit-amount"
                                name="amount" required>
                        </div>

                        <!-- Date Field -->
                        <div>
                            <label for="edit-date" class="form-label font-medium text-gray-700">Date</label>
                            <input type="date" class="form-control" id="edit-date" name="date" required>
                        </div>

                        <!-- Category Field -->
                        <div>
                            <label for="edit-category" class="form-label font-medium text-gray-700">Category</label>
                            <select class="form-select" id="edit-category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                                    <?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="transactionID" id="edit-id">

                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 border-blue-600">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END EDIT EXPENSE MODAL -->
    <!-- Load Bootstrap JS (required for modal functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

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

    // Handling the edit
    function handleedit(id) {
        document.getElementById('edit-id').value = id;
    }


    // Custom modal replacement for confirmation (as alert() and confirm() are forbidden)
    function confirmDelete(id) {
        // For a production app, you would use a custom Tailwind modal here.
        // Since custom modals require significantly more code, we will use a temporary
        // message box function (which you must replace with a proper modal later).

        const confirmation = window.confirm(`Are you sure you want to delete Transaction ID ${id}?`);

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

    <?php if ($UpdateTransaction): ?>
    showToast("Transaction updated successfully!");
    <?php endif; ?>

    <?php if ($DeleteTransaction): ?>
    showToast("Deleted transaction successfully!");
    <?php endif; ?>
    </script>

</body>

</html>