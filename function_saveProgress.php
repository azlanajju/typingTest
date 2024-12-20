<?php
session_start();
include("./configurations/config.php");

// Check if user is logged in and data is provided
if (!isset($_SESSION['userID']) || !isset($_POST['wpm']) || !isset($_POST['accuracy']) || !isset($_POST['timeTaken'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit;
}

// Default to level 1 if not specified
$levelID = isset($_POST['levelId']) ? $_POST['levelId'] : 1;

try {
    $stmt = $conn->prepare("
        INSERT INTO UserProgress (UserID, LevelID, WordsPerMinute, Accuracy, TimeTaken)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iiddi", 
        $_SESSION['userID'],
        $levelID,
        $_POST['wpm'],
        $_POST['accuracy'],
        $_POST['timeTaken']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>