<?php
/**
 * FILE: admin/views/dashboard.php
 * Main dashboard template with tabs (FAQ removed).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// $active_tab is passed from display_plugin_dashboard()
?>
<div class="wrap blogshq-admin-wrap">
    <div class="blogshq-header">
        <div class="blogshq-header-content">
            <h1 class="blogshq-title">
                <svg class="blogshq-logo-icon" width="32" height="32" viewBox="0 0 32 32" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="8" fill="url(#gradient)" />
                    <path d="M16 8L20 16L16 24L12 16L16 8Z" fill="white" />
                    <defs>
                        <linearGradient id="gradient" x1="0" y1="0" x2="32" y2="32">
                            <stop offset="0%" stop-color="#f2a200" />
                            <stop offset="100%" stop-color="#ff6b00" />
                        </linearGradient>
                    </defs>
                </svg>
                <?php echo esc_html( get_admin_page_title() ); ?>
            </h1>
            <p class="blogshq-subtitle">
                <?php esc_html_e( 'Powerful tools to enhance your WordPress blog', 'blogshq' ); ?></p>
        </div>
        <div class="blogshq-header-actions">
            <a href="https://github.com/codewithsourabh/blogshq" target="_blank" rel="noopener noreferrer"
                class="blogshq-btn blogshq-btn-secondary">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path
                        d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z" />
                </svg>
                <?php esc_html_e( 'Documentation', 'blogshq' ); ?>
            </a>
        </div>
    </div>
    <nav class="blogshq-nav-tabs" role="tablist">
        <a href="?page=blogshq&tab=logos" class="blogshq-nav-tab <?php echo $active_tab === 'logos' ? 'active' : ''; ?>"
            role="tab" aria-selected="<?php echo $active_tab === 'logos' ? 'true' : 'false'; ?>">
            <svg class="tab-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M10 2L12.5 7L18 8L14 12L15 18L10 15L5 18L6 12L2 8L7.5 7L10 2Z" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span><?php esc_html_e( 'Category Logos', 'blogshq' ); ?></span>
        </a>
        <a href="?page=blogshq&tab=toc" class="blogshq-nav-tab <?php echo $active_tab === 'toc' ? 'active' : ''; ?>"
            role="tab" aria-selected="<?php echo $active_tab === 'toc' ? 'true' : 'false'; ?>">
            <svg class="tab-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M3 5H17M3 10H17M3 15H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <span><?php esc_html_e( 'Table of Contents', 'blogshq' ); ?></span>
        </a>
        <a href="?page=blogshq&tab=ai-share"
            class="blogshq-nav-tab <?php echo $active_tab === 'ai-share' ? 'active' : ''; ?>" role="tab"
            aria-selected="<?php echo $active_tab === 'ai-share' ? 'true' : 'false'; ?>">
            <svg class="tab-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M10 2L12 8L18 10L12 12L10 18L8 12L2 10L8 8L10 2Z" stroke="currentColor" stroke-width="1.5"
                    stroke-linejoin="round" />
            </svg>
            <span><?php esc_html_e( 'AI Share', 'blogshq' ); ?></span>
        </a>
    </nav>

    <div class="blogshq-tab-content">
        <?php
	// Load the appropriate tab content (FAQ removed)
	switch ( $active_tab ) {
		case 'logos':
			blogshq_get_template( 'logos' );
			break;
		case 'toc':
			blogshq_get_template( 'toc' );
			break;
		case 'ai-share':
			blogshq_get_template( 'ai-share' );
			break;
	}
	?>
    </div>
</div>