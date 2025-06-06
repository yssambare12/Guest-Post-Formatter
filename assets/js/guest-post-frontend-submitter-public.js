/**
 * Public-facing JavaScript for the Guest Post Frontend Submitter plugin.
 */
(function($) {
    'use strict';

    /**
     * Initialize the form validation and submission handling.
     */
    function initGuestPostForm() {
        const $form = $('#gpfs-submission-form');
        
        if (!$form.length) {
            return;
        }
        
        // Form validation
        $form.on('submit', function(e) {
            const $requiredFields = $form.find('[required]');
            let isValid = true;
            
            // Check all required fields
            $requiredFields.each(function() {
                const $field = $(this);
                
                if (!$field.val().trim()) {
                    isValid = false;
                    $field.addClass('gpfs-error');
                } else {
                    $field.removeClass('gpfs-error');
                }
            });
            
            // Validate content length if needed
            const $content = $('#gpfs_content');
            if ($content.length) {
                // Get content from TinyMCE if it's active
                let content = '';
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('gpfs_content')) {
                    content = tinyMCE.get('gpfs_content').getContent();
                } else {
                    content = $content.val().trim();
                }
                
                if (content.length < 50) {
                    isValid = false;
                    
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('gpfs_content')) {
                        $(tinyMCE.get('gpfs_content').getContainer()).addClass('gpfs-error');
                    } else {
                        $content.addClass('gpfs-error');
                    }
                    
                    if (!$form.find('.gpfs-content-error').length) {
                        $content.closest('.gpfs-form-field').append('<p class="gpfs-content-error gpfs-error-message">Content must be at least 50 characters.</p>');
                    }
                } else {
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('gpfs_content')) {
                        $(tinyMCE.get('gpfs_content').getContainer()).removeClass('gpfs-error');
                    } else {
                        $content.removeClass('gpfs-error');
                    }
                    $form.find('.gpfs-content-error').remove();
                }
            }
            
            // Validate email format
            const $email = $('#gpfs_author_email');
            if ($email.length && $email.val().trim()) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test($email.val().trim())) {
                    isValid = false;
                    $email.addClass('gpfs-error');
                    
                    if (!$form.find('.gpfs-email-error').length) {
                        $email.after('<p class="gpfs-email-error gpfs-error-message">Please enter a valid email address.</p>');
                    }
                } else {
                    $email.removeClass('gpfs-error');
                    $form.find('.gpfs-email-error').remove();
                }
            }
            
            // Validate file size if featured image is included
            const $featuredImage = $('#gpfs_featured_image');
            if ($featuredImage.length && $featuredImage[0].files.length > 0) {
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if ($featuredImage[0].files[0].size > maxSize) {
                    isValid = false;
                    $featuredImage.addClass('gpfs-error');
                    
                    if (!$form.find('.gpfs-image-error').length) {
                        $featuredImage.after('<p class="gpfs-image-error gpfs-error-message">Image must be less than 2MB.</p>');
                    }
                } else {
                    $featuredImage.removeClass('gpfs-error');
                    $form.find('.gpfs-image-error').remove();
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Scroll to the first error
                const $firstError = $form.find('.gpfs-error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 500);
                }
            }
        });
        
        // Clear error styling on input
        $form.on('input', '[required]', function() {
            $(this).removeClass('gpfs-error');
            $(this).next('.gpfs-error-message').remove();
        });
        
        // Preview image when selected
        const $featuredImage = $('#gpfs_featured_image');
        if ($featuredImage.length) {
            $featuredImage.on('change', function() {
                const file = this.files[0];
                
                if (file) {
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        alert(gpfs_ajax.strings.invalid_file_type);
                        this.value = '';
                        return;
                    }
                    
                    // Validate file size (5MB max)
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        alert(gpfs_ajax.strings.file_too_large);
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const $previewContainer = $('#gpfs-image-preview-container');
                        
                        // Clear previous preview
                        $previewContainer.empty();
                        
                        // Create image element
                        const $img = $('<img>', {
                            src: e.target.result,
                            alt: 'Featured image preview',
                            class: 'gpfs-image-preview'
                        });
                        
                        // Add remove button
                        const $removeBtn = $('<button>', {
                            type: 'button',
                            class: 'gpfs-remove-image',
                            text: gpfs_ajax.strings.remove_image
                        }).on('click', function(e) {
                            e.preventDefault();
                            $featuredImage.val('');
                            $previewContainer.empty();
                        });
                        
                        // Add to preview container
                        $previewContainer.append($img, $removeBtn);
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
        }
    }
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        initGuestPostForm();
    });

})(jQuery);
