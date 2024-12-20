<?php
session_start();
include("./configurations/config.php");

if (!isset($_SESSION['userID'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    // First, get the user's completed levels and their max level
    $stmt = $conn->prepare("
        WITH UserMaxLevel AS (
            SELECT 
                MAX(s.LevelNumber) as MaxCompletedLevel
            FROM UserProgress up
            JOIN Strings s ON up.LevelID = s.LevelID
            WHERE up.UserID = ?
        )
        SELECT 
            CASE
                -- When user has completed levels, get next available level
                WHEN uml.MaxCompletedLevel IS NOT NULL THEN
                    COALESCE(
                        (
                            -- Try to get next level
                            SELECT MIN(s.LevelID)
                            FROM Strings s
                            WHERE s.LevelNumber > uml.MaxCompletedLevel
                            AND s.IsActive = TRUE
                        ),
                        (
                            -- If no next level, get the highest available level
                            SELECT s.LevelID
                            FROM Strings s
                            WHERE s.IsActive = TRUE
                            ORDER BY s.LevelNumber DESC
                            LIMIT 1
                        )
                    )
                -- For new users or users with no progress
                ELSE
                    (
                        -- Get the first available level
                        SELECT s.LevelID
                        FROM Strings s
                        WHERE s.IsActive = TRUE
                        ORDER BY s.LevelNumber ASC
                        LIMIT 1
                    )
            END as NextLevelID
        FROM UserMaxLevel uml
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