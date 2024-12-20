<?php

include("../configurations/config.php");

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill all fields.';
    } else {
        // Prepare a select statement to check if the email exists
        $stmt = $conn->prepare("SELECT UserID, FullName, Email, Password, CreatedOn, Status FROM users WHERE Email = ?");
        if ($stmt === false) {
            $error_message = 'Failed to prepare statement: ' . $conn->error;
        } else {
            // Bind parameters
            $stmt->bind_param('s', $email);

            // Execute statement
            $stmt->execute();

            // Store result to check if email exists
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                // Bind result variables
                $stmt->bind_result($userID, $fullName, $storedEmail, $hashedPassword, $createdOn, $status);

                if ($stmt->fetch()) {
                    // Verify the password
                    if (password_verify($password, $hashedPassword)) {
                        // Password is correct, start a session and redirect to the home page
                        session_start();
                        $_SESSION['loggedin'] = true;
                        $_SESSION['userID'] = $userID;
                        $_SESSION['email'] = $storedEmail;
                        $_SESSION['fullName'] = $fullName;
                        header('Location: ../');
                    } else {
                        $error_message = 'Incorrect password.';
                    }
                }
            } else {
                $error_message = 'No account found with that email address.';
            }
            // Close the statement
            $stmt->close();
        }
    }
} else {
    $error_message = '';
}

// Close the connection
$conn->close();
?>
