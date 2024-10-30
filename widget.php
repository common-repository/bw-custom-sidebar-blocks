<?php
/*---------------------------------------------------------------------------------*/
/* Custom Sidebar Blocks widget */
/*---------------------------------------------------------------------------------*/

class BW_Widget_Custom_Blocks extends WP_Widget {

	function BW_Widget_Custom_Blocks() {
  	   	$widget_ops = array('description' => 'Display "Sidebar" post type in sidebar' );
       	parent::WP_Widget(false, $name = "#BW - Custom Sidebar Block", $widget_ops);    
   	}

	function widget($args, $instance) {        
	
		extract( $args );
		extract( $instance, EXTR_SKIP );
		
		if ( empty( $sidebar ) ) return;
						
		global $post, $csb_image_name;
		$postid = (int) $sidebar;			
		$post = get_post( $postid );
		
		if ( empty( $post ) ) return;
									  
        $custom = get_post_custom( $post->ID );
        
        if ( !empty( $custom['bw_csb_link_custom'][0] ) ) $link = $custom['bw_csb_link_custom'][0];
        elseif ( !empty( $custom['bw_csb_link'][0] ) ) $link = get_permalink( $custom['bw_csb_link'][0] ); ?>

		<?php echo $before_widget; ?>
		
			<?php if ( $hidetitle != 1 ) echo $before_title . $post->post_title . $after_title; ?>
						
			<?php 
			if ( !empty( $custom['image_link'][0] ) ) echo '<a href="' . $custom['image_link'][0] . '">';
			if ( has_post_thumbnail() ) the_post_thumbnail( BW_CSB_IMAGE_NAME, array( 'class' => 'sidebar-image' ) );
			if ( !empty( $custom['image_link'][0] ) ) echo '</a>';
			?>
			                                                                        
        	<span><?php echo apply_filters( 'the_content', $post->post_content ); ?></span>
        
		<?php echo $after_widget; ?>  
	
		<?php 		
		
		
	}
                   		
   function update($new_instance, $old_instance) {                
       return $new_instance;
   }

   function form($instance) {                
     
     	extract( $instance, EXTR_SKIP );
     
       	$posts = get_posts( array( 'post_type' => BW_CSB_CPT, 'post_status' => 'publish,draft', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
       	   
       ?> 
          
       <p>
       <label for="<?php echo $this->get_field_id('sidebar'); ?>">Sidebar
       <select class="widefat" id="<?php echo $this->get_field_id('sidebar'); ?>" name="<?php echo $this->get_field_name('sidebar'); ?>">
       		<option value="0">Select sidebar...</option>
       		<?php foreach ( $posts as $row ) : ?>
			<option value="<?php echo $row->ID ?>" <?php selected( $sidebar, $row->ID ); ?>><?php echo $row->post_title; if ( $row->post_status != "publish" ) echo " (draft)"; ?></option>
			<?php endforeach; ?>
		</select>
       </label>
       </p>  
       
       <p>
       <label for="<?php echo $this->get_field_id('hidetitle'); ?>">Hide Title?
       <input type="checkbox" id="<?php echo $this->get_field_id('hidetitle'); ?>" name="<?php echo $this->get_field_name('hidetitle'); ?>" value="1" <?php checked( $hidetitle, 1 ); ?> />
       </p>
       
       <?php
   }

} 
register_widget('BW_Widget_Custom_Blocks');
?>