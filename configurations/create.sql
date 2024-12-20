CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(255) NOT NULL, -- Add the full name field
    UserName VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL, -- Add the password field
    CreatedOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedOn TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Update on each modification
    Status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'
);



CREATE TABLE Levels (
    LevelID INT AUTO_INCREMENT PRIMARY KEY,
    LevelNumber INT NOT NULL, -- Level order or number
    LevelName VARCHAR(255) NOT NULL, -- e.g., "Beginner", "Intermediate", "Advanced"
    LevelDescription TEXT, -- Optional description for the level
    Difficulty ENUM('Easy', 'Medium', 'Hard') NOT NULL,
    IsActive BOOLEAN DEFAULT TRUE -- For managing active levels
);


CREATE TABLE UserProgress (
    ProgressID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    LevelID INT NOT NULL,
    StringLevelID INT NOT NULL,

    WordsPerMinute DECIMAL(5, 2) NOT NULL,
    Accuracy DECIMAL(5, 2) NOT NULL,
    TimeTaken INT NOT NULL, -- In seconds
    TestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (LevelID) REFERENCES Levels(LevelID)
);




CREATE TABLE Strings (
    StringID INT AUTO_INCREMENT PRIMARY KEY,
    LevelID INT NOT NULL,
        LevelNumber INT NOT NULL, -- Level order or number

    TextContent TEXT NOT NULL,
    Length INT GENERATED ALWAYS AS (CHAR_LENGTH(TextContent)) STORED, -- Auto-calculate length
    IsActive BOOLEAN DEFAULT TRUE, -- Manage active/inactive strings
    FOREIGN KEY (LevelID) REFERENCES Levels(LevelID)
);
