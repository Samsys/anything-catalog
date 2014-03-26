<?php
/**
 * Anything Catalog.
 *
 * @package   Anything_Catalog
 * @author    Ricardo Correia <me@rcorreia.com>, ...
 * @license   GPL-2.0+
 * @link      http://Anything.pt
 * @copyright 2014 - @rfvcorreia, @samsyspt
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Anything_Catalog
 * @author Ricardo Correia, Anything <ricardo.correia@Anything.pt>
 */
class Anything_Catalog {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'Anything-catalog';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Hook CPT Generation into the 'init' action
		add_action( 'init', array( $this, 'ssys_CatalogCptGen'), 0 );
				
		// Hook Taxonomy Generation into the 'init' action
		add_action( 'init', array( $this,'ssys_CatalogTaxGen'), 0 );
		
		//Hook Featured Metabox to Metaboxes
		add_action( 'add_meta_boxes', array( $this, 'Ssys_catalog_featured_meta_box_add'));  
		
		//Hook Save Featured Metabox data to save post
		add_action( 'save_post', array( $this, 'Ssys_catalog_featured_meta_box_save'));  
		
		//Filter Templates for the new CPT
		add_filter( 'template_include', array( $this,'ssys_template_chooser'));
		
		/*// @TODO Filter New Column in posts table for the new CPT
		add_filter('manage_ _posts_columns', 'Ssys_catalog_columns_book_head'); 
		
		add_action('manage_ _posts_custom_column', 'Ssys_catalog_columns_book_content', 10, 2);
		 //*/ 
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 *@return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}
		
	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}
	
	/**
	 * Register New Post Type With User Defined Values
	 *
	 * @param	object	$newCPT		Object with selected CPT variables From DB
	 * @since	1.0.0
	 */
	 
	public function ssys_CatalogCptGen() {
		
		//Get Post Type Information From WPDB Option	
		$newCPT = get_option('SsysCatalogCPT');
		if($newCPT['iconURL'] == '') $newCPT['iconURL'] = 'dashicons-portfolio';
		
		$labels = array(
			'name'                => _x( $newCPT['name_plural'], 'Post Type General Name', 'Anything-catalog' ),
			'singular_name'       => _x( $newCPT['name_singular'], 'Post Type Singular Name', 'Anything-catalog' ),
			'menu_name'           => __( $newCPT['menu_name'], 'Anything-catalog' ),
			'parent_item_colon'   => __( 'Parent Item', 'Anything-catalog' ),
			'all_items'           => __( 'All Items', 'Anything-catalog' ),
			'view_item'           => __( 'View Item', 'Anything-catalog' ),
			'add_new_item'        => __( 'Add New Item', 'Anything-catalog' ),
			'add_new'             => __( 'Add New', 'Anything-catalog' ),
			'edit_item'           => __( 'Edit Item', 'Anything-catalog' ),
			'update_item'         => __( 'Update Item', 'Anything-catalog' ),
			'search_items'        => __( 'Search Item', 'Anything-catalog' ),
			'not_found'           => __( 'No Items Found', 'Anything-catalog' ),
			'not_found_in_trash'  => __( 'No Items Found in Trash', 'Anything-catalog' ),
		);
		
		$args = array(
			'label'               => __( $newCPT['label'], 'Anything-catalog' ),
			'description'         => __( $newCPT['description'], 'Anything-catalog' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', ),
			'taxonomies'          => array(''), //Array of associated taxonomies
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => $newCPT['iconURL'],
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
		register_post_type( $newCPT['name'], $args );
	
	}
	
	/**
	 * Register New Taxonomy With User Defined Values
	 *
	 * @param	object	$newTax		Object with selected Taxonomy variables
	 * @since	1.0.0
	 */
	 
	// Register Custom Taxonomy
	public function ssys_CatalogTaxGen()  {
		
		$newTaxonomies = (object) get_option('SsysCatalogTax');
		
		//Loop through all defined taxonomies in DB
		foreach( $newTaxonomies as $newTax ){
			
			$labels = array(
				'name'                       => _x( $newTax['name_plural'], 'Taxonomy General Name', 'Anything-catalog' ),
				'singular_name'              => _x( $newTax['name_singular'], 'Taxonomy Singular Name', 'Anything-catalog' ),
				'menu_name'                  => __( $newTax['menu_name'], 'Anything-catalog' ),
				'all_items'                  => __( 'All Items', 'Anything-catalog' ),
				'parent_item'                => __( 'Parent Item', 'Anything-catalog' ),
				'parent_item_colon'          => __( 'Parent Item', 'Anything-catalog' ),
				'new_item_name'              => __( 'New Item Name', 'Anything-catalog' ),
				'add_new_item'               => __( 'Add New Item', 'Anything-catalog' ),
				'edit_item'                  => __( 'Edit Item', 'Anything-catalog' ),
				'update_item'                => __( 'Update Item', 'Anything-catalog' ),
				'separate_items_with_commas' => __( 'Separate Items with commas', 'Anything-catalog' ),
				'search_items'               => __( 'Search Items', 'Anything-catalog' ),
				'add_or_remove_items'        => __( 'Add or remove Items', 'Anything-catalog' ),
				'choose_from_most_used'      => __( 'Choose from the most used Items', 'Anything-catalog' ),
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => true,
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => false,
			);
			
			//Get Associated CPT and relate
			register_taxonomy( $newTax['name'] , $newTax['associated'] , $args );
			
		}
	}
	
	/**
	 * Return Template Hierarchy
	 * 
	 * @param Template Name
	 * @since	1.0.0
	 * @return function filter Template File Name
	 */
	public function ssys_get_template_hierarchy( $template ) {
 
	    // Get the template slug
	    $template_slug = rtrim( $template, '.php' );
	    $template = $template_slug . '.php';
	 
	    // Check if a custom template exists in the theme folder, if not, load the plugin template file
	    if ( $theme_file = locate_template( array( 'Anything-catalog/' . $template ) ) ) {
	        $file = $theme_file;
	    }
	    else {
	        $file = plugin_dir_path( __FILE__ ) . 'templates/' . $template;
	    }
	 
	    return apply_filters( 'ssys_repl_template_' . $template, $file );
	}
	
	/**
	 * Return Plugin Template Files
	 * 
	 * @param Template Name
	 * @since	1.0.0
	 */
	public function ssys_template_chooser( $template ) {
 		
		$newCPT = (object) get_option('SsysCatalogCPT');
		
	    // Post ID
	    $post_id = get_the_ID();
	 
	    // For all other CPT
	    if ( get_post_type( $post_id ) != $newCPT->name || is_search() ) {
	        return $template;
	    }
	 
	    // Else use custom template
	    if ( is_single() ) {
	    	
	        return self::ssys_get_template_hierarchy( 'single' );
	    
		}elseif (is_tax()){
			
			return self::ssys_get_template_hierarchy( 'taxonomy-page' );
		
		}elseif (is_archive()){
	    
			 return self::ssys_get_template_hierarchy( 'archive-page' );
	    
		}
	 
	}
	
	/**
	 * Creates Custom Field For CPT Featured
	 * 
	 * @param Template Name
	 * @since	1.0.0
	 * @return function filter Template File Name
	 */
	
    public function Ssys_catalog_featured_meta_box_add(){
    	$newCPT = get_option('SsysCatalogCPT');
		
        add_meta_box( 'ssys-catalog-featured_id', __('Featured','Anything-catalog'), array($this, 'ssys_catalog_featured_meta_box_cb'), $newCPT['name'], 'side', 'high' );  
    
	}  
	
	/**
	 * Prints the box content.
	 * 
	 * @param  WP_Post $post The object for the current post/page.
	 * @since  1.0.0
	 * 
	 */
	 
	 public function Ssys_catalog_featured_meta_box_cb($post){
	 		
	 	// Add an nonce field so we can check for it later.
  		wp_nonce_field( 'Ssys_catalog_featured_meta_box_cb_nonce', 'meta_box_nonce' );
		
		$checkValue = get_post_meta( $post->ID, '_ssys_catalog_fetuared', true ); 
		
		// Retrieve existing value from DB
		$check = isset( $checkValue ) ? esc_attr( $checkValue ) : ''; 
		
		// Prints metabox with value
		
		$checked = 'off';
		
		if($check == 'on') $checked = 'checked="checked"';
		
 		echo '<input type="checkbox" id="_ssys_catalog_fetuared" name="_ssys_catalog_fetuared" ' . $checked . ' />';
	 	
		echo '<label for="ssys_catalog_featured">' . __( 'Featured', 'Anything-catalog' ) . '</label>';
	}
	
	/**
	 * Saves meta box content.
	 * 
	 * @param  WP_Post $post_id The id for the current post/page.
	 * @since  1.0.0
	 * 
	 */
	
	public function Ssys_catalog_featured_meta_box_save( $post_id ){
		
		 // Bail if we're doing an auto save  
    	 if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		 
		 // if our nonce isn't there, or we can't verify it, bail 
	    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'Ssys_catalog_featured_meta_box_cb_nonce' ) ) return; 
	     
	    // if our current user can't edit this post, bail  
	    if( !current_user_can( 'edit_post' ) ) return;
		
		//Check checkbox value
		$chk = isset( $_POST['_ssys_catalog_fetuared'] ) ? 'on' : 'off'; 
		
		//Updates post meta
		update_post_meta( $post_id, '_ssys_catalog_fetuared', $chk );  
	}
	 
}
