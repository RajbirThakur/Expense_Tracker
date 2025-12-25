<?php

    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'expense_tracker';

    $conn = mysqli_connect($servername, $username, $password, $database);

    // if($conn)
    //     echo "Connected with the database";
    // else
    //     echo mysqli_error($conn);

?>