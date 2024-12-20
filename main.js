let testText = ""; // Will be populated from database
const textDisplay = document.getElementById('textDisplay');
const inputBox = document.getElementById('inputBox');
const resultsDiv = document.getElementById('results');
let startTime;
let currentLevelId = 1; // Default level
let cursor; // Declare cursor variable
let currentStringId = 1; // Add stringId variable

// Create and append the cursor immediately
cursor = document.createElement('div');
cursor.classList.add('cursor');
textDisplay.appendChild(cursor);

// Function to fetch text for a given level
async function fetchTextString(levelId) {
    try {
        const response = await fetch(`get_text.php?levelId=${levelId}`);
        const data = await response.json();
        
        if (data.success) {
            testText = data.text;
            currentStringId = data.stringId;
            currentLevelId = data.levelId;
            currentStringLevelNumber = data.stringLevelNumber;
            
            loadText();
        } else {
            if (data.error === 'No more strings available') {
                // Handle completion (maybe show a completion message in the UI)
                document.getElementById('textDisplay').innerHTML = 'All levels completed!';
            } else {
                // Handle other errors silently
                document.getElementById('textDisplay').innerHTML = 'Error loading text. Please try again.';
            }
        }
    } catch (error) {
        document.getElementById('textDisplay').innerHTML = 'Error loading text. Please try again.';
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
            body: `wpm=${wpm}&accuracy=${accuracy}&timeTaken=${timeTaken}&levelId=${currentLevelId}&stringId=${currentStringId}`
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

function enableNextButton() {
    document.getElementById('nextButton').style.display = "block";

}
function disableNextButton() {
    document.getElementById('nextButton').style.display = "none";

}
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

// Initial text load
fetchTextString(currentLevelId);

function endTest() {
    console.log('Test ended, calculating results'); // Debug log
    
    const timeTaken = (Date.now() - startTime) / 1000;
    const wpm = calculateWPM(timeTaken);
    const accuracy = calculateAccuracy();
    
    // Display results
    const resultsDiv = document.getElementById('results');
    resultsDiv.innerHTML = `
        <div class="result-stats">
            <div>WPM: ${Math.round(wpm)}</div>
            <div>Accuracy: ${Math.round(accuracy)}%</div>
            <div>Time: ${Math.round(timeTaken)}s</div>
        </div>
    `;
    
    // Enable next button
    enableNextButton();
    
    // Disable input box
    inputBox.disabled = true;
    
    // Save progress
    saveProgress(wpm, accuracy, timeTaken);
}
