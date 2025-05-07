<?php
/**
 * Extendable functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Extendable
 * @since Extendable 1.0
 */


if ( ! defined( 'EXTENDABLE_THEME_VERSION' ) ) {
	$theme_version = wp_get_theme()->get( 'Version' );
	define( 'EXTENDABLE_THEME_VERSION', is_string( $theme_version ) ? $theme_version : '1.0.0' );
}

if ( ! function_exists( 'extendable_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Extendable 1.0
	 *
	 * @return void
	 */
	function extendable_support() {

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		global $wp_version;
		// Add style for WordPress older versions.
		if ( version_compare( $wp_version, '6.0.2', '<=' ) ) {
			$editor_style = array(
				'style.css',
				'/assets/css/deprecate-style.css',
			);
		} else {
			$editor_style = 'style.css';
		}
		// Enqueue editor styles.
		add_editor_style( $editor_style );
	}

endif;

add_action( 'after_setup_theme', 'extendable_support' );


if ( ! function_exists( 'extendable_styles' ) ) :

	/**
	 * Enqueue styles.
	 *
	 * @since Extendable 1.0
	 *
	 * @return void
	 */
	function extendable_styles() {

		// Register theme stylesheet.
		wp_register_style(
			'extendable-style',
			get_template_directory_uri() . '/style.css',
			array(),
			EXTENDABLE_THEME_VERSION
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'extendable-style' );

		global $wp_version;
		if ( version_compare( $wp_version, '6.0.2', '<=' ) ) {
			// Register deprecate stylesheet.
			wp_register_style(
				'extendable-deprecate-style',
				get_template_directory_uri() . '/assets/css/deprecate-style.css',
				array(),
				EXTENDABLE_THEME_VERSION
			);
			// Enqueue deprecate stylesheet.
			wp_enqueue_style( 'extendable-deprecate-style' );
		}
	}

endif;

add_action( 'wp_enqueue_scripts', 'extendable_styles' );

/**
 * Enqueue block-specific styles.
 *
 * @since Extendable 2.0.11
 *
 * @return void
 */
function extendable_enqueue_block_styles() {
	// Check for specific blocks and enqueue their styles
	if ( has_block( 'contact-form-7/contact-form-selector' ) ) {
		wp_enqueue_style(
			'extendable-contact-form-7-style',
			get_template_directory_uri() . '/assets/css/contact-form-7.css',
			array(),
			EXTENDABLE_THEME_VERSION
		);
	}

	if ( has_block( 'wpforms/form-selector' ) ) {
		wp_enqueue_style(
			'extendable-wpforms-style',
			get_template_directory_uri() . '/assets/css/wpforms.css',
			array(),
			EXTENDABLE_THEME_VERSION
		);
	}
}

add_action( 'enqueue_block_assets', 'extendable_enqueue_block_styles' );

/**
 * Registers pattern categories.
 *
 * @since Extendable 1.0
 *
 * @return void
 */
function extendable_register_pattern_categories() {
	$block_pattern_categories = array(
		'header' => array( 'label' => __( 'Headers', 'extendable' ) ),
		'footer' => array( 'label' => __( 'Footers', 'extendable' ) ),
	);

	/**
	 * Filters the theme block pattern categories.
	 *
	 * @since Extendable 1.0
	 *
	 * @param array[] $block_pattern_categories {
	 *     An associative array of block pattern categories, keyed by category name.
	 *
	 *     @type array[] $properties {
	 *         An array of block category properties.
	 *
	 *         @type string $label A human-readable label for the pattern category.
	 *     }
	 * }
	 */
	$block_pattern_categories = apply_filters( 'extendable_block_pattern_categories', $block_pattern_categories );

	foreach ( $block_pattern_categories as $name => $properties ) {
		if ( ! WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
			register_block_pattern_category( $name, $properties );
		}
	}
}
add_action( 'init', 'extendable_register_pattern_categories', 9 );


/**
 * Enqueue dynamic CSS for primary-foreground duotone filter.
 * 
 * Ensure default logo works well on light and dark backgrounds
 *
 * @since Extendable 2.0.11
 *
 * @return void
 */
function extendable_enqueue_dynamic_duotone_css() {
    $theme_json      = WP_Theme_JSON_Resolver::get_merged_data();
    $duotone_presets = $theme_json->get_settings()['color']['duotone']['theme'] ?? [];

    $preset_index = array_search( 'primary-foreground', array_column( $duotone_presets, 'slug' ) );
    $primary_color   = '#000000';
    $foreground_color = '#ffffff';
    if ( false !== $preset_index ) {
        $primary_color   = $duotone_presets[ $preset_index ]['colors'][0];
        $foreground_color = $duotone_presets[ $preset_index ]['colors'][1];
    }
    list( $r, $g, $b ) = array_map( fn( $c ) => hexdec( $c ) / 255, sscanf( $primary_color, "#%02x%02x%02x" ) );
    $css = "
        .wp-block-site-logo img[src*='extendify-demo-'] {
            filter: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\"><filter id=\"solid-color\"><feColorMatrix color-interpolation-filters=\"sRGB\" type=\"matrix\" values=\"0 0 0 0 {$r} 0 0 0 0 {$g} 0 0 0 0 {$b} 0 0 0 1 0\"/></filter></svg>#solid-color') !important;
        }
    ";
    wp_add_inline_style( 'wp-block-library', $css );
}
add_action( 'wp_enqueue_scripts', 'extendable_enqueue_dynamic_duotone_css' );

/**
 * Exclude WooCommerce Templates from the Block Editor When WooCommerce Is Inactive
 *
 * @package Extendable
 * @since Extendable 2.0.21
 */

 function extendable_exclude_wc_block_templates( $templates, $query ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		$wc_template_slugs = array( 'checkout', 'single-product', 'archive-product' );
		foreach ( $templates as $key => $template ) {
			if ( isset( $template->slug ) && in_array( $template->slug, $wc_template_slugs, true ) ) {
				unset( $templates[ $key ] );
			}
		}
	}
	return $templates;
}
add_filter( 'get_block_templates', 'extendable_exclude_wc_block_templates', 10, 2 );


//Creating portfolio Custom post type
function register_portfolio_cpt() {
    $labels = array(
        'name' => 'Portfolio',
        'singular_name' => 'Portfolio Item',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Portfolio Item',
        'edit_item' => 'Edit Portfolio Item',
        'new_item' => 'New Portfolio Item',
        'view_item' => 'View Portfolio Item',
        'search_items' => 'Search Portfolio',
        'not_found' => 'No Portfolio Items found',
        'not_found_in_trash' => 'No Portfolio Items found in Trash',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'portfolio-archive'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true, // Allow Gutenberg support
    );

    register_post_type('portfolio', $args);
}
add_action('init', 'register_portfolio_cpt');


//Load more shortcode
function render_portfolio_shortcode($args) {
    $args = array(
        'post_type' => 'portfolio',
        'posts_per_page' => 2,
        'paged' => 1,
    );

    $query = new WP_Query($args);
    ob_start();
    ?>

    <div id="portfolio-grid" class="wp-block-group alignwide">
        <?php if ($query->have_posts()):
            while ($query->have_posts()): $query->the_post(); 
			  get_template_part('template-parts/portfolio-block'); 
            endwhile; wp_reset_postdata();
         else: ?>
            <p>No projects found.</p>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <button id="load-more-portfolio" class="wp-block-button__link">Load More</button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 3;
        const loadMoreButton = document.getElementById('load-more-portfolio');

        const spinner = document.createElement('div');
        spinner.id = 'portfolio-spinner';
        spinner.style.display = 'none';
        spinner.style.textAlign = 'center';
        spinner.style.marginTop = '20px';

        loadMoreButton.parentNode.insertBefore(spinner, loadMoreButton.nextSibling);

        loadMoreButton.addEventListener('click', function() {
            spinner.style.display = 'block';
            loadMoreButton.disabled = true;
			
			fetch(`<?php echo admin_url('admin-ajax.php'); ?>?action=load_more_portfolio&page=${currentPage}&is_portfolio=1`)
				.then(response => response.json())
				.then(result => {
				spinner.style.display = 'none';

				if (result.success && result.data.html.trim() !== '') {
				document.getElementById('portfolio-grid').insertAdjacentHTML('beforeend', result.data.html);
				}

				if (result.data.done) {
				loadMoreButton.remove();
				} else {
				loadMoreButton.disabled = false;
				}
				currentPage++;
			});
        });
    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('portfolio_grid', 'render_portfolio_shortcode');

function load_more_portfolio_ajax() {
    $paged = isset($_GET['page']) ? intval($_GET['page']) : 1;
	$is_portfolio = isset($_GET['is_portfolio']) && $_GET['is_portfolio'] == 1;

    $args = array(
        'post_type' => 'portfolio',
        'posts_per_page' => 1,
        'paged' => $paged,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()):
        while ($query->have_posts()): $query->the_post();
          get_template_part('template-parts/portfolio-block');
        endwhile;
		$html = ob_get_clean();
        wp_reset_postdata();
		
	else:$html = '';
    endif;

	$is_last = $paged >= $query->max_num_pages;

	wp_send_json_success([
		'html' => $html,
		'done' => $is_last,
	]);

    die();
}
add_action('wp_ajax_load_more_portfolio', 'load_more_portfolio_ajax');
add_action('wp_ajax_nopriv_load_more_portfolio', 'load_more_portfolio_ajax');

//Register meta field for portfolio external link
function portfolio_add_meta_box() {
	add_meta_box(
		'portfolio_external_link',
		'External Link',
		'portfolio_external_link_callback',
		'portfolio', // your custom post type
		'side',
		'default'
	);
}
add_action('add_meta_boxes', 'portfolio_add_meta_box');
function portfolio_external_link_callback($post) {
	wp_nonce_field('portfolio_save_meta_box_data', 'portfolio_meta_box_nonce');
	$value = get_post_meta($post->ID, '_portfolio_external_link', true);
	echo '<label for="portfolio_external_link">URL</label>';
	echo '<input type="url" id="portfolio_external_link" name="portfolio_external_link" value="' . esc_attr($value) . '" style="width:100%;" />';
}

function portfolio_save_meta_box_data($post_id) {
	if (!isset($_POST['portfolio_meta_box_nonce']) ||
		!wp_verify_nonce($_POST['portfolio_meta_box_nonce'], 'portfolio_save_meta_box_data')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

	if (!current_user_can('edit_post', $post_id)) return;

	if (isset($_POST['portfolio_external_link'])) {
		update_post_meta($post_id, '_portfolio_external_link', sanitize_text_field($_POST['portfolio_external_link']));
	}
}
add_action('save_post', 'portfolio_save_meta_box_data');

function highlight_keywords_in_content($content) {
	  // Check for normal page view
	  $is_portfolio_page = is_page('portfolio') || is_page('about');

	  // Check for AJAX request
	  $is_ajax_portfolio = defined('DOING_AJAX') && DOING_AJAX && (
       (isset($_GET['is_portfolio']) && $_GET['is_portfolio'] == 1)
    );
  
	  if (!$is_portfolio_page && !$is_ajax_portfolio) {
		  return $content;
	  }

    $keywords = ['TypeScript', 'JavaScript', 'Java Spring Boot', 'JQuery', 'custom CSS', 'CI/CD', 'Git', 'AWS','WordPress', 'API', 'Bootstrap', 'React', 'Tailwind'];
	
    foreach ($keywords as $word) {
        $pattern = '/\b(' . preg_quote($word, '/') . ')\b/i';
        $replacement = '<span class="highlight-word">$1</span>';
        $content = preg_replace($pattern, $replacement, $content);
    }

    return $content;
}
add_filter('the_content', 'highlight_keywords_in_content', 20);


