(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Settings page functionality
         */
        
        // Show/hide API key help text
        $('#ytes_youtube_api_key').on('focus', function() {
            $(this).next('.description').slideDown();
        });
        
        // Validate YouTube Channel ID format
        $('#ytes_youtube_channel_id').on('blur', function() {
            var channelId = $(this).val();
            if (channelId && !channelId.match(/^UC[\w-]{22}$/)) {
                $(this).css('border-color', '#dc3232');
                if (!$(this).next('.ytes-error-message').length) {
                    $(this).after('<p class="ytes-error-message" style="color: #dc3232; margin-top: 5px;">Channel ID should start with "UC" and be 24 characters long.</p>');
                }
            } else {
                $(this).css('border-color', '');
                $(this).next('.ytes-error-message').remove();
            }
        });
        
        // Validate email format
        $('input[type="email"]').on('blur', function() {
            var email = $(this).val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $(this).css('border-color', '#dc3232');
            } else {
                $(this).css('border-color', '');
            }
        });
        
        /**
         * Subscribers page functionality
         */
        
        // Confirm before exporting
        $('input[name="action"][value="export_subscribers"]').closest('form').on('submit', function(e) {
            var count = $('.ytes-stat-box h3').first().text();
            if (count && parseInt(count) > 0) {
                return confirm('Export ' + count + ' subscribers to CSV?');
            }
        });
        
        /**
         * Analytics page functionality
         */
        
        // Refresh stats (if you add AJAX refresh later)
        $('.ytes-refresh-stats').on('click', function(e) {
            e.preventDefault();
            location.reload();
        });
        
        /**
         * Add tooltips to form fields
         */
        $('[data-tooltip]').each(function() {
            var $field = $(this);
            var tooltip = $field.data('tooltip');
            
            $field.on('mouseenter', function() {
                var $tooltip = $('<div class="ytes-tooltip">' + tooltip + '</div>');
                $('body').append($tooltip);
                
                var offset = $field.offset();
                $tooltip.css({
                    top: offset.top - $tooltip.outerHeight() - 10,
                    left: offset.left
                }).fadeIn(200);
            });
            
            $field.on('mouseleave', function() {
                $('.ytes-tooltip').fadeOut(200, function() {
                    $(this).remove();
                });
            });
        });
        
        /**
         * Settings saved notification
         */
        if ($('.updated').length) {
            setTimeout(function() {
                $('.updated').fadeOut();
            }, 3000);
        }
        
    });
    
})(jQuery);
