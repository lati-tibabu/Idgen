<?php

include("../config.php");

require_once('vendor/autoload.php');

use Picqer\Barcode\BarcodeGeneratorPNG;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Create a new TCPDF instance
$pdf = new TCPDF();

// if (isset($_POST['submit'])) {
$name = $_POST["name"];
$email = $_POST["email"];
$id_number_input = $_POST["id_no"];

$id_number = strtolower(trim($id_number_input));
$gender = $_POST["gender"];
$e_year = $_POST["e_year"];
$department = $_POST["department"];

// Image renaming and uploading to the server 
$imageDirectory = __DIR__ . '/../storage/id2023/'; // Adjust the path as needed
$imageFileName = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$destination = $imageDirectory . $imageFileName;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $destination)) {
    exit("Can't move the uploaded file");
}

// Generating a passkey and hashing it
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*';
$passkey = '';

for ($i = 0; $i < 6; $i++) {
    $passkey .= $chars[rand(0, strlen($chars) - 1)];
}

$hashed_passkey = password_hash($passkey, PASSWORD_DEFAULT);

// Generating QR code and barcode and storing them on the server

$id_no_1 = substr($id_number, 4, 8);
$id_no_1 = strrev($id_no_1) . "00000";

try {
    $barcodeData = substr($id_no_1, 3);
    $barcodeGenerator = new BarcodeGeneratorPNG();

    // Generate the barcode as a PNG image
    $barcodeImage = $barcodeGenerator->getBarcode($barcodeData, $barcodeGenerator::TYPE_CODE_128);

    // Save the barcode image as a PNG file
    $codeFileName = $barcodeData . uniqid() . '.png';
    $barcodeFilePath = __DIR__ . '/../storage/idbar2023/' . $codeFileName;
    file_put_contents($barcodeFilePath, $barcodeImage);
    // Create a new QR code instance
    $qrCode = QrCode::create($barcodeData);

    // Create a PNG writer
    $writer = new PngWriter();

    // Write the QR code to a PNG file
    $writer->write($qrCode)->saveToFile(__DIR__ . '/../storage/idqr2023/' . $codeFileName);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

// Set barcode data
// echo substr($id_no_1, 3);

// Now, you can insert the data into the database
// Replace this part with your database insertion code.
// You should uncomment and adapt it according to your database structure and connection.
$query = "INSERT INTO id_info (id_number, id_name, id_gender, id_year, id_department, id_photo, id_bar, id_qr, email, id_passkey) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = mysqli_prepare($con, $query);

// Bind the parameters
mysqli_stmt_bind_param($stmt, "ssssssssss", $id_number, $name, $gender, $e_year, $department, $imageFileName, $codeFileName, $codeFileName, $email, $hashed_passkey);

// Execute the statement
$result = mysqli_stmt_execute($stmt);

if ($result) {
    // echo "<script>alert(`Entry added!`)</script>";
    $message_body = "<html><body>";
    $message_body .= "<h2>ID Registration Successful</h2>";
    $message_body .= "<p>Dear <strong>{$name}</strong>,</p>";
    $message_body .= "<p>Your ID registration on the 'ID Gen' platform has been successfully processed. Below are the details you'll need:</p>";
    $message_body .= "<ul>";
    $message_body .= "<li>ID Number: <strong>{$id_number}</strong></li>";
    $message_body .= "<li>Passkey: <strong>{$passkey}</strong></li>";
    $message_body .= "</ul>";
    $message_body .= "<p>You can now download your registered ID from our portal by following these steps:</p>";
    $message_body .= "<ol>";
    $message_body .= "<li>Visit our ID download portal: <a href='https://www.idgen.com'>www.idgen.com</a></li>";
    $message_body .= "<li>Enter your ID Number and Passkey when prompted.</li>";
    $message_body .= "<li>Click the 'Download ID' button to access your registered ID.</li>";
    $message_body .= "</ol>";
    $message_body .= "<p>Make sure to keep your Passkey confidential as it provides access to your registered ID. If you have any questions or encounter any issues, please don't hesitate to contact our support team at <a href='mailto:support@idgen.com'>support@idgen.com</a>.</p>";
    $message_body .= "<p>Thank you for choosing our 'ID Gen' platform for ID registration. We hope you find it convenient and secure.</p>";
    $message_body .= "<p>Best regards,<br>The ID Gen Team</p>";
    $message_body .= "</body></html>";

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jedifesh@gmail.com';
        $mail->Password = 'vtwqxmtgvhvpczir';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('flexidon3@gmail.com');
        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = 'Your ID Registration on ID Gen is Complete!';
        $mail->Body = $message_body;

        $mail->send();

        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '    <meta charset="UTF-8">';
        echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '    <title>ID Registration Successful</title>';
        echo '    <style>';
        echo '        body {';
        echo '            font-family: sans-serif;';
        echo '        }';
        echo '        body {';
        echo '            display: flex;';
        echo '            justify-content: center;';
        echo '            align-items: center;';
        echo '            min-height: 100vh;';
        echo '            margin: 0;';
        echo '            background-color: #f2f2f2;';
        echo '        }';
        echo '';
        echo '        .card {';
        echo '            background-color: #ffffff;';
        echo '            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);';
        echo '            border-radius: 10px;';
        echo '            padding: 20px;';
        echo '            text-align: center;';
        echo '        }';
        echo '';
        echo '        h1 {';
        echo '            color: #124e5a;';
        echo '        }';
        echo '';
        echo '        p {';
        echo '            font-size: 18px;';
        echo '        }';
        echo '';
        echo '        .back-button {';
        echo '            background-color: #124e5a;';
        echo '            color: #fff;';
        echo '            border: none;';
        echo '            border-radius: 5px;';
        echo '            padding: 10px 20px;';
        echo '            cursor: pointer;';
        echo '            transition: background-color 0.3s;';
        echo '            text-decoration: none;';
        echo '        }';
        echo '';
        echo '        .back-button:hover {';
        echo '            background-color: #e6953cff;';
        echo '        }';
        echo '    </style>';
        echo '</head>';
        echo '<body>';
        echo '    <div class="card">';
        echo '        <h1>ID Registration Successful</h1>';
        echo '        <p>Your ID has been registered successfully.</p>';
        echo '        <a class="back-button" href="../register.html">Back</a>';
        echo '        <!-- You can add more details or links here if needed -->';
        echo '    </div>';
        echo '</body>';
        echo '</html>';
        exit();
    } catch (Exception $e) {
        echo "Email could not be sent. Error: {$mail->ErrorInfo}";
    }
} else {
    $errorMsg = mysqli_error($con);

    if (substr($errorMsg, 0, 15) == "Duplicate entry") {
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '    <meta charset="UTF-8">';
        echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '    <title>User Already Exists</title>';
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
        echo '            font-size: 18px;';
        echo '            margin-top: 20px;';
        echo '        }';
        echo '';
        echo '        .back-button {';
        echo '            margin-top: 20px;';
        echo '        }';
        echo '';
        echo '        .back-button a {';
        echo '            display: inline-block;';
        echo '            padding: 10px 20px;';
        echo '            background-color: #007bff;';
        echo '            color: #fff;';
        echo '            text-decoration: none;';
        echo '            border-radius: 5px;';
        echo '            transition: background-color 0.3s;';
        echo '        }';
        echo '';
        echo '        .back-button a:hover {';
        echo '            background-color: #0056b3;';
        echo '        }';
        echo '    </style>';
        echo '</head>';
        echo '<body>';
        echo '    <div class="container">';
        echo '        <h1>ID Already Registered</h1>';
        echo '        <p class="message">The ID you are trying to register already exists in our system.</p>';
        echo '        <div class="back-button">';
        echo '            <a href="#" onclick="history.back()">Back</a>';
        echo '        </div>';
        echo '    </div>';
        echo '</body>';
        echo '</html>';
    } else {
        // echo substr($errorMsg, 0, 15);
        echo $errorMsg;
    }
}

// Close the statement and the connection
mysqli_stmt_close($stmt);
mysqli_close($con);
// header("Location: ../admin-content/cms-pages/news.php");

// exit();
// }
