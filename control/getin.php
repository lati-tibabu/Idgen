<?php
include("../config.php"); // Include your database connection

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number_input = $_POST["id_no"];
    $id_number = strtolower(trim($id_number_input));
    $id_passkey = $_POST["passkey"];

    // Create a prepared statement
    $query = "SELECT * FROM id_info WHERE id_number = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        die("Error: " . mysqli_error($con));
    }

    // Bind parameters and execute the statement
    mysqli_stmt_bind_param($stmt, "s", $id_number);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($id_passkey, $row['id_passkey'])) {
            // Credentials are valid
            // You can perform actions here, e.g., redirect the user or display a success message
            $_SESSION['id_number'] = $row['id_number'];

            header("Location: ../app.php?id_no={$id_number}");
            exit();
        }
    }

    // Authentication failed (invalid credentials)
    // header("Location: ../login.php?error=1"); // Redirect with an error code
    // echo "error login";
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '    <meta charset="UTF-8">';
    echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '    <title>Invalid Credentials</title>';
    echo '    <style>';
    echo '        body {';
    echo '            font-family: Arial, sans-serif;';
    echo '            text-align: center;';
    echo '            background-color: #f0f0f0;';
    echo '            margin: 0;';
    echo '            padding: 0;';
    echo '        }';
    echo '';
    echo '        .container {';
    echo '            max-width: 600px;';
    echo '            margin: 0 auto;';
    echo '            padding: 20px;';
    echo '            background-color: #ffffff;';
    echo '            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);';
    echo '            border-radius: 5px;';
    echo '            margin-top: 50px;';
    echo '        }';
    echo '';
    echo '        h1 {';
    echo '            font-size: 24px;';
    echo '            color: #333;';
    echo '            margin: 20px 0;';
    echo '        }';
    echo '';
    echo '        .message {';
    echo '            font-size: 24px;';
    echo '            margin-top: 20px;';
    echo '        }';
    echo '';
    echo '        .forgot-password {';
    echo '            margin-top: 20px;';
    echo '        }';
    echo '';
    echo '        .back-button {';
    echo '            margin-top: 20px;';
    echo '        }';
    echo '';
    echo '        .back-button a, .forgot-password a {';
    echo '            display: inline-block;';
    echo '            padding: 10px 20px;';
    echo '            background-color: #007bff;';
    echo '            color: #fff;';
    echo '            text-decoration: none;';
    echo '            border-radius: 5px;';
    echo '            transition: background-color 0.3s;';
    echo '            margin-right: 10px;';
    echo '        }';
    echo '';
    echo '        .back-button a:hover, .forgot-password a:hover {';
    echo '            background-color: #0056b3;';
    echo '        }';
    echo '    </style>';
    echo '</head>';
    echo '<body>';
    echo '    <div class="container">';
    echo '        <h1>Invalid Credentials</h1>';
    echo '        <p class="message">The ID Number and Passkey you entered are incorrect.</p>';
    echo '        <div class="back-button">';
    echo '            <a href="#" onclick="history.back()">Back</a>';
    echo '        </div>';
    echo '    </div>';
    echo '</body>';
    echo '</html>';
    exit();
}

// Close the database connection
mysqli_close($con);
