<?php
$con = mysqli_connect("localhost", "root2", "password", "id_gen");

if(!$con){
    echo(mysqli_error($con));
}

?>