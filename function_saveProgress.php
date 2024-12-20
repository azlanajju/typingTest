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
$stringID = isset($_POST['stringId']) ? $_POST['stringId'] : 1;

try {
    // First get the LevelNumber from Strings table
    $getLevelNumber = $conn->prepare("
        SELECT LevelNumber 
        FROM Strings 
        WHERE StringID = ?
    ");
    
    $getLevelNumber->bind_param("i", $stringID);
    $getLevelNumber->execute();
    $result = $getLevelNumber->get_result();
    $stringData = $result->fetch_assoc();
    
    if (!$stringData) {
        throw new Exception("String not found");
    }

    // Now insert into UserProgress using LevelNumber as StringLevelID
    $stmt = $conn->prepare("
        INSERT INTO UserProgress (
            UserID, 
            LevelID, 
            StringLevelID, 
            WordsPerMinute, 
            Accuracy, 
            TimeTaken
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iiiddi", 
        $_SESSION['userID'],
        $levelID,
        $stringData['LevelNumber'],  // Using LevelNumber instead of StringID
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