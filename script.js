function showForm(formId) {
    document.querySelectorAll('.form-box').forEach(form => {
        form.classList.remove('active');
    });
    document.getElementById(formId).classList.add('active');
}

function checkPasswordComplexity(password) {
    const rules = [
        { test: p => p.length >= 8, message: "at least 8 characters" },
        { test: p => /[A-Z]/.test(p), message: "an uppercase letter" },
        { test: p => /[a-z]/.test(p), message: "a lowercase letter" },
        { test: p => /[0-9]/.test(p), message: "a number" },
        { test: p => /[\W_]/.test(p), message: "a special character" },
    ];

    const failed = rules.filter(rule => !rule.test(password)).map(rule => rule.message);
    return failed;
}

function attachPasswordValidation(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const hint = document.createElement('p');
    hint.className = 'hint';
    input.insertAdjacentElement('afterend', hint);

    input.addEventListener('input', () => {
        const failed = checkPasswordComplexity(input.value);
        if (input.value.length === 0) {
            hint.textContent = '';
        } else if (failed.length > 0) {
            hint.textContent = 'Still needs: ' + failed.join(', ');
            hint.style.color = 'yellow';
        } else {
            hint.textContent = 'Looks good!';
            hint.style.color = 'lightgreen';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    attachPasswordValidation('register-password');
    attachPasswordValidation('reset-password');
});