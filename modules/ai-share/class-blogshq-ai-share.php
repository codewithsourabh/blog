<?php
/**
 * AI Share Block Module
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/modules/ai-share
 * @since      1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class BlogsHQ_AI_Share {

	public function init() {
		add_shortcode( 'ai_share', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	public function enqueue_frontend_assets() {
		if ( ! is_singular() ) {
			return;
		}

		global $post;
		if ( ! isset( $post->post_content ) || ! has_shortcode( $post->post_content, 'ai_share' ) ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script(
			'blogshq-ai-share',
			BLOGSHQ_PLUGIN_URL . "assets/js/ai-share{$suffix}.js",
			array(),
			BLOGSHQ_VERSION,
			true
		);

		wp_localize_script(
			'blogshq-ai-share',
			'blogshqAiShare',
			array(
				'copiedText' => __( 'Copied!', 'blogshq' ),
			)
		);
	}

	public function render_shortcode( $atts ) {
		$permalink = get_permalink();
		if ( ! $permalink ) {
			return '';
		}

		$url         = esc_url( $permalink );
		$encoded_url = rawurlencode( $url );

		ob_start();
		?>
		<div class="summarize-share-block" role="region" aria-label="<?php esc_attr_e( 'Summarize and share actions', 'blogshq' ); ?>">
			
			<div class="summarize-share-section summarize-section">
				<span class="summarize-share-label" id="summarize-label">
					<?php esc_html_e( 'Summarize with:', 'blogshq' ); ?>
				</span>
				<div class="summarize-share-icons" aria-labelledby="summarize-label">
					
					<a target="_blank" 
					   rel="noopener noreferrer"
					   href="https://chat.openai.com/?q=Read+and+summarize+ONLY+the+content+from+<?php echo esc_attr( $encoded_url ); ?>+Do+not+reference+external+sources" 
					   class="summarize-share-link summarize-ai-link" 
					   aria-label="<?php esc_attr_e( 'Summarize with ChatGPT', 'blogshq' ); ?>">
						<?php echo $this->get_chatgpt_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'ChatGPT', 'blogshq' ); ?></span>
					</a>

					<a target="_blank" 
					   rel="noopener noreferrer"
					   href="https://claude.ai/new?q=Please+read+and+summarize+the+article+at+<?php echo esc_attr( $encoded_url ); ?>+Use+only+information+from+this+specific+page" 
					   class="summarize-share-link summarize-ai-link" 
					   aria-label="<?php esc_attr_e( 'Summarize with Claude.ai', 'blogshq' ); ?>">
						<?php echo $this->get_claude_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'Claude.ai', 'blogshq' ); ?></span>
					</a>

					<a target="_blank" 
					   rel="noopener noreferrer"
					   href="https://www.google.com/search?udm=50&aep=11&q=Summarize+the+key+insights+from+the+article+at+<?php echo esc_attr( $encoded_url ); ?>+using+only+content+from+this+URL" 
					   class="summarize-share-link summarize-ai-link" 
					   aria-label="<?php esc_attr_e( 'Summarize with Google AI', 'blogshq' ); ?>">
						<?php echo $this->get_google_ai_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'Google AI', 'blogshq' ); ?></span>
					</a>

					<a target="_blank" 
					   rel="noopener noreferrer"
					   href="https://x.com/i/grok?text=Please+summarize+this+article+<?php echo esc_attr( $encoded_url ); ?>" 
					   class="summarize-share-link summarize-ai-link" 
					   aria-label="<?php esc_attr_e( 'Summarize with Grok', 'blogshq' ); ?>">
						<?php echo $this->get_grok_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'Grok', 'blogshq' ); ?></span>
					</a>

					<a target="_blank" 
					   rel="noopener noreferrer"
					   href="https://www.perplexity.ai/search/new?q=Read+and+summarize+ONLY+this+article+<?php echo esc_attr( $encoded_url ); ?>+without+using+external+sources.+Focus+exclusively+on+the+content+from+BlogsHQ" 
					   class="summarize-share-link summarize-ai-link" 
					   aria-label="<?php esc_attr_e( 'Summarize with Perplexity', 'blogshq' ); ?>">
						<?php echo $this->get_perplexity_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'Perplexity', 'blogshq' ); ?></span>
					</a>

				</div>
			</div>

			<div class="summarize-share-section share-section">
				<span class="summarize-share-label" id="share-label">
					<?php esc_html_e( 'Share:', 'blogshq' ); ?>
				</span>
				<div class="summarize-share-icons" aria-labelledby="share-label">
					
					<a href="#" 
					   class="summarize-share-link share-link blogshq-copy-link"
					   data-url="<?php echo esc_attr( $url ); ?>"
					   aria-label="<?php esc_attr_e( 'Copy page link', 'blogshq' ); ?>">
						<?php echo $this->get_copy_link_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'Copy link', 'blogshq' ); ?></span>
					</a>

					<a href="https://x.com/intent/tweet?url=<?php echo esc_attr( $encoded_url ); ?>"
					   class="summarize-share-link share-link"
					   target="_blank"
					   rel="noopener noreferrer"
					   aria-label="<?php esc_attr_e( 'Share on X', 'blogshq' ); ?>">
						<?php echo $this->get_x_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'X (Twitter)', 'blogshq' ); ?></span>
					</a>

					<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $encoded_url ); ?>"
					   class="summarize-share-link share-link"
					   target="_blank"
					   rel="noopener noreferrer"
					   aria-label="<?php esc_attr_e( 'Share on Facebook', 'blogshq' ); ?>">
						<?php echo $this->get_facebook_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'Facebook', 'blogshq' ); ?></span>
					</a>

					<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $encoded_url ); ?>"
					   class="summarize-share-link share-link"
					   target="_blank"
					   rel="noopener noreferrer"
					   aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'blogshq' ); ?>">
						<?php echo $this->get_linkedin_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="summarize-share-tooltip"><?php esc_html_e( 'LinkedIn', 'blogshq' ); ?></span>
					</a>

				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	private function get_chatgpt_icon() {
		return '<svg class="summarize-share-icon summarize-ai-icon" role="img" aria-label="ChatGPT logo" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.2093 8.37281C18.5279 7.59285 18.6344 6.74245 18.5179 5.90803C18.4014 5.07359 18.0661 4.28485 17.5461 3.62198C17.026 2.9591 16.3398 2.44569 15.5571 2.13393C14.7744 1.82216 13.9231 1.72315 13.0896 1.84696C12.5732 1.18169 11.8899 0.66493 11.1092 0.349152C10.3284 0.0333742 9.4779 -0.0701761 8.6442 0.0490152C7.81047 0.168206 7.02312 0.505894 6.3621 1.02779C5.7011 1.54969 5.18995 2.23721 4.88058 3.02053C4.04581 3.13481 3.25617 3.46802 2.59189 3.98633C1.92761 4.50463 1.41239 5.18953 1.09856 5.97147C0.784728 6.7534 0.683489 7.60446 0.805127 8.4382C0.926768 9.27191 1.26694 10.0586 1.7911 10.7183C1.53531 11.3451 1.41581 12.0192 1.44052 12.6957C1.46523 13.3722 1.63359 14.0357 1.93441 14.6423C2.23524 15.2487 2.66165 15.7843 3.18532 16.2134C3.709 16.6424 4.31796 16.9551 4.97178 17.1307C5.37703 17.2417 5.79509 17.299 6.21524 17.3012C6.4483 17.301 6.68103 17.2836 6.91155 17.2493C7.42811 17.9143 8.11143 18.4308 8.89215 18.7464C9.6729 19.0619 10.5232 19.1654 11.3568 19.0461C12.1904 18.9268 12.9775 18.5891 13.6385 18.0673C14.2994 17.5455 14.8105 16.858 15.1198 16.0748C15.9545 15.9605 16.7442 15.6273 17.4085 15.109C18.0727 14.5907 18.588 13.9058 18.9018 13.1239C19.2156 12.3419 19.3169 11.4909 19.1953 10.6572C19.0736 9.82345 18.7335 9.03674 18.2093 8.37707V8.37281ZM14.6749 3.27537C15.5341 3.50504 16.2695 4.06135 16.7242 4.82563C17.1789 5.58991 17.317 6.50163 17.109 7.36628C17.028 7.31258 16.9462 7.2606 16.861 7.21202L13.0684 5.01826C12.9647 4.95843 12.8472 4.92692 12.7275 4.92692C12.6078 4.92692 12.4902 4.95843 12.3865 5.01826L8.63655 7.18389V5.60889L12.0883 3.61628C12.4755 3.39135 12.9035 3.24542 13.3475 3.1869C13.7915 3.12839 14.2426 3.15845 14.6749 3.27537ZM11.3638 10.333L10.0002 11.1205L8.63655 10.333V8.75804L10.0002 7.97054L11.3638 8.75804V10.333ZM5.90928 4.77281C5.90959 4.10633 6.10526 3.45457 6.47208 2.89812C6.83889 2.34167 7.36077 1.90494 7.97316 1.64196C8.58556 1.37897 9.26163 1.30126 9.91772 1.41843C10.5738 1.5356 11.1812 1.84252 11.6646 2.30122C11.5795 2.34469 11.4942 2.38644 11.409 2.43929L7.61382 4.62537C7.51028 4.68515 7.42427 4.77111 7.36443 4.87463C7.3046 4.97814 7.27303 5.09557 7.27291 5.21514V9.54554L5.90928 8.75804V4.77281ZM2.57348 6.04525C3.00789 5.28764 3.71809 4.72706 4.55587 4.48048C4.5499 4.57764 4.54564 4.6748 4.54564 4.77281V9.15182C4.54561 9.27145 4.57711 9.38909 4.63695 9.49282C4.69679 9.59645 4.78288 9.68254 4.88655 9.74245L8.63655 11.9072L7.27291 12.6989L3.8212 10.7021C3.03823 10.25 2.4669 9.50536 2.23291 8.63207C1.99892 7.75875 2.12142 6.82825 2.57348 6.04525ZM5.32547 15.8157C4.46631 15.586 3.73091 15.0297 3.27617 14.2655C2.82142 13.5012 2.68334 12.5895 2.89138 11.7248C2.97234 11.7785 3.05416 11.8305 3.13939 11.8791L6.932 14.0728C7.03565 14.1326 7.15322 14.1642 7.27291 14.1642C7.3926 14.1642 7.51017 14.1326 7.61382 14.0728L11.3638 11.9072V13.4822L7.91211 15.4748C7.52488 15.6997 7.0969 15.8456 6.65291 15.9042C6.20893 15.9627 5.75776 15.9326 5.32547 15.8157ZM14.0911 14.3183C14.0915 14.9849 13.8964 15.6371 13.5299 16.194C13.1635 16.7509 12.6417 17.1882 12.0294 17.4516C11.4169 17.7151 10.7407 17.7933 10.0844 17.6764C9.428 17.5595 8.82036 17.2528 8.33655 16.7941C8.42178 16.7506 8.507 16.7046 8.59223 16.6552L12.3865 14.4657C12.4901 14.4059 12.5761 14.32 12.6359 14.2165C12.6958 14.1129 12.7274 13.9955 12.7275 13.8759V9.54554L14.0911 10.333V14.3183ZM17.4269 13.0458C16.9925 13.8035 16.2823 14.364 15.4445 14.6106C15.4505 14.5135 15.4547 14.4163 15.4547 14.3183V9.93927C15.4547 9.81954 15.4233 9.702 15.3635 9.59827C15.3035 9.49463 15.2175 9.40854 15.1138 9.34863L11.3638 7.18389L12.7275 6.39639L16.1792 8.38901C16.9622 8.84109 17.5335 9.58573 17.7675 10.459C18.0015 11.3324 17.8789 12.2628 17.4269 13.0458Z" fill="#3B3D4A"></path></svg>';
	}

	private function get_claude_icon() {
		return '<svg class="summarize-share-icon summarize-ai-icon" role="img" aria-label="Claude.ai logo" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.92689 15.3L9.86002 13.0932L9.92613 12.9016L9.86002 12.795L9.66837 12.7949L9.01103 12.7545L6.76376 12.6937L4.8148 12.6127L2.92654 12.5115L2.45145 12.4103L2.0061 11.8232L2.05197 11.5303L2.4515 11.2617L3.02379 11.3117L4.28844 11.3981L6.1861 11.5289L7.56284 11.61L9.60232 11.8219H9.92613L9.97205 11.691L9.86136 11.61L9.77495 11.5289L7.81114 10.1981L5.68532 8.7917L4.57183 7.98184L3.96987 7.57158L3.66618 7.18689L3.5353 6.34739L4.08195 5.74543L4.81618 5.79536L5.00378 5.84534L5.74752 6.41757L7.3361 7.64719L9.41056 9.17505L9.71424 9.42747L9.83572 9.34112L9.85057 9.28036L9.71424 9.05229L8.58591 7.01281L7.38197 4.93835L6.84611 4.07857L6.70439 3.56299C6.65446 3.35111 6.61804 3.17297 6.61804 2.95564L7.24025 2.1107L7.58441 2L8.4145 2.1107L8.76411 2.41438L9.27968 3.59408L10.1152 5.4513L11.4109 7.97661L11.7902 8.7257L11.9926 9.41946L12.0683 9.63135L12.1991 9.6313V9.50982L12.3056 8.0872L12.5027 6.34067L12.6943 4.09342L12.7605 3.46042L13.0736 2.70183L13.6958 2.29156L14.1816 2.52374L14.5812 3.09597L14.5259 3.46581L14.2883 5.00986L13.8227 7.42852L13.519 9.04817H13.6959L13.8984 8.84574L14.7176 7.75789L16.0944 6.03699L16.7017 5.35406L17.4104 4.59958L17.8651 4.24053L18.7249 4.24047L19.3579 5.18121L19.0745 6.15302L18.1891 7.27596L17.4548 8.22749L16.402 9.6447L15.7447 10.7784L15.8055 10.8689L15.9621 10.8541L18.3403 10.3479L19.6252 10.1157L21.1585 9.85258L21.8522 10.1765L21.9278 10.5058L21.6552 11.1794L20.0153 11.5842L18.092 11.969L15.2278 12.6465L15.1927 12.6721L15.2332 12.7221L16.5235 12.8436L17.0755 12.8733H18.4266L20.9425 13.0609L21.5998 13.4955L21.9939 14.0273L21.9278 14.4323L20.9155 14.9478L19.5496 14.6239L16.3616 13.8654L15.2682 13.5927L15.1171 13.5926V13.6831L16.0282 14.574L17.6977 16.0816L19.7885 18.0252L19.8951 18.5057L19.6265 18.8849L19.3431 18.8445L17.5061 17.4623L16.7975 16.8402L15.1927 15.489H15.0861V15.6307L15.4559 16.172L17.409 19.1076L17.5101 20.0078L17.3685 20.3008L16.8623 20.4776L16.3063 20.3764L15.1631 18.7715L13.9833 16.9642L13.0319 15.3446L12.9158 15.4107L12.3543 21.4587L12.0911 21.7678L11.4837 22L10.9776 21.6153L10.709 20.9931L10.9776 19.7635L11.3016 18.1588L11.5646 16.8833L11.8023 15.2987L11.944 14.7723L11.9346 14.7372L11.8185 14.752L10.624 16.3919L8.80726 18.8471L7.36979 20.3858L7.02564 20.5221L6.42901 20.213L6.48438 19.6609L6.81775 19.1697L8.80726 16.639L10.0071 15.0706L10.7819 14.1649L10.7764 14.034H10.7306L5.44651 17.4649L4.50572 17.5864L4.10079 17.2072L4.15078 16.585L4.34243 16.3825L5.93101 15.2892L5.92561 15.2947L5.92689 15.3Z" fill="#3B3D4A"></path></svg>';
	}

	private function get_google_ai_icon() {
		return '<svg class="summarize-share-icon summarize-ai-icon" role="img" aria-label="Google AI logo" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_5142_3218)">
                        <mask id="mask0_5142_3218" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
                            <path d="M24 0H0V24H24V0Z" fill="white"></path>
                        </mask>
                        <g mask="url(#mask0_5142_3218)">
                            <mask id="mask1_5142_3218" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="2" y="2" width="20" height="20">
                                <path d="M12 2C12.2095 2 12.3923 2.14331 12.4435 2.34672C12.5999 2.96885 12.8059 3.57744 13.0595 4.1666C13.7228 5.70758 14.6329 7.05624 15.7883 8.21166C16.9443 9.36739 18.2927 10.2775 19.8334 10.9407C20.4227 11.1944 21.0313 11.4004 21.6535 11.5568C21.8569 11.608 22 11.7905 22 12C22 12.2095 21.8569 12.3923 21.6533 12.4435C21.0311 12.5999 20.4225 12.8059 19.8334 13.0595C18.2924 13.7228 16.944 14.6329 15.7883 15.7883C14.6329 16.9443 13.7228 18.2927 13.0595 19.8334C12.8058 20.4227 12.5997 21.0313 12.4432 21.6535C12.4184 21.7524 12.3614 21.8402 12.2811 21.9029C12.2008 21.9656 12.1019 21.9998 12 22C11.7905 22 11.608 21.8569 11.5568 21.6533C11.4003 21.0311 11.1942 20.4225 10.9404 19.8334C10.2775 18.2924 9.36769 16.944 8.21166 15.7883C7.05593 14.6329 5.70758 13.7228 4.1666 13.0595C3.57741 12.8059 2.96884 12.5997 2.34672 12.4432C2.24788 12.4185 2.1601 12.3615 2.0973 12.2812C2.0345 12.201 2.00025 12.1022 2 12.0003C2 11.7907 2.14331 11.6082 2.34672 11.5571C2.96886 11.4006 3.57744 11.1946 4.1666 10.9407C5.70758 10.2778 7.05624 9.3677 8.21166 8.21197C9.36739 7.05655 10.2775 5.70789 10.9407 4.16691C11.1943 3.57771 11.4004 2.96914 11.5568 2.34703C11.5814 2.24808 11.6385 2.16019 11.7188 2.09733C11.799 2.03446 11.898 2.0002 12 2Z" fill="black"></path>
                                <path d="M12 2C12.2095 2 12.3923 2.14331 12.4435 2.34672C12.5999 2.96885 12.8059 3.57744 13.0595 4.1666C13.7228 5.70758 14.6329 7.05624 15.7883 8.21166C16.9443 9.36739 18.2927 10.2775 19.8334 10.9407C20.4227 11.1944 21.0313 11.4004 21.6535 11.5568C21.8569 11.608 22 11.7905 22 12C22 12.2095 21.8569 12.3923 21.6533 12.4435C21.0311 12.5999 20.4225 12.8059 19.8334 13.0595C18.2924 13.7228 16.944 14.6329 15.7883 15.7883C14.6329 16.9443 13.7228 18.2927 13.0595 19.8334C12.8058 20.4227 12.5997 21.0313 12.4432 21.6535C12.4184 21.7524 12.3614 21.8402 12.2811 21.9029C12.2008 21.9656 12.1019 21.9998 12 22C11.7905 22 11.608 21.8569 11.5568 21.6533C11.4003 21.0311 11.1942 20.4225 10.9404 19.8334C10.2775 18.2924 9.36769 16.944 8.21166 15.7883C7.05593 14.6329 5.70758 13.7228 4.1666 13.0595C3.57741 12.8059 2.96884 12.5997 2.34672 12.4432C2.24788 12.4185 2.1601 12.3615 2.0973 12.2812C2.0345 12.201 2.00025 12.1022 2 12.0003C2 11.7907 2.14331 11.6082 2.34672 11.5571C2.96886 11.4006 3.57744 11.1946 4.1666 10.9407C5.70758 10.2778 7.05624 9.3677 8.21166 8.21197C9.36739 7.05655 10.2775 5.70789 10.9407 4.16691C11.1943 3.57771 11.4004 2.96914 11.5568 2.34703C11.5814 2.24808 11.6385 2.16019 11.7188 2.09733C11.799 2.03446 11.898 2.0002 12 2Z" fill="url(#paint0_linear_5142_3218)"></path>
                            </mask>
                            <g mask="url(#mask1_5142_3218)">
                                <g filter="url(#filter0_f_5142_3218)">
                                    <path d="M0.194819 17.6361C2.50566 18.4569 5.16169 16.918 6.12726 14.1992C7.09283 11.4806 6.00213 8.6113 3.69129 7.79058C1.38045 6.96986 -1.27558 8.50867 -2.24146 11.2273C-3.20672 13.9461 -2.11602 16.8155 0.194819 17.6361Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter1_f_5142_3218)">
                                    <path d="M10.4553 8.67246C13.6297 8.67246 16.2034 6.04202 16.2034 2.79765C16.2034 -0.447014 13.63 -3.07715 10.4553 -3.07715C7.28059 -3.07715 4.70654 -0.446705 4.70654 2.79765C4.70654 6.04202 7.28028 8.67246 10.4553 8.67246Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter2_f_5142_3218)">
                                    <path d="M8.22115 27.4598C11.5352 27.2979 14.0516 23.6862 13.8417 19.3931C13.6321 15.1 10.7752 11.7508 7.46114 11.9129C4.14712 12.0751 1.63072 15.6864 1.8406 19.9796C2.05048 24.2728 4.90713 27.6219 8.22115 27.4598Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter3_f_5142_3218)">
                                    <path d="M8.22115 27.4598C11.5352 27.2979 14.0516 23.6862 13.8417 19.3931C13.6321 15.1 10.7752 11.7508 7.46114 11.9129C4.14712 12.0751 1.63072 15.6864 1.8406 19.9796C2.05048 24.2728 4.90713 27.6219 8.22115 27.4598Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter4_f_5142_3218)">
                                    <path d="M11.5405 24.8626C14.3185 23.1721 15.0622 19.3225 13.2014 16.264C11.3404 13.2052 7.57956 12.0959 4.80119 13.7861C2.02282 15.4769 1.27915 19.3265 3.14003 22.3853C5.00152 25.4438 8.7621 26.553 11.5405 24.8626Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter5_f_5142_3218)">
                                    <path d="M22.7698 15.2502C25.8925 15.2502 28.424 12.8124 28.424 9.80571C28.424 6.79865 25.8925 4.36084 22.7698 4.36084C19.6473 4.36084 17.1157 6.79865 17.1157 9.80571C17.1157 12.8128 19.6473 15.2502 22.7698 15.2502Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter6_f_5142_3218)">
                                    <path d="M-2.02613 14.619C0.849325 16.8053 5.04971 16.1196 7.35592 13.0866C9.66215 10.054 9.20109 5.8228 6.32563 3.63647C3.45018 1.44983 -0.749897 2.13556 -3.05642 5.1685C-5.36264 8.20114 -4.90128 12.4327 -2.02613 14.619Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter7_f_5142_3218)">
                                    <path d="M12.7072 17.8506C16.139 20.2102 20.6883 19.5531 22.8678 16.3824C25.0477 13.212 24.0324 8.72931 20.6004 6.36977C17.1683 4.00962 12.6194 4.66731 10.4396 7.8374C8.25999 11.0081 9.27521 15.4908 12.7072 17.8506Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter8_f_5142_3218)">
                                    <path d="M18.9463 1.28019C19.8194 2.46735 18.6972 4.77511 16.4404 6.43535C14.1831 8.0956 11.6457 8.47899 10.7726 7.29213C9.89952 6.10466 11.0214 3.7966 13.2783 2.13666C15.5355 0.476411 18.0735 0.0933281 18.9463 1.28019Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter9_f_5142_3218)">
                                    <path d="M11.7786 6.96335C15.2692 3.72546 16.4672 -0.658297 14.4546 -2.82798C12.4421 -4.99767 7.98073 -4.13226 4.49011 -0.894373C0.999502 2.34352 -0.198758 6.72728 1.81406 8.89696C3.82657 11.0667 8.28799 10.2012 11.7786 6.96335Z" fill="#3B3D4A"></path>
                                </g>
                                <g filter="url(#filter10_f_5142_3218)">
                                    <path d="M4.62302 18.5931C6.69778 20.078 9.07951 20.3036 9.94307 19.0973C10.8066 17.8907 9.82473 15.709 7.74996 14.2241C5.6755 12.7393 3.29347 12.5136 2.43022 13.7199C1.56666 14.9265 2.54826 17.1082 4.62302 18.5931Z" fill="#3B3D4A"></path>
                                </g>
                            </g>
                        </g>
                    </g>
                    <defs>
                        <filter id="filter0_f_5142_3218" x="-4.40963" y="5.75321" width="12.7053" height="13.9203" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="0.908308" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter1_f_5142_3218" x="-4.0745" y="-11.8582" width="29.0589" height="29.3116" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="4.39052" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter2_f_5142_3218" x="-5.63673" y="4.44213" width="26.9558" height="30.4883" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="3.73255" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter3_f_5142_3218" x="-5.63673" y="4.44213" width="26.9558" height="30.4883" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="3.73255" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter4_f_5142_3218" x="-5.34987" y="5.53051" width="27.041" height="27.5879" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="3.73255" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter5_f_5142_3218" x="10.0221" y="-2.73282" width="25.4957" height="25.0765" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="3.54683" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter6_f_5142_3218" x="-10.9535" y="-4.07162" width="26.2065" height="26.3986" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="3.21452" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter7_f_5142_3218" x="3.54972" y="-0.787927" width="26.2079" height="25.7961" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="2.87077" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter8_f_5142_3218" x="5.33884" y="-4.54715" width="19.0411" height="17.6661" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="2.56874" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter9_f_5142_3218" x="-3.5018" y="-8.36264" width="23.2722" height="22.7941" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="2.1696" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <filter id="filter10_f_5142_3218" x="-3.25414" y="7.5735" width="18.8816" height="17.6704" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                            <feGaussianBlur stdDeviation="2.68542" result="effect1_foregroundBlur_5142_3218"></feGaussianBlur>
                        </filter>
                        <linearGradient id="paint0_linear_5142_3218" x1="7.68526" y1="15.3818" x2="18.0733" y2="6.62415" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#4893FC"></stop>
                            <stop offset="0.27" stop-color="#4893FC"></stop>
                            <stop offset="0.777" stop-color="#969DFF"></stop>
                            <stop offset="1" stop-color="#BD99FE"></stop>
                        </linearGradient>
                        <clipPath id="clip0_5142_3218">
                            <rect width="24" height="24" fill="white"></rect>
                        </clipPath>
                    </defs>
                </svg>';
			}


	private function get_grok_icon() {
		return '<svg class="summarize-share-icon summarize-ai-icon" role="img" aria-label="Grok logo" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_5142_3219)"><path d="M9.78674 14.6712L16.3053 9.85348C16.6249 9.6173 17.0817 9.70942 17.234 10.0763C18.0354 12.0111 17.6773 14.3362 16.0828 15.9326C14.4883 17.529 12.2697 17.8791 10.2419 17.0817L8.02661 18.1086C11.2039 20.2829 15.0622 19.7452 17.4732 17.3297C19.3856 15.415 19.9779 12.8051 19.4241 10.4515L19.4291 10.4565C18.626 6.99904 19.6266 5.61705 21.6762 2.79107C21.7246 2.72407 21.7732 2.65706 21.8217 2.58838L19.1245 5.28872V5.28034L9.78509 14.6729" fill="#3B3D4A"></path><path d="M8.44159 15.8419C6.16107 13.6608 6.55426 10.2854 8.50012 8.33889C9.93901 6.89825 12.2965 6.31028 14.3544 7.17465L16.5647 6.15281C16.1665 5.86469 15.6562 5.55478 15.0706 5.33701C12.4237 4.2465 9.25471 4.78924 7.10305 6.9418C5.03337 9.01396 4.38251 12.2001 5.50018 14.9189C6.33507 16.9508 4.96644 18.3881 3.58777 19.8388C3.0992 20.353 2.60897 20.8673 2.21411 21.4117L8.43989 15.8435" fill="#3B3D4A"></path></g><defs><clipPath id="clip0_5142_3219"><rect width="20.0001" height="19.4119" fill="white" transform="translate(2 2.29395)"></rect></clipPath></defs></svg>';
	}

	private function get_perplexity_icon() {
		return '<svg class="summarize-share-icon summarize-ai-icon" role="img" aria-label="Perplexity logo" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_5142_3220)"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.89591 1.5L11.8757 7.00951V7.00825V1.51271H13.0397V7.03416L19.0463 1.5V7.78166H21.5125V16.8424H19.0539V22.4359L13.0397 17.152V22.4965H11.8757V17.2391L5.90269 22.5V16.8424H3.43652V7.78166H5.89591V1.5ZM10.9982 8.93148H4.60055V15.6925H5.90123V13.5598L10.9982 8.93148ZM7.06666 14.07V19.9343L11.8757 15.6986V9.70203L7.06666 14.07ZM13.0732 15.6426V9.69638L17.8837 14.0646V16.8424H17.8899V19.8744L13.0732 15.6426ZM19.0539 15.6925H20.3484V8.93148H13.9984L19.0539 13.5119V15.6925ZM17.8823 7.78166V4.14482L13.935 7.78166H17.8823ZM11.0072 7.78166H7.05995V4.14482L11.0072 7.78166Z" fill="#3B3D4A"></path></g><defs><clipPath id="clip0_5142_3220"><rect width="19.1271" height="21.9129" fill="white" transform="translate(2.43652 1.04346)"></rect></clipPath></defs></svg>';
	}

	private function get_copy_link_icon() {
		return '<svg class="summarize-share-icon share-icon" role="img" aria-label="Copy link icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.8195 3.86848C14.1149 1.57314 17.8363 1.57314 20.1317 3.86848C22.427 6.16383 22.427 9.88532 20.1317 12.1807L18.2653 14.0471C17.8747 14.4376 17.2416 14.4376 16.851 14.0471C16.4605 13.6566 16.4605 13.0234 16.851 12.6329L18.7175 10.7665C20.2318 9.25216 20.2318 6.79699 18.7175 5.2827C17.2032 3.7684 14.748 3.7684 13.2337 5.2827L11.3673 7.14914C10.9768 7.53966 10.3436 7.53966 9.95307 7.14914C9.56254 6.75861 9.56254 6.12545 9.95307 5.73492L11.8195 3.86848ZM7.11793 9.98427C7.50846 10.3748 7.50846 11.008 7.11793 11.3985L5.28245 13.234C3.76815 14.7483 3.76815 17.2034 5.28245 18.7177C6.79675 20.232 9.25191 20.232 10.7662 18.7177L12.6017 16.8822C12.9922 16.4917 13.6254 16.4917 14.0159 16.8822C14.4064 17.2728 14.4064 17.9059 14.0159 18.2965L12.1804 20.1319C9.88507 22.4273 6.16358 22.4273 3.86824 20.1319C1.57289 17.8366 1.57289 14.1151 3.86824 11.8198L5.70372 9.98427C6.09425 9.59375 6.72741 9.59375 7.11793 9.98427Z" fill="#3B3D4A"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M15.9537 8.13749C16.3442 8.52802 16.3442 9.16118 15.9537 9.5517L9.09696 16.4084C8.70644 16.799 8.07328 16.799 7.68275 16.4084C7.29223 16.0179 7.29223 15.3848 7.68275 14.9942L14.5395 8.13749C14.93 7.74697 15.5632 7.74697 15.9537 8.13749Z" fill="#3B3D4A"></path></svg>';
	}

	private function get_x_icon() {
		return '<svg class="summarize-share-icon share-icon" role="img" aria-label="X logo" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.2117 16.7815H15.0886L7.75913 7.15474H8.96408L16.2117 16.7815Z" fill="#3B3D4A"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 1.5C4.18629 1.5 1.5 4.18629 1.5 7.5V16.5C1.5 19.8137 4.18629 22.5 7.5 22.5H16.5C19.8137 22.5 22.5 19.8137 22.5 16.5V7.5C22.5 4.18629 19.8137 1.5 16.5 1.5H7.5ZM17.8262 6H15.7999L12.4606 9.83444L9.57387 6H5.39299L10.3884 12.5632L5.65384 17.9999H7.68116L11.3358 13.8053L14.5294 17.9999H18.607L13.3996 11.0833L17.8262 6Z" fill="#3B3D4A"></path></svg>';
	}

	private function get_facebook_icon() {
		return '<svg class="summarize-share-icon share-icon" role="img" aria-label="Facebook logo" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 1.5C4.18629 1.5 1.5 4.18629 1.5 7.5V16.5C1.5 19.8137 4.18629 22.5 7.5 22.5H16.5C19.8137 22.5 22.5 19.8137 22.5 16.5V7.5C22.5 4.18629 19.8137 1.5 16.5 1.5H7.5ZM8.30273 9.73826H9.99902V7.8243C9.99902 6.10992 10.9014 5.21484 12.9347 5.21484H15.0879V8.04199H13.4566C12.9347 8.04199 12.8262 8.25572 12.8262 8.79571V9.73826H15.0879L14.8854 12H12.8262V18.7852H9.99902V12H8.30273V9.73826Z" fill="#3B3D4A"></path></svg>';
	}

	private function get_linkedin_icon() {
		return '<svg class="summarize-share-icon share-icon" role="img" aria-label="LinkedIn logo" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 1.5C4.18629 1.5 1.5 4.18629 1.5 7.5V16.5C1.5 19.8137 4.18629 22.5 7.5 22.5H16.5C19.8137 22.5 22.5 19.8137 22.5 16.5V7.5C22.5 4.18629 19.8137 1.5 16.5 1.5H7.5ZM7.04294 8.60884C7.75777 8.60884 8.33693 8.02498 8.33693 7.30442C8.33693 6.58438 7.75777 6 7.04294 6C6.32812 6 5.74896 6.58438 5.74896 7.30442C5.74896 8.02498 6.32812 8.60884 7.04294 8.60884ZM5.73853 18.0006V9.65237H8.34736V18.0006H5.73853ZM9.91995 9.65237H12.5121V10.7768C13.6057 8.75285 18.2609 8.60309 18.2609 12.7146V18.0006H15.6584V13.6183C15.6584 10.9824 12.5126 11.1817 12.5126 13.6183V18.0006H9.91995V9.65237Z" fill="#3B3D4A"></path></svg>';
	}
}