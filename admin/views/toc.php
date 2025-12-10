<?php
/**
 * FILE: admin/views/toc.php
 * Template for TOC settings.
 */
?>

<div class="blogshq-settings-section">
    <form method="post" action="" class="blogshq-ajax-form">
        <input type="hidden" name="form_type" value="toc">
        <?php
        if ( class_exists( 'BlogsHQ_TOC' ) ) {
            $toc = new BlogsHQ_TOC();
            $toc->render_admin_page();
        } else {
            echo '<div class="notice notice-error"><p>BlogsHQ_TOC class not found. Please check your plugin installation.</p></div>';
        }
        ?>
    </form>
</div>


