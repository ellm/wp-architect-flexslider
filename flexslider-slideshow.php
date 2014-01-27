<?php  
/* 
    Plugin Name: wp-Architect Homepage Flexslider Slideshow  
    Description: Homepage Flexslider Slideshow for WP-Architect Theme
    Author: Matthew Ell 
    Version: 1.0 
*/  

// * jQuery FlexSlider v2.2.0
// * http://www.woothemes.com/flexslider/
// *
// * Copyright 2012 WooThemes
// * Free to use under the GPLv2 license.
// * http://www.gnu.org/licenses/gpl-2.0.html
// *
// * Contributing author: Tyler Smith (@mbmufffin)

// Creates Admin Menu "Slides" Custom Post type

function wp_arch_ss_init() {  
    
    $args = array(  
        'public' => true,  
        'labels' => array(
            'name' => _x('Slides', 'post type general name'),
            'singular_name' => _x('Slide', 'post type singular name'),
            'add_new' => _x('Add New', 'event'),
            'add_new_item' => __('Add New Slide'),
            'edit_item' => __('Edit Slide'),
            'new_item' => __('New Slide'),
            'view_item' => __('View Slide'),
            'search_items' => __('Search Slide'),
            'not_found' =>  __('No events found'),
            'not_found_in_trash' => __('No events found in Trash'), 
            'parent_item_colon' => ''
        ),
        'description'  => 'Nivo Homepage Slideshow',
        'exclude_from_search' => true,
        'hierarchical' => true,
        'supports' => array(  
            'title',  
            'thumbnail',
            'custom-fields',
            'page-attributes'
        )
    ); 
    // http://codex.wordpress.org/Function_Reference/register_post_type
    register_post_type('np_images', $args);
}  

add_action('init', 'wp_arch_ss_init');

// Enqueue Styles and Scripts 
// http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
function wp_arch_ss_enqueue() {
    
    if ( is_front_page() && !is_admin() ) {
    
    // enqueue css
    wp_enqueue_style('wp_arch_slideshow_styles', plugins_url('flexslider.css', __FILE__), array(), '01', 'all');

    // If jQuery is not loaded, load jQuery
    wp_enqueue_script('jquery');

    // enqueue script | @Dependents: jQuery
    wp_enqueue_script('wp_arch_slideshow_scripts', plugins_url('jquery.flexslider-min.js', __FILE__), array('jquery'), "1", true);

    // enqueue script | @Dependents: jQuery & wp_arch_slideshow_scripts
    wp_enqueue_script('wp_arch_slideshow_scripts_init', plugins_url('init.js', __FILE__), array('wp_arch_slideshow_scripts'), "1", true);
    
    }
}


add_action('wp_enqueue_scripts', 'wp_arch_ss_enqueue');


// // Create Image Size for Slides
add_image_size('np_function', 1280, 425, true); 

// // Thumbnail Support
add_theme_support( 'post-thumbnails' ); 

// // // Create Slideshow
function np_function( $atts) { 

    extract( shortcode_atts ( array(
        'width' => '',
        'height' => '',
        ), $atts ) ); 
    
    $args = array(  
        'post_type' => 'np_images',  
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );   

    $result = '<section class="slideshow">';  
    $result .= '<div class="flexslider">';
    $result .= '<ul class="slides">';
  
    // The Query
    //http://codex.wordpress.org/Function_Reference/WP_Query
    $query = new WP_Query($args);

    // The Loop
    if ( $query->have_posts() ) {
        while ($query->have_posts()) {  
            $query->the_post();
            $id = get_the_ID();
            $type = array( 1280,425);
            $the_url = wp_get_attachment_image_src(get_post_thumbnail_id($id), $type);
            $the_link = get_post_meta($id, 'link', true);

            if ($the_link == '') {
                $result .='<li><img title="'.get_the_title().'" src="' . $the_url[0] . '" alt=""/></li>';
            } else {
                $result .='<li><a href="'.$the_link.'">'.'<img title="'.get_the_title().'" src="' . $the_url[0] . '" alt=""/></a></li>';
            }
        }
    } else {
        // no slides found
    }  
    /* Restore original Post Data */
    wp_reset_postdata();

    $result .= '</ul>';
    $result .= '</div>';   
    $result .='</section>';  
    return $result;  
}  

add_shortcode('np-shortcode', 'np_function');  

?>
