(function( $ ) {
    'use strict';

    $(document).ready(function() {
        console.log('KumaPix Survey: Script starting...');

        /**
         * Helper function to set a cookie.
         * @param {string} name - The name of the cookie.
         * @param {string} value - The value of the cookie.
         * @param {number} days - The number of days until the cookie expires.
         */
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        /**
         * Helper function to get a cookie.
         * @param {string} name - The name of the cookie.
         * @returns {string|null} - The cookie value or null if not found.
         */
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // --- Main Plugin Initialization ---
        console.log('KumaPix Survey: Initializing main logic...');

        const popupOverlay = $('#kocs-popup-overlay');

        // 1. Primary Check: Do not run if the popup HTML doesn't exist.
        if (!popupOverlay.length) {
            console.error('KumaPix Survey: ABORT! Popup container "#kocs-popup-overlay" not found in the HTML.');
            return;
        }
        console.log('KumaPix Survey: OK - Popup container found.');

        // 2. Secondary Check: Do not run if the user has already completed the survey.
        if (getCookie('kocs_survey_completed')) {
            console.log('KumaPix Survey: ABORT! Cookie "kocs_survey_completed" found. Survey will not be shown.');
            return;
        }
        console.log('KumaPix Survey: OK - No survey cookie found.');
        
        // 3. Tertiary Check: Do not run if parameters from WordPress are missing.
        if (typeof kocs_params === 'undefined') {
            console.error('KumaPix Survey: ABORT! The "kocs_params" object is missing. Check if wp_localize_script is working correctly.');
            return;
        }
        if (!kocs_params.trigger) {
            console.error('KumaPix Survey: ABORT! "kocs_params.trigger" is not set. Check your plugin settings in the WordPress admin.');
            return;
        }
        console.log('KumaPix Survey: OK - Parameters object found.');
        console.log('KumaPix Survey: Full parameters object:', kocs_params);

        // All checks passed. Define remaining variables and functions.
        const closeBtn = $('#kocs-close-btn');
        const questionEl = $('#kocs-question');
        const answersEl = $('#kocs-answers');
        const surveyContent = $('#kocs-survey-content');
        const thankYouContent = $('#kocs-thank-you');
        let surveyShown = false;

        /**
         * Sets up the triggers based on admin settings.
         */
        function setupTriggers() {
            if (kocs_params.trigger === 'exit_intent') {
                $(document).on('mouseleave', function(e) {
                    if (e.clientY < 0) { // Mouse leaves from top of viewport
                        showPopup();
                    }
                });
            } else if (kocs_params.trigger === 'timed') { // <-- CORRECTED THIS LINE
                setTimeout(showPopup, parseInt(kocs_params.delay, 10));
            }
        }
        
        /**
         * Populates the survey with the question and answers.
         */
        function populateSurvey() {
            questionEl.text(kocs_params.question);
            answersEl.empty();
            kocs_params.answers.forEach(function(answer) {
                const button = $('<button></button>')
                    .addClass('kocs-answer-btn')
                    .text(answer)
                    .attr('data-answer', answer);
                answersEl.append(button);
            });
        }

        /**
         * Shows the survey popup.
         */
        function showPopup() {
            if (surveyShown) {
                return; // Prevent showing multiple times in a single session
            }
            populateSurvey();
            popupOverlay.removeClass('kocs-hidden');
            surveyShown = true;
            
            // Unbind the exit intent listener after showing once to prevent it from firing again
            if (kocs_params.trigger === 'exit_intent') {
                $(document).off('mouseleave');
            }
        }

        /**
         * Hides the survey popup.
         */
        function hidePopup() {
            popupOverlay.addClass('kocs-hidden');
        }

        // --- Event Handlers ---

        closeBtn.on('click', hidePopup);

        popupOverlay.on('click', function(e) {
            if (e.target === this) {
                hidePopup();
            }
        });

        answersEl.on('click', '.kocs-answer-btn', function() {
            const answer = $(this).data('answer');
            const question = questionEl.text();

            $('.kocs-answer-btn').prop('disabled', true);
            
            surveyContent.addClass('kocs-hidden');
            thankYouContent.removeClass('kocs-hidden');

            $.ajax({
                url: kocs_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'kocs_submit_survey',
                    nonce: kocs_params.nonce,
                    question: question,
                    answer: answer
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Survey submitted successfully.');
                        setCookie('kocs_survey_completed', 'true', 3);
                    } else {
                        console.error('Survey submission failed:', response.data.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                },
                complete: function() {
                    setTimeout(hidePopup, 3000);
                }
            });
        });

        // Initialize the triggers now that all checks have passed
        console.log('KumaPix Survey: All checks passed. Setting up triggers...');
        setupTriggers();
    });

})( jQuery );

