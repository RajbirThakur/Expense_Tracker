<?php
// PHP SETUP AND MOCK DATA
// In a real application, you would fetch this data from your 'transactions' table.

session_start();
if(!isset($_SESSION['loggedIn'])){
    header("location: login.php");
}

include 'dbconnect.php';

// Mock User Data (Replace with your actual session data)
$user_name = $_SESSION['username'] ?? 'Liam Moore';
$user_email = $_SESSION['email'] ?? 'liamoore@gmail.com';
$user_id = $_SESSION['userID'];


// 1. Yearly Expenses (Line Chart)
$yearly_labels = [];
$yearly_data = [];
$current_year = date('Y');
$sql = "SELECT 
        DATE_FORMAT(date, '%b') AS month_name, 
        SUM(amount) AS monthly_spent
        FROM `transactions` 
        WHERE `userID` = '$user_id' AND YEAR(date) = '$current_year'
        GROUP BY month_name, MONTH(date)
        ORDER BY MONTH(date) ASC";
$res = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($res)){
    $yearly_labels[] = $row['month_name'];
    $yearly_data[] = $row['monthly_spent']; // Example expense amounts
}        

// 2. Expense Category (Bar Chart) - Example data for categories
$category_labels = [];
$category_data = []; // Example total spent in each category
$category_colors = ['#EF4444', '#3B82F6', '#10B981', '#F59E0B', '#6366F1', '#EC4899', '#A855F7', '#06B6D4'];
$sql = "SELECT category, SUM(amount) AS total_spent 
        FROM `transactions`
        WHERE `userID` = '$user_id'
        GROUP BY category 
        ORDER BY total_spent DESC";
$res = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($res)){
    $category_labels[] = $row['category'];
    $category_data[] = $row['total_spent']; // Example expense amounts
}  


/*
* --------------------------------------------------------------------------
* HOW TO REPLACE MOCK DATA WITH DATABASE DATA:
* --------------------------------------------------------------------------
* * 1. **Connect to DB and Fetch Data:**
* require 'dbconnect.php'; // Your database connection file
* // Example SQL for Yearly Data (requires more complex grouping)
* // For simplicity, let's assume you fetch an array of results for each chart.
*
* 2. **Process Data for Chart.js:**
* // Example for Category Data (replace this with your actual DB loop)
* $db_category_labels = [];
* $db_category_data = [];
* // while ($row = mysqli_fetch_assoc($result_categories)) {
* //     $db_category_labels[] = $row['category_name'];
* //     $db_category_data[] = $row['total_spent'];
* // }
*
* 3. **Encode to JSON:**
* // $yearly_labels = json_encode($db_yearly_labels);
* // $yearly_data = json_encode($db_yearly_data);
* // $category_labels = json_encode($db_category_labels);
* // $category_data = json_encode($db_category_data);
*/

// Encode mock data for Chart.js (keep this if you haven't replaced it)
$yearly_labels_json = json_encode($yearly_labels);
$yearly_data_json = json_encode($yearly_data);
$category_labels_json = json_encode($category_labels);
$category_data_json = json_encode($category_data);
$category_colors_json = json_encode($category_colors);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

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
    </style>
</head>

<body class="flex min-h-screen">

    <button id="menu-toggle" class="fixed top-4 left-4 z-50 p-2 bg-white rounded-lg shadow-md lg:hidden">
        <i data-lucide="menu" class="w-6 h-6 text-gray-700"></i>
    </button>

    <?php include 'nav.php'; ?>

    <main class="flex-grow p-6 lg:p-10">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 pb-2 border-b-2 border-green-500/50">
                Dashboard
            </h1>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">

                <a href="addExpenses.php"
                    class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition duration-300 text-center flex flex-col items-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-3">
                        <i data-lucide="plus-circle" class="w-8 h-8 text-green-600"></i>
                    </div>
                    <span class="text-lg font-semibold text-gray-800">Add Expenses</span>
                </a>

                <a href="manageExpenses.php"
                    class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition duration-300 text-center flex flex-col items-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3">
                        <i data-lucide="receipt-text" class="w-8 h-8 text-blue-600"></i>
                    </div>
                    <span class="text-lg font-semibold text-gray-800">Manage Expenses</span>
                </a>

                <a href="profile.php"
                    class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition duration-300 text-center flex flex-col items-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-3">
                        <i data-lucide="user" class="w-8 h-8 text-purple-600"></i>
                    </div>
                    <span class="text-lg font-semibold text-gray-800">User Profile</span>
                </a>
            </div>

            <h2 class="text-2xl font-bold text-gray-700 mb-6 border-b border-gray-300 pb-2">Full-Expense Report</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Expense by Month (Whole Year)
                    </h3>
                    <div class="h-80">
                        <canvas id="yearlyExpensesChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Expense by Category</h3>
                    <div class="h-80">
                        <canvas id="categoryExpensesChart"></canvas>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script>
    // Initialize Lucide icons
    lucide.createIcons();

    // --- Mobile Sidebar Toggle Logic (copied from the other file for consistency) ---
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

    // --- CHART.JS CONFIGURATION ---

    // PHP data injected into JavaScript
    const yearlyLabels = <?php echo $yearly_labels_json; ?>;
    const yearlyData = <?php echo $yearly_data_json; ?>;
    const categoryLabels = <?php echo $category_labels_json; ?>;
    const categoryData = <?php echo $category_data_json; ?>;
    const categoryColors = <?php echo $category_colors_json; ?>;

    // 1. Yearly Expenses Chart (Line Chart)
    const yearlyCtx = document.getElementById('yearlyExpensesChart').getContext('2d');
    new Chart(yearlyCtx, {
        type: 'line',
        data: {
            labels: yearlyLabels,
            datasets: [{
                label: 'Expense Amount',
                data: yearlyData,
                borderColor: 'rgb(22, 163, 74)', // Primary green color
                backgroundColor: 'rgba(22, 163, 74, 0.2)',
                tension: 0.3, // Makes the line smooth
                borderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: 'Total Spending Over Time',
                    padding: {
                        top: 10,
                        bottom: 10
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount'
                    }
                }
            }
        }
    });

    // 2. Expense Category Chart (Bar Chart)
    const categoryCtx = document.getElementById('categoryExpensesChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Total Spent',
                data: categoryData,
                backgroundColor: categoryColors, // Uses the colors defined in PHP
                borderColor: 'rgba(0, 0, 0, 0.1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
    </script>
</body>

</html>
<!-- ```eof

### Key Features of this Dashboard

* **Aesthetic & Consistent UI:** It uses the same sidebar, profile look, and green color scheme as your "Add Expenses"
interface.
* **Action Cards:** The top section features three clear action cards for quick navigation, as seen in your reference
image.
* **Data Integration Ready:** The PHP section includes **mock data** and comments on where and how to integrate your
actual database results using `json_encode()` to safely pass the data to Chart.js.
* **Chart.js Implementation:**
* **Line Chart (`yearlyExpensesChart`):** Plots expense amounts over months for a clean trend view.
* **Bar Chart (`categoryExpensesChart`):** Clearly visualizes total spending broken down by expense category.
* **Responsive Design:** The two charts sit side-by-side on large screens but stack vertically on mobile screens. -->