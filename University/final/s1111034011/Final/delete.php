<?php
require_once 'DB_open.php'; 

$id = $_GET['id'];
$id = mysqli_real_escape_string($link, $id);

$sql = "DELETE FROM animals WHERE id = $id";

if (mysqli_query($link, $sql)) {
    header("Location: index.php"); 
    exit;
}

require_once 'DB_close.php'; 
?>
