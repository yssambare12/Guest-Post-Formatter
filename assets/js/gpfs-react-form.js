/**
 * React components for the Guest Post Frontend Submitter form
 */

// Make sure React and ReactDOM are loaded
const { useState, useEffect, useRef } = React;
const { createRoot } = ReactDOM;

/**
 * Main Form Component
 */
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
    success: true,
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
        <div className="gpfs-error-message" role="alert">
          {props.formError}
        </div>
      )}
      
      {props.formSuccess && (
        <div className="gpfs-success-message" role="alert">
          <svg className="inline w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
          </svg>
          {props.successMessage || "Thanks for your submission! We'll review and get back soon."}
        </div>
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
        <div className="gpfs-form-row">
          <label htmlFor="gpfs_title" className="gpfs-label">
            {props.labels.title || 'Post Title'} <span className="text-red-500">*</span>
          </label>
          <input 
            type="text" 
            id="gpfs_title" 
            name="gpfs_title" 
            className={`gpfs-input gpfs-focus-visible ${formState.errors.title ? 'border-red-300' : ''}`}
            value={formState.title}
            onChange={handleChange}
            required
            aria-required="true"
            aria-invalid={formState.errors.title ? 'true' : 'false'}
            aria-describedby={formState.errors.title ? 'gpfs_title_error' : undefined}
          />
          {formState.errors.title && (
            <p id="gpfs_title_error" className="gpfs-error" role="alert">{formState.errors.title}</p>
          )}
        </div>
        
        {/* Content field - This will be replaced by TinyMCE */}
        <div className="gpfs-form-row">
          <label htmlFor="gpfs_content" className="gpfs-label">
            {props.labels.content || 'Post Content'} <span className="text-red-500">*</span>
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
        <div className="gpfs-form-row">
          <label htmlFor="gpfs_author_name" className="gpfs-label">
            {props.labels.authorName || 'Author Name'} <span className="text-red-500">*</span>
          </label>
          <input 
            type="text" 
            id="gpfs_author_name" 
            name="gpfs_author_name" 
            className={`gpfs-input gpfs-focus-visible ${formState.errors.authorName ? 'border-red-300' : ''}`}
            value={formState.authorName}
            onChange={(e) => handleChange({target: {name: 'authorName', value: e.target.value}})}
            required
            aria-required="true"
            aria-invalid={formState.errors.authorName ? 'true' : 'false'}
            aria-describedby={formState.errors.authorName ? 'gpfs_author_name_error' : undefined}
          />
          {formState.errors.authorName && (
            <p id="gpfs_author_name_error" className="gpfs-error" role="alert">{formState.errors.authorName}</p>
          )}
        </div>
        
        {/* Author Email field */}
        <div className="gpfs-form-row">
          <label htmlFor="gpfs_author_email" className="gpfs-label">
            {props.labels.authorEmail || 'Author Email'} <span className="text-red-500">*</span>
          </label>
          <input 
            type="email" 
            id="gpfs_author_email" 
            name="gpfs_author_email" 
            className={`gpfs-input gpfs-focus-visible ${formState.errors.authorEmail ? 'border-red-300' : ''}`}
            value={formState.authorEmail}
            onChange={(e) => handleChange({target: {name: 'authorEmail', value: e.target.value}})}
            required
            aria-required="true"
            aria-invalid={formState.errors.authorEmail ? 'true' : 'false'}
            aria-describedby={formState.errors.authorEmail ? 'gpfs_author_email_error' : undefined}
          />
          {formState.errors.authorEmail && (
            <p id="gpfs_author_email_error" className="gpfs-error" role="alert">{formState.errors.authorEmail}</p>
          )}
        </div>
        
        {/* Author Bio field */}
        <div className="gpfs-form-row">
          <label htmlFor="gpfs_author_bio" className="gpfs-label">
            {props.labels.authorBio || 'Author Bio'}
          </label>
          <textarea 
            id="gpfs_author_bio" 
            name="gpfs_author_bio" 
            rows="4" 
            className="gpfs-textarea gpfs-focus-visible"
            value={formState.authorBio}
            onChange={(e) => handleChange({target: {name: 'authorBio', value: e.target.value}})}
            placeholder={props.placeholders.authorBio || 'Tell us about yourself (optional)'}
            aria-describedby="gpfs_author_bio_desc"
          ></textarea>
          <p id="gpfs_author_bio_desc" className="mt-1 text-sm text-gray-500">
            {props.descriptions.authorBio || 'Share a brief bio that will be displayed with your post.'}
          </p>
        </div>
        
        {/* Category dropdown */}
        <div className="gpfs-form-row">
          <label htmlFor="gpfs_category" className="gpfs-label">
            {props.labels.category || 'Post Category'} <span className="text-red-500">*</span>
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
            <option value="">{props.placeholders.category || 'Select a category'}</option>
            {props.categories.map(category => (
              <option key={category.id} value={category.id}>{category.name}</option>
            ))}
          </select>
          {formState.errors.category && (
            <p id="gpfs_category_error" className="gpfs-error" role="alert">{formState.errors.category}</p>
          )}
        </div>
        
        {/* Excerpt field (optional) */}
        {props.showExcerpt && (
          <div className="gpfs-form-row">
            <label htmlFor="gpfs_excerpt" className="gpfs-label">
              {props.labels.excerpt || 'Excerpt'}
            </label>
            <textarea 
              id="gpfs_excerpt" 
              name="gpfs_excerpt" 
              rows="3" 
              className="gpfs-textarea gpfs-focus-visible"
              value={formState.excerpt}
              onChange={(e) => handleChange({target: {name: 'excerpt', value: e.target.value}})}
              aria-describedby="gpfs_excerpt_desc"
            ></textarea>
            <p id="gpfs_excerpt_desc" className="mt-1 text-sm text-gray-500">
              {props.descriptions.excerpt || 'A short summary of your post. If left empty, an excerpt will be generated from your content.'}
            </p>
          </div>
        )}
        
        {/* Featured image field (optional) */}
        {props.showFeaturedImage && (
          <div className="gpfs-form-row">
            <label htmlFor="gpfs_featured_image" className="gpfs-label">
              {props.labels.featuredImage || 'Featured Image'}
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
              {props.descriptions.featuredImage || 'Upload an image to be used as the featured image for your post. Allowed formats: JPEG, PNG, GIF.'}
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
                {props.labels.captcha || 'Security Question'} <span className="text-red-500">*</span>
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
                <span>{props.labels.submitting || 'Submitting...'}</span>
              </>
            ) : (
              props.labels.submit || 'Submit Post'
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

/**
 * Initialize the React form
 */
function initGuestPostReactForm() {
  // Find all form containers
  const formContainers = document.querySelectorAll('.gpfs-react-form-container');
  
  formContainers.forEach(container => {
    // Get data from the container
    const data = JSON.parse(container.dataset.formConfig || '{}');
    
    // Create root and render
    const root = createRoot(container);
    root.render(<GuestPostForm {...data} />);
  });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initGuestPostReactForm);
