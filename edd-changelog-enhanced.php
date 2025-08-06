<?php
/**
 * Plugin Name: EDD Changelog Enhanced
 * Plugin URI: https://gravitykit.com
 * Description: Enhanced changelog endpoint with semantic HTML, microformats, and SEO optimization for EDD Software Licensing downloads. Designed for iframe embedding.
 * Version: 1.0.0
 * Author: GravityKit
 * Author URI: https://gravitykit.com
 * Text Domain: edd-changelog-enhanced
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
if ( ! defined( 'EDD_CHANGELOG_ENHANCED_VERSION' ) ) {
    define( 'EDD_CHANGELOG_ENHANCED_VERSION', '1.0.0' );
}
if ( ! defined( 'EDD_CHANGELOG_ENHANCED_FILE' ) ) {
    define( 'EDD_CHANGELOG_ENHANCED_FILE', __FILE__ );
}
if ( ! defined( 'EDD_CHANGELOG_ENHANCED_PATH' ) ) {
    define( 'EDD_CHANGELOG_ENHANCED_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'EDD_CHANGELOG_ENHANCED_URL' ) ) {
    define( 'EDD_CHANGELOG_ENHANCED_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'EDD_CHANGELOG_ENHANCED_SLUG' ) ) {
    define( 'EDD_CHANGELOG_ENHANCED_SLUG', 'changelog' );
}

/**
 * Main EDD Changelog Enhanced Class
 */
class EDD_Changelog_Enhanced {

    /**
     * Instance of this class
     */
    private static $instance;

    /**
     * Get instance
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain( 'edd-changelog-enhanced', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // Register our enhanced changelog
        add_action( 'init', array( $this, 'register_changelog_endpoint' ) );
        add_action( 'template_redirect', array( $this, 'handle_changelog_request' ), -999 );
        
        // Cache invalidation hooks
        add_action( 'updated_post_meta', array( $this, 'invalidate_changelog_cache' ), 10, 4 );
        add_action( 'added_post_meta', array( $this, 'invalidate_changelog_cache' ), 10, 4 );
        add_action( 'deleted_post_meta', array( $this, 'invalidate_changelog_cache' ), 10, 4 );
    }

    /**
     * Deregister old EDD SL changelog functionality
     *
     * @return void
     */
    public function deregister_old_changelog() {
        global $edd_sl;

        // Check if EDD SL is available and properly initialized
        if ( ! $this->is_edd_sl_available() ) {
            return;
        }

        // Verify the global is the expected EDD_Software_Licensing class
        if ( ! is_object( $edd_sl ) || ! method_exists( $edd_sl, 'changelog_endpoint' ) ) {
            $this->log_error( 'EDD Software Licensing object is not properly initialized' );
            return;
        }

        // Remove the old changelog endpoint action
        $removed_endpoint = remove_action( 'init', array( $edd_sl, 'changelog_endpoint' ) );

        // Remove the old changelog display action
        $removed_display = remove_action( 'template_redirect', array( $edd_sl, 'show_changelog' ), -999 );

        // Log successful deregistration
        if ( ! $removed_endpoint || ! $removed_display ) {
            error_log( 'EDD Changelog Enhanced: Failed to deregister old changelog functionality' );
        }
    }

    /**
     * Check if EDD Software Licensing is available and active
     *
     * @return bool True if EDD SL is available
     */
    private function is_edd_sl_available() {
        // Check if EDD SL plugin is active
        if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
            return false;
        }

        // Check if the global variable exists and is properly set
        global $edd_sl;
        if ( empty( $edd_sl ) ) {
            return false;
        }

        // Verify it's the correct instance
        return ( $edd_sl instanceof EDD_Software_Licensing );
    }

    /**
     * Register changelog endpoint
     */
    public function register_changelog_endpoint() {
        add_rewrite_endpoint( EDD_CHANGELOG_ENHANCED_SLUG, EP_PERMALINK );
    }

    /**
     * Handle changelog requests
     *
     * @return void
     */
    public function handle_changelog_request() {
        global $wp_query;

        // Validate query parameters
        if ( ! $this->validate_changelog_request( $wp_query ) ) {
            return;
        }

        // Deregister old EDD SL changelog hooks only when handling changelog requests
        $this->deregister_old_changelog();

        // Get and validate download
        $download = $this->get_valid_download( $wp_query->query_vars['download'] );
        if ( ! $download ) {
            wp_die( __( 'Download not found.', 'edd-changelog-enhanced' ), 404 );
            return;
        }

        // Get changelog content
        $changelog = $this->get_changelog_content( $download->ID );

        if ( empty( $changelog ) ) {
            wp_die( __( 'No changelog found for this download.', 'edd-changelog-enhanced' ), 404 );
            return;
        }

        // Set headers for iframe embedding and SEO
        $this->set_changelog_headers( $download );

        // Check for cached HTML output first
        $cached_html = $this->get_cached_changelog_html( $download->ID, $changelog );
        
        if ( $cached_html !== false ) {
            echo $cached_html;
        } else {
            $this->output_and_cache_enhanced_changelog( $download, $changelog );
        }

        exit;
    }

    /**
     * Validate changelog request parameters
     *
     * @param WP_Query $wp_query The WordPress query object
     * @return bool True if request is valid
     */
    private function validate_changelog_request( $wp_query ) {
        if ( ! is_object( $wp_query ) ) {
            return false;
        }

        // Check required query variables
        if ( ! isset( $wp_query->query_vars[ EDD_CHANGELOG_ENHANCED_SLUG ] ) || ! isset( $wp_query->query_vars['download'] ) ) {
            return false;
        }

        // Validate download slug
        $download_slug = $wp_query->query_vars['download'];
        if ( empty( $download_slug ) || ! is_string( $download_slug ) ) {
            return false;
        }

        // Basic sanitization check
        if ( $download_slug !== sanitize_title( $download_slug ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get and validate download post
     *
     * @param string $download_slug The download slug to look up
     * @return WP_Post|false The download post object or false
     */
    private function get_valid_download( $download_slug ) {
        if ( empty( $download_slug ) || ! is_string( $download_slug ) ) {
            return false;
        }

        $download = get_page_by_path( $download_slug, OBJECT, 'download' );

        if ( ! is_object( $download ) || ! isset( $download->post_type ) ) {
            return false;
        }

        if ( 'download' !== $download->post_type ) {
            return false;
        }

        // Check if post is published
        if ( 'publish' !== $download->post_status ) {
            return false;
        }

        return $download;
    }

    /**
     * Get changelog content for a download
     *
     * @param int $download_id The download ID
     * @return string The formatted changelog content
     */
    private function get_changelog_content( $download_id ) {
        // Get raw changelog from post meta
        $raw_changelog = get_post_meta( $download_id, '_edd_sl_changelog', true );

        if ( empty( $raw_changelog ) ) {
            return '';
        }

        // Ensure proper UTF-8 encoding for emojis and special characters
        if ( ! mb_check_encoding( $raw_changelog, 'UTF-8' ) ) {
            $raw_changelog = mb_convert_encoding( $raw_changelog, 'UTF-8', 'auto' );
        }
        
        // Fix common emoji encoding issues
        $raw_changelog = $this->fix_emoji_encoding( $raw_changelog );

        // Apply the same formatting as used in the current implementation
        $changelog = stripslashes( wpautop( $raw_changelog, true ) );

        /**
         * Filter the changelog content before output
         *
         * @param string $changelog The formatted changelog content
         * @param int    $download_id The download ID
         * @param string $raw_changelog The raw changelog content from meta
         */
        return apply_filters( 'edd_changelog_enhanced_content', $changelog, $download_id, $raw_changelog );
    }

    /**
     * Set headers for changelog response with version-based long-term caching
     */
    private function set_changelog_headers( $download ) {
        // Set no-cache for admin and logged-in users to prevent issues
        if ( is_admin() || is_user_logged_in() ) {
            header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
            header( 'Content-Type: text/html; charset=UTF-8' );
            return;
        }

        // Allow iframe embedding
        header( 'X-Frame-Options: SAMEORIGIN' );

        // Get current site domain for CSP
        $parsed_url = parse_url( home_url() );
        $site_domain = isset( $parsed_url['host'] ) ? $parsed_url['host'] : 'localhost';

        header( 'Content-Security-Policy: frame-ancestors \'self\' *.' . $site_domain );

        // Set content type with explicit UTF-8 charset
        header( 'Content-Type: text/html; charset=UTF-8' );

        // Get version for long-term caching
        $version = get_post_meta( $download->ID, '_edd_sl_version', true );
        $changelog = get_post_meta( $download->ID, '_edd_sl_changelog', true );
        
        if ( ! empty( $version ) ) {
            // Long-term caching based on version (30 days for immutable content)
            $max_age = 30 * 24 * 3600; // 30 days
            header( 'Cache-Control: public, max-age=' . $max_age . ', immutable' );
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $max_age ) . ' GMT' );
            
            // Version-based ETag for better cache validation
            $etag = md5( $changelog . $version );
        } else {
            // Fallback to shorter cache without version
            header( 'Cache-Control: public, max-age=3600' );
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 3600 ) . ' GMT' );
            
            // Fallback ETag based on content and modification time
            $etag = md5( $changelog . $download->post_modified );
        }
        
        header( 'ETag: "' . $etag . '"' );

        // Handle conditional requests
        if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"' ) {
            http_response_code( 304 );
            exit;
        }
    }

    /**
     * Parse changelog content and structure it with proper microformats
     *
     * @param string $changelog The changelog HTML content to parse
     * @return array Array of structured changelog entries
     */
    private function parse_changelog_entries( $changelog ) {
        $entries = array();

        if ( empty( $changelog ) || ! is_string( $changelog ) ) {
            return $entries;
        }

        // Use regex to split by version headings and capture everything until next version  
        $pattern = '/(<h4[^>]*>[^<]*\d+\.\d+(?:\.\d+)?[^<]*<\/h4>)/i';
        $parts = preg_split( $pattern, $changelog, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
        
        for ( $i = 0; $i < count( $parts ); $i++ ) {
            $part = trim( $parts[ $i ] );
            
            // Check if this is a version heading
            if ( $this->is_version_heading_html( $part ) ) {
                $heading_text = trim( strip_tags( $part ) );
                
                // Get all content until next version heading
                $content = '';
                for ( $j = $i + 1; $j < count( $parts ); $j++ ) {
                    $next_part = trim( $parts[ $j ] );
                    // Stop if we hit another version heading
                    if ( $this->is_version_heading_html( $next_part ) ) {
                        break;
                    }
                    $content .= $next_part;
                }
                
                // Convert h4 section headings to h3
                $content = str_replace( array( '<h4', '</h4>' ), array( '<h3', '</h3>' ), $content );
                
                $entry = array(
                    'version' => $this->extract_version( $heading_text ),
                    'date' => $this->extract_date( $heading_text ),
                    'raw_heading' => $heading_text,
                    'content' => $content
                );
                
                if ( ! empty( $entry['version'] ) ) {
                    $entries[] = $entry;
                }
                
                // Skip the content parts we already processed
                $i = $j - 1;
            }
        }

        return $entries;

        try {
            // Create a DOMDocument to parse the HTML
            $dom = new DOMDocument();

            // Suppress libxml errors temporarily
            libxml_use_internal_errors( true );

            // Load HTML with error handling
            // Check if constants are defined (PHP 5.4+)
            $flags = 0;
            if ( defined( 'LIBXML_HTML_NOINVALID' ) ) {
                $flags |= LIBXML_HTML_NOINVALID;
            }
            if ( defined( 'LIBXML_HTML_NOIMPLIED' ) ) {
                $flags |= LIBXML_HTML_NOIMPLIED;
            }
            
            $result = $dom->loadHTML( '<div>' . $changelog . '</div>', $flags );

            if ( ! $result ) {
                $this->log_error( 'Failed to parse changelog HTML structure' );
                return array(); // Return empty array to trigger fallback
            }

            // Check if we have a valid document structure
            if ( ! $dom->documentElement || ! $dom->documentElement->hasChildNodes() ) {
                $this->log_error( 'Invalid or empty HTML document structure' );
                return array();
            }

            $current_entry = null;
            $current_section = null;

            foreach ( $dom->documentElement->childNodes as $node ) {
                if ( $node->nodeType !== XML_ELEMENT_NODE ) {
                    continue;
                }

                if ( $node->tagName === 'h4' ) {
                    $heading_text = trim( $node->textContent );

                    if ( empty( $heading_text ) ) {
                        continue;
                    }

                    // Check if this looks like a version heading (contains version number and date)
                    if ( $this->is_version_heading( $heading_text ) ) {
                        // Save previous entry if exists
                        if ( $current_entry ) {
                            $entries[] = $current_entry;
                        }

                        // Start new entry
                        $current_entry = array(
                            'version' => $this->extract_version( $heading_text ),
                            'date' => $this->extract_date( $heading_text ),
                            'raw_heading' => $heading_text,
                            'sections' => array()
                        );
                        $current_section = null;
                    } else {
                        // This is a section heading (🐛 Fixed, ✨ Added, etc.)
                        $current_section = array(
                            'title' => $heading_text,
                            'content' => ''
                        );
                    }
                } else {
                    // Add content to current section or entry
                    $content = $dom->saveHTML( $node );

                    if ( $current_section ) {
                        $current_section['content'] .= $content;
                    } elseif ( $current_entry ) {
                        if ( ! isset( $current_entry['sections']['general'] ) ) {
                            $current_entry['sections']['general'] = array(
                                'title' => '',
                                'content' => ''
                            );
                        }
                        $current_entry['sections']['general']['content'] .= $content;
                    }

                    // If we just finished a section, add it to the entry
                    if ( $current_section && $node->nextSibling &&
                         $node->nextSibling->nodeType === XML_ELEMENT_NODE &&
                         $node->nextSibling->tagName === 'h4' ) {
                        if ( $current_entry ) {
                            $current_entry['sections'][] = $current_section;
                        }
                        $current_section = null;
                    }
                }
            }

            // Add the last section and entry
            if ( $current_section && $current_entry ) {
                $current_entry['sections'][] = $current_section;
            }
            if ( $current_entry ) {
                $entries[] = $current_entry;
            }

        } catch ( Exception $e ) {
            $this->log_error( 'Exception during changelog parsing: ' . $e->getMessage() );
            return array(); // Return empty array to trigger fallback
        } finally {
            // Clear any libxml errors
            libxml_clear_errors();
        }

        return $entries;
    }

    /**
     * Check if heading text looks like a version heading
     */
    private function is_version_heading( $text ) {
        // Look for patterns like "1.1.1 on July 4, 2025" or "Version 2.0"
        return preg_match( '/^\d+\.\d+(?:\.\d+)?(?:\s+on\s+|\s+\-\s+|\s+)/', $text ) ||
               preg_match( '/^version\s+\d+/i', $text );
    }

    /**
     * Check if HTML string is a version heading
     *
     * @param string $html HTML string to check
     * @return bool True if it's a version heading
     */
    private function is_version_heading_html( $html ) {
        return preg_match( '/^<h4[^>]*>[^<]*\d+\.\d+(?:\.\d+)?[^<]*<\/h4>$/i', $html );
    }

    /**
     * Extract version number from heading text
     */
    private function extract_version( $text ) {
        if ( preg_match( '/(\d+\.\d+(?:\.\d+)?)/', $text, $matches ) ) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Extract and parse date from heading text
     *
     * @param string $text The heading text to extract date from
     * @return string ISO 8601 formatted date string or empty string
     */
    private function extract_date( $text ) {
        if ( empty( $text ) || ! is_string( $text ) ) {
            return '';
        }

        // Try to match "on Month Day, Year" pattern
        if ( preg_match( '/on\s+([A-Za-z]+\s+\d+,\s+\d+)/', $text, $matches ) ) {
            try {
                $date = DateTime::createFromFormat( 'F j, Y', $matches[1] );
                if ( $date && $date->format( 'F j, Y' ) === $matches[1] ) {
                    return $date->format( 'c' ); // ISO 8601 format
                }
            } catch ( Exception $e ) {
                $this->log_error( 'Date parsing error for pattern "F j, Y": ' . $e->getMessage() );
            }
        }

        // Try other date patterns
        if ( preg_match( '/(\d{4}-\d{2}-\d{2})/', $text, $matches ) ) {
            try {
                $date = DateTime::createFromFormat( 'Y-m-d', $matches[1] );
                if ( $date && $date->format( 'Y-m-d' ) === $matches[1] ) {
                    return $date->format( 'c' );
                }
            } catch ( Exception $e ) {
                $this->log_error( 'Date parsing error for pattern "Y-m-d": ' . $e->getMessage() );
            }
        }

        return '';
    }

    /**
     * Safely format a date string for display
     *
     * @param string $date_string ISO 8601 date string
     * @return string Formatted date or fallback message
     */
    private function format_date_safely( $date_string ) {
        if ( empty( $date_string ) ) {
            return __( 'No date available', 'edd-changelog-enhanced' );
        }

        try {
            // Try to create DateTime object from ISO 8601 format
            $date = new DateTime( $date_string );
            if ( $date ) {
                return $date->format( 'F j, Y' );
            }
        } catch ( Exception $e ) {
            $this->log_error( 'Date formatting error: ' . $e->getMessage() . ' for date: ' . $date_string );
        }

        return __( 'Invalid date', 'edd-changelog-enhanced' );
    }

    /**
     * Fix common emoji encoding issues
     *
     * @param string $content Content that may have encoding issues
     * @return string Fixed content
     */
    private function fix_emoji_encoding( $content ) {
        // Log the problematic content for debugging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $this->log_error( 'Raw changelog content: ' . bin2hex( $content ) );
        }
        
        // Common emoji encoding fixes - expanded list with context-aware replacements
        $fixes = array(
            // UTF-8 replacement character (�) patterns that might represent different emojis
            'ð Initial'     => '🚀 Initial',     // Rocket for initial release
            'ð Added'       => '✨ Added',       // Sparkles for new features
            'ð Fixed'       => '🐛 Fixed',       // Bug for fixes
            'ð Changed'     => '🔧 Changed',     // Wrench for changes
            'ð Improved'    => '⚡ Improved',    // Lightning for improvements
            'ð Updated'     => '🔄 Updated',     // Arrows for updates
            'ð Security'    => '🛡️ Security',    // Shield for security
            'ð Performance' => '🚀 Performance', // Rocket for performance
            
            // Fallback single character replacements
            'ð'     => '🚀', // Default to rocket if no context
            'ð'     => '🐛', // Bug  
            'â¨'     => '✨', // Sparkles
            'ð§'     => '🔧', // Wrench
            'ð'     => '🎉', // Party
            'ð'     => '📝', // Memo
            'â'     => '⚡', // Lightning
            'ð¡ï¸'   => '🛡️', // Shield
            'ð'     => '🔥', // Fire
            'ð'     => '💡', // Bulb
            'ð­'     => '🎯', // Target
            'ð'     => '✅', // Check mark
            'â'     => '❌', // Cross mark
            'ð'     => '📋', // Clipboard
            'ð¨'     => '🔨', // Hammer
            'ð'     => '🔄', // Arrows for refresh/update
        );
        
        // Apply fixes
        foreach ( $fixes as $broken => $fixed ) {
            if ( strpos( $content, $broken ) !== false ) {
                $content = str_replace( $broken, $fixed, $content );
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    $this->log_error( "Fixed emoji: {$broken} -> {$fixed}" );
                }
            }
        }
        
        // Try WordPress's own emoji fixing function if available
        if ( function_exists( 'wp_encode_emoji' ) ) {
            $content = wp_encode_emoji( $content );
        }
        
        // Also try to fix double-encoded UTF-8
        if ( function_exists( 'mb_convert_encoding' ) ) {
            // Detect if content might be double-encoded
            $test_decode = mb_convert_encoding( $content, 'UTF-8', 'UTF-8' );
            if ( $test_decode !== $content && mb_check_encoding( $test_decode, 'UTF-8' ) ) {
                $content = $test_decode;
            }
        }
        
        return $content;
    }

    /**
     * Get featured image data for social sharing
     *
     * @param int $download_id The download post ID
     * @return array|null Featured image data with URL, width, height, and alt text
     */
    private function get_download_featured_image( $download_id ) {
        if ( empty( $download_id ) || ! is_numeric( $download_id ) ) {
            return null;
        }

        // Get the featured image ID
        $featured_image_id = get_post_thumbnail_id( $download_id );
        
        if ( empty( $featured_image_id ) ) {
            return null;
        }

        // Get image data for large size (suitable for social sharing)
        $image_data = wp_get_attachment_image_src( $featured_image_id, 'large' );
        
        if ( empty( $image_data ) || ! is_array( $image_data ) ) {
            return null;
        }

        // Get additional image metadata
        $alt_text = get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true );
        $image_meta = wp_get_attachment_metadata( $featured_image_id );

        return array(
            'url'    => esc_url( $image_data[0] ),
            'width'  => (int) $image_data[1],
            'height' => (int) $image_data[2],
            'alt'    => ! empty( $alt_text ) ? esc_attr( $alt_text ) : '',
            'mime_type' => ! empty( $image_meta['mime_type'] ) ? $image_meta['mime_type'] : 'image/jpeg'
        );
    }

    /**
     * Generate a simplified cache key using plugin version
     *
     * @param int    $download_id The download post ID
     * @param string $changelog   The changelog content (unused, kept for compatibility)
     * @return string The cache key
     */
    private function generate_cache_key( $download_id, $changelog ) {
        $download_version = get_post_meta( $download_id, '_edd_sl_version', true );
        $plugin_version = EDD_CHANGELOG_ENHANCED_VERSION;
        
        // Simple cache key using only download ID, download version, and plugin version
        return sprintf( 
            'edd_changelog_%d_%s_%s',
            $download_id,
            $download_version ? $download_version : 'noversion',
            $plugin_version
        );
    }

    /**
     * Get cached changelog HTML using simplified cache key
     *
     * @param int    $download_id The download post ID
     * @param string $changelog   The changelog content
     * @return string|false Cached HTML or false if not found
     */
    private function get_cached_changelog_html( $download_id, $changelog ) {
        if ( empty( $download_id ) ) {
            return false;
        }

        $cache_key = $this->generate_cache_key( $download_id, $changelog );
        return get_option( $cache_key, false );
    }

    /**
     * Cache changelog HTML using simplified cache key
     *
     * @param int    $download_id The download post ID
     * @param string $changelog   The changelog content
     * @param string $html        The rendered HTML to cache
     * @return void
     */
    private function cache_changelog_html( $download_id, $changelog, $html ) {
        if ( empty( $download_id ) || empty( $html ) ) {
            return;
        }

        $cache_key = $this->generate_cache_key( $download_id, $changelog );
        
        // Cache the HTML (don't autoload)
        update_option( $cache_key, $html, false );
    }

    /**
     * Output enhanced changelog with caching
     *
     * @param WP_Post $download The download post object
     * @param string  $changelog The changelog content
     * @return void
     */
    private function output_and_cache_enhanced_changelog( $download, $changelog ) {
        // Start output buffering to capture the HTML
        ob_start();
        
        // Generate the changelog HTML
        $this->output_enhanced_changelog( $download, $changelog );
        
        // Get the generated HTML
        $html = ob_get_contents();
        ob_end_clean();
        
        // Cache the HTML for future requests
        $this->cache_changelog_html( $download->ID, $changelog, $html );
        
        // Output the HTML
        echo $html;
    }

    /**
     * Invalidate cached HTML when version or changelog changes
     *
     * @param int    $meta_id     ID of the metadata entry
     * @param int    $object_id   Post ID
     * @param string $meta_key    Meta key
     * @param mixed  $meta_value  Meta value
     * @return void
     */
    public function invalidate_changelog_cache( $meta_id, $object_id, $meta_key, $meta_value ) {
        // Only handle EDD version and changelog meta updates for download posts
        if ( ! in_array( $meta_key, array( '_edd_sl_version', '_edd_sl_changelog' ), true ) ) {
            return;
        }

        // Verify this is a download post
        if ( get_post_type( $object_id ) !== 'download' ) {
            return;
        }

        // Clear cached HTML for this download
        $this->clear_changelog_cache( $object_id );
    }

    /**
     * Clear cached HTML for a specific download
     *
     * @param int $download_id The download post ID
     * @return void
     */
    private function clear_changelog_cache( $download_id ) {
        if ( empty( $download_id ) ) {
            return;
        }

        // Get current changelog to generate cache key
        $changelog = get_post_meta( $download_id, '_edd_sl_changelog', true );
        
        if ( ! empty( $changelog ) ) {
            $cache_key = $this->generate_cache_key( $download_id, $changelog );
            delete_option( $cache_key );
        }
    }

    /**
     * Log errors for debugging
     *
     * @param string $message Error message to log
     * @return void
     */
    private function log_error( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'EDD Changelog Enhanced: ' . $message );
        }
    }

    /**
     * Output enhanced changelog with semantic HTML and microformats
     *
     * @param WP_Post $download The download post object
     * @param string  $changelog The changelog content
     * @return void
     */
    private function output_enhanced_changelog( $download, $changelog ) {
        // Prepare template variables
        $download_name = get_the_title( $download->ID );
        $download_url = get_permalink( $download->ID );
        $last_modified = get_post_modified_time( 'c', false, $download->ID );
        $entries = $this->parse_changelog_entries( $changelog );

        // Get featured image for social sharing
        $featured_image = $this->get_download_featured_image( $download->ID );

        // Create a closure for date formatting to make it available in template
        $format_date_safely = function( $date_string ) {
            return $this->format_date_safely( $date_string );
        };

        // Load template
        $this->load_template( 'changelog', compact(
            'download',
            'download_name',
            'download_url',
            'last_modified',
            'changelog',
            'entries',
            'featured_image',
            'format_date_safely'
        ) );
    }

    /**
     * Load a template file with variables
     *
     * @param string $template_name Template name without extension
     * @param array  $variables Variables to extract for template
     * @return void
     */
    private function load_template( $template_name, $variables = array() ) {
        // Extract variables for use in template
        extract( $variables );

        // Check for theme override first
        $theme_template = locate_template( "edd-changelog-enhanced/{$template_name}.php" );

        if ( $theme_template ) {
            include $theme_template;
        } else {
            // Use plugin template
            $plugin_template = EDD_CHANGELOG_ENHANCED_PATH . "templates/{$template_name}.php";

            if ( file_exists( $plugin_template ) ) {
                include $plugin_template;
            } else {
                $this->log_error( "Template not found: {$template_name}" );
                wp_die( __( 'Template not found.', 'edd-changelog-enhanced' ), 500 );
            }
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Flush rewrite rules to register the endpoint
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules to clean up
        flush_rewrite_rules();
    }
}

// Initialize the plugin
EDD_Changelog_Enhanced::instance();