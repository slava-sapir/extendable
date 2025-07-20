<?php
   $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
   $link = get_post_meta(get_the_ID(), '_portfolio_external_link', true);
   $final_link = $link ? esc_url($link) :  home_url();
?>

<a href="<?php echo esc_url($final_link); ?>" style="color:inherit;text-decoration:none;" target="_blank" rel="noopener noreferrer">
    <div class="wp-block-cover alignwide extendify-image-import" style="border-radius:5px;padding:var(--wp--preset--spacing--80);margin-bottom:var(--wp--preset--spacing--30);min-height:50vh;">
        <?php if ($featured_img_url): ?>
            <img class="wp-block-cover__image-background" src="<?php echo esc_url($featured_img_url); ?>" alt="<?php the_title_attribute(); ?>" data-object-fit="cover" />
        <?php endif; ?>
        <span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-60 has-background-dim"></span>
        <div class="wp-block-cover__inner-container is-layout-flow wp-block-cover-is-layout-flow">
            <div class="wp-block-group is-vertical is-layout-flex wp-container-core-group-is-layout-98bb686d wp-block-group-is-layout-flex">
                <div class="wp-block-group has-global-padding is-layout-constrained wp-block-group-is-layout-constrained">
                    <h4 class="wp-block-heading has-white-color has-text-color has-small-font-size" style="margin-top:24px;font-style:normal;font-weight:400">
                        <?php echo esc_html( get_the_excerpt()) ; ?>
                    </h4>
                    <h3 class="wp-block-heading has-white-color has-text-color has-large-font-size" style="margin-top:12px">
                        <!-- <a href="<?php echo esc_url($final_link); ?>" style="color:inherit;text-decoration:none;" target="_blank" rel="noopener noreferrer" class="my-hover-link"> -->
                            <?php the_title(); ?>
                        <!-- </a> -->
                    </h3>
                    <?php echo apply_filters('the_content', get_the_content()); ?>
                </div>
            </div>
        </div>
    </div>
</a>
