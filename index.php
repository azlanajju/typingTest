<?php
    session_start();
    include("./configurations/config.php");

    // Check if user is logged in
    if (!isset($_SESSION['userID'])) {
        header("Location: ./login");
        exit();
    }

include("./function_getDetailsForSession.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swift Keys - <?php echo htmlspecialchars($user['UserName']); ?></title>
    <link rel="stylesheet" href="./style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="./images/swift-keys-padded-logo.svg" alt="Speed Typing Test Logo" class="logo-img">
        </div>
        <div class="profile">
    <div onclick="window.location.href='./dashboard.php'"  class="profile-info">
        <?php if (!empty($user['ProfilePicture'])): ?>
            <img src="<?php echo htmlspecialchars($user['ProfilePicture']); ?>" alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <img src="./images/default-user-icon.svg" alt="Default Profile Picture" class="profile-pic">
        <?php endif; ?>
        <div class="user-details">
            <span class="profile-name"><?php echo htmlspecialchars($user['FullName']); ?></span>
            <span class="profile-username">@<?php echo htmlspecialchars($user['Email']); ?></span>
        </div>
    </div>
    <div class="logout-button">
        <form action="logout.php" method="POST">
            <button type="submit">Logout</button>
        </form>
    </div>
</div>
    </header>

    <div onclick="inputBox.focus()" class="test-container">
        <div class="text-display" id="textDisplay">
            <!-- Dynamic Text Will Load Here -->
        </div>

        <div class="results" id="results"></div>
        <div onclick="restartTest(); disableNextButton();" class="next-button" id="nextButton">Next →</div>
        <div class="tooltip">Press Tab → Enter to restart or click Next</div>
    </div>
    <div class="input-area">
        <input type="text" class="input-box" autocomplete="off" id="inputBox" placeholder="Start typing when ready..." autofocus onpaste="return false;">
    </div>

    <script src="./main.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>



