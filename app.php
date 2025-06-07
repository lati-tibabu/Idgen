<?php

session_start();

if (isset($_SESSION['id_number'])) {
    $id_number = $_SESSION['id_number'];
} else {
    header('Location: index.html');
    exit;
}

include("config.php");

$id_number = $_GET["id_no"];

$query = "SELECT * FROM id_info WHERE id_number = ?";

// Create a prepared statement
$stmt = mysqli_prepare($con, $query);

// Bind the parameter
mysqli_stmt_bind_param($stmt, "s", $id_number);

// Execute the statement
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = array();

$count = 0;

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
        $count = $count + 1;
    }
}

// Close the statement
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Gen</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>
    <script src="https://kit.fontawesome.com/cfee536f6f.js" crossorigin="anonymous" defer></script>
    <div class="main-container">
        <div class="large-text">
            ID GEN
        </div>
        <div class="back" onclick="back()">
            <i class="fa-solid fa-xmark"></i>
        </div>
        <div class="sub-container info-box">
            <div class="form-info">
                The following is your ID info
            </div>

            <div class="id-card-detail">
                <div class="id-card front-side">
                    <div class="text-info information">
                        <div class="text-info1 info">
                            <p id="id_name"><?php echo $data[0]['id_name'] ?></p>
                            <p id="id_number"><?php echo $data[0]['id_number'] ?></p>
                            <p id="id_gender"><?php echo $data[0]['id_gender'] ?></p>
                            <p id="id_year"><?php echo $data[0]['id_year'] ?></p>
                            <p id="id_department"><?php echo $data[0]['id_department'] ?></p>
                        </div>
                        <div class="barcode-info">
                            <img src="storage/idbar2023/<?php echo $data[0]['id_bar'] ?>" alt="">
                        </div>
                    </div>
                    <div class="photo-info information">
                        <img src="storage/id2023/<?php echo $data[0]['id_photo'] ?>" alt="">
                    </div>
                </div>
                <div class="id-card back-side">
                    <div class="qrcode-info">
                        <img src="storage/idqr2023/<?php echo $data[0]['id_qr'] ?>" alt="">
                    </div>
                </div>
            </div>
            <div class="control-area">
                <!-- <button>
                    Edit ID info
                </button> -->

                <button onclick="logout()">
                    Logout
                </button>
            </div>
        </div>
    </div>
    <script>
        function back() {
            window.history.back();
        }

        function logout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "control/getout.php";
            }
        }
    </script>
</body>

</html>