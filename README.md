# Guest Post Frontend Submitter

A WordPress plugin that enables front-end guest post submission with a simple shortcode.

## Introduction ..

Guest Post Frontend Submitter allows visitors to submit posts from the front-end of your WordPress site. It's perfect for blogs that accept guest contributions, community websites, or any site that wants to encourage user-generated content.

## Features

* Simple shortcode to display the submission form anywhere on your site
* Customizable form fields including title, content, excerpt, and featured image
* Security features to prevent spam submissions
* Admin settings to control post status and category
* Email notifications with one-click approve/reject actions
* Responsive design that works on all devices
* Lightweight and optimized for performance
* Developer-friendly with hooks and filters for customization.

## Installation

### Via ZIP File

1. Download the ZIP file from the [GitHub repository](https://github.com/yourusername/guest-post-frontend-submitter)
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

### Via GitHub

1. Clone the repository to your WordPress plugins directory:
   ```
   cd wp-content/plugins/
   git clone https://github.com/yourusername/guest-post-frontend-submitter.git
   ```
2. Go to WordPress Admin > Plugins
3. Activate "Guest Post Frontend Submitter"

## Usage

### Basic Shortcode

Add the submission form to any page or post using the shortcode:

```
[guest_post_form]
```

### Enhanced React Form

For the enhanced React-based form with TailwindCSS styling:

```
[guest_post_react_form]
```

### Shortcode Attributes

Customize the form with these optional attributes:

* `title` - Form title (default: "Submit a Guest Post")
* `success_message` - Message displayed after successful submission
* `button_text` - Submit button text (default: "Submit Post")
* `show_excerpt` - Show excerpt field (yes/no, default: yes)
* `show_featured_image` - Show featured image upload field (yes/no, default: yes)

Example:

```
[guest_post_form title="Share Your Story" button_text="Submit Your Post" show_excerpt="no"]
```

### Configuration

1. Go to WordPress Admin > Guest Post Submitter > Settings
2. Configure the general settings, submission limits, email templates, anti-spam features, and more
3. Save your changes

## Troubleshooting

### Form Not Displaying

* Make sure the shortcode is correctly formatted: `[guest_post_form]`
* Check if there are any JavaScript errors in the browser console
* Try switching to a default WordPress theme to rule out theme conflicts

### Email Notifications Not Working

* Check your spam folder
* Verify that your WordPress site can send emails (try a plugin like Check Email)
* Make sure the notification email address is correct in the settings

### Submissions Not Saving

* Check if your server has proper permissions to write to the database
* Verify that the post status setting is correct
* Check for any error messages during submission

## FAQ

### Can I customize the submission form?

Yes, you can customize the form using shortcode attributes and the Form Style settings. You can also use CSS to further customize the appearance.

### How do I moderate submissions?

You can moderate submissions through email notifications, the dashboard widget, or the Submissions page. You can approve or reject posts with a single click.

### Can I limit the number of submissions?

Yes, you can limit the number of submissions per IP address per day in the Submission Limits settings.

### How do I prevent spam submissions?

The plugin includes several anti-spam features: reCAPTCHA, honeypot, domain blocking, keyword filtering, and OpenAI Moderation.

### Can I add guest post authors to my email list?

Yes, the plugin integrates with Mailchimp and ConvertKit to add guest post authors to your email list with their consent.

## Development

### Prerequisites

* Node.js and npm
* WordPress development environment

### Setup

1. Clone the repository
2. Install dependencies:
   ```
   npm install
   ```

### Build

To build the React components:

```
npm run build
```

For development with hot reloading:

```
npm start
```

## License

This plugin is licensed under the GPL v2 or later.

## Credits

* [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate)
* [TailwindCSS](https://tailwindcss.com/)
* [React](https://reactjs.org/)
