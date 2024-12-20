<?php 
// Database configuration


// Create a new mysqli instance
$mysqli = new mysqli("localhost", "root", "", "typingtest");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}