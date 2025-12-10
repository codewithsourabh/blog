/**
 * AI Share Block JavaScript
 *
 * @package BlogsHQ
 * @since 1.0.0
 */

(function() {
	'use strict';

	/**
	 * Initialize copy link functionality
	 */
	function initCopyLink() {
		var copyLinks = document.querySelectorAll('.blogshq-copy-link');
		
		copyLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				copyToClipboard(this);
			});
		});
	}

	/**
	 * Copy URL to clipboard
	 */
	function copyToClipboard(element) {
		var url = element.getAttribute('data-url');
		
		if (!url) {
			url = window.location.href;
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			// Modern clipboard API
			navigator.clipboard.writeText(url)
				.then(function() {
					showCopiedFeedback(element);
				})
				.catch(function(err) {
					console.error('Clipboard API failed:', err);
					fallbackCopyToClipboard(url, element);
				});
		} else {
			// Fallback for older browsers
			fallbackCopyToClipboard(url, element);
		}
	}

	/**
	 * Show copied feedback message
	 */
	function showCopiedFeedback(element) {
		var feedback = element.querySelector('.copied-link-feedback');
		
		if (!feedback) {
			feedback = document.createElement('span');
			feedback.className = 'copied-link-feedback';
			feedback.textContent = blogshqAiShare.copiedText || 'Copied!';
			element.appendChild(feedback);
		}
		
		feedback.classList.add('show');
		
		setTimeout(function() {
			feedback.classList.remove('show');
		}, 2000);
	}

	/**
	 * Fallback copy method for older browsers
	 */
	function fallbackCopyToClipboard(text, element) {
		var textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed';
		textArea.style.top = '0';
		textArea.style.left = '0';
		textArea.style.width = '2em';
		textArea.style.height = '2em';
		textArea.style.padding = '0';
		textArea.style.border = 'none';
		textArea.style.outline = 'none';
		textArea.style.boxShadow = 'none';
		textArea.style.background = 'transparent';

		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			if (successful) {
				showCopiedFeedback(element);
			} else {
				console.error('Fallback copy failed');
			}
		} catch (err) {
			console.error('Fallback: Could not copy text:', err);
		}

		document.body.removeChild(textArea);
	}

	/**
	 * Initialize on DOM ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCopyLink);
	} else {
		initCopyLink();
	}

})();