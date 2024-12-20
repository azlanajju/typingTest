const testText = "Typing tests are fun and help improve your speed and accuracy.";
const textDisplay = document.getElementById('textDisplay');
const inputBox = document.getElementById('inputBox');
const resultsDiv = document.getElementById('results');
let startTime;

function loadText() {
    textDisplay.innerHTML = '';
    for (let char of testText) {
        const span = document.createElement('span');
        span.textContent = char;
        textDisplay.appendChild(span);
    }
}

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
}

inputBox.addEventListener('input', () => {
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

loadText();

function updateCursorPosition() {
    const spans = textDisplay.querySelectorAll('span');
    const typedLength = inputBox.value.length;

    if (typedLength < spans.length) {
        const targetSpan = spans[typedLength];
        const { offsetLeft, offsetTop } = targetSpan;
        cursor.style.left = `${offsetLeft}px`;
        cursor.style.top = `${offsetTop}px`;
    } else {
        const lastSpan = spans[spans.length - 1];
        cursor.style.left = `${lastSpan.offsetLeft + lastSpan.offsetWidth}px`;
        cursor.style.top = `${lastSpan.offsetTop}px`;
    }
}

// Create and append the cursor
let cursor = document.createElement('div');
cursor.classList.add('cursor');
textDisplay.appendChild(cursor);

updateCursorPosition();
let tabWasLastPressed = false;

document.addEventListener('keydown', (event) => {
    if (event.key === 'Tab') {
        tabWasLastPressed = true;
        event.preventDefault(); // Prevent default tab behavior
    } else if (event.key === 'Enter' && tabWasLastPressed) {
        restartTest();
        tabWasLastPressed = false; // Reset the flag
    } else {
        tabWasLastPressed = false; // Reset if any other key is pressed
    }
});
function restartTest() {
    inputBox.value = '';
    inputBox.disabled = false;
    resultsDiv.innerHTML = '';
    loadText();
    startTime = null;
    
    // Remove old cursor and create new one
    if (cursor) cursor.remove();
    cursor = document.createElement('div');
    cursor.classList.add('cursor');
    textDisplay.appendChild(cursor);
    
    updateCursorPosition();
    inputBox.focus();
}
document.addEventListener('keydown', (event) => {
    if (/^[a-zA-Z0-9]$/.test(event.key)) {
        inputBox.focus();
    }
});
