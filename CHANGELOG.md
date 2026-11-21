# Changelog

## 1.2.1

### Fixed
- Stricter URL validation: video IDs must be exactly 11 characters, and `youtube.com` / `youtu.be` must be the actual host rather than appearing anywhere in the link.
- Block editor and Elementor editor now use the same URL rules as the front end, and Elementor shows a setup message for invalid URLs.
- Clicking the privacy notice no longer loads the video. The thumbnail and play button still do.
- Privacy notice styles are scoped to the embed, so they can't affect other content using a `.privacy-notice` class.

### Accessibility
- Screen readers now hear the privacy notice ("This will enable YouTube tracking") when focusing the play button, not just "Play YouTube video".
- Elementor editor preview matches the front end, including the new-tab warning on the privacy link.
- Settings page field descriptions are announced with their fields.

### Added
- Optional "Video title" field on the block and Elementor widget. Screen readers announce the play button as “Play ‘Your title’” and the loaded player uses it as its title. Embeds without a title work as before.

## 1.2.0

### Fixed
- Plugin no longer overwrites its own files when reactivated.
- Front-end script no longer loads twice on Elementor pages (which created the YouTube iframe twice per click), and only loads on pages with an embed.
- Embeds inside Elementor popups, tabs and other late-loaded content now work.
- Setup messages for a missing or invalid URL are only shown to logged-in editors, not visitors.
- Custom thumbnails always fill the frame, including inside Elementor.
- Embeds keep a true 16:9 ratio at all widths and in narrow columns.
- Removed forced margins that gave inconsistent spacing at different screen sizes and blocked Elementor's own margin control.
- Missing `wp-i18n` editor dependency; replaced deprecated `wp-editor`.

### Added
- Settings > YouTube Privacy: editable notice text, link text and privacy policy link.
- Support for Shorts, live, `youtube-nocookie.com` and `watch?...&v=` links, plus start times (`t=` / `start=`).
- Keyboard and screen reader support: real play button, visible focus styles, iframe title, new-tab warning on the privacy link.
- Block editor preview that matches the front end, and a way to remove a custom thumbnail.
- Block alignment (wide, full) and margin controls.
- Translation support.
- Settings link on the Plugins screen; settings removed when the plugin is deleted.

### Changed
- Privacy notice inherits the site's font.
- Privacy notice uses a solid background, so default colours pass WCAG AAA contrast. Custom colours set in Elementor are not checked.
- Notice and play button scale down on small embeds so they don't overlap.
- Default privacy link is now Google's privacy policy.
- Block registered via `block.json` (API version 3).
- Asset versions follow the plugin version, so updates bust caches.
- Requires WordPress 6.3+ and PHP 7.4+.

## 1.1.0 and earlier
- Initial releases.
