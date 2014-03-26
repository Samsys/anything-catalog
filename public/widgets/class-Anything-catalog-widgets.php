<?php 
/**
 * Creates Anything Catalog widgets.
 *
 * @see WP_Widget::widget()
 *
 * @package   Anything_Catalog
 * @author    Ricardo Correia <me@rcorreia.com>, ...
 * @license   GPL-2.0+
 * @link      http://Anything.pt
 * @copyright 2014 - @rfvcorreia, @samsyspt
 */

 function register_ssys_catalog_widget() {
    register_widget( 'Ssys_Featured_Posts' );
}
add_action( 'widgets_init', 'register_ssys_catalog_widget' );

/**
 * Initializes featured posts widget.
 *
 * @see WP_Widget::widget()
 * 
 * @since   1.0.0
 */
class Ssys_Featured_Posts extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$newCPT = get_option('SsysCatalogCPT');
		
		parent::__construct(
			'Ssys_Featured_Posts', // Base ID
			__('Featured '.$newCPT['name_plural'] , 'Anything-catalog'), // Name
			array( 'description' => __( 'Adds a widget with featured items from '.$newCPT['name_plural'] , 'Anything-catalog' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$newCPT = get_option('SsysCatalogCPT');
		
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		//Print Title if defined
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		
		//Get featured products 
		$args = array(
				   'post_type' => $newCPT['name'],
				   'post_status' => array( 'publish' ),
				   'posts_per_page' => $instance['num_prod'],
				   'orderby' => 'date',
    			   'order' => 'DESC',
    			   'meta_query' => array(                  
				       array(
				         'key' => '_ssys_catalog_fetuared',                 
				         'value' => 'on',                  
				         'type' => 'CHAR',                  
				         'compare' => '='                   
				       )
					)
				);
				
		$featured_query = new WP_Query($args);
		
		if($featured_query->have_posts()) : ?>
		<div id="featured" class="<?php echo $instance['class_container']; ?>">
			<?php while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
				<div class="<?php echo $instance['class_item']; ?>">
					<figure>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
					</figure>
					<h3><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title();?></a></h3>
					<?php the_excerpt();?>
				</div>
			<?php endwhile; ?>
		</div>
		<?php
		endif;
		
		echo $args['after_widget'];
		
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'Anything-catalog' );
		}
		
		if ( isset( $instance[ 'num_prod' ] ) ) {
			$num_prod = $instance[ 'num_prod' ];
		}
		else {
			$num_prod = __( '4', 'Anything-catalog' );
		}
		
		if ( isset( $instance[ 'class_container' ] ) ) {
			$class_container = $instance[ 'class_container' ];
		}
		else {
			$class_container = __( 'featured-container', 'Anything-catalog' );
		}
		
		if ( isset( $instance[ 'class_item' ] ) ) {
			$class_item = $instance[ 'class_item' ];
		}
		else {
			$class_item = __( 'featured-item', 'Anything-catalog' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:','Anything-catalog' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'num_prod' ); ?>"><?php _e( 'Number of products to display:','Anything-catalog' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'num_prod' ); ?>" name="<?php echo $this->get_field_name( 'num_prod' ); ?>" type="text" value="<?php echo esc_attr( $num_prod ); ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'num_prod' ); ?>"><?php _e( 'Class for the container:','Anything-catalog' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'class_container' ); ?>" name="<?php echo $this->get_field_name( 'class_container' ); ?>" type="text" value="<?php echo esc_attr( $class_container ); ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'num_prod' ); ?>"><?php _e( 'Class for the Item:','Anything-catalog' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'class_item' ); ?>" name="<?php echo $this->get_field_name( 'class_item' ); ?>" type="text" value="<?php echo esc_attr( $class_item ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['num_prod'] = ( ! empty( $new_instance['num_prod'] ) ) ? strip_tags( $new_instance['num_prod'] ) : '';
		$instance['class_container'] = ( ! empty( $new_instance['class_container'] ) ) ? strip_tags( $new_instance['class_container'] ) : '';
		$instance['class_item'] = ( ! empty( $new_instance['class_item'] ) ) ? strip_tags( $new_instance['class_item'] ) : '';
		
		return $instance;
	}

} // class Ssys_Featured_Posts