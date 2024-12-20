<?php
session_start();
include("./configurations/config.php");

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

if (!isset($_GET['levelId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Level ID required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT TextContent 
        FROM Strings 
        WHERE LevelID = ? AND IsActive = TRUE 
        ORDER BY RAND() 
        LIMIT 1
    ");

    $stmt->bind_param("i", $_GET['levelId']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $text = $result->fetch_assoc();
        echo json_encode(['success' => true, 'text' => $text['TextContent']]);
    } else {
        // Fallback text if no strings found for the level
        echo json_encode([
            'success' => true, 
            'text' => 'Default typing test text for practice.'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

$stmt->close();
$conn->close();
?>