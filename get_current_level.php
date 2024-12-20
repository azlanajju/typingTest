<?php
session_start();
include("./configurations/config.php");

if (!isset($_SESSION['userID'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    // Get the latest progress by checking max level and string level
    $stmt = $conn->prepare("
        WITH MaxProgress AS (
            SELECT 
                up.LevelID,
                MAX(up.StringLevelID) as MaxStringLevel
            FROM UserProgress up
            WHERE up.UserID = ?
            GROUP BY up.LevelID
            ORDER BY up.LevelID DESC, up.StringLevelID DESC
            LIMIT 1
        ),
        MaxStrings AS (
            SELECT 
                s.LevelID,
                MAX(s.LevelNumber) as MaxLevelNumber
            FROM Strings s
            WHERE s.IsActive = TRUE
            GROUP BY s.LevelID
        )
        SELECT 
            CASE
                WHEN mp.LevelID IS NULL THEN 1 -- New user starts at level 1
                WHEN mp.MaxStringLevel >= ms.MaxLevelNumber THEN mp.LevelID + 1 -- Move to next level
                ELSE mp.LevelID -- Stay in current level
            END as NextLevelID
        FROM MaxProgress mp
        LEFT JOIN MaxStrings ms ON mp.LevelID = ms.LevelID
    ");

    $stmt->bind_param("i", $_SESSION['userID']);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode(['levelId' => (int)($data['NextLevelID'] ?? 1)]);

} catch (Exception $e) {
    error_log("Error in get_current_level.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>