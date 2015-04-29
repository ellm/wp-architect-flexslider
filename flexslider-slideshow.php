<?php  
/* 
    Plugin Name: WP-Architect Homepage Slideshow
    Description: Homepage Flexslider Slideshow for WP-Architect Theme
    Author: Matthew Ell 
    Version: 1.0 
*/  

/*  Copyright 2014  Matthew Ell  (email : ell.matthew@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Create Custom Post Type for Slides 
add_action('init', 'wp_arch_ss_cpt_init');
function wp_arch_ss_cpt_init() {  
    // Register Custom Post Type
    // http://codex.wordpress.org/Function_Reference/register_post_type
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
        'menu_position' => 5, // Show below Posts
        'menu_icon' => '' ,
        'description'  => 'Homepage Slideshow',
        'exclude_from_search' => true,
        'hierarchical' => true,
        'supports' => array(  
            'title',  
            'thumbnail',
            'page-attributes'
        )
    );
    register_post_type('wp_arch_ss_slide', $args);
}

// Enqueue Styles and Scripts 
add_action('wp_enqueue_scripts', 'wp_arch_ss_enqueue');
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

/* Meta Box Fields Set Up ////////////////////////////////// */
// Add Custom Meta Boxes
add_action( 'add_meta_boxes', 'wp_arch_ss_add_meta_boxes');
function wp_arch_ss_add_meta_boxes() {
    // Caption Meta Box
    add_meta_box( 'wp_arch_ss_metabox_options', 'Slide Options', 'wp_arch_ss_metabox_cb', 'wp_arch_ss_slide', 'normal', 'default', array() );
}

// Meta box output
function wp_arch_ss_metabox_cb( $post ) {

    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'wp_arch_ss_metabox_action', 'wp_arch_ss_metabox_nonce' );
    
    // Use get_post_meta() to retrieve an existing value from the database and use the value for the form.
    $wp_arch_ss_stored_meta = get_post_meta( $post->ID );
    ?>
    
    <label for="wp_arch_ss_metabox_link_text"><?php _e( 'Add a URL: ', 'wp_arch_ss_textdomain' ); ?> <input id="wp_arch_ss_metabox_link_text" name="wp_arch_ss_metabox_link_text" value="<?php if ( isset ( $wp_arch_ss_stored_meta['wp_arch_ss_metabox_link_text'] ) ) echo $wp_arch_ss_stored_meta['wp_arch_ss_metabox_link_text'][0]; ?>" /></label>
    <br /> <br />
    <label for="wp_arch_ss_metabox_link_checkbox">
        <?php _e( 'Open link in a new window ?: ', 'wp_arch_ss_textdomain' ); ?> 
        <input type="checkbox" id="wp_arch_ss_metabox_link_checkbox" name="wp_arch_ss_metabox_link_checkbox" value="yes" <?php if ( isset ( $wp_arch_ss_stored_meta['wp_arch_ss_metabox_link_checkbox'] ) ) checked( $wp_arch_ss_stored_meta['wp_arch_ss_metabox_link_checkbox'][0], 'yes' ); ?> />
    </label>
    <br /> <br />
    <label for="wp_arch_ss_metabox_caption_text">
        <?php _e( 'Add a caption: ', 'wp_arch_ss_textdomain' ); ?>
    </label>
    <br/> 
    <textarea style="width:100%;" rows="5" id="wp_arch_ss_metabox_caption_text" name="wp_arch_ss_metabox_caption_text"><?php if ( isset ( $wp_arch_ss_stored_meta['wp_arch_ss_metabox_caption_text'] ) ) echo $wp_arch_ss_stored_meta['wp_arch_ss_metabox_caption_text'][0]; ?></textarea>

    <?php 
}

// Save Meta Box Data
add_action( 'save_post', 'wp_arch_ss_metabox_save_data' );
function wp_arch_ss_metabox_save_data( $post_id ) {
 
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
 
    // Checks for inputs and sanitizes/saves if needed
    if( isset( $_POST[ 'wp_arch_ss_metabox_caption_text' ] ) ) {
        update_post_meta( $post_id, 'wp_arch_ss_metabox_caption_text', sanitize_text_field( $_POST[ 'wp_arch_ss_metabox_caption_text' ] ) );
    }
    if( isset( $_POST[ 'wp_arch_ss_metabox_link_text' ] ) ) {
        update_post_meta( $post_id, 'wp_arch_ss_metabox_link_text', sanitize_text_field( $_POST[ 'wp_arch_ss_metabox_link_text' ] ) );
    }
    // Checkbox
    if( isset( $_POST[ 'wp_arch_ss_metabox_link_checkbox' ] ) ) {
        update_post_meta( $post_id, 'wp_arch_ss_metabox_link_checkbox', 'yes' );
    } else {
        update_post_meta( $post_id, 'wp_arch_ss_metabox_link_checkbox', '' );
    }
 
}

/* Options Setup ////////////////////////////////// */
// Init
add_action('admin_init' , 'wp_arch_ss_opt_init');
function wp_arch_ss_opt_init() {
    // Register Fields
    register_setting('wp_arch_ss_options' , 'wp_arch_ss_slide_opt_width' );
    register_setting('wp_arch_ss_options' , 'wp_arch_ss_slide_opt_height' );

    // Thumbnail Support
    add_theme_support( 'post-thumbnails' ); 

    // Create Image Size for Slides
    $wp_arch_ss_opt_width_data = get_option('wp_arch_ss_slide_opt_width');
    $wp_arch_ss_opt_height_data = get_option('wp_arch_ss_slide_opt_height');

    // Add Thumbnail support
    add_image_size('wp_arch_ss_slide_image', $wp_arch_ss_opt_width_data, $wp_arch_ss_opt_height_data, true); 
}

// Options page output
function wp_arch_ss_options_page() {
    ?>
    <div class="wrap">
        <h2>Homepage Slideshow Settings</h2>
        <?php // If the form has been saved, show confim message ?>
        <?php if( isset($_GET['settings-updated']) ) { ?>
            <div id="message" class="updated">
                <p><strong><?php _e('Settings saved.') ?></strong></p>
                <p>You must <a href="/wp-admin/plugin-install.php?tab=search&type=term&s=regenerate+thumbnails">regenerate thumbnails</a> before new image sizes take effect on images that already exist in the Media Library.</p>
            </div>
        <?php } ?>
        <p>Configure homepage slideshow settings below:</p>
        <form method="post" action="options.php" id="wp_arch_ss_options_form">
        <?php settings_fields( 'wp_arch_ss_options' ); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">Default Slide Size</th>
                        <td>
                            <label for="wp_arch_ss_slide_opt_width">Width</label>
                            <input type="text" name="wp_arch_ss_slide_opt_width" id="wp_arch_ss_slide_opt_width" value="<?php echo esc_attr(get_option('wp_arch_ss_slide_opt_width') ); ?>" class="small-text" />
                            <label for="wp_arch_ss_slide_opt_height">Height</label>
                            <input type="text" name="wp_arch_ss_slide_opt_height" id="wp_arch_ss_slide_opt_height" value="<?php echo esc_attr(get_option('wp_arch_ss_slide_opt_height') ); ?>" class="small-text" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
        </form>
    </div>
    <?php 
}

// Add Slide Settings
add_action( 'admin_menu' , 'wp_arch_ss_plugin_menu' );
function wp_arch_ss_plugin_menu() {
    add_submenu_page( 'edit.php?post_type=wp_arch_ss_slide' , 'Slideshow Settings', 'Slide Settings', 'manage_options', 'wp_arch_ss_plugin', 'wp_arch_ss_options_page' );
}

// Add Menu Icon to Admin Sidebar
add_action( 'admin_head', 'add_menu_icons_styles' );
function add_menu_icons_styles() {
    echo '<style>#menu-posts-wp_arch_ss_slide div.wp-menu-image:before { content: "\f233"; }</style>';
}

/* Output Slideshow ////////////////////////////////// */
add_shortcode('slideshow', 'wp_arch_ss_function');
function wp_arch_ss_function( $atts) { 

    extract( shortcode_atts ( array(
        'width' => '',
        'height' => '',
        ), $atts ) ); 
    
    $args = array(  
        'post_type' => 'wp_arch_ss_slide',  
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
            $type = 'wp_arch_ss_slide_image';
            $the_url = wp_get_attachment_image_src(get_post_thumbnail_id($id), $type);
            $meta_data = get_post_meta($id);
            if ( isset($meta_data["wp_arch_ss_metabox_link_checkbox"][0]) ) {
                $wp_arch_ss_checkbox_target = ( $meta_data["wp_arch_ss_metabox_link_checkbox"][0] == "yes" ? '_blank' : '_self' );
            }
            if ( empty($meta_data['wp_arch_ss_metabox_link_text'][0]) ) {
                $result .='<li><img title="'.get_the_title().'" src="' . $the_url[0] . '" alt=""/>'; 
                if ( !empty($meta_data['wp_arch_ss_metabox_caption_text'][0]) ) { $result .='<h3 class="flex-caption">' . $meta_data['wp_arch_ss_metabox_caption_text'][0] . '</h3>'; }
                $result .='</li>';
            } else {
                $result .='<li><a href="'.$meta_data['wp_arch_ss_metabox_link_text'][0].'" target="' . $wp_arch_ss_checkbox_target . '">'.'<img title="'.get_the_title().'" src="' . $the_url[0] . '" alt=""/>';
                if ( !empty($meta_data['wp_arch_ss_metabox_caption_text'][0]) ) { $result .='<h3 class="flex-caption">' . $meta_data['wp_arch_ss_metabox_caption_text'][0] . '</h3>'; }
                $result .='</a></li>';
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
/* Inlcude Simple Page Ordering  */
include 'simple-page-ordering.php';
