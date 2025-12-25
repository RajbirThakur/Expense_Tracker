<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar"
    class="sidebar fixed inset-y-0 left-0 transform -translate-x-full lg:relative lg:translate-x-0 w-64 bg-white z-40 flex flex-col pt-4 pb-8 overflow-y-auto hide-scrollbar">

    <div class="p-6 text-center border-b border-gray-100">
        <div class="w-20 h-20 mx-auto bg-gray-200 rounded-full flex items-center justify-center mb-3">
            <i data-lucide="user-circle-2" class="w-16 h-16 text-gray-500"></i>
        </div>
        <h2 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($user_name); ?></h2>
        <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($user_email); ?></p>
        <p class="text-xs text-gray-400 mt-1">ID: <?php echo htmlspecialchars($user_id); ?></p>
    </div>

    <nav class="flex-grow mt-4 px-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">Management</p>

        <a href="index.php"
            class="sidebar-item flex items-center space-x-3 p-3 rounded-xl
   <?php echo ($currentPage == 'index.php') ? 'sidebar-item-active text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5 text-white"></i>
            <span>Dashboard</span>
        </a>

        <a href="addExpenses.php"
            class="sidebar-item flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:text-gray-800 mt-1
            <?php echo ($currentPage == 'addExpenses.php') ? 'sidebar-item-active text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
            <i data-lucide="circle-dollar-sign" class="w-5 h-5"></i>
            <span>Add Expenses</span>
        </a>

        <a href="manageExpenses.php"
            class="sidebar-item flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:text-gray-800 mt-1
            <?php echo ($currentPage == 'manageExpenses.php') ? 'sidebar-item-active text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
            <i data-lucide="list-checks" class="w-5 h-5"></i>
            <span>Manage Expenses</span>
        </a>

        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2 px-3">Settings</p>

        <a href="profile.php"
            class="sidebar-item flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:text-gray-800
            <?php echo ($currentPage == 'profile.php') ? 'sidebar-item-active text-white' : 'text-gray-600 hover:text-gray-800'; ?>">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span>Profile</span>
        </a>

        <a href="logout.php"
            class="sidebar-item flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:text-red-500 mt-1">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>