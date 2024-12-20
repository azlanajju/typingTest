<?php
// Check if user details are already stored in the session
    if (!isset($_SESSION['userDetails']) || empty($_SESSION['userDetails'])) {
        // Fetch user data
        $user_id = $_SESSION['userID'];
        $query = "SELECT FullName, UserName, ProfilePicture, Email, Status 
                  FROM Users 
                  WHERE userID = ? AND Status = 'Active'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // Redirect if user is inactive or not found
        if (!$user || $user['Status'] !== 'Active') {
            session_destroy();
            header("Location: ./");
            exit();
        }

        // Store user details in session
        $_SESSION['userDetails'] = $user;
    } else {
        // Use the stored user details from the session
        $user = $_SESSION['userDetails'];
    }

    ?>