<?php
/*
Plugin Name: #BW Custom Sidebar Blocks
Plugin URI: http://support.briteweb.com/plugins/bw-custom-sidebar-blocks/
Version: 1.2
Description: Adds custom sidebar block CPT and widget to add to sidebar
Author: #BRITEWEB
Author URI: http://www.briteweb.com/
*/

global $settings_option, $default_size;

$default_size = array( 'w' => 200, 'h' => 0, 'crop' => 0 );

define( 'BW_CSB_IMAGE_NAME', 'csb_image' );
define( 'BW_CSB_IMAGE_OPTION', 'bw_csb_image_size' );
define( 'BW_CSB_CPT', 'bw_sidebars' );

/* ========| CREATE CPT |======== */

add_action( 'init', 'bw_csb_create_post_type' );
function bw_csb_create_post_type() {
	register_post_type( BW_CSB_CPT, 
		array(	
			'label' => 'Sidebars',
			'description' => '',
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array('slug' => ''),
			'query_var' => true,
			'supports' => array('title','editor','custom-fields','revisions','thumbnail','page-attributes',),
			'taxonomies' => array('location',),
			'labels' => array (
				'name' => 'Sidebars',
				'singular_name' => 'Sidebar',
				'menu_name' => 'Sidebars',
				'add_new' => 'Add Sidebar',
				'add_new_item' => 'Add New Sidebar',
				'edit' => 'Edit',
				'edit_item' => 'Edit Sidebar',
				'new_item' => 'New Sidebar',
				'view' => 'View Sidebar',
				'view_item' => 'View Sidebar',
				'search_items' => 'Search Sidebars',
				'not_found' => 'No Sidebars Found',
				'not_found_in_trash' => 'No Sidebars Found in Trash',
				'parent' => 'Parent Sidebar',
			)
		)
	);
}

/* ========| CREATE CUSTOM META BOX |======== */

add_action( 'add_meta_boxes', 'bw_csb_add_custom_box' ); // Create custom field box
add_action( 'save_post', 'bw_csb_save_postdata' ); // Handle saving custom fields

/* Adds a box to the main column on the Post and Page edit screens */
function bw_csb_add_custom_box() {
    add_meta_box( 'bw_csb_section', 'Custom Sidebar Block Options', 'bw_csb_meta_box', BW_CSB_CPT, 'side' );
}

/* Prints the box content */
function bw_csb_meta_box() {

	global $post;

  	// Use nonce for verification
  	wp_nonce_field( plugin_basename( __FILE__ ), 'bw_csb_noncename' );
  	
  	$val = get_post_meta( $post->ID, 'bw_csb_link', TRUE );
  	$cval = get_post_meta( $post->ID, 'bw_csb_link_custom', TRUE );
  	$types = get_post_types( null, 'objects' );
  	bw_remove_some( $types, array( "attachment", "revision", "acf", "nav_menu_item", "wooframework", "twittercache" ) );
  	
  	$allposts = get_posts( array( 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_type' => 'any' ) );
  	  	
	echo '	<p><strong>Link to</strong></p>
			<p><select type="text" id="bw_link_post" name="bw_link_post"><option value="0"></option>';
	foreach ( $types as $type ) {
		echo '	<optgroup label="' . $type->label . '">';
		foreach ( $allposts as $p )
			if ( $p->post_type == $type->name ) 
				echo '<option value="' . $p->ID . '" ' . selected( $p->ID, $val, false ) . '>' . $p->post_title . '</option>';
		echo '	</optgroup>';
	}
	echo '	</select></p>';
	
	echo '	<p><strong>Custom Link (overrides post link above)</strong></p>
			<p><input type="text" id="bw_csb_link_custom" name="bw_csb_link_custom" value="' . $cval . '" /></p>';
	
	wp_reset_query();
		
}

/* When the post is saved, saves our custom data */
function bw_csb_save_postdata( $post_id ) {

  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['bw_csb_noncename'], plugin_basename( __FILE__ ) ) ) return;
  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) ) return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) ) return;
  }
  
  // OK, we're authenticated: we need to find and save the data
  
	$old = get_post_meta( $post_id, 'bw_csb_link', true );	
	$new = $_POST['bw_link_post'];
	if ( empty( $new ) ) $new = "";
	
	if  ( $new ) update_post_meta( $post_id, 'bw_csb_link', $new );
	elseif ( empty( $new ) ) delete_post_meta( $post_id, 'bw_csb_link' );
	
	$cold = get_post_meta( $post_id, 'bw_csb_link_custom', true );	
	$cnew = $_POST['bw_csb_link_custom'];
	if ( empty( $cnew ) ) $cnew = "";
			
	if  ( $cnew ) update_post_meta( $post_id, 'bw_csb_link_custom', $cnew );
	elseif ( empty( $cnew ) ) delete_post_meta( $post_id, 'bw_csb_link_custom' );
	
}


/* ========| INCLUDE CSB WIDGET |======== */

add_action( 'setup_theme', 'bw_csb_widget' );
function bw_csb_widget() {
	require_once(dirname(__FILE__).'/widget.php');
}

/* ========| ADD FIELD TO ADMIN MEDIA SETTINGS |======== */

add_action( 'admin_menu', 'bw_csb_add_image_size_admin' );
function bw_csb_add_image_size_admin() {
	register_setting( 'media', 'bw_csb_image_size' );
	add_settings_field( 'bw_csb_image_size', '#BW CSB Size', 'bw_csb_add_image_size_admin_field', 'media' , 'default' );
}


function bw_csb_add_image_size_admin_field( $args ) {

	global $default_size;

	$sizes = (array) get_option( BW_CSB_IMAGE_OPTION );	
	$width = empty( $sizes['w'] ) ? $default_size['w'] : esc_attr( $sizes['w'] );
	
	?>
	
	<label for="bw_csb_image_size_w"><?php _e('Max Width'); ?></label>
	<input name="bw_csb_image_size[w]" type="text" id="bw_csb_image_size_w" value="<?php echo $width; ?>" class="small-text" />
	
	<?php
}

/* ========| ADD CUSTOM IMAGE SIZE |======== */

add_action ( 'init', 'bw_csb_add_image_size' );
function bw_csb_add_image_size() {

	global $default_size;

	$sizes = (array) get_option( BW_CSB_IMAGE_OPTION );
	//pre_dump($sizes);	
	$width = empty( $sizes['w'] ) ? $default_size['w'] : esc_attr( $sizes['w'] );
	$height = $default_size['h'];
	$crop = $default_size['crop'];

	add_image_size( BW_CSB_IMAGE_NAME, $width, $height, $crop );

}

/* ========| UTILITY FUNCTIONS |======== */

if ( !function_exists( 'bw_trim_value' ) ) {
function bw_trim_value( &$value ) { 
    $value = trim( $value ); 
}
}

if ( !function_exists( 'bw_remove_some' ) ) {
function bw_remove_some( &$arr, $vals ) {
	if ( !empty( $arr ) ) foreach ( $vals as $val ) {
		if ( !empty( $val ) && isset( $arr[$val] ) ) unset( $arr[$val] );
	}
}
}


?>