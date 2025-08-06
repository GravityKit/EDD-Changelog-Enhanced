<?php
/**
 * EDD Changelog Enhanced Template
 *
 * This template can be overridden by copying it to your theme's folder:
 * yourtheme/edd-changelog-enhanced/changelog.php
 *
 * @package EDD_Changelog_Enhanced
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" class="changelog-iframe">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">

    <title><?php echo esc_html( sprintf( __( '%s - Changelog', 'edd-changelog-enhanced' ), $download_name ) ); ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo esc_attr( sprintf( __( 'Changelog and release notes for %s. Stay updated with the latest features, improvements, and bug fixes.', 'edd-changelog-enhanced' ), $download_name ) ); ?>">
    <meta name="keywords" content="<?php echo esc_attr( sprintf( __( '%s, changelog, release notes, updates, version history', 'edd-changelog-enhanced' ), $download_name ) ); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo esc_attr( sprintf( __( '%s - Changelog', 'edd-changelog-enhanced' ), $download_name ) ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( sprintf( __( 'Changelog and release notes for %s', 'edd-changelog-enhanced' ), $download_name ) ); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo esc_url( $download_url . EDD_CHANGELOG_ENHANCED_SLUG . '/' ); ?>">
    <meta property="article:modified_time" content="<?php echo esc_attr( $last_modified ); ?>">
    
    <!-- Canonical and parent relationship -->
    <link rel="canonical" href="<?php echo esc_url( $download_url . EDD_CHANGELOG_ENHANCED_SLUG . '/' ); ?>">
    <link rel="up" href="<?php echo esc_url( $download_url ); ?>" title="<?php echo esc_attr( $download_name ); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo esc_attr( sprintf( __( '%s - Changelog', 'edd-changelog-enhanced' ), $download_name ) ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( sprintf( __( 'Latest updates and release notes for %s', 'edd-changelog-enhanced' ), $download_name ) ); ?>">

    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "<?php echo esc_js( $download_name ); ?>",
        "url": "<?php echo esc_url( $download_url ); ?>",
        "releaseNotes": "<?php echo esc_url( $download_url . EDD_CHANGELOG_ENHANCED_SLUG . '/' ); ?>",
        "dateModified": "<?php echo esc_attr( $last_modified ); ?>",
        "applicationCategory": "DeveloperApplication",
        "operatingSystem": "WordPress",
        "softwareVersion": "<?php echo ! empty( $entries ) && ! empty( $entries[0]['version'] ) ? esc_js( $entries[0]['version'] ) : ''; ?>"
    }
    </script>

    <!-- Changelog Styles -->
    <link rel="stylesheet" href="<?php echo esc_url( EDD_CHANGELOG_ENHANCED_URL . 'assets/css/changelog.css' ); ?>?v=<?php echo EDD_CHANGELOG_ENHANCED_VERSION; ?>">
</head>
<body class="changelog-page">
    <div class="changelog-container">
        <header class="changelog-header">
            <h1 class="changelog-title"><?php echo esc_html( sprintf( __( '%s Changelog', 'edd-changelog-enhanced' ), $download_name ) ); ?></h1>
            <p class="changelog-subtitle">
                Release notes and version history for 
                <a href="<?php echo esc_url( $download_url ); ?>" rel="up" title="<?php echo esc_attr( sprintf( __( 'Back to %s product page', 'edd-changelog-enhanced' ), $download_name ) ); ?>">
                    <?php echo esc_html( $download_name ); ?>
                </a>.
                <?php if ( ! empty( $download->post_excerpt ) ) : ?>
                    <?php echo esc_html( $download->post_excerpt ); ?>
                <?php endif; ?>
            </p>
        </header>

        <main class="changelog-entries">
            <?php if ( ! empty( $entries ) ) : ?>
                <?php foreach ( $entries as $entry ) : ?>
                    <article class="h-entry" id="version-<?php echo esc_attr( $entry['version'] ); ?>">
                        <header class="entry-header">
                            <h2 class="p-name"><?php echo esc_html( $entry['raw_heading'] ); ?></h2>
                            <?php if ( ! empty( $entry['date'] ) ) : ?>
                                <time class="dt-published" datetime="<?php echo esc_attr( $entry['date'] ); ?>">
                                    <?php echo esc_html( $format_date_safely( $entry['date'] ) ); ?>
                                </time>
                            <?php endif; ?>
                        </header>

                        <div class="e-content">
                            <?php echo wp_kses_post( $entry['content'] ); ?>
                        </div>
                        
                        <a class="u-url" href="<?php echo esc_url( $download_url . EDD_CHANGELOG_ENHANCED_SLUG . '/' . ( ! empty( $entry['version'] ) ? '#version-' . $entry['version'] : '' ) ); ?>">
                            <?php echo esc_html( sprintf( __( 'Version %s permalink', 'edd-changelog-enhanced' ), $entry['version'] ) ); ?>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Fallback to original content if parsing fails -->
                <article class="h-entry">
                    <h2 class="p-name"><?php echo esc_html( sprintf( __( '%s Changelog', 'edd-changelog-enhanced' ), $download_name ) ); ?></h2>
                    <div class="e-content">
                        <?php echo wp_kses_post( $changelog ); ?>
                    </div>
                    <time class="dt-published" datetime="<?php echo esc_attr( $last_modified ); ?>">
                        <?php echo esc_html( get_post_modified_time( 'F j, Y', false, $download->ID ) ); ?>
                    </time>
                    <a class="u-url" href="<?php echo esc_url( $download_url . EDD_CHANGELOG_ENHANCED_SLUG . '/' ); ?>">
                        <?php echo esc_html( __( 'Changelog permalink', 'edd-changelog-enhanced' ) ); ?>
                    </a>
                </article>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>