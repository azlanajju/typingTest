<?php
include("../configurations/config.php");

// Initialize error message
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error_message = 'Please fill all fields.';
    } elseif ($password !== $confirmPassword) {
        $error_message = 'Passwords do not match.';
    } else {
        // Check if the email already exists
        $checkStmt = $mysqli->prepare("SELECT id FROM Users WHERE Email = ?");
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            // Email already exists
            $error_message = 'Email already exists. Please use a different email.';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Prepare an insert statement
            $stmt = $mysqli->prepare("INSERT INTO Users (FullName, Email, Password, CreatedOn, Status) VALUES (?, ?, ?, NOW(), 'Active')");
            if ($stmt === false) {
                $error_message = 'Failed to prepare statement: ' . $mysqli->error;
            } else {
                // Bind parameters
                $fullName = $firstName . ' ' . $lastName;
                $stmt->bind_param('sss', $fullName, $email, $hashedPassword);

                // Execute statement
                if ($stmt->execute()) {
                    // Start session and set user session variables
                    session_start();
                    $_SESSION['user'] = [
                        'fullName' => $fullName,
                        'email' => $email,
                    ];

                    // Redirect to home page
                    header('Location: ../');
                    exit;
                } else {
                    $error_message = 'Failed to sign up: ' . $stmt->error;
                }
                // Close the statement
                $stmt->close();
            }
        }
        // Close the check statement
        $checkStmt->close();
    }
} else {
    $error_message = 'Invalid request method.';
}

// Close the connection
$mysqli->close();
?>
