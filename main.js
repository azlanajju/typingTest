let testText = ""; // Will be populated from database
const textDisplay = document.getElementById('textDisplay');
const inputBox = document.getElementById('inputBox');
const resultsDiv = document.getElementById('results');
let startTime;
let currentLevelId = 1; // Default level
let cursor; // Declare cursor variable

// Create and append the cursor immediately
cursor = document.createElement('div');
cursor.classList.add('cursor');
textDisplay.appendChild(cursor);

// Function to fetch text for a given level
async function fetchTextString(levelId) {
    try {
        const response = await fetch(`get_text.php?levelId=${levelId}`);
        const data = await response.json();
        if (data.text) {
            testText = data.text;
            loadText();
        } else {
            console.error('No text received from server');
        }
    } catch (error) {
        console.error('Error fetching text:', error);
    }
}

// Function to save progress
async function saveProgress(wpm, accuracy, timeTaken) {
    try {
        const response = await fetch('function_saveProgress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `wpm=${wpm}&accuracy=${accuracy}&timeTaken=${timeTaken}&levelId=${currentLevelId}`
        });

        const data = await response.json();
        if (!data.success) {
            console.error('Error saving progress:', data.error);
        }
    } catch (error) {
        console.error('Error saving progress:', error);
    }
}

// Function to load text for the typing test
function loadText() {
    if (!testText) return;
    textDisplay.innerHTML = '';
    for (let char of testText) {
        const span = document.createElement('span');
        span.textContent = char;
        textDisplay.appendChild(span);
    }
    // Re-append cursor after loading text
    textDisplay.appendChild(cursor);
    updateCursorPosition();
}

// Function to calculate typing results
function calculateResults() {
    const typedText = inputBox.value;
    const endTime = new Date();
    const timeTaken = (endTime - startTime) / 1000; 
    const wordsTyped = typedText.trim().split(/\s+/).length;
    const wpm = Math.round((wordsTyped / timeTaken) * 60);
    let correctCount = 0;

    testText.split('').forEach((char, index) => {
        if (typedText[index] === char) correctCount++;
    });

    const accuracy = ((correctCount / testText.length) * 100).toFixed(2);

    resultsDiv.innerHTML = `WPM: ${wpm}, Accuracy: ${accuracy}%`;
    
    saveProgress(wpm, parseFloat(accuracy), Math.round(timeTaken));
}

// Add event listeners for level buttons
document.querySelectorAll('.level-btn').forEach(button => {
    button.addEventListener('click', async (e) => {
        currentLevelId = e.target.dataset.levelId;
        await fetchTextString(currentLevelId);
        restartTest();
    });
});

// Event listener for input changes
inputBox.addEventListener('input', () => {
    if (!testText) return;
    if (!startTime) startTime = new Date();

    const inputValue = inputBox.value;
    const spans = textDisplay.querySelectorAll('span');
    spans.forEach((span, index) => {
        if (inputValue[index] == null) {
            span.classList.remove('correct', 'incorrect');
        } else if (inputValue[index] === span.textContent) {
            span.classList.add('correct');
            span.classList.remove('incorrect');
        } else {
            span.classList.add('incorrect');
            span.classList.remove('correct');
        }
    });

    updateCursorPosition();

    if (inputValue.length === testText.length) {
        calculateResults();
        inputBox.disabled = true;
    }
});

// Simple cursor position update function
function updateCursorPosition() {
    const spans = textDisplay.querySelectorAll('span');
    const typedLength = inputBox.value.length;

    if (typedLength < spans.length) {
        const targetSpan = spans[typedLength];
        const { offsetLeft, offsetTop } = targetSpan;
        cursor.style.left = `${offsetLeft}px`;
        cursor.style.top = `${offsetTop}px`;
    } else if (spans.length > 0) {
        const lastSpan = spans[spans.length - 1];
        cursor.style.left = `${lastSpan.offsetLeft + lastSpan.offsetWidth}px`;
        cursor.style.top = `${lastSpan.offsetTop}px`;
    }
}

// Handle Tab key press
let tabWasLastPressed = false;

document.addEventListener('keydown', (event) => {
    if (event.key === 'Tab') {
        tabWasLastPressed = true;
        event.preventDefault();
    } else if (event.key === 'Enter' && tabWasLastPressed) {
        restartTest();
        tabWasLastPressed = false;
    } else {
        tabWasLastPressed = false;
    }
});

// Function to restart the typing test
async function restartTest() {
    inputBox.value = '';
    inputBox.disabled = false;
    resultsDiv.innerHTML = '';
    startTime = null;
    
    await fetchTextString(currentLevelId);
    loadText(); // This will re-append the cursor
    inputBox.focus();
}

// Add this function to get user's current level
async function getCurrentLevel() {
    try {
        console.log('Fetching current level...');
        const response = await fetch('get_current_level.php');
        const data = await response.json();
        console.log('Received level data:', data);
        
        if (data.error) {
            console.error('Server error:', data.error);
            return 1; // Default to level 1 if there's an error
        }
        
        if (data.levelId) {
            console.log('Setting current level to:', data.levelId);
            return data.levelId;
        } else {
            console.log('No level ID found, defaulting to 1');
            return 1; // Default to level 1 if no level found
        }
    } catch (error) {
        console.error('Error fetching current level:', error);
        return 1; // Default to level 1 if error occurs
    }
}

// Modify the initialization function
async function initializeTest() {
    try {
        console.log('Initializing test...');
        currentLevelId = await getCurrentLevel();
        console.log('Current level set to:', currentLevelId);
        await fetchTextString(currentLevelId);
    } catch (error) {
        console.error('Error in test initialization:', error);
        // Fall back to level 1 if there's an error
        currentLevelId = 1;
        await fetchTextString(currentLevelId);
    }
}

// Make sure this is at the bottom of your file
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, starting initialization...');
    initializeTest();
});
