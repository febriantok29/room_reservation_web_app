<script>
    document.addEventListener('DOMContentLoaded', function() {
        const errorMap = @json($errors->toArray());

        const getFieldsByName = (name) => {
            const selectors = [
                `[name="${name}"]`,
                `[name="${name}[]"]`,
            ];

            return document.querySelectorAll(selectors.join(','));
        };

        Object.entries(errorMap).forEach(([fieldName, messages]) => {
            const fields = getFieldsByName(fieldName);
            if (!fields.length) {
                return;
            }

            fields.forEach((field) => {
                field.classList.add('is-invalid', 'field-error-highlight');
                field.setAttribute('aria-invalid', 'true');

                const existingFeedback = field.parentElement?.querySelector(
                    `.invalid-feedback[data-field="${fieldName}"]`);
                if (existingFeedback || !messages?.length) {
                    return;
                }

                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block';
                feedback.dataset.field = fieldName;
                feedback.textContent = messages[0];
                field.parentElement?.appendChild(feedback);
            });
        });

        const setLoadingState = (button, loadingText) => {
            if (!button) {
                return;
            }

            if (!button.dataset.originalDisabled) {
                button.dataset.originalDisabled = button.disabled ? '1' : '0';
            }

            if (button.tagName === 'INPUT') {
                if (!button.dataset.originalValue) {
                    button.dataset.originalValue = button.value;
                }
                button.value = loadingText;
            } else {
                if (!button.dataset.originalHtml) {
                    button.dataset.originalHtml = button.innerHTML;
                }

                button.innerHTML =
                    `<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>${loadingText}`;
            }

            button.disabled = true;
            button.classList.add('is-loading');
        };

        const resetButtonState = (button) => {
            if (!button) {
                return;
            }

            if (button.tagName === 'INPUT' && button.dataset.originalValue) {
                button.value = button.dataset.originalValue;
            }

            if (button.tagName !== 'INPUT' && button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }

            button.disabled = button.dataset.originalDisabled === '1';
            button.classList.remove('is-loading');
        };

        const forms = document.querySelectorAll('form[data-submit-guard]');

        forms.forEach((form) => {
            form.dataset.submitting = '0';

            form.addEventListener('submit', function(event) {
                if (form.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = '1';

                const loadingText = form.dataset.loadingText || 'Memproses...';
                const submitButtons = form.querySelectorAll(
                    'button[type="submit"], input[type="submit"]');
                submitButtons.forEach((button) => setLoadingState(button, loadingText));
            });
        });

        window.addEventListener('pageshow', function() {
            forms.forEach((form) => {
                form.dataset.submitting = '0';

                const submitButtons = form.querySelectorAll(
                    'button[type="submit"], input[type="submit"]');
                submitButtons.forEach((button) => resetButtonState(button));
            });
        });
    });
</script>
