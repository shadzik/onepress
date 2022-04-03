<?php
/**
 * OnePress Theme Customizer.
 *
 * @package OnePress
 */

/**
 * Add upsell message for section
 *
 * @return string
 */
function onepress_add_upsell_for_section( $wp_customize, $section_id ){
	if ( apply_filters( 'onepress_add_upsell_for_section', true, $section_id ) ) {

		$name =  $section_id.'__upsell';
		$wp_customize->add_setting( $name,
			array(
				'sanitize_callback' => 'onepress_sanitize_text',
			)
		);
		$wp_customize->add_control( new OnePress_Misc_Control( $wp_customize, $name,
			array(
				'type'        => 'custom_message',
				'section'     => $section_id,
				'description' => __('<h4 class="customizer-group-heading-message">Advanced Section Styling</h4><p class="customizer-group-heading-message">Check out the <a target="_blank" href="https://www.famethemes.com/plugins/onepress-plus/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=onepress_customizer#get-started">OnePress Plus</a> version for full control over the section styling which includes background color, image, video, parallax effect, custom style and more ...</p>', 'onepress' )
			)
		));
	}
}


/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function onepress_customize_register( $wp_customize ) {


	// Load custom controls.
	$path = get_template_directory();
	require $path. '/inc/customizer-controls.php';

	// Remove default sections.

	// Custom WP default control & settings.
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	/**
	 * Hook to add other customize
	 */
	do_action( 'onepress_customize_before_register', $wp_customize );


	$pages  =  get_pages();
	$option_pages = array();
	$option_pages[0] = esc_html__( 'Select page', 'onepress' );
	foreach( $pages as $p ){
		$option_pages[ $p->ID ] = $p->post_title;
	}

	$users = get_users( array(
		'orderby'      => 'display_name',
		'order'        => 'ASC',
		'number'       => '',
	) );

	$option_users[0] = esc_html__( 'Select member', 'onepress' );
	foreach( $users as $user ){
		$option_users[ $user->ID ] = $user->display_name;
	}

	/**
	 * Load Customize Configs
	 * @since 2.1.0
	 */
	// Site Identity.
	require_once $path. '/inc/customize-configs/site-identity.php';

	//Site Options
	require_once $path. '/inc/customize-configs/options.php';
	require_once $path. '/inc/customize-configs/options-global.php';
	require_once $path. '/inc/customize-configs/options-colors.php';
	require_once $path. '/inc/customize-configs/options-header.php';
	require_once $path. '/inc/customize-configs/options-navigation.php';
	require_once $path. '/inc/customize-configs/options-sections-navigation.php';
	require_once $path. '/inc/customize-configs/options-page.php';
	require_once $path. '/inc/customize-configs/options-blog-posts.php';
	require_once $path. '/inc/customize-configs/options-single.php';
	require_once $path. '/inc/customize-configs/options-footer.php';

	/**
	 * @since 2.1.1
	 * Load sections if enabled
	 */
	$sections = Onepress_Config::get_sections();


	foreach( $sections as $key => $section ) {

		if ( Onepress_Config::is_section_active( $key ) ) {
			$file = $path. '/inc/customize-configs/section-'.$key.'.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}

	}

	/*
	// Section Hero
	require_once $path. '/inc/customize-configs/section-hero.php';
	// Section Hero
	require_once $path. '/inc/customize-configs/section-about.php';
	// Video Popup
	require_once $path. '/inc/customize-configs/section-videolightbox.php';
	// Section Gallery
	require_once $path. '/inc/customize-configs/section-gallery.php';
	// Section Features
	require_once $path. '/inc/customize-configs/section-features.php';
	// Section Services
	require_once $path. '/inc/customize-configs/section-services.php';
	// Section Counter
	require_once $path. '/inc/customize-configs/section-counter.php';
	// Section Team
	require_once $path. '/inc/customize-configs/section-team.php';
	// Section News
	require_once $path. '/inc/customize-configs/section-news.php';
	// Section Contact
	require_once $path. '/inc/customize-configs/section-contact.php';
	*/

	// Section Up sell
	require_once $path. '/inc/customize-configs/section-upsell.php';
	
	/**
	 * Hook to add other customize
	 */
	do_action( 'onepress_customize_after_register', $wp_customize );

	/**
	 * Move WC Panel to bottom
	 * @since 2.1.1
	 */
	if ( onepress_is_wc_active() ) {
		$wp_customize->get_panel( 'woocommerce' )->priority = 300;
	}

}
add_action( 'customize_register', 'onepress_customize_register' );
/**
 * Selective refresh
 */
require get_template_directory() . '/inc/customizer-selective-refresh.php';


/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function onepress_customize_preview_js() {
    wp_enqueue_script( 'onepress_customizer_liveview', get_template_directory_uri() . '/assets/js/customizer-liveview.js', array( 'customize-preview', 'customize-selective-refresh' ), false, true );
}
add_action( 'customize_preview_init', 'onepress_customize_preview_js', 65 );



add_action( 'customize_controls_enqueue_scripts', 'opneress_customize_js_settings' );
function opneress_customize_js_settings(){
    if ( ! class_exists( 'Onepress_Dashboard' ) ) {
        return;
    }

    $actions = Onepress_Dashboard::get_instance()->get_recommended_actions();
    $number_action = $actions['number_notice'];

    wp_localize_script( 'customize-controls', 'onepress_customizer_settings', array(
        'number_action' => $number_action,
        'is_plus_activated' => class_exists( 'OnePress_Plus' ) ? 'y' : 'n',
        'action_url' => admin_url( 'themes.php?page=ft_onepress&tab=recommended_actions' ),
    ) );
}

/**
 * Customizer Icon picker
 */
function onepress_customize_controls_enqueue_scripts(){
    wp_localize_script( 'customize-controls', 'C_Icon_Picker',
        apply_filters( 'c_icon_picker_js_setup',
            array(
                'search'    => esc_html__( 'Search', 'onepress' ),
                'fonts' => array(
                    'font-awesome' => array(
                        // Name of icon
                        'name' => esc_html__( 'Font Awesome 5 Brands', 'onepress' ),
                        // prefix class example for font-awesome fa-fa-{name}
                        'prefix' => 'fab',
                        // font url
                        'url' => esc_url( get_template_directory_uri() .'/assets/css/fontawesome-all.min.css' ),
			// Icon class name, separated by |
			'icons' => 'fa-500px|fa-accessible-icon|fa-accusoft|fa-acquisitions-incorporated|fa-adn|fa-adobe|fa-adversal|fa-affiliatetheme|fa-airbnb|fa-algolia|fa-alipay|fa-amazon|fa-amazon-pay|fa-amilia|fa-android|fa-angellist|fa-angrycreative|fa-angular|fa-app-store|fa-app-store-ios|fa-apper|fa-apple|fa-apple-pay|fa-artstation|fa-asymmetrik|fa-atlassian|fa-audible|fa-autoprefixer|fa-avianex|fa-aviato|fa-aws|fa-bandcamp|fa-battle-net|fa-behance|fa-behance-square|fa-bimobject|fa-bitbucket|fa-bitcoin|fa-bity|fa-black-tie|fa-blackberry|fa-blogger|fa-blogger-b|fa-bluetooth|fa-bluetooth-b|fa-bootstrap|fa-btc|fa-buffer|fa-buromobelexperte|fa-buy-n-large|fa-buysellads|fa-canadian-maple-leaf|fa-cc-amazon-pay|fa-cc-amex|fa-cc-apple-pay|fa-cc-diners-club|fa-cc-discover|fa-cc-jcb|fa-cc-mastercard|fa-cc-paypal|fa-cc-stripe|fa-cc-visa|fa-centercode|fa-centos|fa-chrome|fa-chromecast|fa-cloudscale|fa-cloudsmith|fa-cloudversify|fa-codepen|fa-codiepie|fa-confluence|fa-connectdevelop|fa-contao|fa-cotton-bureau|fa-cpanel|fa-creative-commons|fa-creative-commons-by|fa-creative-commons-nc|fa-creative-commons-nc-eu|fa-creative-commons-nc-jp|fa-creative-commons-nd|fa-creative-commons-pd|fa-creative-commons-pd-alt|fa-creative-commons-remix|fa-creative-commons-sa|fa-creative-commons-sampling|fa-creative-commons-sampling-plus|fa-creative-commons-share|fa-creative-commons-zero|fa-critical-role|fa-css3|fa-css3-alt|fa-cuttlefish|fa-d-and-d|fa-d-and-d-beyond|fa-dailymotion|fa-dashcube|fa-deezer|fa-delicious|fa-deploydog|fa-deskpro|fa-dev|fa-deviantart|fa-dhl|fa-diaspora|fa-digg|fa-digital-ocean|fa-discord|fa-discourse|fa-dochub|fa-docker|fa-draft2digital|fa-dribbble|fa-dribbble-square|fa-dropbox|fa-drupal|fa-dyalog|fa-earlybirds|fa-ebay|fa-edge|fa-edge-legacy|fa-elementor|fa-ello|fa-ember|fa-empire|fa-envira|fa-erlang|fa-ethereum|fa-etsy|fa-evernote|fa-expeditedssl|fa-facebook|fa-facebook-f|fa-facebook-messenger|fa-facebook-square|fa-fantasy-flight-games|fa-fedex|fa-fedora|fa-figma|fa-firefox|fa-firefox-browser|fa-first-order|fa-first-order-alt|fa-firstdraft|fa-flickr|fa-flipboard|fa-fly|fa-font-awesome|fa-font-awesome-alt|fa-font-awesome-flag|fa-fonticons|fa-fonticons-fi|fa-fort-awesome|fa-fort-awesome-alt|fa-forumbee|fa-foursquare|fa-free-code-camp|fa-freebsd|fa-fulcrum|fa-galactic-republic|fa-galactic-senate|fa-get-pocket|fa-gg|fa-gg-circle|fa-git|fa-git-alt|fa-git-square|fa-github|fa-github-alt|fa-github-square|fa-gitkraken|fa-gitlab|fa-gitter|fa-glide|fa-glide-g|fa-gofore|fa-goodreads|fa-goodreads-g|fa-google|fa-google-drive|fa-google-pay|fa-google-play|fa-google-plus|fa-google-plus-g|fa-google-plus-square|fa-google-wallet|fa-gratipay|fa-grav|fa-gripfire|fa-grunt|fa-gulp|fa-hacker-news|fa-hacker-news-square|fa-hackerrank|fa-hips|fa-hire-a-helper|fa-hooli|fa-hornbill|fa-hotjar|fa-houzz|fa-html5|fa-hubspot|fa-ideal|fa-imdb|fa-instagram|fa-instagram-square|fa-intercom|fa-internet-explorer|fa-invision|fa-ioxhost|fa-itch-io|fa-itunes|fa-itunes-note|fa-java|fa-jedi-order|fa-jenkins|fa-jira|fa-joget|fa-joomla|fa-js|fa-js-square|fa-jsfiddle|fa-kaggle|fa-keybase|fa-keycdn|fa-kickstarter|fa-kickstarter-k|fa-korvue|fa-laravel|fa-lastfm|fa-lastfm-square|fa-leanpub|fa-less|fa-line|fa-linkedin|fa-linkedin-in|fa-linode|fa-linux|fa-lyft|fa-magento|fa-mailchimp|fa-mandalorian|fa-markdown|fa-mastodon|fa-maxcdn|fa-mdb|fa-medapps|fa-medium|fa-medium-m|fa-medrt|fa-meetup|fa-megaport|fa-mendeley|fa-microblog|fa-microsoft|fa-mix|fa-mixcloud|fa-mixer|fa-mizuni|fa-modx|fa-monero|fa-napster|fa-neos|fa-nimblr|fa-node|fa-node-js|fa-npm|fa-ns8|fa-nutritionix|fa-odnoklassniki|fa-odnoklassniki-square|fa-old-republic|fa-opencart|fa-openid|fa-opera|fa-optin-monster|fa-orcid|fa-osi|fa-page4|fa-pagelines|fa-palfed|fa-patreon|fa-paypal|fa-penny-arcade|fa-periscope|fa-phabricator|fa-phoenix-framework|fa-phoenix-squadron|fa-php|fa-pied-piper|fa-pied-piper-alt|fa-pied-piper-hat|fa-pied-piper-pp|fa-pied-piper-square|fa-pinterest|fa-pinterest-p|fa-pinterest-square|fa-playstation|fa-product-hunt|fa-pushed|fa-python|fa-qq|fa-quinscape|fa-quora|fa-r-project|fa-raspberry-pi|fa-ravelry|fa-react|fa-reacteurope|fa-readme|fa-rebel|fa-red-river|fa-reddit|fa-reddit-alien|fa-reddit-square|fa-redhat|fa-renren|fa-replyd|fa-researchgate|fa-resolving|fa-rev|fa-rocketchat|fa-rockrms|fa-rust|fa-safari|fa-salesforce|fa-sass|fa-schlix|fa-scribd|fa-searchengin|fa-sellcast|fa-sellsy|fa-servicestack|fa-shirtsinbulk|fa-shopify|fa-shopware|fa-simplybuilt|fa-sistrix|fa-sith|fa-sketch|fa-skyatlas|fa-skype|fa-slack|fa-slack-hash|fa-slideshare|fa-snapchat|fa-snapchat-ghost|fa-snapchat-square|fa-soundcloud|fa-sourcetree|fa-speakap|fa-speaker-deck|fa-spotify|fa-squarespace|fa-stack-exchange|fa-stack-overflow|fa-stackpath|fa-staylinked|fa-steam|fa-steam-square|fa-steam-symbol|fa-sticker-mule|fa-strava|fa-stripe|fa-stripe-s|fa-studiovinari|fa-stumbleupon|fa-stumbleupon-circle|fa-superpowers|fa-supple|fa-suse|fa-swift|fa-symfony|fa-teamspeak|fa-telegram|fa-telegram-plane|fa-tencent-weibo|fa-the-red-yeti|fa-themeco|fa-themeisle|fa-think-peaks|fa-tiktok|fa-trade-federation|fa-trello|fa-tripadvisor|fa-tumblr|fa-tumblr-square|fa-twitch|fa-twitter|fa-twitter-square|fa-typo3|fa-uber|fa-ubuntu|fa-uikit|fa-umbraco|fa-uniregistry|fa-unity|fa-unsplash|fa-untappd|fa-ups|fa-usb|fa-usps|fa-ussunnah|fa-vaadin|fa-viacoin|fa-viadeo|fa-viadeo-square|fa-viber|fa-vimeo|fa-vimeo-square|fa-vimeo-v|fa-vine|fa-vk|fa-vnv|fa-vuejs|fa-waze|fa-weebly|fa-weibo|fa-weixin|fa-whatsapp|fa-whatsapp-square|fa-whmcs|fa-wikipedia-w|fa-windows|fa-wix|fa-wizards-of-the-coast|fa-wolf-pack-battalion|fa-wordpress|fa-wordpress-simple|fa-wpbeginner|fa-wpexplorer|fa-wpforms|fa-wpressr|fa-xbox|fa-xing|fa-xing-square|fa-y-combinator|fa-yahoo|fa-yammer|fa-yandex|fa-yandex-international|fa-yarn|fa-yelp|fa-yoast|fa-youtube|fa-youtube-square|fa-zhihu'
                        ),
                )

            )
        )
    );
}

add_action( 'customize_controls_enqueue_scripts', 'onepress_customize_controls_enqueue_scripts' );
