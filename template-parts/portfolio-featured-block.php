<?php
   $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
   $link = get_post_meta(get_the_ID(), '_portfolio_external_link', true);
   $final_link = $link ? esc_url($link) :  home_url();
?>
<p> Pick up the project</p>
<a href="<?php echo esc_url($final_link); ?>" class="pfb-card-link" target="_blank" rel="noopener noreferrer">
    <div class="pfb-card-bgimg" style="background-image:url('<?php echo esc_url($featured_img_url); ?>')">
        <div class="pfb-card-overlay">
            <div class="pfb-card-content">
                <!-- <h4 class="pfb-card-subtitle"> -->
                <h4>
                    <?php echo esc_html(get_the_excerpt()); ?>
                </h4>
                <!-- <h3 class="pfb-card-title"> -->
                <h3>
                    <?php the_title(); ?>
                </h3>
                <!-- <div class="pfb-card-desc"> -->
                <p>
                    <?php echo apply_filters('the_content', get_the_content()); ?>
                </p>
            </div>
        </div>
    </div>
</a>