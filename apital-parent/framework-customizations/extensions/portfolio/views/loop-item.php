<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

//get post thumbnail
$thumbnail_id = get_post_thumbnail_id();
if( !empty( $thumbnail_id ) ) {
    $thumbnail    = get_post( $thumbnail_id );
    $image        = wp_get_attachment_image_src($thumbnail->ID,array(1440,960));
    $thumbnail_title = $thumbnail->post_title;
} else {
    $image = '';
    $thumbnail_title = '';
}

$term_list = wp_get_post_terms($post->ID, 'fw-portfolio-category', array("fields" => "names"));

?>
<div class="portfolio-wrapper">
    <div>
        <?php if(!empty($image)):?>
            <img src="<?php echo esc_url($image[0]); ?>" alt="<?php echo esc_attr($thumbnail_title); ?>">
            <a class="w-inline-block portfolio-overlay" href="<?php the_permalink() ?>">
                <div class="pico-wrp">
                    <div class="portfolio-ico" data-ix="zom-out-pico">
                        <div class="w-embed"><i class="fa fa-share"></i>
                        </div>
                    </div>
                </div>
            </a>
        <?php endif; ?>
    </div>
</div>
<a class="w-inline-block portfolio-text-wrapper" href="<?php the_permalink() ?>">
    <h5 class="portfolio-tittle"><?php the_title(); ?></h5>
    <?php if(!empty($term_list)):?>
        <div class="portfolio-sub">
            <?php $names = '';
                foreach($term_list as $term):
                    $names .= strtolower($term) . ', ';
                endforeach;

                echo substr($names, 0,  strlen($names)-2);
            ?>
        </div>
    <?php endif;?>
</a>