#### 1.5.1: November 12, 2015
* Tweak: Fixed a thumbnail bug when cross linking different post types.
* Tweak: Fixed a bug where extra added ignored words where not properly used.
* Tweak: Updated Dutch ignored words.

#### 1.5.0: October 28, 2015
* Feature: Thumbnail size can be set via options per post type.
* Feature: Use first image in content if no featured image is set.
* Feature: Added ability to set a placeholder image for if no image is found.
* Feature: Added ability to relink related posts from installer.
* Feature: Added ability to reinstall related posts from installer.
* Feature: Added joined-words feature. Allow multiple words to be parsed as one word.
* Feature: Added Brazilian Portuguese commonly used words.
* Feature: Added Czech commonly used words.
* Feature: Added Bulgarian commonly used words.
* Feature: Added Russian commonly used words.
* Feature: Added Swedish commonly used words.
* Feature: Added Spanish commonly used words.
* Feature: Added Norwegian Bokmål commonly used words.
* Tweak: Display Post Type in backend meta box.
* Tweak: Major install performance enhancements.
* Tweak: Added filter: rp4wp_get_children_link_args in RP4WP_Post_Link_Manager:get_children().
* Tweak: Added filter: rp4wp_get_children_child_args in RP4WP_Post_Link_Manager:get_children()
* Tweak: Strip all non letters or number characters from content.
* Tweak: Load Google API jQueryUI assets over HTTPS.
* Tweak: Fixed an issue with encoding non ASCII characters.
* Tweak: Improved trimming of punctuation in words.
* Tweak: Added new and improved CLI feedback to CLI commands.
* Tweak: CSS frontend tweaks to correct small align issues.

#### 1.4.3: September 1, 2015
* Tweak: Various license related improvements.

#### 1.4.2: July 29, 2015
* Tweak: Added id attribute to [rp4wp] shortcode.
* Tweak: Added limit attribute to [rp4wp] shortcode.
* Tweak: Added template attribute to [rp4wp] shortcode.
* Tweak: Added limit argument to rp4wp_children template function.
* Tweak: Set post type per link to already existing links for existing free links on activation.

#### 1.4.1: June 10, 2015
* Tweak: Fixed error caused when saving configurator, moved usort callback to separate method.
* Tweak: Made post age column filterable with filter 'rp4wp_post_age_column'.

#### 1.4.0: June 7, 2015
* Feature: Related Post Configurator. Full control on how your related posts are displayed.
* Feature: Installer is now displaying total number of posts todo and done.
* Feature: Added pagination to manual post link table.
* Feature: Widget now loads it's own template file.
* Feature: Added ability to exclude posts from being related.
* Feature: Added possibility to only link posts in past X days (option per post type).
* Feature: CLI - New command: install
* Feature: CLI - New command: cache
* Feature: CLI - New command: link
* Feature: CLI - New command: remove_related
* Tweak: Fixed a bug that caused first time site activation problems.
* Tweak: Fixed a bug that caused CSS not to be loaded on pages (is_singular instead of is_single).
* Tweak: Fixed a backend image path bug.
* Tweak: Check if post types are used in other relations before deleting word cache.
* Tweak: generate_related_posts_list method now has a template file parameter.
* Tweak: rp4wp_children function now has a template parameter.
* Tweak: Show love HTML is now a template part.
* Tweak: Removed 'display image' option, configurator will take care of this.
* Tweak: Removed 'Styling' tab and options in favor of configurator.
* Tweak: Updated French stop words.
* Tweak: Implemented Composer autoloader in favor of custom autoloader.
* Tweak: Loading hooks and filters from static files now instead of dynamic directory loading.
* Tweak: Updated translations.

#### 1.3.4: April 20, 2015
* Escaped view filter URL when manually linking posts to prevent possible XSS.

#### 1.3.3: March 26, 2015
* Feature: Add option to disable SSL verification in licensing requests.
* Fix: Check if settings is set in step 3 of installer to prevent fatal error.
* Tweak: Made themes filterable, new filter: rp4wp_themes.
* Tweak: Added premium constant to detect Premium version of plugin.
* Tweak: Added 'rp4wp_get_related_posts_sql' filter to alter related posts SQL.
* Tweak: Added 'rp4wp_ignored_words_lang' filter to alter ignored words.

#### 1.3.2: January 5, 2015
* Fixed a bug where UTF-8 encoded characters were not correctly parsed.
* Introduced icon alternative for when iconv isn't installed on server.
* Added CSS media query to themes, mobile is always one column.
* Display Post Type labels instead of raw post type name on settings page.

#### 1.3.1: January 1, 2015
* Now preventing double form submitting in settings screen.
* Added 'show love' option.
* Added related post object as second parameter to 'rp4wp_post_title' filter.

#### 1.3.0: December 28, 2014
* Added cross post type related posts.
* Moved license settings to as separate tab in settings.
* Added nonces to all AJAX calls in wizard.
* Made related Posts block title WPML string translatable.
* Added translations: French, Italian, Portuguese, Portuguese (Brazil), Swedish.
* Updated translations: Dutch, German, Serbian.

#### 1.2.8: December 19, 2014
* Added the possibility of using post meta by filter.

#### 1.2.7: December 18, 2014
* Fixed an updater conflict.
* Improved template system.

#### 1.2.6: December 3, 2014
* Fixed a widget error.
* Fixed a shortcode error.

#### 1.2.5: December 2, 2014
* Implemented automatic RTL detection.

#### 1.2.4: December 1, 2014
* Added RTL support.
* Fixed a bug where only posts where cached & linked.
* Fixed a bug where scheduled posts where not linked.

#### 1.2.3: November 25, 2014
* Added related post id as second argument to 'rp4wp_post_excerpt' filter.
* Only run upgrade script if there are posts to upgrade.

#### 1.2.2: November 18, 2014
* Fixed an free to premium upgrade bug.

#### 1.2.1: November 17, 2014
* Fixed an excerpt length bug.
* Added a dynamic per option filter.
* Display notice per setting if overwritten by filter.

#### 1.2.0: November 14, 2014
* Added full Network / Multisite support.
* Fixed a hardcoded database table bug.

#### 1.1.2: November 12, 2014
* Fixed multisite/network compatibility.
* Fixed an UTF-8 - iconv bug.
* Remove shortcodes from the related posts excerpt.

#### 1.1.1: November 7, 2014
* Fixed a display thumbnail bug.
* Fixed an auto post link on new post bug.
* Added filter 'rp4wp_disable_css' to disable all CSS generated on website.

#### 1.1.0: October 30, 2014
* Weights are now manageable via options, see weights tab.
* Implemented a template system.
* Added filter 'rp4wp_post_title'.
* Added filter 'rp4wp_post_excerpt'.
* Manually added links (starting this release) will no longer be deleted on de-/installation.

#### 1.0.2: October 28, 2014
* Fixed a bug where permission were checked to soon.
* Fixed a rp4wp_children template function bug.
* Removed an unused query var.
* Updated Dutch, German, Serbian, Swedish translations.

#### 1.0.1: October 17, 2014
* Fixed a link screen post type bug.
* Fixed a "Skip linking" button bug.
* Dutch translation update.

#### 1.0.0: October 14, 2014
* Initial release
