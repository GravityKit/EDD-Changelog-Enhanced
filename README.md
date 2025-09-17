# EDD Changelog Enhanced

Enhanced changelog endpoint with semantic HTML, microformats, and SEO optimization for EDD Software Licensing downloads. Designed for iframe embedding.

## Overview

EDD Changelog Enhanced replaces the default EDD Software Licensing changelog functionality with a modern, SEO-optimized solution that supports iframe embedding, semantic HTML, and microformats.

## Features

- **Semantic HTML5** with proper heading hierarchy
- **Microformats** support for better machine readability
- **SEO optimization** with meta tags and structured data
- **Social media integration** with featured image support for Twitter, Facebook, and LinkedIn
- **Iframe embedding** support with security headers
- **Mobile-responsive** design
- **Print-friendly** styling
- **Theme override** support
- **UTF-8 and emoji** handling
- **Intelligent caching** with automatic invalidation
- **Sitemap integration** with Yoast SEO for changelog URLs (v1.1+)

## Requirements

- WordPress 5.0+
- PHP 7.4+
- EDD Software Licensing plugin

## Installation

1. Upload the plugin files to `/wp-content/plugins/edd-changelog-enhanced/`
2. Activate the plugin through the WordPress admin
3. Plugin will automatically enhance existing changelog endpoints

## Usage

The plugin automatically registers changelog endpoints for EDD downloads:

```
/{download-slug}/changelog/
```

### Social Media Integration

When EDD downloads have featured images, the changelog pages automatically include:
- Open Graph meta tags for Facebook and LinkedIn sharing
- Twitter Card metadata with large image preview
- Schema.org ImageObject structured data

### Template Override

Themes can override the changelog template by creating:

```
yourtheme/edd-changelog-enhanced/changelog.php
```

## File Structure

```
edd-changelog-enhanced/
├── edd-changelog-enhanced.php      # Main plugin file
├── assets/
│   └── css/
│       └── changelog.css           # Responsive styling
└── templates/
    └── changelog.php               # HTML template
```

## Technical Details

### Architecture
- **Main Class**: `EDD_Changelog_Enhanced` (Singleton)
- **Endpoint**: Custom rewrite endpoint
- **Template System**: WordPress template hierarchy with overrides
- **Caching System**: Simplified version-based caching with plugin version integration
- **Sitemap Integration**: Automatic changelog URL injection via Yoast SEO filters

### Security Features
- Input validation and sanitization
- XSS prevention
- Safe HTML filtering
- Security headers for iframe embedding

### SEO Features
- Open Graph and Twitter Card meta tags with featured image support
- Schema.org structured data with ImageObject integration
- Canonical URLs
- Proper heading hierarchy
- Dynamic Twitter card types (summary_large_image when image exists)

### Caching System

The plugin uses a simplified, efficient caching strategy:

- **Cache Key Format**: `edd_changelog_{download_id}_{download_version}_{plugin_version}`
- **Automatic Invalidation**: Cache clears when download version or plugin version changes
- **Database Storage**: Uses WordPress options table (non-autoloaded)
- **No Manual Management**: No cache key tracking or complex cleanup routines

**Cache Lifecycle**:
1. Generated HTML is cached using version-based keys
2. Cache automatically becomes invalid when versions change
3. Manual invalidation via WordPress meta update hooks

### Sitemap Integration (v1.1+)

The plugin automatically adds changelog URLs to Yoast SEO sitemaps for better search engine discovery:

- **Automatic URL Addition**: Each download's `/changelog/` URL is added to the sitemap
- **Smart Date Detection**: Uses the latest version date from changelog as `lastmod` value
- **Proper Formatting**: Utilizes Yoast's `sitemap_url()` method for correct XML structure
- **No Images**: Changelog entries are marked with empty image arrays (changelogs don't have images)
- **Priority Adjustment**: Changelog URLs receive slightly lower priority than main download pages

**How it works**:
1. Hooks into `wpseo_sitemap_entry` to collect download URLs
2. Parses changelog content to extract latest version date
3. Adds formatted changelog URLs via `wpseo_sitemap_download_content` filter
4. Each changelog URL includes accurate `lastmod` based on version release date

### Performance
- **Simplified HTML caching** - Efficient cache keys without content hashing
- **Automatic cache invalidation** - No stale cache issues from version updates
- **HTTP caching with ETags** - Version-based ETags for browser cache validation
- **Minimal CSS footprint** - Optimized styling for fast loading
- **Mobile-first responsive design** - Optimized for all devices

## Development

The plugin follows WordPress coding standards and includes:

- Defensive programming practices
- Extensive input validation
- Error logging for debugging
- Graceful degradation

## Changelog Format

The plugin parses changelogs with version headings like:

```html
<h4>1.0.0 on January 1, 2024</h4>
<ul>
<li>🚀 Initial release</li>
<li>✨ New feature added</li>
<li>🐛 Bug fixes</li>
</ul>
```

## Contributing

We welcome contributions from the community! Whether it's bug fixes, new features, documentation improvements, or translations, we appreciate your help in making this plugin better.

### How to Contribute

1. **Fork the repository** on GitHub
2. **Create a feature branch** from `main` (`git checkout -b feature/amazing-feature`)
3. **Make your changes** and commit them with clear, descriptive messages
4. **Test your changes** to ensure they work as expected
5. **Push to your fork** (`git push origin feature/amazing-feature`)
6. **Open a Pull Request** with a clear title and description

### Guidelines

- Follow WordPress coding standards
- Include proper documentation for new features
- Add tests if applicable
- Keep commits atomic and focused
- Update the README if needed

We'll review your PR as soon as possible and provide feedback. Thank you for contributing!

## License

GPL v2 or later

## About GravityKit

This plugin is brought to you by [GravityKit](https://www.gravitykit.com), the company behind the best Gravity Forms add-ons. We create powerful tools that extend WordPress and Gravity Forms to help you build better websites and web applications.

### Some of Our Products

- **[GravityView](https://www.gravitykit.com/products/gravityview/)** - Display, edit, and export Gravity Forms entries on your website
- **[GravityBoard](https://www.gravitykit.com/products/gravityboard/)** - Create a kanban board using Gravity Forms entries
- **[GravityEdit](https://www.gravitykit.com/products/gravityedit/)** - Fast inline editing for Gravity Forms entries
- **[GravityExport](https://www.gravitykit.com/products/gravityexport/)** - Export Gravity Forms entries to Excel, CSV, and PDF
- **[GravityImport](https://www.gravitykit.com/products/gravityimport/)** - Import entries into Gravity Forms
- **[GravityActions](https://www.gravitykit.com/products/gravityactions/)** - Perform bulk actions on Gravity Forms entries
- **[GravityCalendar](https://www.gravitykit.com/products/gravitycalendar/)** - Display Gravity Forms entries in a calendar
- **[GravityCharts](https://www.gravitykit.com/products/gravitycharts/)** - Create charts from Gravity Forms data
- **[GravityMath](https://www.gravitykit.com/products/gravitymath/)** - Perform calculations on Gravity Forms data

[See more at gravitykit.com](https://www.gravitykit.com).