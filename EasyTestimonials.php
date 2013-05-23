<?php
/*
Plugin Name: Easy Testimonials!
Plugin URI: http://bigwilliam.com/easy-testimonials-for-wordpress
Description: Super easy way to display testimonials on your website
Version: 1.0
Author: BigWilliam <hello@bigwilliam.com>
Author URI: http://bigwilliam.com
License: GNU GPL http://www.gnu.org/licenses/gpl.html
*/

/*********** Testimonials Posts Type *********************/
// Register Custom Post Type
function bw_testimonials() {
	$labels = array(
		'name'                => _x( 'Testimonials', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Testimonial', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Testimonials', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Testimonial', 'text_domain' ),
		'all_items'           => __( 'All Testimonials', 'text_domain' ),
		'view_item'           => __( 'View Testimonial', 'text_domain' ),
		'add_new_item'        => __( 'Add New Testimonial', 'text_domain' ),
		'add_new'             => __( 'New Testimonial', 'text_domain' ),
		'edit_item'           => __( 'Edit Testimonial', 'text_domain' ),
		'update_item'         => __( 'Update Testimonial', 'text_domain' ),
		'search_items'        => __( 'Search Testimonials', 'text_domain' ),
		'not_found'           => __( 'No testimonials found', 'text_domain' ),
		'not_found_in_trash'  => __( 'No testimonials found in Trash', 'text_domain' ),
	);

	$args = array(
		'label'               => __( 'testimonials', 'text_domain' ),
		'description'         => __( 'Testimonials from clients', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', ),
		'taxonomies'          => array( 'category', 'testimonial_id', ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => plugins_url( '/assets/img/icon_16.png', __FILE__ ),
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);

	register_post_type( 'testimonials', $args );
}

// Hook into the 'init' action
add_action( 'init', 'bw_testimonials', 0 );

/***************************/
/********** WIDGET *********/
/***************************/

class bw_easy_testimonials extends WP_Widget {

	// constructor
	function bw_easy_testimonials() {
		$widget_ops = array( 'classname' => 'Easy Testimonials', 'description' => __('A simple way to display testimonials on your website.'));
		$control_ops = array( 'width' => 200, 'height' => 500, 'id_base' => 'bw-easy-testimonials');
		$this->WP_Widget('bw-easy-testimonials', __('Easy Testimonials'), $widget_ops, $control_ops);
	}

	// the form in the admin section
	function form( $instance ) {

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} 
		else {
			$title = __('New title', 'text_domain');
		}

		// title
		echo "<p>";
		echo "<label for=\"" . $this->get_field_id('title') . "\" >" . _e('Title:');
		echo "<input type=\"text\" class=\"widefat\"" .
			 "id=\"" . $this->get_field_id('title') . "\" " .
			 "name=\"" . $this->get_field_name('title') . "\" " . 
			 "value=\"" . esc_attr($title) . "\"/> ";
		echo "</label>";
		echo "</p>";

		// Category
		echo "<p>";
		echo "<label for=\"" . $this->get_field_id('category_id') . "\" >Show Testimonials from Category:</label>";
		echo "<select id=\"" . $this->get_field_id('category_id') . "\"" .
			 "name=\"" . $this->get_field_name('category_id') . "\">";
		echo "<option value='show-all-testimonials'>Show All</option>";

		$category = get_categories('orderby=ID&order=ASC'); // get array of all categories
		foreach ($category as $cat) {
			$option = "<option value=\"" . $cat->cat_name . "\"";
			if ($cat->cat_name == $instance['category_id']) 
				$option .= " selected='selected' ";
			$option .= ">";
			$option .= $cat->cat_name;
			$option .= "</option>";
			echo $option;
		}

		echo "</select>";
		echo "</p>";


		// possible options for scrolling testimonials here 
	}

	// updates the widget
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['category_id'] = strip_tags($new_instance['category_id']);

		return $instance;

	}

	// the widget frontend
	function widget( $args, $instance ) {

		extract($args, EXTR_SKIP);

		$title = esc_attr($instance['title']);
		$category_name = $instance['category_id'];

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		if ( $instance['category_id'] == "show-all-testimonials") {
			$query_args = array( 'post_type' => 'testimonials');
		}
		else {
			$query_args = array( 'post_type' => 'testimonials', 'category_name' => $category_name );
		}
		
		$loop = new WP_Query( $query_args );

		while ( $loop->have_posts() ) : 
			$loop->the_post();
			echo '<blockquote>';
			echo '<p>' . get_the_content() . '</p>';
			echo '<small>' . get_the_title() . '</small>';
			echo '</blockquote>';
		endwhile;

		wp_reset_postdata();

		echo $after_widget;

	}

}
add_action( 'widgets_init', create_function('','return register_widget("bw_easy_testimonials");'));

?>