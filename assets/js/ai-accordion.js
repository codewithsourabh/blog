/**
 * AI Summary Accordion JavaScript
 *
 * @package BlogsHQ
 * @since 1.0.0
 */

(function() {
    'use strict';

    function initAccordion() {
        var triggers = document.querySelectorAll('.blogshq-ai-accordion-trigger');
        
        triggers.forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                var expanded = this.getAttribute('aria-expanded') === 'true';
                var contentId = this.getAttribute('aria-controls');
                var content = document.getElementById(contentId);
                var accordion = this.closest('.blogshq-ai-accordion');
                
                if (!content) return;
                
                // Toggle accordion
                this.setAttribute('aria-expanded', !expanded);
                content.setAttribute('aria-hidden', expanded);
                
                if (expanded) {
                    accordion.classList.remove('is-open');
                    content.style.maxHeight = null;
                } else {
                    accordion.classList.add('is-open');
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccordion);
    } else {
        initAccordion();
    }
})();