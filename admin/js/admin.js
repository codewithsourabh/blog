(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize color picker
        if ($.fn.wpColorPicker) {
            $('.blogshq-color-picker').wpColorPicker();
        }

        // AJAX Tab Switching
        const $tabContent = $('.blogshq-tab-content');
        const $navTabs = $('.blogshq-nav-tab');

        $navTabs.on('click', function(e) {
            e.preventDefault();

            const $clickedTab = $(this);
            const tab = $clickedTab.attr('href').split('tab=')[1];

            if ($clickedTab.hasClass('active')) {
                return;
            }
            $navTabs.removeClass('active').attr('aria-selected', 'false');
            $clickedTab.addClass('active').attr('aria-selected', 'true');
            $tabContent.addClass('blogshq-loading');
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', tab);
            window.history.pushState({ tab: tab }, '', newUrl);

            $.ajax({
                url: blogshqAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'blogshq_load_tab',
                    tab: tab,
                    nonce: blogshqAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $tabContent.html(response.data.content);
                        if ($.fn.wpColorPicker) {
                            $('.blogshq-color-picker').wpColorPicker();
                        }
                        $(document).trigger('blogshq:tab-loaded', [tab]);
                    } else {
                        console.error('Error loading tab:', response.data.message);
                        $tabContent.html('<div class="notice notice-error"><p>' +
                            blogshqAdmin.strings.error + '</p></div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    $tabContent.html('<div class="notice notice-error"><p>' +
                        blogshqAdmin.strings.error + '</p></div>');
                },
                complete: function() {
                    $tabContent.removeClass('blogshq-loading');
                }
            });
        });

        // Handle browser back/forward buttons
        $(window).on('popstate', function(e) {
            if (e.originalEvent.state && e.originalEvent.state.tab) {
                const tab = e.originalEvent.state.tab;
                const $targetTab = $('.blogshq-nav-tab[href*="tab=' + tab + '"]');
                if ($targetTab.length) {
                    $targetTab.trigger('click');
                }
            }
        });

        // Form submission handler (AJAX) with scroll to notice
        $(document).on('submit', '.blogshq-settings-section form', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('input[type="submit"]');
            const originalText = $submitBtn.val();

            // Disable submit button
            $submitBtn.prop('disabled', true).val(blogshqAdmin.strings.saving || 'Saving...');

            $.ajax({
                url: blogshqAdmin.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=blogshq_save_settings',
                success: function(response) {
                    let $notice;
                    if (response.success) {
                        $form.before('<div class="notice notice-success is-dismissible"><p>' +
                            (response.data.message || blogshqAdmin.strings.saved) + '</p></div>');
                        $notice = $form.prev('.notice-success');
                        // Auto-dismiss after 3 seconds
                        setTimeout(function() {
                            $('.notice-success').fadeOut(function() {
                                $(this).remove();
                            });
                        }, 3000);
                    } else {
                        $form.before('<div class="notice notice-error is-dismissible"><p>' +
                            (response.data.message || blogshqAdmin.strings.error) + '</p></div>');
                        $notice = $form.prev('.notice-error');
                    }
                    // Scroll to notice
                    if ($notice && $notice.length) {
                        $('html, body').animate({
                            scrollTop: $notice.offset().top - 40
                        }, 400);
                    }
                },
                error: function() {
                    $form.before('<div class="notice notice-error is-dismissible"><p>' +
                        blogshqAdmin.strings.error + '</p></div>');
                    var $notice = $form.prev('.notice-error');
                    if ($notice.length) {
                        $('html, body').animate({
                            scrollTop: $notice.offset().top - 40
                        }, 400);
                    }
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).val(originalText);
                    // Make notices dismissible
                    $('.notice.is-dismissible').each(function() {
                        const $notice = $(this);
                        $('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>')
                            .appendTo($notice)
                            .on('click', function() {
                                $notice.fadeOut(function() {
                                    $(this).remove();
                                });
                            });
                    });
                }
            });
        });
    });

})(jQuery);
