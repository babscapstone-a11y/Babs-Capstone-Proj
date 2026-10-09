{{--
    Live strong-password checklist. Mirrors Password::defaults() in AppServiceProvider
    (8+ characters, lowercase, uppercase, number, special character).
    Usage: @include('partials.password-checklist', ['inputId' => 'password'])
    The surrounding form will not submit until every rule is met.
--}}
@once
<style>
    .pwd-rules { list-style:none; margin:.55rem 0 0; padding:0; display:grid; grid-template-columns:1fr 1fr; gap:.25rem .75rem; text-align:left; }
    .pwd-rules li { font-size:.76rem; color:var(--muted, #6B7280); display:flex; align-items:center; gap:.4rem; line-height:1.35; }
    .pwd-rules li i { font-size:.4rem; width:.75rem; text-align:center; flex-shrink:0; }
    .pwd-rules li.met { color:var(--green-600, #16A34A); }
    .pwd-rules li.met i { font-size:.7rem; }
    .pwd-rules.show-missing li:not(.met) { color:var(--red-600, #DC2626); }
    @media (max-width:420px) { .pwd-rules { grid-template-columns:1fr; } }
</style>
<script>
    window.BabsPasswordRules = {
        length: function (v) { return v.length >= 8; },
        lower:  function (v) { return /\p{Ll}/u.test(v); },
        upper:  function (v) { return /\p{Lu}/u.test(v); },
        number: function (v) { return /\p{N}/u.test(v); },
        symbol: function (v) { return /[\p{Z}\p{S}\p{P}]/u.test(v); }
    };
    // Updates the checklist and returns true when every rule is met
    window.checkPasswordChecklist = function (input) {
        var list = document.getElementById(input.id + 'Rules');
        var allMet = true;
        list.querySelectorAll('li').forEach(function (li) {
            var met = window.BabsPasswordRules[li.dataset.rule](input.value);
            li.classList.toggle('met', met);
            li.querySelector('i').className = met ? 'fas fa-check' : 'fas fa-circle';
            if (!met) allMet = false;
        });
        return allMet;
    };
</script>
@endonce

<ul class="pwd-rules" id="{{ $inputId }}Rules" aria-live="polite">
    <li data-rule="length"><i class="fas fa-circle"></i> At least 8 characters</li>
    <li data-rule="lower"><i class="fas fa-circle"></i> A lowercase letter</li>
    <li data-rule="upper"><i class="fas fa-circle"></i> An uppercase letter</li>
    <li data-rule="number"><i class="fas fa-circle"></i> A number</li>
    <li data-rule="symbol"><i class="fas fa-circle"></i> A special character (e.g. ! @ # $ %)</li>
</ul>
<script>
(function () {
    var input = document.getElementById(@json($inputId));
    if (!input) return;
    var list = document.getElementById(input.id + 'Rules');
    input.setAttribute('aria-describedby', list.id);
    input.addEventListener('input', function () { window.checkPasswordChecklist(input); });
    window.checkPasswordChecklist(input);

    // Stop the form submitting (before any other submit handler) until the password is strong
    if (input.form) {
        input.form.addEventListener('submit', function (e) {
            if (!window.checkPasswordChecklist(input)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                list.classList.add('show-missing');
                input.focus();
            }
        }, true);
    }
})();
</script>
