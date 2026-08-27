<?php
$conn = mysqli_connect("localhost", "root", "", "bazaarly");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

session_start();
?>