# YouTube Privacy Block & Elementor Widget

A WordPress plugin for embedding YouTube videos without loading anything from YouTube until the visitor clicks. Before that, a thumbnail, play button and privacy notice are shown. Videos load from `youtube-nocookie.com`.

Works as a block in the block editor and as a widget in Elementor.

## Requirements

- WordPress 6.3 or later
- PHP 7.4 or later
- Elementor 3.5 or later (only for the Elementor widget)

## Installation

1. Download the release zip.
2. In WordPress, go to Plugins > Add New > Upload Plugin and upload the zip.
3. If the plugin is already installed, choose "Replace current with uploaded".

The zip's top-level folder must be `youtube-privacy-block` for WordPress to treat it as an update.

## Usage

- **Block editor:** add the "YouTube Privacy Embed" block and paste a YouTube link.
- **Elementor:** add the "YouTube Privacy Embed" widget and paste a YouTube link.

Supported links: `youtube.com/watch`, `youtu.be`, Shorts, live, embed links and bare video IDs. Start times (`t=` or `start=`) are kept.

Each embed can also have:

- **Video title** (optional): used by screen readers to name the play button and player. Not shown on the page.
- **Custom thumbnail** (optional): otherwise a default is used.

## Settings

Settings > YouTube Privacy lets you change the notice text, link text and privacy policy link for all embeds. Blank fields use the defaults.

## Releasing

1. Update the version in the plugin header and in `YOUTUBE_PRIVACY_VERSION` in `youtube-privacy-block.php`.
2. Add an entry to `CHANGELOG.md`.
3. Zip the `youtube-privacy-block` folder (excluding `.git`, `.gitignore`, `README.md` and `CHANGELOG.md` if preferred).

The plugin header sets `Update URI: false` so WordPress never tries to update it from wordpress.org.

## Translations

The text domain is `youtube-privacy`. Translation files go in a `languages` folder.
