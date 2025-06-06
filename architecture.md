# Guest Post Frontend Submitter - Architecture

This document provides an overview of the plugin's architecture, file structure, and data flow.

## File Structure

```
guest-post-frontend-submitter/
├── admin/                      # Admin-specific functionality
│   ├── class-gpfs-admin.php    # Admin class
│   ├── css/                    # Admin CSS files
│   ├── js/                     # Admin JavaScript files
│   └── partials/               # Admin page templates
├── assets/                     # Public-facing assets
│   ├── css/                    # CSS files
│   └── js/                     # JavaScript files
├── build/                      # Compiled React components
├── includes/                   # Core plugin functionality
│   ├── class-gpfs-anti-spam.php       # Anti-spam functionality
│   ├── class-gpfs-core.php            # Core plugin class
│   ├── class-gpfs-email-marketing.php # Email marketing integration
│   ├── class-gpfs-form-handler.php    # Form submission handler
│   ├── class-gpfs-loader.php          # Action/filter loader
│   ├── class-gpfs-notification.php    # Email notification system
│   ├── class-gpfs-react-form.php      # React form integration
│   └── class-gpfs-shortcode.php       # Shortcode functionality
├── languages/                  # Translation files
├── src/                        # React source files
├── tests/                      # Unit tests
├── architecture.md             # This file
├── guest-post-frontend-submitter.php  # Main plugin file
├── index.php                   # Silence is golden
├── package.json                # npm package configuration
├── product-requirements.md     # Product requirements
├── README.md                   # Plugin documentation
└── uninstall.php               # Cleanup on uninstall
```

## Core Components

### 1. Main Plugin File

`guest-post-frontend-submitter.php` is the entry point of the plugin. It:
- Defines constants
- Loads dependencies
- Initializes the plugin

### 2. Core Class

`class-gpfs-core.php` is the main plugin class that:
- Defines hooks for admin and public-facing functionality
- Registers scripts and styles
- Initializes the plugin components

### 3. Loader Class

`class-gpfs-loader.php` is responsible for:
- Registering actions and filters
- Maintaining references to all hooks
- Running the hooks when WordPress loads

### 4. Form Handler

`class-gpfs-form-handler.php` handles:
- Form submission processing
- Input validation and sanitization
- Post creation
- Featured image handling
- Custom fields processing

### 5. Shortcode Handler

`class-gpfs-shortcode.php` manages:
- Shortcode registration
- Form rendering
- Error handling
- Success messages

### 6. Notification System

`class-gpfs-notification.php` handles:
- Email notifications to admin
- Email notifications to authors
- Approval/rejection functionality

### 7. Admin Interface

`class-gpfs-admin.php` provides:
- Admin menu registration
- Settings page
- Submissions management
- Dashboard widget

### 8. Anti-Spam

`class-gpfs-anti-spam.php` implements:
- reCAPTCHA integration
- Honeypot field
- Domain blocking
- Keyword filtering
- OpenAI Moderation API integration

### 9. Email Marketing

`class-gpfs-email-marketing.php` handles:
- Mailchimp integration
- ConvertKit integration
- Consent management

### 10. React Integration

`class-gpfs-react-form.php` manages:
- React component initialization
- Data passing between PHP and React
- React form rendering

## Data Flow

### Form Submission Flow

1. User fills out the form on the frontend
2. Form is submitted via POST request
3. `GPFS_Form_Handler::process_submission()` validates the submission
4. Anti-spam checks are performed
5. Post is created with appropriate status
6. Featured image is uploaded and attached
7. Author metadata is saved
8. Email notification is sent to admin
9. Success message is displayed to user

### Moderation Flow

1. Admin receives email notification with approve/reject links
2. Admin clicks approve or reject link
3. `GPFS_Notification::approve_post()` or `GPFS_Notification::reject_post()` is called
4. Post status is updated accordingly
5. Email notification is sent to author
6. Admin is redirected to appropriate page

## Hooks and Filters

### Actions

- `gpfs_after_post_submission`: Fired after a post is successfully submitted
- `gpfs_before_post_approval`: Fired before a post is approved
- `gpfs_after_post_approval`: Fired after a post is approved
- `gpfs_before_post_rejection`: Fired before a post is rejected
- `gpfs_after_post_rejection`: Fired after a post is rejected
- `gpfs_email_marketing_subscription`: Fired when a user subscribes to email marketing

### Filters

- `gpfs_form_fields`: Modify form fields before rendering
- `gpfs_pre_insert_post_data`: Modify post data before insertion
- `gpfs_admin_notification_email`: Modify admin notification email
- `gpfs_author_notification_email`: Modify author notification email
- `gpfs_shortcode_atts`: Modify shortcode attributes

## React Setup

The plugin uses React for enhanced form functionality:

1. React components are defined in the `src/` directory
2. Components are compiled using `@wordpress/scripts`
3. Compiled assets are stored in the `build/` directory
4. Assets are enqueued in WordPress using `wp_enqueue_script()`
5. Data is passed from PHP to React using `wp_localize_script()`

## Database Schema

The plugin uses the standard WordPress posts table with custom meta fields:

- `_gpfs_author_name`: Author's name
- `_gpfs_author_email`: Author's email
- `_gpfs_author_bio`: Author's bio
- `_gpfs_author_ip`: Author's IP address
- `_gpfs_marketing_consent`: Marketing consent status

## Settings Storage

Plugin settings are stored in the WordPress options table:

- `gpfs_settings`: An array of all plugin settings

## Security Considerations

- All user inputs are sanitized using WordPress sanitization functions
- Nonces are used for form submissions and admin actions
- Capabilities are checked for admin actions
- Anti-spam measures are implemented
- Featured image uploads are validated for type and size
