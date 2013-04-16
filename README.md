media-ally
==========

A WordPress plugin to create an accessibility report for media files.

The initial version provides a list of images without alt text in Settings &rarr; Accessibility Report. It also offers an option to turn on an 'Alt/Transcript' column in the Media Library, where you will see either a check mark or a link to add alt text. 

Other forms of media will be added in future versions. Your input is welcome! 

Behind the scenes, the plugin uses the new NOT EXISTS option in meta queries to find all the images that don't have the `_wp_attachment_image_alt custom` field set. However, audio and video files present a challenge. Do we...

a) Get audio/video files whose parents have empty content? 

b) Get all audio/video post formats with empty content other than the embed/shortcode?

c) What about embedding YouTube videos? Should we prompt the user to include a link to the transcript? Would users even know how to find that?

d) all of the above?

e) other?