<?php
$featured_image_id = get_post_thumbnail_id( get_the_ID() );
$final_link        = get_permalink();
$project_text      = wp_strip_all_tags( get_the_title() . ' ' . get_the_excerpt() . ' ' . strip_shortcodes( get_the_content() ) );
$summary_source    = wp_strip_all_tags( strip_shortcodes( get_the_content() ) );
$summary_parts     = preg_split( '/\b(?:Project Overview|Overview|The Challenge)\b/i', $summary_source );
$summary           = wp_trim_words( trim( $summary_parts[0] ?? $summary_source ), 30, '&hellip;' );
$project_title     = get_the_title();
$project_type      = ( false !== stripos( $project_title, 'Netflix' ) || false !== stripos( $project_title, 'Flash Cards' ) )
	? 'Independent Project'
	: 'Production Website';
$technology_terms  = array(
	'WordPress'    => 'WordPress',
	'WooCommerce'  => 'WooCommerce',
	'ACF'          => 'ACF',
	'Gutenberg'    => 'Gutenberg',
	'Next.js'      => 'Next.js',
	'React'        => 'React',
	'TypeScript'   => 'TypeScript',
	'JavaScript'   => 'JavaScript',
	'Tailwind'     => 'Tailwind CSS',
	'Bootstrap'    => 'Bootstrap',
	'OpenAI'       => 'OpenAI',
	'PHP'          => 'PHP',
);
$technologies      = array();

foreach ( $technology_terms as $search_term => $label ) {
	if ( false !== stripos( $project_text, $search_term ) ) {
		$technologies[] = $label;
	}

	if ( 3 === count( $technologies ) ) {
		break;
	}
}

if ( empty( $technologies ) ) {
	$technologies = array( 'Responsive UI', 'Custom Development' );
}
?>

<article class="portfolio-card">
	<div class="portfolio-card__media">
		<?php if ( $featured_image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$featured_image_id,
				'large',
				false,
				array(
					'class'    => 'portfolio-card__image',
					'alt'      => $project_title,
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '(max-width: 782px) calc(100vw - 2.5rem), (max-width: 1200px) 50vw, 560px',
				)
			);
			?>
		<?php endif; ?>
	</div>

	<div class="portfolio-card__body">
		<p class="portfolio-card__type"><?php echo esc_html( $project_type ); ?></p>
		<h3 class="portfolio-card__title"><?php echo esc_html( $project_title ); ?></h3>

		<ul class="portfolio-card__technologies" aria-label="Technologies used">
			<?php foreach ( $technologies as $technology ) : ?>
				<li><?php echo esc_html( $technology ); ?></li>
			<?php endforeach; ?>
		</ul>

		<p class="portfolio-card__summary"><?php echo wp_kses_post( $summary ); ?></p>
		<a class="portfolio-card__link" href="<?php echo esc_url( $final_link ); ?>">
			View case study <span class="portfolio-card__arrow" aria-hidden="true">&rarr;</span>
		</a>
	</div>
</article>
