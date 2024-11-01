# Zhu Posts Icon Carousel for WordPress

Contributors:         davidpullin
Plugin Name:          Zhu Posts Icon Carousel
Plugin URI:           
Description:          Rotating carousel of icons of posts with optional preview below
Tags:                 posts, carousel, recent posts, scroller, widget
Version:              1.1.1
Stable Tag:           1.1.1
Requires at least:    5.3.0
Tested up to:         5.8
Requires PHP:         7.0
Author:               David Pullin
Author URI:           https://ict-man.me
License:              GPL v2 or later
License URI:          https://www.gnu.org/licenses/gpl-2.0.en.html
Text Domain:          zhu_pic_domain

== Description ==

A WordPress Widget displaying featured images of recent posts in a rotating carousel.  

The carousel area displays the featured images of recent posts and scrolls from left to right.  An optional preview area sits below showing the posts featured image and its excerpt.  Clicking on images or the preview will open a post in a new web browser tab.

The widget is theme agnostic and includes options to enable various display elements, carousel speed, pause time, and image sizes.  You can also edit the widget's CSS within the widget's settings panel thus giving you control over the look and feel.

There is also a demo mode that uses random stock images and Lorem ipsum.  Useful for when working on a new site where you may not have many posts.  However, you must have at least one published post for the widget to work.

The carousel will not display posts that are password protected.


== Frequently Asked Questions ==

Empty


== Upgrade Notice ==

1.1.0   August 2021.

Minor changes and fixes to work better when widgets are using the block editor that was introduced into WordPress 5.8.


== Screenshots ==

1. Example with carousel and preview areas
2. Example with just carousel images
3. CSS Editor
4. Title, Quality, Scroll and Pause settings
5. Image and Preview settings


== Installation ==

1. Upload this plugin to your /wp-content/plugins/ directory.


== Settings ==


=== Title ===

The widget title to display.  If blank no title is displayed.  You can further control the title, along with other items on the widget via CSS


=== Number of Posts to Display ===

The maximum number of recent posts to display.  The minimum is 1.


=== Widget Height ===

The height of the widget.  A fixed height is required as posts excerpts lengths may differ. 


=== Scroll Duration ===

How long in milliseconds to take rotating to the next image.


=== Pause Duration ===

How long in milliseconds should the carousel pause before rotating to the next image.  Thus allowing your user time to read the excerpt.


=== Carousel Image Size ===

The size of each image on the carousel.  You can set your own size using the 'custom' option or select from one of your WordPress's registered image sizes.


=== Show Preview ===

Option to turn off the rendering of the preview area below the carousel.


=== Show Preview Title ===

Option to control the rendering of preview's post title.


=== Show Preview Title ===

Option to control the rendering of the post's date.


=== Preview Excerpt Length ===

The number of words to use as the the post's excerpt.


=== Show Preview Image ===

Option to control the display of the image in the preview.  Like the carouse image size option you can also control the size of the preview's image.


=== CSS Editor ===

A default CSS template is generated when you add an instance of this widget to your website.  Use this to further control the display of this widget.  The CSS will only be applied to that instance of the widget.  Thus, if required, you can have multiple instances of this widget on a page with a different look and feel.


=== Demo Mode ===

When enabled Demo Mode uses random images and Lorem ipsum.  Useful for when working on a new site where you may not have many posts.  However, you must have at least one published post for the widget to work.

When in Demo Mode image sizes will be displayed as per the custom image size option.


== Advanced Notes - Used of WordPress Filters ==

The widget's title is passed through WordPress's widget_title filter which may be processed via your installed theme.

The widget temporarily adds a hook into WordPress's excerpt_length filter to ensure that the excerpt length used is the one as set within the widget's options.

The URL of posts are passed through WordPress's the_permalink filter.


== Changelog ==

