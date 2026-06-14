// Force English browser validation messages regardless of OS language
document.addEventListener('DOMContentLoaded', function () {
    function enforceEnglishValidation(form) {
        var inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function (input) {
            input.addEventListener('invalid', function (e) {
                e.preventDefault();
                var el = e.target;
                el.setCustomValidity('');

                if (el.validity.valueMissing) {
                    el.setCustomValidity('Please fill in this field.');
                } else if (el.validity.typeMismatch) {
                    if (el.type === 'email') {
                        el.setCustomValidity('Please enter a valid email address.');
                    } else if (el.type === 'url') {
                        el.setCustomValidity('Please enter a valid URL.');
                    } else {
                        el.setCustomValidity('Please enter a valid value.');
                    }
                } else if (el.validity.tooShort) {
                    el.setCustomValidity('Please enter at least ' + el.minLength + ' characters (currently ' + el.value.length + ').');
                } else if (el.validity.tooLong) {
                    el.setCustomValidity('Please shorten this text to ' + el.maxLength + ' characters or less.');
                } else if (el.validity.rangeUnderflow) {
                    el.setCustomValidity('Value must be greater than or equal to ' + el.min + '.');
                } else if (el.validity.rangeOverflow) {
                    el.setCustomValidity('Value must be less than or equal to ' + el.max + '.');
                } else if (el.validity.patternMismatch) {
                    el.setCustomValidity(el.title ? el.title : 'Please match the requested format.');
                } else if (el.validity.stepMismatch) {
                    el.setCustomValidity('Please enter a valid value.');
                } else if (el.validity.badInput) {
                    el.setCustomValidity('Please enter a valid value.');
                }

                el.reportValidity();
            });

            // Reset custom message on input so it re-validates correctly
            input.addEventListener('input', function () {
                input.setCustomValidity('');
            });
        });
    }

    // Apply to all forms on the page
    document.querySelectorAll('form').forEach(enforceEnglishValidation);

    // Also watch for dynamically added forms
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) {
                    if (node.tagName === 'FORM') {
                        enforceEnglishValidation(node);
                    } else {
                        node.querySelectorAll && node.querySelectorAll('form').forEach(enforceEnglishValidation);
                    }
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
