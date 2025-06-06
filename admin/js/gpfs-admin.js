/**
 * Admin-specific JavaScript for the Guest Post Frontend Submitter plugin.
 */
(function($) {
    'use strict';

    /**
     * Initialize the admin functionality.
     */
    function initGpfsAdmin() {
        // Tab functionality
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and content
            $('.nav-tab').removeClass('nav-tab-active');
            $('.gpfs-tab-content').removeClass('active');
            
            // Add active class to clicked tab
            $(this).addClass('nav-tab-active');
            
            // Show corresponding content
            $($(this).attr('href')).addClass('active');
            
            // Store the active tab in localStorage
            localStorage.setItem('gpfs_active_tab', $(this).attr('href'));
        });
        
        // Restore active tab from localStorage
        const activeTab = localStorage.getItem('gpfs_active_tab');
        if (activeTab) {
            $('.nav-tab[href="' + activeTab + '"]').trigger('click');
        }
        
        // Toggle submission limits fields based on checkbox
        const $enableLimits = $('input[name="gpfs_settings[enable_submission_limits]"]');
        const $maxSubmissions = $('input[name="gpfs_settings[max_submissions_per_day]"]').closest('tr');
        
        function toggleSubmissionLimits() {
            if ($enableLimits.is(':checked')) {
                $maxSubmissions.show();
            } else {
                $maxSubmissions.hide();
            }
        }
        
        $enableLimits.on('change', toggleSubmissionLimits);
        toggleSubmissionLimits();
        
        // Toggle email marketing service settings
        const $emailMarketingService = $('#gpfs_email_marketing_service');
        
        function toggleEmailMarketingSettings() {
            const service = $emailMarketingService.val();
            $('.service-settings').hide();
            
            if (service === 'mailchimp') {
                $('#mailchimp-settings').show();
            } else if (service === 'convertkit') {
                $('#convertkit-settings').show();
            }
        }
        
        $emailMarketingService.on('change', toggleEmailMarketingSettings);
        toggleEmailMarketingSettings();
        
        // Toggle custom theme settings
        const $formTheme = $('#gpfs_form_theme');
        const $customThemeSettings = $('#custom-theme-settings');
        
        function toggleCustomThemeSettings() {
            if ($formTheme.val() === 'custom') {
                $customThemeSettings.show();
            } else {
                $customThemeSettings.hide();
            }
        }
        
        $formTheme.on('change', toggleCustomThemeSettings);
        toggleCustomThemeSettings();
        
        // Live preview for form styling
        function updateFormPreview() {
            const theme = $formTheme.val();
            const $preview = $('#form-preview');
            
            // Reset to default
            $preview.removeClass('theme-light theme-dark theme-custom');
            
            if (theme === 'light') {
                $preview.addClass('theme-light');
                $preview.css({
                    '--background-color': '#ffffff',
                    '--text-color': '#333333',
                    '--button-color': '#0073aa',
                    '--button-text-color': '#ffffff'
                });
            } else if (theme === 'dark') {
                $preview.addClass('theme-dark');
                $preview.css({
                    '--background-color': '#333333',
                    '--text-color': '#ffffff',
                    '--button-color': '#0073aa',
                    '--button-text-color': '#ffffff'
                });
            } else if (theme === 'custom') {
                $preview.addClass('theme-custom');
                $preview.css({
                    '--background-color': $('input[name="gpfs_settings[background_color]"]').val(),
                    '--text-color': $('input[name="gpfs_settings[text_color]"]').val(),
                    '--button-color': $('input[name="gpfs_settings[button_color]"]').val(),
                    '--button-text-color': $('input[name="gpfs_settings[button_text_color]"]').val()
                });
            }
        }
        
        $formTheme.on('change', updateFormPreview);
        $('input[type="color"]').on('input', updateFormPreview);
        updateFormPreview();
        
        // Add merge tag insertion buttons for email templates
        const mergeTags = [
            {tag: '{site_name}', desc: 'Website Name'},
            {tag: '{site_url}', desc: 'Website URL'},
            {tag: '{post_title}', desc: 'Post Title'},
            {tag: '{post_content}', desc: 'Post Content'},
            {tag: '{author_name}', desc: 'Author Name'},
            {tag: '{author_email}', desc: 'Author Email'},
            {tag: '{author_bio}', desc: 'Author Bio'},
            {tag: '{submission_date}', desc: 'Submission Date'},
            {tag: '{edit_link}', desc: 'Edit Link'},
            {tag: '{post_link}', desc: 'Post Link'},
            {tag: '{approve_link}', desc: 'Approve Link'},
            {tag: '{reject_link}', desc: 'Reject Link'}
        ];
        
        // Add merge tag buttons to each textarea
        $('textarea[name^="gpfs_settings["]').each(function() {
            const $textarea = $(this);
            const $container = $('<div class="gpfs-merge-tag-buttons"></div>');
            
            // Add heading
            $container.append('<span class="gpfs-merge-tag-heading">Insert Merge Tag: </span>');
            
            // Add buttons for each merge tag
            mergeTags.forEach(function(tag) {
                const $button = $('<button type="button" class="button button-small gpfs-merge-tag-button" data-tag="' + tag.tag + '">' + tag.desc + '</button>');
                $container.append($button);
                
                // Add click handler
                $button.on('click', function(e) {
                    e.preventDefault();
                    
                    // Get cursor position
                    const cursorPos = $textarea[0].selectionStart;
                    const textBefore = $textarea.val().substring(0, cursorPos);
                    const textAfter = $textarea.val().substring(cursorPos);
                    
                    // Insert tag at cursor position
                    $textarea.val(textBefore + $(this).data('tag') + textAfter);
                    
                    // Set focus back to textarea
                    $textarea.focus();
                });
            });
            
            // Add container after textarea
            $textarea.after($container);
        });
        
        // Add tooltips to form fields
        $('.form-table th').each(function() {
            const $th = $(this);
            const $description = $th.next('td').find('.description');
            
            if ($description.length) {
                const tooltipText = $description.text();
                const $tooltip = $('<span class="gpfs-tooltip dashicons dashicons-info"></span>');
                
                $tooltip.attr('title', tooltipText);
                $th.append($tooltip);
                
                // Initialize tooltip
                $tooltip.tipTip({
                    attribute: 'title',
                    fadeIn: 50,
                    fadeOut: 50,
                    delay: 200
                });
            }
        });
    }
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        initGpfsAdmin();
    });

})(jQuery);
