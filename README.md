# EDD Changelog Enhanced

Enhanced changelog endpoint with semantic HTML, microformats, and SEO optimization for EDD Software Licensing downloads. Designed for iframe embedding.

## Overview

EDD Changelog Enhanced replaces the default EDD Software Licensing changelog functionality with a modern, SEO-optimized solution that supports iframe embedding, semantic HTML, and microformats.

## Features

- **Semantic HTML5** with proper heading hierarchy
- **Microformats** support for better machine readability
- **SEO optimization** with meta tags and structured data
- **Iframe embedding** support with security headers
- **Mobile-responsive** design
- **Print-friendly** styling
- **Theme override** support
- **UTF-8 and emoji** handling
- **HTTP caching** with ETags

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

### Security Features
- Input validation and sanitization
- XSS prevention
- Safe HTML filtering
- Security headers for iframe embedding

### SEO Features
- Open Graph and Twitter Card meta tags
- Schema.org structured data
- Canonical URLs
- Proper heading hierarchy

### Performance
- HTTP caching with ETags
- Content-based cache invalidation
- Minimal CSS footprint
- Mobile-first responsive design

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

## License

GPL v2 or later

## Support

For support and feature requests, visit [GravityKit](https://gravitykit.com).