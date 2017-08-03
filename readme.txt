=== .TUBE Video Curator ===
Contributors: .TUBE gTLD
Tags: videos, video importer, import videos, youtube, youtube api, vimeo, vimeo api, twitch, twitch api, channel, playlist, video to post, create posts from youtube
Requires at least: 4.3
Tested up to: 4.7.5
Stable tag: 1.1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The .TUBE Video Curator makes it easy to create posts from YouTube, Vimeo, and Twitch, and works seamlessly with most themes and custom post types. 

== Description ==
The .TUBE Video Curator Plugin for WordPress makes it easy to create posts from YouTube, Vimeo, and Twitch channels. You can search for channels and playlists, bulk import existing videos as new posts, and even auto-import new videos as posts so you can "set it and forget it." Display options let you customize the player and video placement with no coding. Plus, the .TUBE Video Curator works well with most themes, supports custom post types and custom taxonomies, and also includes some useful hooks and filters for advanced users.

= Benefits At-a-Glance =
* Create posts from videos in seconds, all you need is an API key
* Works seamlessly with most themes and custom post types
* Supports YouTube, Vimeo and Twitch
* Curate individual videos by keyword or URL
* Easily control video player options via settings page
* Automatically imports video thumbnails as Featured Image
* Ability to import tags into your choice of taxonomy (tag, category, custom)
* Filters allow customization of imports and embeds for advanced users 

== Installation ==
Here's how to install the plugin and get it working.

1. Upload `/tube-video-curator/` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set up a YourTube and/or Vimeo and/or Twitch API key
4. Follow the links on the plugin dashboard to find and curate videos

== Frequently Asked Questions ==
= Will this plugin work with my theme? =
Most likely, yes, provided your theme has at least one post type that support thumbnails and custom fields

Pro-tip: If you use the [.TUBE WordPress Theme](https://www.get.tube/wordpress/tube-theme) we guarantee it'll work seamlessly.

= Why doesn't the video appear on the post? =
It's possible your theme is outputting the post content in an unconventional way, for example not using "the_content()" on the single post page. In this case, you may need to use the [tube_video] shortcode to place videos in your posts.

= How can I get an API key? =
To use this plugin, **you'll need to create an API key** with YouTube, Vimeo, or Twitch. The API Keys settings page includes instructions for how to get an API Key from each of these sites.

== Screenshots ==
1. Dashboard: The Dashboard serves as "home base" and offers quick access to all of the plugin features and settings.
2. Display Settings: Quickly customize your video placement and player options with just a few clicks.
3. Import Settings: Control how new video content gets imported as WordPress posts.
4. Add New Channel / Playlist: Search for video sources, then save them for later so you can curate videos at your convenience.
5. View Channel / Playlist: Instantly publish videos, mark them for review, or skip.
6. Add Video via URL: Quickly add an individual YouTube or Vimeo video using just a URL.
7. Search for Videos: Search for recent YouTube and Vimeo videos using any search term you can imagine, then quickly add them as new WordPress posts.

== Changelog ==

= 1.1.5 =
*Released: May 31, 2017*

Enhancements

* Allow style attribute in iframe for theatre video
* Deep link to Twitch API key from Dashboard


= 1.1.4 =
*Released: January 6, 2017*

Bug Fixes

* Fix for botched branch / tag in v1.1.3


= 1.1.3 =
*Released: December 17, 2016*

Enhancements

* Revised docs for how to get Twitch.TV API keys


= 1.1.2 =
*Released: December 15, 2016*

Enhancements

* Support for translations
* Prepended title slug to imported image filenames for better SEO
* Added alt attribute to imported images for better SEO
* Added "source" link in caption for imported images

Bug Fixes

* Update Twitch Channel videos API call to retrieve all videos, not just "highlights" (i.e. broadcasts=true)
* Fix for erroneous Twitch error when adding video via Vimeo URL (thanks Markus)


= 1.1.1.1 =
*Released: September 12, 2016*

Bug Fixes

* Fixed bug that prevented post_updated_messages from displaying


= 1.1.1 =
*Released: August 26, 2016*

General

* Tested up to WP 4.6


= 1.1.0.1 =
*Released: August 22, 2016*

Bug Fixes

* Fix for undefined function in API key settings (thanks Charlie)


= 1.1.0 =
*Released: August 15, 2016*

Enhancements

* Support for Twitch API Keys
* Support for Twitch Channels
* Support for Twitch Add via URL
* Shared functions for has_api_keys and no_api_keys_message
* Change required capability for Add Via Url from manage_options to publish_posts
* Change required capability for Search Videos from manage_options to publish_posts
* Change required capability for Dashboard from manage_options to publish_posts
* Custom admin menu for users with only "publish_posts" capability 
* Ensure YouTube video is embeddable when getting video details

Bug Fixes

* Update linkify class to allow for % and ~ in URLs


= 1.0.4.2 =
*Released: July 18, 2016*

Bug Fixes

* Add esc_attr to query input on Search for Videos page (fixes issue with quotes in queries)


= 1.0.4.1 =
*Released: July 13, 2016*

Bug Fixes

* Fix issue where imported tags were pre-pended with a "d"


= 1.0.4 =
*Released: July 4, 2016*

Enhancements

* Update all get.tube URLs to use https


= 1.0.3 =
*Released: July 1, 2016*

Bug Fixes

* Fix bug in Site selector for Search Videos and Add Via URL when only one site available


= 1.0.2 =
*Released: July 1, 2016*

Enhancements

* Ability to access video data in the get_auto_import_status function and filter
* Clarified various admin messages

Bug Fixes

* Fix bug in Import Settings if no available taxonomies
* Fix bug that prevented Video Site from getting set on Add Via URL
* Removed unused functions and unneeded default options


= 1.0.1 =
*Released: June 30, 2016*

Bug Fixes

* Fix JS bug with label for skipped videos


= 1.0.0 =
*Released: June 30, 2016*

* Initial release

== Notes ==
For more information about .TUBE or to buy a .TUBE domain please visit [get.TUBE](https://www.get.tube) today.