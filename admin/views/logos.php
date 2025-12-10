<?php
/**
 * FILE: admin/views/logos.php
 * Template for category logos settings.
 */
?>

<div class="blogshq-settings-section">
<?php
if ( class_exists( 'BlogsHQ_Logos' ) ) {
    $logos = new BlogsHQ_Logos();
    
    // Get categories
    $categories = get_categories( array( 'hide_empty' => false ) );
    ?>
    
    <h2><?php esc_html_e( 'Category Logos', 'blogshq' ); ?></h2>
    <p><?php esc_html_e( 'Assign light and dark mode logos to your categories. Use the shortcode [blogshq_category_logo] to display them.', 'blogshq' ); ?></p>
    
    <form method="post" action="" class="blogshq-ajax-form">
        <input type="hidden" name="form_type" value="category_logo">
        <?php wp_nonce_field( 'blogshq_logos_settings', 'blogshq_logos_nonce' ); ?>
        
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 20%;"><?php esc_html_e( 'Category', 'blogshq' ); ?></th>
                    <th style="width: 15%;"><?php esc_html_e( 'Slug', 'blogshq' ); ?></th>
                    <th style="width: 45%;"><?php esc_html_e( 'Logo URLs', 'blogshq' ); ?></th>
                    <th style="width: 20%;"><?php esc_html_e( 'Preview', 'blogshq' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $categories as $cat ) : 
                    $logo_light = get_term_meta( $cat->term_id, 'blogshq_logo_url_light', true );
                    $logo_dark  = get_term_meta( $cat->term_id, 'blogshq_logo_url_dark', true );
                    $cat_id     = absint( $cat->term_id );
                    ?>
                    <tr>
                        <td data-label="<?php esc_attr_e( 'Category', 'blogshq' ); ?>">
                            <strong><?php echo esc_html( $cat->name ); ?></strong>
                        </td>
                        <td data-label="<?php esc_attr_e( 'Slug', 'blogshq' ); ?>">
                            <code><?php echo esc_html( $cat->slug ); ?></code>
                        </td>
                        <td data-label="<?php esc_attr_e( 'Logo URLs', 'blogshq' ); ?>">
                            <label style="display: block; margin-bottom: 8px;">
                                <span style="display: inline-block; width: 60px;"><?php esc_html_e( 'Light:', 'blogshq' ); ?></span>
                                <input type="url" 
                                       placeholder="<?php esc_attr_e( 'Light mode logo URL', 'blogshq' ); ?>"
                                       name="logo_url_light[<?php echo esc_attr( $cat_id ); ?>]"
                                       value="<?php echo esc_attr( $logo_light ); ?>" 
                                       style="width: calc(100% - 70px);" />
                            </label>
                            <label style="display: block;">
                                <span style="display: inline-block; width: 60px;"><?php esc_html_e( 'Dark:', 'blogshq' ); ?></span>
                                <input type="url" 
                                       placeholder="<?php esc_attr_e( 'Dark mode logo URL', 'blogshq' ); ?>"
                                       name="logo_url_dark[<?php echo esc_attr( $cat_id ); ?>]"
                                       value="<?php echo esc_attr( $logo_dark ); ?>" 
                                       style="width: calc(100% - 70px);" />
                            </label>
                        </td>
                        <td data-label="<?php esc_attr_e( 'Preview', 'blogshq' ); ?>">
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <?php if ( $logo_light ) : ?>
                                    <div title="<?php esc_attr_e( 'Light Logo', 'blogshq' ); ?>">
                                        <img src="<?php echo esc_url( $logo_light ); ?>" 
                                             style="max-width: 48px; height: auto; border: 1px solid #ddd; padding: 4px; border-radius: 4px;" 
                                             alt="<?php esc_attr_e( 'Light logo', 'blogshq' ); ?>" />
                                    </div>
                                <?php endif; ?>
                                <?php if ( $logo_dark ) : ?>
                                    <div title="<?php esc_attr_e( 'Dark Logo', 'blogshq' ); ?>">
                                        <img src="<?php echo esc_url( $logo_dark ); ?>" 
                                             style="max-width: 48px; height: auto; border: 1px solid #333; padding: 4px; background: #1e1e1e; border-radius: 4px;" 
                                             alt="<?php esc_attr_e( 'Dark logo', 'blogshq' ); ?>" />
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" 
                   class="button-primary" 
                   name="blogshq_save_logos" 
                   value="<?php esc_attr_e( 'Save Logos', 'blogshq' ); ?>">
        </p>
    </form>

    <div class="blogshq-info-box">
        <h3><?php esc_html_e( 'Shortcode Usage:', 'blogshq' ); ?></h3>
        <p><strong><?php esc_html_e( 'Display logo for current category or post:', 'blogshq' ); ?></strong></p>
        <code>[blogshq_category_logo]</code>
        
        <p style="margin-top: 15px;"><strong><?php esc_html_e( 'Display logo by category ID:', 'blogshq' ); ?></strong></p>
        <code>[blogshq_category_logo id="123"]</code>
        
        <p style="margin-top: 15px;"><strong><?php esc_html_e( 'Display logo by category slug:', 'blogshq' ); ?></strong></p>
        <code>[blogshq_category_logo slug="technology"]</code>
    </div>
    
<?php
} else {
    echo '<div class="notice notice-error"><p>BlogsHQ_Logos class not found. Please check your plugin installation.</p></div>';
}
?>
</div>