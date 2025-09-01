(function ($) {
    'use strict';

    $(function () {
        const overlay = $('#kocs-popup-overlay');
        const container = $('#kocs-popup-container');
        const closeBtn = $('#kocs-close-btn');
        const surveyContent = $('#kocs-survey-content');
        const thankYouContent = $('#kocs-thank-you');
        const questionEl = $('#kocs-question');
        const answersEl = $('#kocs-answers');

        let surveyShown = false;

        // --- Popup Logic ---

        function showPopup() {
            if (surveyShown || sessionStorage.getItem('kocsSurveyCompleted')) {
                return;
            }
            populateSurvey();
            overlay.removeClass('kocs-hidden');
            surveyShown = true;
        }

        function hidePopup() {
            overlay.addClass('kocs-hidden');
        }

        function populateSurvey() {
            questionEl.text(kocs_params.question);
            answersEl.empty();
            kocs_params.answers.forEach(answer => {
                const button = $('<button></button>')
                    .addClass('kocs-answer-btn')
                    .text(answer)
                    .attr('data-answer', answer);
                answersEl.append(button);
            });
        }

        // --- Trigger Logic ---

        function setupTriggers() {
            if (kocs_params.trigger === 'exit_intent') {
                $(document).on('mouseleave', function (e) {
                    if (e.clientY < 0) {
                        showPopup();
                    }
                });
            } else if (kocs_params.trigger === 'timed') {
                setTimeout(showPopup, kocs_params.delay);
            }
        }

        // --- Event Handlers ---

        closeBtn.on('click', hidePopup);

        overlay.on('click', function (e) {
            if ($(e.target).is(overlay)) {
                hidePopup();
            }
        });

        answersEl.on('click', '.kocs-answer-btn', function () {
            const answer = $(this).data('answer');
            submitSurvey(answer);
        });

        // --- AJAX Submission ---
        
        function submitSurvey(answer) {
            $.ajax({
                url: kocs_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'kocs_submit_survey',
                    nonce: kocs_params.nonce,
                    question: kocs_params.question,
                    answer: answer
                },
                success: function (response) {
                    if (response.success) {
                        showThankYou();
                    } else {
                        // Optionally handle error
                        hidePopup();
                    }
                },
                error: function () {
                    // Optionally handle error
                    hidePopup();
                }
            });
        }
        
        function showThankYou() {
            surveyContent.addClass('kocs-hidden');
            thankYouContent.removeClass('kocs-hidden');
            sessionStorage.setItem('kocsSurveyCompleted', 'true');
            setTimeout(hidePopup, 3000);
        }

        // Initialize
        if (overlay.length) {
            setupTriggers();
        }

    });

})(jQuery);
