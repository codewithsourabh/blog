/**
 * Link Icon JavaScript for TOC
 *
 * @package BlogsHQ
 * @since 1.0.0
 */
(function() {
    'use strict';

    var settings = blogshqLinkIcon || {};
    var headingSelectors = settings.headings || ['h2'];
    var iconColor = settings.iconColor || '#2E62E9';
    var copiedText = settings.copiedText || 'The link has been copied to your clipboard.';
    var copyLabel = settings.copyLabel || 'Copy link to this section';

function showPopup() {
    var popup = document.getElementById('blogshq-link-popup');
    
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'blogshq-link-popup';
        
        // Create container for icon and text
        var content = document.createElement('div');
        content.style.cssText = 'display: flex; align-items: center; gap: 10px;';
        
        // Create SVG check icon
        var svgIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svgIcon.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        svgIcon.setAttribute('viewBox', '0 0 640 640');
        svgIcon.setAttribute('width', '20');
        svgIcon.setAttribute('height', '20');
        svgIcon.style.cssText = 'flex-shrink: 0; color: #fff;';
        
        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', 'M320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576zM438 209.7C427.3 201.9 412.3 204.3 404.5 215L285.1 379.2L233 327.1C223.6 317.7 208.4 317.7 199.1 327.1C189.8 336.5 189.7 351.7 199.1 361L271.1 433C276.1 438 282.9 440.5 289.9 440C296.9 439.5 303.3 435.9 307.4 430.2L443.3 243.2C451.1 232.5 448.7 217.5 438 209.7z');
        path.setAttribute('fill', 'currentColor');
        
        svgIcon.appendChild(path);
        
        // Create text element
        var text = document.createElement('span');
        text.textContent = copiedText;
        text.style.cssText = 'line-height: 1.4;';
        
        content.appendChild(svgIcon);
        content.appendChild(text);
        popup.appendChild(content);
        
        popup.style.cssText = 'display:none;position:fixed;bottom:-40px;left:50%;transform:translateX(-50%);padding:16px 24px;border-radius:8px;color:white;font-weight:500;font-size:16px;box-shadow:0 4px 24px rgba(0,0,0,0.07);transition:bottom 0.5s,opacity 0.5s;z-index:9999;';
        popup.style.backgroundColor = iconColor;
        document.body.appendChild(popup);
    } else {
        // Update content if popup already exists
        var existingContent = popup.querySelector('div');
        if (existingContent) {
            var text = existingContent.querySelector('span');
            if (text) {
                text.textContent = copiedText;
            }
        }
    }

    var isMobile = window.innerWidth <= 768;
    var bottomPosition = isMobile ? '60px' : '40px';
    
    popup.style.display = 'block';
    
    setTimeout(function() {
        popup.style.bottom = bottomPosition;
        popup.style.opacity = '1';
    }, 10);
    
    setTimeout(function() {
        popup.style.bottom = '-40px';
        popup.style.opacity = '0';
        setTimeout(function() {
            popup.style.display = 'none';
        }, 500);
    }, 3000);
}
    function copyLinkToClipboard(headingId) {
        var link = window.location.origin + window.location.pathname + '#' + headingId;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link)
                .then(function() {
                    showPopup();
                })
                .catch(function(err) {
                    console.error('Failed to copy:', err);
                });
        } else {
            var textArea = document.createElement('textarea');
            textArea.value = link;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                showPopup();
            } catch (err) {
                console.error('Fallback copy failed:', err);
            }
            
            document.body.removeChild(textArea);
        }
    }

    function createLinkIcon() {
        var icon = document.createElement('button');
        icon.className = 'link-icon';
        icon.type = 'button';
        icon.setAttribute('role', 'button');
        icon.setAttribute('tabindex', '0');
        icon.setAttribute('aria-label', copyLabel);
        icon.style.cssText = 'display:inline-flex;align-items:center;margin-left:8px;vertical-align:middle;cursor:pointer;text-decoration:none;background:none;border:none;padding:0;';
        
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 640 640');
        svg.setAttribute('width', '20');
        svg.setAttribute('height', '20');
        svg.style.fill = iconColor;
        svg.style.verticalAlign = 'middle';
        svg.style.display = 'block';
        
        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', 'M451.5 160C434.9 160 418.8 164.5 404.7 172.7C388.9 156.7 370.5 143.3 350.2 133.2C378.4 109.2 414.3 96 451.5 96C537.9 96 608 166 608 252.5C608 294 591.5 333.8 562.2 363.1L491.1 434.2C461.8 463.5 422 480 380.5 480C294.1 480 224 410 224 323.5C224 322 224 320.5 224.1 319C224.6 301.3 239.3 287.4 257 287.9C274.7 288.4 288.6 303.1 288.1 320.8C288.1 321.7 288.1 322.6 288.1 323.4C288.1 374.5 329.5 415.9 380.6 415.9C405.1 415.9 428.6 406.2 446 388.8L517.1 317.7C534.4 300.4 544.2 276.8 544.2 252.3C544.2 201.2 502.8 159.8 451.7 159.8zM307.2 237.3C305.3 236.5 303.4 235.4 301.7 234.2C289.1 227.7 274.7 224 259.6 224C235.1 224 211.6 233.7 194.2 251.1L123.1 322.2C105.8 339.5 96 363.1 96 387.6C96 438.7 137.4 480.1 188.5 480.1C205 480.1 221.1 475.7 235.2 467.5C251 483.5 269.4 496.9 289.8 507C261.6 530.9 225.8 544.2 188.5 544.2C102.1 544.2 32 474.2 32 387.7C32 346.2 48.5 306.4 77.8 277.1L148.9 206C178.2 176.7 218 160.2 259.5 160.2C346.1 160.2 416 230.8 416 317.1C416 318.4 416 319.7 416 321C415.6 338.7 400.9 352.6 383.2 352.2C365.5 351.8 351.6 337.1 352 319.4C352 318.6 352 317.9 352 317.1C352 283.4 334 253.8 307.2 237.5z');
        svg.appendChild(path);
        
        icon.appendChild(svg);
        return icon;
    }

    function addIconsToHeadings() {
        var selectorParts = [];
        headingSelectors.forEach(function(tag) {
            selectorParts.push(tag + '[id]');
        });
        var selector = selectorParts.join(', ');
        
        var headings = document.querySelectorAll(selector);
        
        if (headings.length === 0) {
            headings = document.querySelectorAll('h1[id], h2[id], h3[id], h4[id], h5[id], h6[id]');
        }
        
        var iconsAdded = 0;
        
        headings.forEach(function(heading) {
            if (heading.querySelector('.link-icon')) {
                return;
            }

            var headingTag = heading.tagName.toLowerCase();
            if (headingSelectors.indexOf(headingTag) === -1) {
                return;
            }

            var icon = createLinkIcon();
            heading.appendChild(icon);
            iconsAdded++;

            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                copyLinkToClipboard(heading.id);
                icon.blur();
            });

            icon.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    copyLinkToClipboard(heading.id);
                }
            });
        });
        
        return iconsAdded;
    }

    /**
     * Smooth scroll to target element with offset
     */
    function smoothScrollToElement(targetId) {
        var target = document.getElementById(targetId);
        
        if (!target) {
            return false;
        }

        // Get the offset (80px as defined in CSS scroll-padding-top)
        var offset = 80;
        var targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;

        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });

        return true;
    }

    /**
     * Add smooth scroll to TOC links and update URL
     */
    function addSmoothScrollToTOC() {
        var tocLinks = document.querySelectorAll('.blogshq-toc a[href^="#"]');
        
        tocLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                var targetId = this.getAttribute('href').substring(1);
                
                // Update URL hash without jumping
                if (history.pushState) {
                    history.pushState(null, null, '#' + targetId);
                } else {
                    // Fallback for older browsers
                    window.location.hash = targetId;
                }
                
                // Smooth scroll to target
                smoothScrollToElement(targetId);
            });
        });
    }

    /**
     * Handle page load with hash in URL
     */
    function handleHashOnLoad() {
        // Check if there's a hash in the URL
        var hash = window.location.hash;
        
        if (hash && hash.length > 1) {
            // Remove the # from the hash
            var targetId = hash.substring(1);
            
            // Wait a bit for the page to fully render
            setTimeout(function() {
                smoothScrollToElement(targetId);
            }, 100);
        }
    }

    /**
     * Handle browser back/forward buttons
     */
    function handleHashChange() {
        var hash = window.location.hash;
        
        if (hash && hash.length > 1) {
            var targetId = hash.substring(1);
            smoothScrollToElement(targetId);
        }
    }

    function init() {
        addIconsToHeadings();
        addSmoothScrollToTOC();
        handleHashOnLoad();
        
        // Listen for hash changes (back/forward navigation)
        window.addEventListener('hashchange', handleHashChange);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();