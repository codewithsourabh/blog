<?php
/**
 * Table of Contents Module
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/modules/toc
 * @since      1.0.0
 */

if (!defined('WPINC')) {
    die;
}

class BlogsHQ_TOC {

    private static $heading_regex = null;
    private static $debug_mode = false;

    public function init() {
        add_shortcode('blogshq_toc', array($this, 'render_shortcode'));
        add_filter('the_content', array($this, 'insert_toc_and_anchors'), 20);
        add_action('save_post', array($this, 'clear_toc_cache'));
        add_action('save_post', array($this, 'clear_summary_on_update'), 10, 2);
        add_action('update_option_blogshq_toc_headings', array($this, 'clear_heading_regex_cache'));
        add_action('wp_footer', array($this, 'enqueue_link_icon_script'));
        add_action('update_option_blogshq_toc_headings', array($this, 'clear_settings_cache'));
        add_action('update_option_blogshq_toc_link_icon_enabled', array($this, 'clear_settings_cache'));
        add_action('update_option_blogshq_toc_link_icon_headings', array($this, 'clear_settings_cache'));
        add_action('update_option_blogshq_toc_link_icon_color', array($this, 'clear_settings_cache'));
        
        // Add meta box to post edit screen
        add_action('add_meta_boxes', array($this, 'add_post_meta_box'));
        
        // Add AJAX handler
        add_action('wp_ajax_blogshq_clear_post_summary', array($this, 'ajax_clear_post_summary'));
    }
	/**
     * Add meta box to post edit screen
     */
    public function add_post_meta_box() {
        add_meta_box(
            'blogshq-tools',
            'BlogsHQ AI Summary',
            array($this, 'render_post_meta_box'),
            'post',
            'side',
            'high'
        );
    }

    /**
     * Render meta box content on post edit page
     */
    public function render_post_meta_box($post) {
        // Get current summary status
        $summary = get_post_meta($post->ID, 'blogshq_ai_summary', true);
        $generated_time = get_post_meta($post->ID, 'blogshq_ai_summary_generated', true);
        
        // Get post status
        $post_status = get_post_status($post->ID);
        $is_published = ($post_status === 'publish');
        
        ?>
        <div style="padding: 10px;">
            <!-- Status Information -->
            <div style="margin-bottom: 15px;">
                <p><strong>Post Status:</strong> 
                    <?php if ($is_published): ?>
                        <span style="color: green;">✓ Published</span>
                    <?php else: ?>
                        <span style="color: orange;">✗ Not Published</span>
                        <small style="display: block; color: #666; margin-top: 5px;">
                            (Summary only generates for published posts)
                        </small>
                    <?php endif; ?>
                </p>
                
                <p><strong>AI Summary Status:</strong> 
                    <?php if (!empty($summary)): ?>
                        <span style="color: green;">✓ Generated</span>
                    <?php else: ?>
                        <span style="color: #666;">✗ Not Generated</span>
                    <?php endif; ?>
                </p>
                
                <?php if ($generated_time): ?>
                    <p><strong>Last Generated:</strong><br>
                        <small><?php echo date('F j, Y g:i a', $generated_time); ?></small>
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Clear Summary Button -->
            <div style="text-align: center;">
                <button type="button" 
                        class="button button-secondary" 
                        style="width: 100%;"
                        onclick="clearPostSummary(<?php echo $post->ID; ?>)"
                        <?php echo empty($summary) ? 'disabled' : ''; ?>>
                    <?php esc_html_e('Clear AI Summary', 'blogshq'); ?>
                </button>
                
                <?php if (empty($summary) && $is_published): ?>
                    <p style="margin-top: 10px; font-size: 12px; color: #666;">
                        <?php esc_html_e('Summary will generate automatically when post is viewed.', 'blogshq'); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Result Message -->
            <div id="blogshq-result-<?php echo $post->ID; ?>" 
                 style="margin-top: 10px; padding: 8px; display: none; border-radius: 4px;"></div>
            
            <!-- Preview Link (if published) -->
            <?php if ($is_published): ?>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                    <p style="margin: 0;">
                        <a href="<?php echo get_permalink($post->ID); ?>" target="_blank" class="button button-small" style="width: 100%; text-align: center;">
                            <?php esc_html_e('View Post', 'blogshq'); ?>
                        </a>
                    </p>
                    <p style="margin: 5px 0 0 0; font-size: 11px; color: #666; text-align: center;">
                        <?php esc_html_e('View post to see AI summary', 'blogshq'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        function clearPostSummary(postId) {
            if (!confirm('Clear AI summary for this post? It will regenerate automatically when someone views the post.')) {
                return;
            }
            
            var resultDiv = document.getElementById('blogshq-result-' + postId);
            resultDiv.innerHTML = '<div style="color: orange; text-align: center;">⏳ Clearing...</div>';
            resultDiv.style.display = 'block';
            resultDiv.style.background = '#fff9e6';
            resultDiv.style.border = '1px solid #ffcc00';
            
            jQuery.post(ajaxurl, {
                action: 'blogshq_clear_post_summary',
                post_id: postId,
                nonce: '<?php echo wp_create_nonce('blogshq_clear_post_summary'); ?>'
            }, function(response) {
                if (response.success) {
                    resultDiv.innerHTML = '<div style="color: green; text-align: center;">✓ ' + response.data + '</div>';
                    resultDiv.style.background = '#f0fff0';
                    resultDiv.style.border = '1px solid #46b450';
                    
                    // Update button state
                    var button = document.querySelector('[onclick="clearPostSummary(' + postId + ')"]');
                    button.disabled = true;
                    button.innerHTML = '<?php esc_html_e('Summary Cleared', 'blogshq'); ?>';
                    
                    // Reload after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    resultDiv.innerHTML = '<div style="color: red; text-align: center;">✗ ' + response.data + '</div>';
                    resultDiv.style.background = '#fff0f0';
                    resultDiv.style.border = '1px solid #dc3232';
                }
            }).fail(function() {
                resultDiv.innerHTML = '<div style="color: red; text-align: center;">✗ AJAX request failed</div>';
                resultDiv.style.background = '#fff0f0';
                resultDiv.style.border = '1px solid #dc3232';
            });
        }
        </script>
        <?php
    }

    /**
     * AJAX: Clear summary for specific post
     */
    public function ajax_clear_post_summary() {
        check_ajax_referer('blogshq_clear_post_summary', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('You do not have permission to clear summaries');
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Check if user can edit this post
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error('You cannot edit this post');
        }
        
        // Clear the summary
        delete_post_meta($post_id, 'blogshq_ai_summary');
        delete_post_meta($post_id, 'blogshq_ai_summary_generated');
        
        // Also clear TOC cache for this post
        $this->clear_toc_cache($post_id);
        
        wp_send_json_success('AI summary cleared. It will regenerate when the post is viewed.');
    }

    private function get_heading_regex() {
        if (null === self::$heading_regex) {
            $selected_headings = get_option('blogshq_toc_headings', array('h2', 'h3', 'h4', 'h5', 'h6'));
            
            if (is_array($selected_headings) && !empty($selected_headings)) {
                self::$heading_regex = implode('|', array_map('preg_quote', $selected_headings));
            } else {
                self::$heading_regex = '';
            }
        }

        return self::$heading_regex;
    }

    public function clear_heading_regex_cache() {
        self::$heading_regex = null;
    }

    private function get_toc_settings() {
        $cache_key = 'blogshq_toc_settings';
        $settings = wp_cache_get($cache_key);
        
        if (false === $settings) {
            $settings = array(
                'headings'           => get_option('blogshq_toc_headings', array('h2', 'h3', 'h4', 'h5', 'h6')),
                'link_icon'          => get_option('blogshq_toc_link_icon_enabled', false),
                'icon_headings'      => get_option('blogshq_toc_link_icon_headings', array('h2')),
                'icon_color'         => get_option('blogshq_toc_link_icon_color', '#2E62E9'),
                'ai_summary_enabled' => get_option('blogshq_toc_ai_summary_enabled', true),
            );
            
            wp_cache_set($cache_key, $settings, '', HOUR_IN_SECONDS);
        }
        
        return $settings;
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'blogshq'));
        }

        if (isset($_POST['blogshq_save_toc'])) {
            $this->save_settings();
        }

        $settings = $this->get_toc_settings();
        $selected_headings  = $settings['headings'];
        $link_icon_enabled  = $settings['link_icon'];
        $link_icon_headings = $settings['icon_headings'];
        $link_icon_color    = $settings['icon_color'];
        $ai_summary_enabled = $settings['ai_summary_enabled'];

        if (!is_array($selected_headings)) {
            $selected_headings = array();
        }
        if (!is_array($link_icon_headings)) {
            $link_icon_headings = array();
        }
        ?>
        <div class="blogshq-toc-settings">
            <h2><?php esc_html_e('Table of Contents Settings', 'blogshq'); ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('blogshq_toc_settings', 'blogshq_toc_nonce'); ?>
                <input type="hidden" name="form_type" value="toc">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Include Headings in TOC:', 'blogshq'); ?>
                        </th>
                        <td>
                            <?php foreach (array('h2', 'h3', 'h4', 'h5', 'h6') as $tag): ?>
                                <label style="margin-right: 16px;">
                                    <input type="checkbox" 
                                           name="toc_headings[]" 
                                           value="<?php echo esc_attr($tag); ?>" 
                                           <?php checked(in_array($tag, $selected_headings, true)); ?> />
                                    <?php echo esc_html(strtoupper($tag)); ?>
                                </label>
                            <?php endforeach; ?>
                            <p class="description">
                                <?php esc_html_e('Select which heading levels to include in the table of contents.', 'blogshq'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Enable AI Summary Block:', 'blogshq'); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="ai_summary_enabled" 
                                       value="1" 
                                       <?php checked($ai_summary_enabled); ?> />
                                <?php esc_html_e('Show "Summarise with BlogsHQ AI" accordion before first H2', 'blogshq'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Displays a collapsible accordion with 3 AI-generated summary points before the first H2 heading in posts.', 'blogshq'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Perplexity API Key:', 'blogshq'); ?>
                        </th>
                        <td>
                            <?php
                            $api_key = get_option('blogshq_perplexity_api_key', '');
                            $api_key_display = !empty($api_key) ? substr($api_key, 0, 10) . '...' : '';
                            ?>
                            <input type="password" 
                                   name="perplexity_api_key" 
                                   value="<?php echo esc_attr($api_key); ?>" 
                                   class="regular-text" 
                                   placeholder="pplx-xxxxxxxxxxxxxx"
                                   autocomplete="off" />
                            
                            <button type="button" class="button button-small" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                                <?php esc_html_e('Show/Hide', 'blogshq'); ?>
                            </button>

                            <?php if (!empty($api_key)): ?>
                                <p class="description" style="color: green;">
                                    ✓ <?php esc_html_e('API Key saved:', 'blogshq'); ?> <?php echo esc_html($api_key_display); ?>
                                </p>
                            <?php endif; ?>
                            <p class="description">
                                <?php esc_html_e('Enter your Perplexity API key. Get one at', 'blogshq'); ?> 
                                <a href="https://www.perplexity.ai/settings/api" target="_blank" rel="noopener">perplexity.ai/settings/api</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Enable Link Icon:', 'blogshq'); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="link_icon_enabled" 
                                       value="1" 
                                       <?php checked($link_icon_enabled); ?> />
                                <?php esc_html_e('Show "copy link" icon after headings', 'blogshq'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Displays a clickable icon next to headings that copies the heading link to clipboard.', 'blogshq'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Show Link Icon After:', 'blogshq'); ?>
                        </th>
                        <td>
                            <?php foreach (array('h2', 'h3', 'h4', 'h5', 'h6') as $tag): ?>
                                <label style="margin-right: 16px;">
                                    <input type="checkbox" 
                                           name="link_icon_headings[]" 
                                           value="<?php echo esc_attr($tag); ?>" 
                                           <?php checked(in_array($tag, $link_icon_headings, true)); ?> />
                                    <?php echo esc_html(strtoupper($tag)); ?>
                                </label>
                            <?php endforeach; ?>
                            <p class="description">
                                <?php esc_html_e('Select which heading levels should display the link icon.', 'blogshq'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Link Icon Color:', 'blogshq'); ?>
                        </th>
                        <td>
                            <input type="text" 
                                   name="link_icon_color" 
                                   value="<?php echo esc_attr($link_icon_color); ?>" 
                                   class="blogshq-color-picker" />
                            <p class="description">
                                <?php esc_html_e('Choose the color for the link icon.', 'blogshq'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" 
                           class="button-primary" 
                           name="blogshq_save_toc" 
                           value="<?php esc_attr_e('Save Settings', 'blogshq'); ?>">
                </p>
            </form>

            <div class="blogshq-info-box" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #2E62E9;">
                <h3><?php esc_html_e('Shortcode Usage:', 'blogshq'); ?></h3>
                <p><?php esc_html_e('Use this shortcode to manually insert a table of contents:', 'blogshq'); ?></p>
                <code>[blogshq_toc]</code>
                <p style="margin-top: 10px;">
                    <strong><?php esc_html_e('Note:', 'blogshq'); ?></strong>
                    <?php esc_html_e('On mobile devices, the TOC is automatically inserted before the first heading.', 'blogshq'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    private function save_settings() {
        if (!isset($_POST['blogshq_toc_nonce']) || 
             !wp_verify_nonce($_POST['blogshq_toc_nonce'], 'blogshq_toc_settings')) {
            wp_die(esc_html__('Security check failed.', 'blogshq'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'blogshq'));
        }

        try {
            $checked = isset($_POST['toc_headings']) && is_array($_POST['toc_headings'])
                ? array_intersect($_POST['toc_headings'], array('h2', 'h3', 'h4', 'h5', 'h6'))
                : array();
            update_option('blogshq_toc_headings', $checked);

            $ai_summary_enabled = isset($_POST['ai_summary_enabled']);
            update_option('blogshq_toc_ai_summary_enabled', $ai_summary_enabled);

            $link_icon_enabled = isset($_POST['link_icon_enabled']);
            update_option('blogshq_toc_link_icon_enabled', $link_icon_enabled);

            $icon_headings = isset($_POST['link_icon_headings']) && is_array($_POST['link_icon_headings'])
                ? array_intersect($_POST['link_icon_headings'], array('h2', 'h3', 'h4', 'h5', 'h6'))
                : array();
            update_option('blogshq_toc_link_icon_headings', $icon_headings);

            $color = isset($_POST['link_icon_color']) ? sanitize_hex_color($_POST['link_icon_color']) : '#2E62E9';
            update_option('blogshq_toc_link_icon_color', $color);

            // Save Perplexity API key
            if (isset($_POST['perplexity_api_key'])) {
                $api_key = trim($_POST['perplexity_api_key']);
                if (!empty($api_key)) {
                    update_option('blogshq_perplexity_api_key', $api_key);
                }
            }

            $this->clear_heading_regex_cache();
            $this->clear_settings_cache();

            add_settings_error(
                'blogshq_messages',
                'blogshq_message',
                __('TOC settings saved successfully.', 'blogshq'),
                'updated'
            );
        } catch (Exception $e) {
            add_settings_error(
                'blogshq_messages',
                'blogshq_message',
                __('Error saving TOC settings.', 'blogshq'),
                'error'
            );
        }

        settings_errors('blogshq_messages');
    }

    private function generate_toc($post_id) {
        $post = get_post($post_id);
        if (empty($post)) {
            return '';
        }

        $tag_regex = $this->get_heading_regex();
        if (empty($tag_regex)) {
            return '';
        }

        $content = $post->post_content;
        preg_match_all('/<(' . $tag_regex . ')[^>]*>(.*?)<\/\1>/i', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return '';
        }

        $toc_output = '<div class="blogshq-toc"><strong>' . esc_html__('In This Article', 'blogshq') . '</strong><ul>';
        
        foreach ($matches as $heading) {
            $heading_text = strip_tags($heading[2]);
            $anchor       = sanitize_title($heading_text);
            $toc_output  .= '<li><a href="#' . esc_attr($anchor) . '">' . esc_html($heading_text) . '</a></li>';
        }
        
        $toc_output .= '</ul></div>';

        return apply_filters('blogshq_toc_output', $toc_output, $matches);
    }

    private function get_cached_toc($post_id) {
        $transient_key = 'blogshq_toc_' . $post_id;
        $toc           = get_transient($transient_key);

        if (false === $toc) {
            $toc = $this->generate_toc($post_id);
            set_transient($transient_key, $toc, HOUR_IN_SECONDS);
        }

        return $toc;
    }

    public function clear_settings_cache() {
        wp_cache_delete('blogshq_toc_settings');
        $this->clear_heading_regex_cache();
    }

    public function clear_toc_cache($post_id) {
        delete_transient('blogshq_toc_' . $post_id);
    }

    public function render_shortcode($atts) {
        global $post;
        
        if (empty($post)) {
            return '';
        }

        return $this->get_cached_toc($post->ID);
    }

    /**
     * Clean summary text by removing unwanted characters and formatting
     * 
     * @param string $text The text to clean
     * @return string Cleaned text
     */
    private function clean_summary_text($text) {
        // Remove quotes (single and double)
        $text = str_replace(array('"', "'"), '', $text);
        
        // Remove commas at the end
        $text = rtrim($text, ',');
        
        // Remove semicolons at the end
        $text = rtrim($text, ';');
        
        // Remove periods at the end (optional - keep if you want periods)
        // $text = rtrim($text, '.');
        
        // Remove starting numbers like "1.", "2.", "3." etc.
        $text = preg_replace('/^\d+\.\s*/', '', $text);
        
        // Remove bullet points, asterisks, dashes
        $text = preg_replace('/^[\*\-•]\s*/', '', $text);
        
        // Remove any parentheses and their content
        $text = preg_replace('/\s*\([^)]*\)/', '', $text);
        
        // Remove brackets and their content
        $text = preg_replace('/\s*\[[^\]]*\]/', '', $text);
        
        // Remove multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim whitespace
        $text = trim($text);
        
        // Capitalize first letter
        $text = ucfirst($text);
        
        return $text;
    }

/**
 * Generate summary using Perplexity API
 * 
 * @param int $post_id Post ID
 * @return array|false Array of 3 summary points or false on failure
 */
private function generate_summary_with_perplexity($post_id) {
    $api_key = get_option('blogshq_perplexity_api_key', '');
    
    if (empty($api_key)) {
        return false;
    }

    $post = get_post($post_id);
    if (!$post) {
        return false;
    }

    // Get post content and strip HTML
    $content = wp_strip_all_tags($post->post_content);
    $content = wp_trim_words($content, 500); // Limit to ~500 words for API
    
    $title = $post->post_title;

    // Prepare API request - ENHANCED for content-only summarization
    $api_url = 'https://api.perplexity.ai/chat/completions';
    
    $prompt = sprintf(
        'Summarize the following article titled "%s" in exactly 3 concise bullet points. Each point should be one clear sentence without numbers, quotes, or commas at the end. Just plain text sentences. Format as a JSON array with key "points". Content: %s',
        $title,
        $content
    );

    $body = array(
        'model' => 'sonar',
        'messages' => array(
            array(
                'role' => 'system',
                'content' => 'You are a summarizer that ONLY uses the provided article content. Do NOT use external knowledge, web search, or prior information. Summarize ONLY the given text into exactly 3 bullet points as valid JSON {"points": ["point1", "point2", "point3"]}. Each point is one plain sentence.'
            ),
            array(
                'role' => 'user',
                'content' => $prompt
            )
        ),
        'max_tokens' => 300,
        'temperature' => 0.2,
        'return_citations' => false,
        'return_images' => false,
        'disable_search' => true  // ENSURES NO EXTERNAL SOURCES
    );

    $args = array(
        'timeout' => 30,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => wp_json_encode($body),
        'method' => 'POST',
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return false;
    }

    $body_response = wp_remote_retrieve_body($response);
    $data = json_decode($body_response, true);

    if (!isset($data['choices'][0]['message']['content'])) {
        return false;
    }

    $ai_response = $data['choices'][0]['message']['content'];
    
    // Try to parse JSON response
    $parsed = json_decode($ai_response, true);
    if (isset($parsed['points']) && is_array($parsed['points']) && count($parsed['points']) === 3) {
        // Clean up the points
        $cleaned_points = array();
        foreach ($parsed['points'] as $point) {
            $cleaned_points[] = $this->clean_summary_text($point);
        }
        return $cleaned_points;
    }

    // Fallback: try to extract bullet points from text
    $lines = preg_split('/[\r\n]+/', trim($ai_response));
    $points = array();
    
    foreach ($lines as $line) {
        $line = $this->clean_summary_text($line);
        if (!empty($line) && strlen($line) > 20) {
            $points[] = $line;
        }
        if (count($points) >= 3) {
            break;
        }
    }

    if (count($points) === 3) {
        return $points;
    }

    return false;
}

    /**
     * Get summary for post (from cache or generate new)
     * 
     * @param int $post_id Post ID
     * @return array|false Array of 3 summary points or false
     */
    private function get_post_summary($post_id) {
        // Check if summary exists in post meta
        $summary = get_post_meta($post_id, 'blogshq_ai_summary', true);
        
        if (!empty($summary) && is_array($summary) && count($summary) === 3) {
            return $summary;
        }

        // Generate new summary
        $summary = $this->generate_summary_with_perplexity($post_id);
        
        if ($summary !== false) {
            // Clean the summary points before saving
            $cleaned_summary = array();
            foreach ($summary as $point) {
                $cleaned_summary[] = $this->clean_summary_text($point);
            }
            
            // Save to post meta
            update_post_meta($post_id, 'blogshq_ai_summary', $cleaned_summary);
            update_post_meta($post_id, 'blogshq_ai_summary_generated', current_time('timestamp'));
            
            return $cleaned_summary;
        }

        return false;
    }

    /**
     * Generate AI Summary Accordion HTML
     */
    private function generate_ai_summary_accordion($post_id) {
        $summary = $this->get_post_summary($post_id);
        
        if ($summary === false || empty($summary)) {
            return ''; // Don't show accordion if no summary
        }

        ob_start();
        ?>
        <div class="blogshq-ai-accordion" role="region" style="border: 2px solid #2E62E9; border-radius: 8px; margin:0; overflow: hidden; background: linear-gradient(135deg, #f8fafc 0%, #e6f0ff 100%); box-shadow: 0 4px 12px rgba(46, 98, 233, 0.1);">
            <button class="blogshq-ai-accordion-trigger" 
                    type="button" 
                    aria-expanded="false"
                    aria-controls="blogshq-ai-accordion-content-<?php echo esc_attr($post_id); ?>"
                    style="width: 100%; background: linear-gradient(135deg, #2E62E9 0%, #1e4bb8 100%); color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: space-between; font-size: 1.1em; font-weight: 600; text-align: left; transition: all 0.3s ease;">
                <span style="display: flex; align-items: center; gap: 12px;">
                    <svg class="blogshq-ai-accordion-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
                        <path d="M12 2L12.5 8L18 10L12.5 12L12 18L11.5 12L6 10L11.5 8L12 2Z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M6 10L12 12L18 10" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="blogshq-ai-accordion-title" style="font-size: 1.2em; font-weight: 700;"><?php esc_html_e('Summarise with BlogsHQ AI', 'blogshq'); ?></span>
                </span>
                <svg class="blogshq-ai-accordion-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s ease; flex-shrink: 0;">
                    <path d="M5 7.5L10 12.5L15 7.5" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <div class="blogshq-ai-accordion-content" 
                 id="blogshq-ai-accordion-content-<?php echo esc_attr($post_id); ?>" 
                 aria-hidden="true"
                 style="padding: 0; max-height: 0; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);">
                <div style="padding: 2em; background: white; border-top: 1px solid #e2e8f0;">
                    <p style="margin: 0 0 1.2em 0; color: #4a5568; font-size: 1.1em; font-weight: 500;">
                        <?php esc_html_e('Here are 3 key points from this article:', 'blogshq'); ?>
                    </p>
                    <ul class="blogshq-ai-summary-list" style="margin: 0; padding-left: 0; list-style-type: none;">
                        <?php foreach ($summary as $point): ?>
                            <li style="margin-bottom: 1.2em; line-height: 1.6; position: relative; padding-left: 1.2em; color: #2d3748; font-size: 1.05em;">
                                <span style="position: absolute; left: 0; top: 0.4em; width: 0.6em; height: 0.6em; background: #2E62E9; border-radius: 50%;"></span>
                                <?php echo esc_html($point); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
<!--                     <div style="margin-top: 1.5em; padding-top: 1.2em; border-top: 1px solid #e2e8f0; font-size: 0.9em; color: #718096; text-align: center;">
                        <?php esc_html_e('Generated by BlogsHQ AI using Perplexity API', 'blogshq'); ?>
                    </div> -->
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Clear AI summary when post is updated
     */
    public function clear_summary_on_update($post_id, $post) {
        // Only clear for published posts
        if ($post->post_status !== 'publish') {
            return;
        }

        // Don't clear on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Delete summary so it regenerates
        delete_post_meta($post_id, 'blogshq_ai_summary');
        delete_post_meta($post_id, 'blogshq_ai_summary_generated');
    }

    public function insert_toc_and_anchors($content) {
        if (!is_singular('post') || is_admin()) {
            return $content;
        }

        // Track if we've already inserted the summary to prevent duplicates
        static $summary_inserted = false;
        
        if ($summary_inserted) {
            return $content;
        }

        $tag_regex = $this->get_heading_regex();
        if (empty($tag_regex)) {
            return $content;
        }

        $used_ids = array();

        $content = preg_replace_callback(
            '/<(' . $tag_regex . ')([^>]*)>(.*?)<\/\1>/i',
            function ($m) use (&$used_ids) {
                $heading_text = strip_tags($m[3]);
                $anchor = sanitize_title(remove_accents($heading_text));
                $anchor = preg_replace('/[^a-z0-9-]/', '', strtolower($anchor));
                
                $original_anchor = $anchor;
                $counter = 1;
                while (in_array($anchor, $used_ids, true)) {
                    $anchor = $original_anchor . '-' . $counter;
                    $counter++;
                }
                $used_ids[] = $anchor;
                
                if (preg_match('/id=["\']([^"\']+)["\']/', $m[2], $id_match)) {
                    return '<' . $m[1] . $m[2] . '>' . $m[3] . '</' . $m[1] . '>';
                }
                
                return '<' . $m[1] . $m[2] . ' id="' . esc_attr($anchor) . '">' . $m[3] . '</' . $m[1] . '>';
            },
            $content
        );

        // Insert AI Summary accordion before first H2
        $settings = $this->get_toc_settings();
        
        if ($settings['ai_summary_enabled']) {
            global $post;
            if ($post && $post->ID) {
                $ai_summary_accordion = $this->generate_ai_summary_accordion($post->ID);
                
                if (!empty($ai_summary_accordion)) {
                    // Insert accordion before first H2
                    $pattern = '/(<h2[^>]*>)/i';
                    if (preg_match($pattern, $content)) {
                        $content = preg_replace($pattern, $ai_summary_accordion . '$1', $content, 1);
                        $summary_inserted = true;
                    }
                }
            }
        }

        // Insert TOC on mobile
        if (wp_is_mobile()) {
            $toc = do_shortcode('[blogshq_toc]');
            $content = preg_replace('/(<(' . $tag_regex . ')[^>]*>)/i', $toc . '$1', $content, 1);
        }

        return $content;
    }

    public function enqueue_link_icon_script() {
        if (!is_singular('post')) {
            return;
        }

        $settings = $this->get_toc_settings();
        
        // Always enqueue for TOC smooth scrolling, even if link icons are disabled
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        wp_enqueue_script(
            'blogshq-link-icon',
            BLOGSHQ_PLUGIN_URL . "assets/js/link-icon{$suffix}.js",
            array(),
            BLOGSHQ_VERSION,
            true
        );

        wp_localize_script(
            'blogshq-link-icon',
            'blogshqLinkIcon',
            array(
                'headings'   => $settings['icon_headings'],
                'iconColor'  => $settings['icon_color'],
                'copiedText' => __('The link has been copied to your clipboard.', 'blogshq'),
                'copyLabel'  => __('Copy link to this section', 'blogshq'),
                'linkIconEnabled' => $settings['link_icon'],
            )
        );

        // Enqueue accordion script
        wp_enqueue_script(
            'blogshq-ai-accordion',
            BLOGSHQ_PLUGIN_URL . "assets/js/ai-accordion{$suffix}.js",
            array(),
            BLOGSHQ_VERSION,
            true
        );

        // Enqueue inline CSS for accordion
        wp_add_inline_style('wp-block-library', '
            .blogshq-ai-accordion-trigger[aria-expanded="true"] .blogshq-ai-accordion-arrow {
                transform: rotate(180deg);
            }
            .blogshq-ai-accordion-trigger[aria-expanded="true"] + .blogshq-ai-accordion-content {
                max-height: 800px !important;
                padding: 0 !important;
            }
            .blogshq-ai-accordion-trigger:hover {
                background: linear-gradient(135deg, #1e4bb8 0%, #153d8c 100%) !important;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(46, 98, 233, 0.2) !important;
            }
            .blogshq-ai-accordion-trigger:active {
                transform: translateY(0);
            }
        ');
    }

    public function clear_all_caches() {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                OR option_name LIKE %s",
                $wpdb->esc_like('_transient_blogshq_') . '%',
                $wpdb->esc_like('_transient_timeout_blogshq_') . '%'
            )
        );
        
        wp_cache_flush();
        $this->clear_heading_regex_cache();
        $this->clear_settings_cache();
    }
}