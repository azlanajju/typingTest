<?php 
// Database configuration


// Create a new conn instance
$conn = new mysqli("localhost", "root", "", "typingtest");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}