(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Handle Email Signup Form Submission
         */
        $('.ytes-email-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var $message = $form.find('.ytes-form-message');
            var $email = $form.find('input[name="ytes_email"]');
            var $name = $form.find('input[name="ytes_name"]');
            
            // Disable button
            $button.prop('disabled', true).text('Subscribing...');
            $message.removeClass('ytes-success ytes-error').empty();
            
            // Send AJAX request
            $.ajax({
                url: ytesData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ytes_email_signup',
                    nonce: ytesData.nonce,
                    email: $email.val(),
                    name: $name.val() || '',
                    source: 'website'
                },
                success: function(response) {
                    if (response.success) {
                        $message.addClass('ytes-success').html(response.data.message);
                        $form[0].reset();
                        
                        // Track successful signup
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'email_signup', {
                                'event_category': 'engagement',
                                'event_label': 'email_form'
                            });
                        }
                    } else {
                        $message.addClass('ytes-error').html(response.data.message);
                    }
                },
                error: function() {
                    $message.addClass('ytes-error').html('An error occurred. Please try again.');
                },
                complete: function() {
                    $button.prop('disabled', false).text($button.data('original-text') || 'Subscribe');
                }
            });
        });
        
        /**
         * Store original button text
         */
        $('.ytes-email-form button[type="submit"]').each(function() {
            $(this).data('original-text', $(this).text());
        });
        
        /**
         * Social Share Button Click Tracking
         */
        $('.ytes-share-button').on('click', function(e) {
            var network = $(this).data('network');
            var href = $(this).attr('href');
            
            // Open in popup for desktop
            if (network !== 'whatsapp' && $(window).width() > 768) {
                e.preventDefault();
                var width = 600;
                var height = 400;
                var left = (screen.width - width) / 2;
                var top = (screen.height - height) / 2;
                
                window.open(
                    href,
                    'share',
                    'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top
                );
            }
            
            // Track with Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'share', {
                    'method': network,
                    'content_type': 'video',
                    'item_id': ytesData.currentPostId || ''
                });
            }
        });
        
        /**
         * CTA Button Click Tracking
         */
        $('.ytes-cta-button').on('click', function() {
            var videoUrl = $(this).data('video-url');
            
            // Track with Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'cta_click', {
                    'event_category': 'engagement',
                    'event_label': videoUrl || 'youtube_cta'
                });
            }
        });
        
        /**
         * YouTube Subscribe Button Tracking
         */
        $('.ytes-subscribe-button').on('click', function() {
            // Track with Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'subscribe_intent', {
                    'event_category': 'engagement',
                    'event_label': 'youtube_subscribe'
                });
            }
        });
        
        /**
         * Handle confirmation/unsubscribe query parameters
         */
        var urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('ytes_confirmed')) {
            showNotification('Your subscription has been confirmed! Thank you!', 'success');
            // Remove parameter from URL
            if (window.history && window.history.replaceState) {
                var cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }
        
        if (urlParams.has('ytes_unsubscribed')) {
            showNotification('You have been unsubscribed.', 'info');
            // Remove parameter from URL
            if (window.history && window.history.replaceState) {
                var cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }
        
        /**
         * Show notification message
         */
        function showNotification(message, type) {
            var $notification = $('<div class="ytes-notification ytes-notification-' + type + '">' + message + '</div>');
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.addClass('ytes-notification-show');
            }, 100);
            
            setTimeout(function() {
                $notification.removeClass('ytes-notification-show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 5000);
        }
        
        /**
         * Email validation
         */
        $('.ytes-email-form input[type="email"]').on('blur', function() {
            var email = $(this).val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $(this).addClass('ytes-input-error');
            } else {
                $(this).removeClass('ytes-input-error');
            }
        });
        
    });
    
})(jQuery);
