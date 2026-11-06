// Password visibility toggles, live requirement checklist, and match indicator.
// No build step required; loaded with `defer` from the guest layout.
(function () {
    'use strict';

    // Show/hide password toggles
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.textContent = show ? 'Hide' : 'Show';
        });
    });

    var passwordInput = document.getElementById('password');
    if (!passwordInput) return;

    // Live password requirements checklist
    var reqList = document.getElementById('pw-requirements');
    if (reqList) {
        var rules = {
            length: function (v) { return v.length >= 8; },
            letters: function (v) { return /[a-zA-Z]/.test(v); },
            numbers: function (v) { return /[0-9]/.test(v); }
        };

        function evaluate() {
            var value = passwordInput.value;
            reqList.querySelectorAll('[data-rule]').forEach(function (item) {
                var rule = rules[item.getAttribute('data-rule')];
                if (!rule) return;
                var pass = rule(value);
                item.classList.toggle('req-pass', pass);
                item.classList.toggle('req-fail', !pass);
                item.setAttribute('aria-checked', pass ? 'true' : 'false');
            });
        }

        passwordInput.addEventListener('input', evaluate);
        evaluate();
    }

    // Password confirmation match indicator
    var confirmInput = document.getElementById('password_confirmation');
    var matchIndicator = document.getElementById('pw-match');
    if (confirmInput && matchIndicator) {
        function evaluateMatch() {
            var confirm = confirmInput.value;
            if (confirm === '') {
                matchIndicator.textContent = '';
                matchIndicator.className = 'match-hint';
                return;
            }
            var match = confirm === passwordInput.value;
            matchIndicator.textContent = match ? 'Passwords match' : 'Passwords do not match';
            matchIndicator.className = 'match-hint ' + (match ? 'match-ok' : 'match-bad');
        }

        passwordInput.addEventListener('input', evaluateMatch);
        confirmInput.addEventListener('input', evaluateMatch);
        evaluateMatch();
    }
})();
