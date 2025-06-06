/**
 * Guest Post Form Component
 * 
 * This component renders the guest post submission form with React.
 */

import React, { useState, useEffect, useRef } from 'react';
import FormField from './FormField';
import SuccessMessage from './SuccessMessage';
import ErrorMessage from './ErrorMessage';

const GuestPostForm = (props) => {
    // Form state
    const [formState, setFormState] = useState({
        title: '',
        content: '',
        authorName: '',
        authorEmail: '',
        authorBio: '',
        category: '',
        excerpt: '',
        captchaAnswer: '',
        featuredImage: null,
        imagePreview: null,
        submitting: false,
        success: false,
        errors: {}
    });

    // Editor ref for TinyMCE
    const editorRef = useRef(null);

    // Handle input changes
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormState(prev => ({
            ...prev,
            [name]: value,
            errors: {
                ...prev.errors,
                [name]: validateField(name, value)
            }
        }));
    };

    // Handle file input change
    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (!file) {
            setFormState(prev => ({
                ...prev,
                featuredImage: null,
                imagePreview: null,
                errors: {
                    ...prev.errors,
                    featuredImage: null
                }
            }));
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            setFormState(prev => ({
                ...prev,
                errors: {
                    ...prev.errors,
                    featuredImage: 'Please upload a valid image file (JPEG, PNG, or GIF)'
                }
            }));
            return;
        }

        // Validate file size (5MB max)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            setFormState(prev => ({
                ...prev,
                errors: {
                    ...prev.errors,
                    featuredImage: 'Image file is too large. Maximum size is 5MB.'
                }
            }));
            return;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            setFormState(prev => ({
                ...prev,
                featuredImage: file,
                imagePreview: e.target.result,
                errors: {
                    ...prev.errors,
                    featuredImage: null
                }
            }));
        };
        reader.readAsDataURL(file);
    };

    // Remove image preview
    const removeImage = () => {
        setFormState(prev => ({
            ...prev,
            featuredImage: null,
            imagePreview: null
        }));
        // Reset file input
        document.getElementById('gpfs_featured_image').value = '';
    };

    // Validate a single field
    const validateField = (name, value) => {
        switch (name) {
            case 'title':
                return value.trim() === '' ? 'Title is required' : null;
            case 'authorName':
                return value.trim() === '' ? 'Author name is required' : null;
            case 'authorEmail':
                return value.trim() === '' ? 'Email is required' : 
                       !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? 'Please enter a valid email address' : null;
            case 'category':
                return value === '' ? 'Please select a category' : null;
            case 'captchaAnswer':
                return value.trim() === '' ? 'Please answer the security question' : null;
            default:
                return null;
        }
    };

    // Validate all fields before submission
    const validateForm = () => {
        const errors = {};
        
        // Validate text inputs
        errors.title = validateField('title', formState.title);
        errors.authorName = validateField('authorName', formState.authorName);
        errors.authorEmail = validateField('authorEmail', formState.authorEmail);
        errors.category = validateField('category', formState.category);
        
        // Validate content from TinyMCE if available
        let content = formState.content;
        if (window.tinyMCE && window.tinyMCE.get('gpfs_content')) {
            content = window.tinyMCE.get('gpfs_content').getContent();
        }
        
        if (!content || content.trim() === '') {
            errors.content = 'Content is required';
        }
        
        // Validate captcha if enabled
        if (props.captchaEnabled) {
            errors.captchaAnswer = validateField('captchaAnswer', formState.captchaAnswer);
        }
        
        setFormState(prev => ({
            ...prev,
            errors
        }));
        
        // Return true if no errors
        return !Object.values(errors).some(error => error !== null);
    };

    // Handle form submission
    const handleSubmit = (e) => {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            // Scroll to first error
            const firstError = document.querySelector('.gpfs-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        
        // Set submitting state
        setFormState(prev => ({
            ...prev,
            submitting: true
        }));
        
        // Get the native form element and submit it
        // This will trigger the regular WordPress form submission
        // which is already set up to handle the server-side processing
        e.target.submit();
    };

    // Update content when TinyMCE changes
    useEffect(() => {
        if (window.tinyMCE) {
            const editor = window.tinyMCE.get('gpfs_content');
            if (editor) {
                editor.on('change', () => {
                    setFormState(prev => ({
                        ...prev,
                        content: editor.getContent()
                    }));
                });
            }
        }
    }, []);

    return (
        <div className="gpfs-card" role="region" aria-label="Guest Post Submission Form">
            {props.formError && (
                <ErrorMessage message={props.formError} />
            )}
            
            {props.formSuccess && (
                <SuccessMessage message={props.successMessage || "Thanks for your submission! We'll review and get back soon."} />
            )}
            
            <div className="gpfs-card-header">
                <h3 id="gpfs-form-title">{props.formTitle || 'Submit a Guest Post'}</h3>
            </div>
            
            <form 
                id="gpfs-submission-form" 
                className="gpfs-form" 
                method="post" 
                encType="multipart/form-data"
                onSubmit={handleSubmit}
                noValidate
            >
                {/* Title field */}
                <FormField
                    type="text"
                    id="gpfs_title"
                    name="gpfs_title"
                    label={props.labels?.title || 'Post Title'}
                    required={true}
                    value={formState.title}
                    onChange={(e) => handleChange({target: {name: 'title', value: e.target.value}})}
                    error={formState.errors.title}
                />
                
                {/* Content field - This will be replaced by TinyMCE */}
                <div className="gpfs-form-row">
                    <label htmlFor="gpfs_content" className="gpfs-label">
                        {props.labels?.content || 'Post Content'} <span className="text-red-500">*</span>
                    </label>
                    <div 
                        className={formState.errors.content ? 'border border-red-300 rounded-md' : ''}
                        aria-invalid={formState.errors.content ? 'true' : 'false'}
                    >
                        {/* TinyMCE will be initialized here by WordPress */}
                        <textarea 
                            id="gpfs_content" 
                            name="gpfs_content" 
                            rows="10"
                            ref={editorRef}
                            required
                            aria-required="true"
                            aria-describedby={formState.errors.content ? 'gpfs_content_error' : undefined}
                        ></textarea>
                    </div>
                    {formState.errors.content && (
                        <p id="gpfs_content_error" className="gpfs-error" role="alert">{formState.errors.content}</p>
                    )}
                </div>
                
                {/* Author Name field */}
                <FormField
                    type="text"
                    id="gpfs_author_name"
                    name="gpfs_author_name"
                    label={props.labels?.authorName || 'Author Name'}
                    required={true}
                    value={formState.authorName}
                    onChange={(e) => handleChange({target: {name: 'authorName', value: e.target.value}})}
                    error={formState.errors.authorName}
                />
                
                {/* Author Email field */}
                <FormField
                    type="email"
                    id="gpfs_author_email"
                    name="gpfs_author_email"
                    label={props.labels?.authorEmail || 'Author Email'}
                    required={true}
                    value={formState.authorEmail}
                    onChange={(e) => handleChange({target: {name: 'authorEmail', value: e.target.value}})}
                    error={formState.errors.authorEmail}
                />
                
                {/* Author Bio field */}
                <FormField
                    type="textarea"
                    id="gpfs_author_bio"
                    name="gpfs_author_bio"
                    label={props.labels?.authorBio || 'Author Bio'}
                    required={false}
                    value={formState.authorBio}
                    onChange={(e) => handleChange({target: {name: 'authorBio', value: e.target.value}})}
                    placeholder={props.placeholders?.authorBio || 'Tell us about yourself (optional)'}
                    description={props.descriptions?.authorBio || 'Share a brief bio that will be displayed with your post.'}
                />
                
                {/* Category dropdown */}
                <div className="gpfs-form-row">
                    <label htmlFor="gpfs_category" className="gpfs-label">
                        {props.labels?.category || 'Post Category'} <span className="text-red-500">*</span>
                    </label>
                    <select 
                        id="gpfs_category" 
                        name="gpfs_category" 
                        className={`gpfs-select gpfs-focus-visible ${formState.errors.category ? 'border-red-300' : ''}`}
                        value={formState.category}
                        onChange={(e) => handleChange({target: {name: 'category', value: e.target.value}})}
                        required
                        aria-required="true"
                        aria-invalid={formState.errors.category ? 'true' : 'false'}
                        aria-describedby={formState.errors.category ? 'gpfs_category_error' : undefined}
                    >
                        <option value="">{props.placeholders?.category || 'Select a category'}</option>
                        {props.categories?.map(category => (
                            <option key={category.id} value={category.id}>{category.name}</option>
                        ))}
                    </select>
                    {formState.errors.category && (
                        <p id="gpfs_category_error" className="gpfs-error" role="alert">{formState.errors.category}</p>
                    )}
                </div>
                
                {/* Excerpt field (optional) */}
                {props.showExcerpt && (
                    <FormField
                        type="textarea"
                        id="gpfs_excerpt"
                        name="gpfs_excerpt"
                        label={props.labels?.excerpt || 'Excerpt'}
                        required={false}
                        value={formState.excerpt}
                        onChange={(e) => handleChange({target: {name: 'excerpt', value: e.target.value}})}
                        description={props.descriptions?.excerpt || 'A short summary of your post. If left empty, an excerpt will be generated from your content.'}
                    />
                )}
                
                {/* Featured image field (optional) */}
                {props.showFeaturedImage && (
                    <div className="gpfs-form-row">
                        <label htmlFor="gpfs_featured_image" className="gpfs-label">
                            {props.labels?.featuredImage || 'Featured Image'}
                        </label>
                        <input 
                            type="file" 
                            id="gpfs_featured_image" 
                            name="gpfs_featured_image" 
                            accept="image/*"
                            className={`gpfs-file-input gpfs-focus-visible ${formState.errors.featuredImage ? 'border-red-300' : ''}`}
                            onChange={handleFileChange}
                            aria-invalid={formState.errors.featuredImage ? 'true' : 'false'}
                            aria-describedby="gpfs_featured_image_desc"
                        />
                        <p id="gpfs_featured_image_desc" className="mt-1 text-sm text-gray-500">
                            {props.descriptions?.featuredImage || 'Upload an image to be used as the featured image for your post. Allowed formats: JPEG, PNG, GIF.'}
                        </p>
                        
                        {formState.errors.featuredImage && (
                            <p className="gpfs-error" role="alert">{formState.errors.featuredImage}</p>
                        )}
                        
                        {formState.imagePreview && (
                            <div className="gpfs-image-preview-container">
                                <img 
                                    src={formState.imagePreview} 
                                    alt="Featured image preview" 
                                    className="gpfs-image-preview"
                                />
                                <button 
                                    type="button" 
                                    onClick={removeImage}
                                    className="gpfs-remove-image"
                                    aria-label="Remove image"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        )}
                    </div>
                )}
                
                {/* CAPTCHA field (if enabled) */}
                {props.captchaEnabled && (
                    <div className="gpfs-form-row">
                        <div className="gpfs-captcha-field">
                            <label htmlFor="gpfs_captcha" className="gpfs-label">
                                {props.labels?.captcha || 'Security Question'} <span className="text-red-500">*</span>
                            </label>
                            <div className="gpfs-captcha-question">
                                {props.captchaQuestion}
                            </div>
                            <input 
                                type="number" 
                                id="gpfs_captcha" 
                                name="gpfs_captcha" 
                                className={`gpfs-input gpfs-focus-visible ${formState.errors.captchaAnswer ? 'border-red-300' : ''}`}
                                value={formState.captchaAnswer}
                                onChange={(e) => handleChange({target: {name: 'captchaAnswer', value: e.target.value}})}
                                required
                                aria-required="true"
                                aria-invalid={formState.errors.captchaAnswer ? 'true' : 'false'}
                                aria-describedby={formState.errors.captchaAnswer ? 'gpfs_captcha_error' : undefined}
                            />
                            {formState.errors.captchaAnswer && (
                                <p id="gpfs_captcha_error" className="gpfs-error" role="alert">{formState.errors.captchaAnswer}</p>
                            )}
                        </div>
                    </div>
                )}
                
                {/* Honeypot field to prevent spam */}
                <div className="gpfs-honeypot" aria-hidden="true">
                    <input type="text" name="gpfs_website" tabIndex="-1" autoComplete="off" />
                </div>
                
                {/* WordPress nonce field */}
                <input type="hidden" name="gpfs_nonce" value={props.nonce} />
                
                {/* Submit button */}
                <div className="gpfs-form-row">
                    <button 
                        type="submit" 
                        name="gpfs_submit_post" 
                        value="1"
                        className="gpfs-submit-button gpfs-focus-visible"
                        disabled={formState.submitting}
                    >
                        {formState.submitting ? (
                            <>
                                <span className="gpfs-loading mr-2" role="status" aria-hidden="true"></span>
                                <span>{props.labels?.submitting || 'Submitting...'}</span>
                            </>
                        ) : (
                            props.labels?.submit || 'Submit Post'
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default GuestPostForm;
