<?php
/**
 * Created by PhpStorm.
 * User: Dungdt
 * Date: 4/4/2016
 * Time: 3:23 PM
 */
if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
$service_type = get_post_meta(get_the_ID(),'service_type',true);
?>
<div  itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />
    <div class="container traveler-single-content">
        <div class="row">
            <div class="col-md-2">
                <?php if(has_post_thumbnail() and get_the_post_thumbnail()){
                    the_post_thumbnail( array( 150, 200 ) );
                }?>
            </div>
            <div class="col-md-10">
                <h3><?php the_title(); ?></h3>
                <div> <i class="fa fa-map-marker"></i>
                    <?php echo get_post_meta(get_the_ID(),'address',true); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2 space-top-5">
                <?php echo traveler_load_view('single/order-form')?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 space-top-5">
                <div class="content-single">
                    <?php
                    if(have_posts()){
                        while(have_posts())
                        {
                            the_post();
                            the_content();
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 space-top-5">
                <?php
                $taxonomy = Traveler_Admin_Taxonomy_Controller::inst()->get_taxonomies();
                if(!empty($taxonomy)) {
                    foreach( $taxonomy as $k => $v ) {
                        if(in_array($service_type,$v['service_type'])){
                            echo "<h4>".$v['label']."</h4>";
                            $terms = get_the_terms( get_the_ID() , $v['name'] );
                            if(!empty( $terms )) {
                                ?>
                                <ul class="booking-item-features">
                                    <?php
                                    foreach( $terms as $key2 => $value2 ) {
                                        ?>
                                        <li class="">
                                            <span class="booking-item-feature-title"><?php echo esc_html( $value2->name ) ?></span>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            <?php
                            }
                        }
                    }
                }?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 space-top-5">
                <div class="content-single">
                    <?php
                    $map_lat = get_post_meta( get_the_ID() , 'map_lat', true );
                    $map_lng = get_post_meta( get_the_ID() , 'map_long', true );
                    $map_zoom = get_post_meta( get_the_ID() , 'map_zoom', true );
                    ?>
                    <div class="traveler_google_map" data-lat="<?php echo esc_attr($map_lat) ?>" data-lng="<?php echo esc_attr($map_lng) ?>" data-zoom="<?php echo esc_attr($map_zoom) ?>"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 space-top-5">
                <?php
                $gallery = get_post_meta(get_the_ID(),'gallery',true);
                $gallery = explode(",",$gallery);
                if(!empty($gallery)){
                    ?>
                    <div class="fotorama" data-width="100%" data-allowfullscreen="true" data-nav="thumbs">
                        <?php
                        foreach($gallery as $k=>$v){
                            echo wp_get_attachment_image($v,'full');
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 space-top-3">
                <?php
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
                ?>
            </div>
        </div>
    </div>
</div>


