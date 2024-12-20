<?php
// dashboard.php
session_start();
include("./configurations/config.php");

if (!isset($_SESSION['userID'])) {
    header("Location: ./login");
    exit();
}

// Get user's stats and progress
$stmt = $conn->prepare("
    SELECT 
        u.UserName,
        u.FullName,
        u.Email,
        u.ProfilePicture,
        MAX(l.LevelName) as CurrentLevel,
        MAX(l.Difficulty) as CurrentDifficulty,
        MAX(up.WordsPerMinute) as BestWPM,
        AVG(up.WordsPerMinute) as AverageWPM,
        AVG(up.Accuracy) as AverageAccuracy,
        COUNT(DISTINCT up.ProgressID) as TotalTests,
        MAX(up.TestDate) as LastTestDate
    FROM Users u
    LEFT JOIN UserProgress up ON u.UserID = up.UserID
    LEFT JOIN Levels l ON up.LevelID = l.LevelID
    WHERE u.UserID = ?
    GROUP BY u.UserID
");

$stmt->bind_param("i", $_SESSION['userID']);
$stmt->execute();
$userStats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Dashboard - <?php echo htmlspecialchars($userStats['UserName']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: #1f2937;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #9ca3af;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .progress-card {
            background: #1f2937;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .level-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .level-badge.easy { background: #4ade80; color: #fff; }
        .level-badge.medium { background: #fbbf24; color: #fff; }
        .level-badge.hard { background: #ef4444; color: #fff; }

        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-button {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            background: #3b82f6;
            color: #fff;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .action-button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
<header class="header">
<div onclick="window.location.href='./'"  class="logo">
            <img style="cursor:pointer" src="./images/swift-keys-padded-logo.svg" alt="Speed Typing Test Logo" class="logo-img">
        </div>
        <div class="profile">
    <div class="profile-info">
        <?php if (!empty($user['ProfilePicture'])): ?>
            <img src="<?php echo htmlspecialchars($user['ProfilePicture']); ?>" alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <img src="./images/default-user-icon.svg" alt="Default Profile Picture" class="profile-pic">
        <?php endif; ?>
        <div class="user-details">
            <span class="profile-name"><?php echo  $_SESSION['userDetails']['FullName'];
?></span>
            <span class="profile-username">@<?php echo     $_SESSION['userDetails']['Email'];?></span>
        </div>
    </div>
    <div class="logout-button">
        <form action="logout.php" method="POST">
            <button type="submit">Logout</button>
        </form>
    </div>
</div>
    </header>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Your Typing Dashboard</h1>
            <p>Track your progress and improve your typing skills</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Current Level</h3>
                <div class="value">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <?php echo htmlspecialchars($userStats['CurrentLevel'] ?? 'Beginner'); ?>
                    <span class="level-badge <?php echo strtolower($userStats['CurrentDifficulty'] ?? 'easy'); ?>">
                        <?php echo htmlspecialchars($userStats['CurrentDifficulty'] ?? 'Easy'); ?>
                    </span>
                </div>
            </div>

            <div class="stat-card">
                <h3>Best WPM</h3>
                <div class="value"><?php echo round($userStats['BestWPM'] ?? 0); ?></div>
            </div>

            <div class="stat-card">
                <h3>Average WPM</h3>
                <div class="value"><?php echo round($userStats['AverageWPM'] ?? 0); ?></div>
            </div>

            <div class="stat-card">
                <h3>Average Accuracy</h3>
                <div class="value"><?php echo round($userStats['AverageAccuracy'] ?? 0); ?>%</div>
            </div>

            <div class="stat-card">
                <h3>Tests Completed</h3>
                <div class="value"><?php echo $userStats['TotalTests'] ?? 0; ?></div>
            </div>

            <div class="stat-card">
                <h3>Last Test</h3>
                <div class="value">
                    <?php 
                    echo $userStats['LastTestDate'] 
                        ? date('M j, Y', strtotime($userStats['LastTestDate']))
                        : 'No tests yet';
                    ?>
                </div>
            </div>
        </div>

        <div class="progress-card">
            <h2>Recent Progress</h2>
            <?php
            $recentTests = $conn->prepare("
                SELECT 
                    up.WordsPerMinute,
                    up.Accuracy,
                    l.LevelName,
                    up.TestDate
                FROM UserProgress up
                JOIN Levels l ON up.LevelID = l.LevelID
                WHERE up.UserID = ?
                ORDER BY up.TestDate DESC
                LIMIT 5
            ");
            $recentTests->bind_param("i", $_SESSION['userID']);
            $recentTests->execute();
            $results = $recentTests->get_result();
            ?>
            <table style="width: 100%; margin-top: 1rem;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Level</th>
                        <th>WPM</th>
                        <th>Accuracy</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($test = $results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M j, Y H:i', strtotime($test['TestDate'])); ?></td>
                        <td><?php echo htmlspecialchars($test['LevelName']); ?></td>
                        <td><?php echo round($test['WordsPerMinute']); ?></td>
                        <td><?php echo round($test['Accuracy'], 1); ?>%</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="quick-actions">
            <a href="test.php" class="action-button">Start New Test</a>
            <a href="./practice" class="action-button">Practice Mode</a>
        </div>
    </div>
</body>
</html>