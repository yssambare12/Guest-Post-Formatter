=== Guest Post Frontend Submitter ===
Contributors: yourname
Tags: guest post, frontend submission, user generated content
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable front-end guest post submission on your WordPress site with a simple shortcode.

== Description ==

Guest Post Frontend Submitter allows visitors to submit posts from the front-end of your WordPress site. It's perfect for blogs that accept guest contributions, community websites, or any site that wants to encourage user-generated content.

= Features =

* Simple shortcode to display the submission form anywhere on your site
* Customizable form fields including title, content, excerpt, and featured image
* Security features to prevent spam submissions
* Admin settings to control post status and category
* Responsive design that works on all devices
* Lightweight and optimized for performance
* Developer-friendly with hooks and filters for customization

= Usage =

Simply add the shortcode `[guest_post_form]` to any page or post where you want the submission form to appear.

Customize the form with these optional attributes:

* `title` - Form title (default: "Submit a Guest Post")
* `success_message` - Message displayed after successful submission
* `button_text` - Submit button text (default: "Submit Post")
* `show_excerpt` - Show excerpt field (yes/no, default: yes)
* `show_featured_image` - Show featured image upload field (yes/no, default: yes)

Example: `[guest_post_form title="Share Your Story" button_text="Submit Your Post"]`

== Installation ==

1. Upload the `guest-post-frontend-submitter` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Guest Post Submitter to configure the plugin
4. Add the shortcode `[guest_post_form]` to any page or post where you want the submission form to appear

== Frequently Asked Questions ==

= Can I customize the form fields? =

Yes, you can show/hide the excerpt and featured image fields using shortcode attributes. For more advanced customization, developers can use the provided hooks and filters.

= What happens after a post is submitted? =

By default, submitted posts are saved as "Pending" so you can review them before publishing. You can change this in the plugin settings to automatically publish posts or save them as drafts.

= How do I prevent spam submissions? =

The plugin includes a honeypot field to catch automated spam submissions. For additional protection, you can integrate with popular anti-spam plugins.

== Screenshots ==

1. Frontend submission form
2. Admin settings page
3. Successful submission message

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release
