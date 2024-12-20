<?php
session_start();
include("./configurations/config.php");

if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$levelId = isset($_GET['levelId']) ? $_GET['levelId'] : 1;

try {
    // Get the next uncompleted string in the current level
    $stmt = $conn->prepare("
        WITH CompletedStrings AS (
            SELECT DISTINCT LevelID, StringLevelID
            FROM UserProgress
            WHERE UserID = ?
        )
        SELECT 
            s.StringID,
            s.TextContent,
            s.LevelNumber,
            s.LevelID
        FROM Strings s
        LEFT JOIN CompletedStrings cs ON 
            s.LevelID = cs.LevelID AND 
            s.LevelNumber = cs.StringLevelID
        WHERE s.LevelID = ?
        AND s.IsActive = TRUE
        AND cs.StringLevelID IS NULL
        ORDER BY s.LevelNumber ASC
        LIMIT 1
    ");

    $stmt->bind_param("ii", $_SESSION['userID'], $levelId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'text' => $row['TextContent'],
            'stringId' => $row['StringID'],
            'levelId' => $row['LevelID'],
            'levelNumber' => $row['LevelNumber'],
            'stringLevelNumber' => $row['LevelNumber']
        ]);
    } else {
        // Only if ALL strings in current level are completed, check next level
        $nextLevelStmt = $conn->prepare("
            SELECT 
                s.StringID,
                s.TextContent,
                s.LevelNumber,
                s.LevelID
            FROM Strings s
            LEFT JOIN (
                SELECT DISTINCT LevelID, StringLevelID
                FROM UserProgress
                WHERE UserID = ?
            ) cs ON 
                s.LevelID = cs.LevelID AND 
                s.LevelNumber = cs.StringLevelID
            WHERE s.LevelID > ?
            AND s.IsActive = TRUE
            AND cs.StringLevelID IS NULL
            ORDER BY s.LevelID ASC, s.LevelNumber ASC
            LIMIT 1
        ");

        $nextLevelStmt->bind_param("ii", $_SESSION['userID'], $levelId);
        $nextLevelStmt->execute();
        $nextLevelResult = $nextLevelStmt->get_result();

        if ($nextLevelResult->num_rows > 0) {
            $row = $nextLevelResult->fetch_assoc();
            echo json_encode([
                'success' => true,
                'text' => $row['TextContent'],
                'stringId' => $row['StringID'],
                'levelId' => $row['LevelID'],
                'levelNumber' => $row['LevelNumber'],
                'stringLevelNumber' => $row['LevelNumber'],
                'newLevel' => true
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No more strings available'
            ]);
        }
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>