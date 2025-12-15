const steps = document.querySelectorAll(".step");
const languageOptions = document.getElementById("languageOptions");
const startBtn = document.getElementById("startBtn");

const data = {
    web: [
        "Frontend: HTML, CSS, JavaScript (React, Angular, Vue)",
        "Backend: Java, Python, PHP, Node.js"
    ],
    mobile: [
        "iOS: Swift, Objective-C",
        "Android: Kotlin, Java",
        "Cross-platform: React Native, Flutter, Xamarin"
    ],
    ai: [
        "Python (TensorFlow, scikit-learn)",
        "R",
        "Java (redes neuronales)"
    ],
    games: [
        "C++ (Unreal Engine)",
        "C# (Unity)",
        "Python (lógica)"
    ],
    desktop: [
        "C#, Java, C++",
        "Python"
    ],
    embedded: [
        "C, C++, Assembly"
    ],
    automation: [
        "Python",
        "PowerShell",
        "Bash",
        "Ruby, Perl"
    ]
};

function goToStep(step) {
    steps.forEach(s => s.classList.remove("active"));
    document.getElementById(`step${step}`).classList.add("active");
}

function selectArea(radio) {
    languageOptions.innerHTML = "";
    startBtn.disabled = true;

    const options = data[radio.value];
    goToStep(3);

    options.forEach(opt => {
        const label = document.createElement("label");
        label.innerHTML = `
            <input type="radio" onchange="enableStart(this)"> ${opt}
        `;
        languageOptions.appendChild(label);
    });
}

function enableStart(cb) {
    startBtn.disabled = false;
}
