<?php

require_once '../config.php';
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create a new TCPDF instance
$pdf = new TCPDF();

// Extract user input
$name = $_POST["name"];
$email = $_POST["email"];
$id_number_input = $_POST["id_no"];
$id_number = strtolower(trim($id_number_input));
$gender = $_POST["gender"];
$e_year = $_POST["e_year"];
$department = $_POST["department"];

// Upload image
$imageDirectory = __DIR__ . '/../storage/id2023/';
if (!is_dir($imageDirectory)) {
    mkdir($imageDirectory, 0777, true);
}
$imageFileName = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$destination = $imageDirectory . $imageFileName;
if (!move_uploaded_file($_FILES["image"]["tmp_name"], $destination)) {
    // It's good practice to provide more specific error feedback if possible
    // For example, check file upload errors: $_FILES['image']['error']
    // Or check if $destination is writable if mkdir succeeded but move_uploaded_file failed.
    exit("Can't move the uploaded file");
}

// Generate passkey
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*';
$passkey = '';
for ($i = 0; $i < 6; $i++) {
    $passkey .= $chars[rand(0, strlen($chars) - 1)];
}
$hashed_passkey = password_hash($passkey, PASSWORD_DEFAULT);

// Generate barcode and QR code
$id_no_1 = strrev(substr($id_number, 4, 8)) . "00000";
$barcodeData = substr($id_no_1, 3);
$barcodeGenerator = new BarcodeGeneratorPNG();
$barcodeImage = $barcodeGenerator->getBarcode($barcodeData, $barcodeGenerator::TYPE_CODE_128);
$codeFileName = $barcodeData . uniqid() . '.png';
$barcodeDirectory = __DIR__ . '/../storage/idbar2023/';
if (!is_dir($barcodeDirectory)) {
    mkdir($barcodeDirectory, 0777, true);
}
$barcodeFilePath = $barcodeDirectory . $codeFileName;
file_put_contents($barcodeFilePath, $barcodeImage);

// Generate QR code
$qrCode = QrCode::create($barcodeData);
$writer = new PngWriter();
$qrCodeDirectory = __DIR__ . '/../storage/idqr2023/';
if (!is_dir($qrCodeDirectory)) {
    mkdir($qrCodeDirectory, 0777, true);
}
$writer->write($qrCode)->saveToFile($qrCodeDirectory . $codeFileName);

// Store in DB
$query = "INSERT INTO id_info (id_number, id_name, id_gender, id_year, id_department, id_photo, id_bar, id_qr, email, id_passkey) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ssssssssss", $id_number, $name, $gender, $e_year, $department, $imageFileName, $codeFileName, $codeFileName, $email, $hashed_passkey);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    $message_body = "<html><body>
        <h2>ID Registration Successful</h2>
        <p>Dear <strong>{$name}</strong>,</p>
        <p>Your ID Number: <strong>{$id_number}</strong><br>Passkey: <strong>{$passkey}</strong></p>
        <p>Please visit <a href='https://www.idgen.com'>www.idgen.com</a> to download your ID.</p>
        <p>Keep your passkey secure.</p>
        </body></html>";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');
        $mail->SMTPSecure = 'ssl';
        $mail->Port = getenv('MAIL_PORT');

        $mail->setFrom(getenv('MAIL_FROM'), 'ID Gen Platform');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your ID Registration on ID Gen is Complete!';
        $mail->Body = $message_body;
        $mail->send();

        // Success HTML output (or redirect)
        echo "<h2>ID Registered and Email Sent Successfully.</h2>";
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    $errorMsg = mysqli_error($con);
    if (str_starts_with($errorMsg, "Duplicate entry")) {
        echo "<h2>User with this ID already exists.</h2>";
    } else {
        echo "Error: " . $errorMsg;
    }
}
