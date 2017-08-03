<?php
/*
Plugin Name: .TUBE Video Curator
Plugin URI: https://www.get.tube/wordpress/tube-video-curator-plugin
Author: .TUBE gTLD
Author URI: https://www.get.tube
Description: The .TUBE Video Curator Plugin for WordPress makes it easy to create posts from YouTube, Vimeo, and Twitch channels, and works seamlessly with most themes and custom post types. 
Version: 1.1.5
Text Domain: tube-video-curator
Domain Path: /languages/
License: GPLv2 or later - http://www.gnu.org/licenses/gpl-2.0.html
*/



global $tube_video_curator;
$tube_video_curator = Tube_Video_Curator::init();


class Tube_Video_Curator {    

  public static $tube_dashboard;  
  
  public static $tube_settings;  
  
  public static $tube_settings_api_keys;  
  
  public static $tube_api_key_nag;  
  
  public static $tube_videos_list; 
  
  public static $tube_find_videos;  
  
  public static $tube_find_sources;  
  
  public static $tube_source_manager;  
  
  public static $tube_youtube_videos;   
  
  public static $tube_vimeo_videos;   
  
  public static $tube_twitch_videos;   
  
  public static $tube_skipped_videos;  
  
  public static $tube_embed;
    
  public static $tube_linkify;  
  
  public static $tube_import_cron;  
  
  //public static $tube_imgur_tools;  
  
  //public static $tube_go_live; 
  
  //public static $tube_add_via_imgur; 
  
  public static $tube_add_via_url; 
  
  public static $tube_theme_filters;   
  
  public static $instance;
  
  public static function init() {
    
    if ( is_null( self::$instance ) )
        self::$instance = new Tube_Video_Curator();
    
    return self::$instance;
      
  }
  
  
  // Constructor  
  function __construct() {
    
    // set constant variables for the plugin
    self::set_constants();
    
    add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    
    // create all the classes used by the plugin
    self::create_classes();
    
    // load the text-domain
    add_action( 'admin_init', array( $this, 'load_tube_vc_textdomain') );
    
    // create the Admin menu
    add_action( 'admin_menu', array( $this, 'create_tube_vc_admin_menu'), 9 );
    
    // customize the admin menu
    add_action( 'admin_menu', array( $this, 'customize_tube_vc_admin_menu'), 99 );
    
    // add action links to the Plugins List page
    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_tube_vc_action_links' ) );
    
    // Init action using for testing autosync capability
    // add_action('init', array( $this, 'test_autosync') );    
    
    // enque the styles for the plugin
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_tube_vc_admin_styles') );    
    
    // Activation hook to set the default settings
    register_activation_hook( __FILE__, array( self::$tube_settings, 'set_default_plugin_settings') );
        
    // Activation hook to set the default Find Videos settings
    register_activation_hook( __FILE__, array( self::$tube_find_videos, 'set_default_query_options') );
        
    // Activation hook to set the default Find Sources settings
    register_activation_hook( __FILE__, array( self::$tube_find_sources, 'set_default_query_options') ); 
    
    // Deactivation hook to clear the autosync cron
    register_deactivation_hook( __FILE__, array( self::$tube_import_cron, 'clear_scheduled_autosync_hook') );
  
     
      
    //  WP Ajax stuff
    
    // Create video post
    add_action('wp_ajax_create_tube_video_post_via_ajax', array( $this, 'create_tube_video_post_via_ajax') );
    add_action('wp_ajax_nopriv_create_tube_video_post_via_ajax', array( $this, 'create_tube_video_post_via_ajax') );
    
    // Get all terms for post type
    add_action('wp_ajax_get_all_terms_for_tube_video_post_type_via_ajax', array( $this, 'get_all_terms_for_tube_video_post_type_via_ajax') );
    add_action('wp_ajax_nopriv_get_all_terms_for_tube_video_post_type_via_ajax', array( $this, 'get_all_terms_for_tube_video_post_type_via_ajax') );
    
    // get all taxonomies for post type
    add_action('wp_ajax_get_all_taxonomies_for_tube_video_post_type_via_ajax', array( $this, 'get_all_taxonomies_for_tube_video_post_type_via_ajax') );
    add_action('wp_ajax_nopriv_get_all_taxonomies_for_tube_video_post_type_via_ajax', array( $this, 'get_all_taxonomies_for_tube_video_post_type_via_ajax') );
    
    // Create skipped Video post
    add_action('wp_ajax_create_skipped_tube_video_post_via_ajax', array( $this, 'create_skipped_tube_video_post_via_ajax') );
    add_action('wp_ajax_nopriv_create_skipped_tube_video_post_via_ajax', array( $this, 'create_skipped_tube_video_post_via_ajax') );
    
    // Restore skipped video
    add_action('wp_ajax_restore_skipped_tube_video_post_via_ajax', array( $this, 'restore_skipped_tube_video_post_via_ajax') );
    add_action('wp_ajax_nopriv_restore_skipped_tube_video_post_via_ajax', array( $this, 'restore_skipped_tube_video_post_via_ajax') );
        
 
  }  
  
  
  // Set up a bunch of constants used by the plugin
  private static function set_constants() {
  
    $constants = array(
      'TUBE_VIDEO_CURATOR_DIR' => plugin_dir_path( __FILE__ ),
      'TUBE_VIDEO_CURATOR_URL' => plugin_dir_url( __FILE__ ),
      'TUBE_VIDEO_CURATOR_SLUG' => 'tube-video-curator',
      'TUBE_VIDEO_CURATOR_DASH_URL' => admin_url('admin.php?page=tube-video-curator'),
      'TUBE_VIDEO_CURATOR_SETTINGS_URL' => admin_url('admin.php?page=tube-video-curator-settings'),
      'TUBE_VIDEO_CURATOR_IMPORT_SETTINGS_URL' => admin_url('admin.php?page=tube-video-curator-settings&tab=import'),
      'TUBE_VIDEO_CURATOR_API_KEYS_URL' => admin_url('admin.php?page=tube-video-curator-api-keys'),
      'TUBE_VIDEO_CURATOR_VIMEO_API_KEYS_URL' => admin_url('admin.php?page=tube-video-curator-api-keys&tab=vimeo-api'),
      'TUBE_VIDEO_CURATOR_TWITCH_API_KEYS_URL' => admin_url('admin.php?page=tube-video-curator-api-keys&tab=twitch-api'),
      'TUBE_VIDEO_CURATOR_VIEW_SOURCES_URL' => admin_url( 'edit.php?post_type=tube_source' ),
      'TUBE_VIDEO_CURATOR_FIND_SOURCES_URL' => admin_url( 'admin.php?page=tube-video-curator-find-sources' ),
      'TUBE_VIDEO_CURATOR_ADD_VIA_URL_URL' => admin_url( 'admin.php?page=tube-video-curator-add-via-url' ),
      'TUBE_VIDEO_CURATOR_FIND_VIDEOS_URL' => admin_url( 'admin.php?page=tube-video-curator-find-videos' ),
      'TUBE_VIDEO_CURATOR_SKIPPED_VIDEOS_URL' => admin_url( 'edit.php?post_type=tube_skipped_video' ),
      'TUBE_VIDEO_CURATOR_LOADER' => plugin_dir_url( __FILE__ ) . '/images/tube-loader.gif',
    );
    
    foreach ( $constants as $key => $value):
      
      if ( !defined( $key ) ) {
        define( $key, $value );
      }
      
    endforeach;
  }

  function load_textdomain() {
    load_plugin_textdomain( TUBE_VIDEO_CURATOR_SLUG, false, dirname( plugin_basename(__FILE__) ) . '/languages' );
  }
  
  
  private static function create_classes() {
    
    // Get the tubeYouTubeVideos instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-youtube-videos.php';
    self::$tube_youtube_videos = new Tube_VC_YouTube_Videos();
        
    // Get the Tube_Vimeo_Videos instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-vimeo-videos.php';
    self::$tube_vimeo_videos = new Tube_VC_Vimeo_Videos();     
        
    // Get the Tube_Twitch_Videos instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-twitch-videos.php';
    self::$tube_twitch_videos = new Tube_VC_Twitch_Videos();      
    
    // Get the Tube_Settings instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-settings.php';
    self::$tube_settings = new Tube_VC_Settings();
    
    // Get the Tube_Settings_Api_Keys instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-settings-api-keys.php';
    self::$tube_settings_api_keys = new Tube_VC_Settings_Api_Keys();
    
    // Get the Tube_Api_Key_Nag instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-api-key-nag.php';
    self::$tube_api_key_nag = new Tube_VC_Api_Key_Nag();

    // Get the Tube_Dashboard instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-dashboard.php';    
    self::$tube_dashboard = new Tube_VC_Dashboard();

    // Get the Tube_Linkify instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-linkify.php';
    self::$tube_linkify = new Tube_VC_Linkify();
    
    // Get the Tube_Videos_List instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-videos-list.php';    
    self::$tube_videos_list = new Tube_VC_Videos_List();
    
    // Get the Tube_Find_Videos instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-find-videos.php';    
    self::$tube_find_videos = new Tube_VC_Find_Videos();
    
    // Get the $tube_find_sources instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-find-sources.php';    
    self::$tube_find_sources = new Tube_VC_Find_Sources();
    
    // Get the tubeSources instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-source-manager.php';    
    self::$tube_source_manager = new Tube_VC_Source_Manager(); 
       
    // Get the Tube_Skipped_Videos instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-skipped-videos.php';
    self::$tube_skipped_videos = new Tube_VC_Skipped_Videos();
    
    // Get the Tube_Embed instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-embed.php';
    self::$tube_embed = new Tube_VC_Embed();
    
    // Get the Tube_Import_Cron instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-import-cron.php';
    self::$tube_import_cron = new Tube_VC_Import_Cron();
    
    // Get the Tube_Imgur_Tools instance [FUTURE]
    //require_once TUBE_VIDEO_CURATOR_DIR . '/classes/FUTURE-class-tube-imgur-tools.php';
    //self::$tube_imgur_tools = new Tube_Imgur_Tools();
    
    // Get the Tube_Add_Via_Imgur instance [FUTURE]
    //require_once TUBE_VIDEO_CURATOR_DIR . '/classes/FUTURE-class-tube-add-via-imgur.php';
    //self::$tube_add_via_imgur = new Tube_Add_Via_Imgur();
    
    // Get the Tube_Go_Live instance [FUTURE]
    //require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-go-live.php';
    //self::$tube_go_live = new Tube_Go_Live();
    
    // Get the Tube_Add_Via_Url instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-add-via-url.php';
    self::$tube_add_via_url = new Tube_VC_Add_Via_Url();
    
    // Get the Tube_Theme_Filters instance
    require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-theme-filters.php';
    self::$tube_theme_filters = new Tube_VC_Theme_Filters();
        
  }

  // function to test the autosycn
  function test_autosync() {
  
    self::$tube_import_cron -> do_autosync();
  
  }
  
  
  // Create the Admin Menu for the plugin
  function create_tube_vc_admin_menu() {
     
    // Main Menu Page
    $menu_page = add_menu_page(
      __('.TUBE Video Curator', 'tube-video-curator'), // The Page title
      __('.TUBE Curator', 'tube-video-curator'), // The Menu Title
      'publish_posts', // The capability required for access to this item
      TUBE_VIDEO_CURATOR_SLUG, // the slug to use for the page in the URL
      array( $this::$tube_dashboard, 'render_options_page_dashboard' ),  // The function to call to render the page
      "dashicons-format-video",
      58 // Position
    );      
  
    // Load the Styes / JS conditionally
    add_action( 'load-' . $menu_page, array( $this, 'load_tube_vc_admin_styles') );
              
              
    // Submenu Page: Settings
    $submenu_page_settings = add_submenu_page( 
      TUBE_VIDEO_CURATOR_SLUG, 
      '.TUBE Plugin: Plugin Settings',
       'Plugin Settings',
      'manage_options', 
      TUBE_VIDEO_CURATOR_SLUG .'-settings', 
      array( $this::$tube_settings, 'render_options_page_settings' )
    );     
    // Load the Styes / JS conditionally
    add_action( 'load-' . $submenu_page_settings, array( $this, 'load_tube_vc_admin_styles') );
    add_action( 'load-' . $submenu_page_settings, array( $this::$tube_settings, 'load_tube_vc_settings_scripts') );
  
  
      
    // Submenu Page: API Settings
    $submenu_page_api_settings = add_submenu_page( 
      TUBE_VIDEO_CURATOR_SLUG, 
      '.TUBE Plugin: API Keys',
       'Your API Keys',
        'manage_options', 
        TUBE_VIDEO_CURATOR_SLUG .'-api-keys', 
        array( $this::$tube_settings_api_keys, 'render_options_page_api_settings' )
    );     
    
    // Load the Styes / JS conditionally
    add_action( 'load-' . $submenu_page_api_settings, array( $this, 'load_tube_vc_admin_styles') );
      
      
    /*     
    // Submenu Page: Go Live      
    $submenu_page_live_stream = add_submenu_page(
      TUBE_VIDEO_CURATOR_SLUG, 
      '.TUBE Plugin: Go Live',
      'Go Live',
      'manage_options', 
      TUBE_VIDEO_CURATOR_SLUG .'-live-stream', 
      array( $this::$tube_go_live, 'render_options_page_live_stream' )
    );     
    
    
    // Submenu Page: Add Via Imgur
    $submenu_page_add_via_imgur = add_submenu_page( 
      TUBE_VIDEO_CURATOR_SLUG, 
      '.TUBE Plugin: Add Via Imgur',
      'Add Via Imgur',
      'manage_options', 
      TUBE_VIDEO_CURATOR_SLUG .'-add-via-imgur', 
      array( $this::$tube_add_via_imgur, 'render_options_page_add_via_imgur' )
    );    
    */
      
      
    // Submenu Page: Add Via Url
    $submenu_page_add_via_url = add_submenu_page(
      TUBE_VIDEO_CURATOR_SLUG, 
      '.TUBE Plugin: Add Via Url',
      'Add Video Via Url',
      'publish_posts', 
      TUBE_VIDEO_CURATOR_SLUG .'-add-via-url', 
      array( $this::$tube_add_via_url, 'render_options_page_add_via_url' )
    );     
    // Using registered $page handle to hook script load
    add_action( 'load-' . $submenu_page_add_via_url, array( $this, 'load_tube_vc_admin_styles') );
    add_action( 'load-' . $submenu_page_add_via_url, array( $this, 'load_tube_vc_find_videos_scripts') );
    add_action( 'load-' . $submenu_page_add_via_url, array( $this::$tube_add_via_url, 'load_tube_vc_add_via_url_scripts') );

      
    // Submenu Page: Find Videos
    $submenu_page_find_videos = add_submenu_page( 
      //$option_page, 
      TUBE_VIDEO_CURATOR_SLUG, 
      '.TUBE Plugin: Search for Videos',
       'Search for Videos',
      'publish_posts', 
      TUBE_VIDEO_CURATOR_SLUG .'-find-videos', 
      array( $this::$tube_find_videos, 'render_options_page_find_videos' )
    );     
    
    // Using registered $page handle to hook script load
    add_action( 'load-' . $submenu_page_find_videos, array( $this, 'load_tube_vc_admin_styles') );
    add_action( 'load-' . $submenu_page_find_videos, array( $this, 'load_tube_vc_find_videos_scripts') );

   
    // Submenu Page: Add Channels & Playlists
    $submenu_page_find_sources = add_submenu_page( 
      //$option_page, 
      TUBE_VIDEO_CURATOR_SLUG, // page is "hidden" from menus
      '.TUBE Plugin: Add New Channels &amp; Playlists',
       'Add Channels &amp; Playlists',
      'manage_options', 
      TUBE_VIDEO_CURATOR_SLUG .'-find-sources', 
      array( $this::$tube_find_sources, 'render_options_page_find_sources' )
    );     
    
    // Using registered $page handle to hook script load
    add_action( 'load-' . $submenu_page_find_sources, array( $this, 'load_tube_vc_admin_styles') );
    add_action( 'load-' . $submenu_page_find_sources, array( $this::$tube_find_sources, 'load_tube_vc_find_sources_scripts') );

  }
  
  
  // Customize the Admin menu
  function customize_tube_vc_admin_menu(){    
      
      // customize the menu
      global $submenu;   
     
      if ( 
        isset( $submenu[TUBE_VIDEO_CURATOR_SLUG] )
         && ( current_user_can( 'manage_options' ) ||  current_user_can( 'publish_posts' ) )
       
       ):
        
        $curator_submenu = $submenu[TUBE_VIDEO_CURATOR_SLUG];
        
        // create a separator device
        $separator = array(
          '<span class="tube-vc-menu-separator"></span>',
          'publish_posts',
          '#',
          NULL        
        );
        
          
        // change the name of first menu item
        $curator_submenu[0][0] = __( 'Dashboard', 'tube-video-curator' );
          
        if( current_user_can( 'manage_options' ) ):
        
          // Do something, for admins only
          
          $revised_menu[] = $curator_submenu[0]; // Dashboard
          
          $revised_menu[] = $separator;     
          
          $revised_menu[] = $curator_submenu[6]; // Your Channels
          
          $revised_menu[] = $curator_submenu[5]; // Add Channels
          
          $revised_menu[] = $separator;     
          
          $revised_menu[] = $curator_submenu[3]; // Add via URL
          
          $revised_menu[] = $curator_submenu[4]; // Search Videos
          
          $revised_menu[] = $curator_submenu[7]; // Skipped Videos
          
          $revised_menu[] = $separator;     
          
          $revised_menu[] = $curator_submenu[1];  // Settings
          
          $revised_menu[] = $curator_submenu[2];  // API Keys 
        
        elseif ( current_user_can( 'publish_posts' ) ):
          
          $revised_menu[] = $curator_submenu[0]; // Dashboard
          
          $revised_menu[] = $separator;     
          
          $revised_menu[] = $curator_submenu[3]; // Your Channels
          
          $revised_menu[] = $separator;     
          
          $revised_menu[] = $curator_submenu[1]; // Add via URL
          
          $revised_menu[] = $curator_submenu[2]; // Search Videos
          
          $revised_menu[] = $curator_submenu[4]; // Skipped Videos
          
        
        endif;
          
        $submenu[TUBE_VIDEO_CURATOR_SLUG] = $revised_menu;

      endif;
      
  }

  // Returns an array of all External Video IDs (to check dupes)
  function get_external_video_ids($site) { 
    
    $args = array( 
      'posts_per_page' => -1,
      'post_type' => get_post_types(),
      'meta_query' => array(
        array(
          'key'     => 'tube_video_id',
          'compare' => 'EXISTS',
        ),
        array(
          'key'     => 'tube_video_site',
          'compare' => '=',
          'value' => $site,
        ),
      ),
    );
    
    
    $posts_query = new WP_Query($args);
    
    $ids = array();
    
    foreach ($posts_query->posts as $post) {
      if($post->tube_video_id != ""){
          
        if ( $post->post_type == 'tube_skipped_video' ):
          $ids[$post->tube_video_id]['status'] = 'skipped';
        else:
          $ids[$post->tube_video_id]['status'] = $post->post_status;
        endif;
         
          $ids[$post->tube_video_id]['id'] = $post->ID;
          $ids[$post->tube_video_id]['permalink'] = get_permalink($post->ID);
          $ids[$post->tube_video_id]['edit'] = get_edit_post_link($post->ID);
        
        
      }      
    }
    
    return $ids;
    
  }
  
  
  //  Get the videos pending review (used in dashboard)
  function get_videos_pending_review( $args = NULL ) {
    
    $args = array( 
      'posts_per_page' => -1,
      'post_status' => 'pending',
      'post_type' => get_option( 'tube_vc_default_import_post_type' ),
      'meta_query' => array(
        array(
          'key'     => 'tube_video_id',
          'compare' => 'EXISTS',
        ),
      ),
    );
    
    
    $posts_query = new WP_Query($args);
    
    if ( ! $posts_query->have_posts() ):
      return NULL;      
    endif;
    
    return $posts_query;
    
    
  }
  
  
    
  // Get all the taxonomies for the tube video post type (via AJAX)
  function get_all_taxonomies_for_tube_video_post_type_via_ajax(  ) {
    
    $taxonomies = $this -> get_all_taxonomies_for_tube_video_post_type(  );    
  
    $taxonomies = json_encode($taxonomies);      
    
    echo $taxonomies;
    
    die();
    
  }
  
  // Get all the taxonomies for the tube video post type
  function get_all_taxonomies_for_tube_video_post_type( $type = NULL ) {
  
    // make sure there's a post type
    if ( ! $type && ! array_key_exists('post_type', $_POST) ):
      return;
    endif;
    
    // if no type via argument, use the post_type from $_POST
    if ( ! $type  ):
        $type = $_POST['post_type'];
    endif;
    
    // Get post type taxonomies.
    $taxonomies = get_object_taxonomies($type);
    
    // don't include the Nav Menu, Link Cat, or Post Format taxonomies
    $ignored = array( 'nav_menu', 'link_category', 'post_format' );
    
    if ( ! $taxonomies || ( count( $taxonomies ) == 0 ) ):
      return NULL;
    endif;
    
    foreach ( $taxonomies as $index => $taxonomy ):
      
      if ( in_array( $taxonomy, $ignored ) ):
        unset( $taxonomies[$index] );
      endif;
        
    endforeach;
    
    // allow taxonomies to be filtered
    $taxonomies = apply_filters( 'tube_vc_filter_importable_taxonomies', $taxonomies);
    
    if ( ! $taxonomies || ( count( $taxonomies ) == 0 ) ):
      return NULL;
    endif;
    
    // create data about the taxonomies
    
    foreach ( $taxonomies as $taxonomy ):
      
      $tax_obj = get_taxonomy( $taxonomy );
      
      $object = new stdClass();
      
      $object->taxonomy_slug = $tax_obj->name;
      
      $object->taxonomy_name = $tax_obj->labels->name;
            
      $taxonomies_data[] = $object;
      
    endforeach;      
    
    // return the taxonomies
    return $taxonomies_data;
    
  }
  
  
  // Get all the terms for the tube video post type (via AJAX)
  function get_all_terms_for_tube_video_post_type_via_ajax( ) { // Returns an array of categories for a post type
  
    $terms = $this -> get_all_terms_for_tube_video_post_type( $type );
    
    $terms = json_encode($terms);      
    
    echo $terms;
    
    die();
    
  }
  
  
  // Get all the terms for the tube video post type
  function get_all_terms_for_tube_video_post_type( $type = NULL ) { // Returns an array of categories for a post type
      
    // make sure there's a post type
    if ( ! $type && ! array_key_exists('post_type', $_POST) ):
      return;
    endif;
    
    // if no type via argument, use the post_type from $_POST
    if ( ! $type  ):
        $type = $_POST['post_type'];
    endif;
    
    // Get all possible taxonomies
    $taxonomies = $this -> get_all_taxonomies_for_tube_video_post_type( $type );
            
    // make sure there are taxonomies, or do nothing
    if ( ! $taxonomies || count($taxonomies) == 0 ):
      
      return;
      
    endif;
    
    // placeholder for the results
    $results = array();
    
    // loop through the taxonomies
    foreach ( $taxonomies as $taxonomy ):
            
        $args = array(
            'orderby'           => 'name', 
            'order'             => 'ASC',
            'hide_empty'        => false, 
            'exclude'           => array(), 
            'exclude_tree'      => array(), 
            'include'           => array(),
            'number'            => '', 
            'fields'            => 'all', 
            'slug'              => '',
            'parent'            => '',
            'hierarchical'      => true, 
            'child_of'          => 0, 
            'get'               => '', 
            'name__like'        => '',
            'description__like' => '',
            'pad_counts'        => false, 
            'offset'            => '', 
            'search'            => '', 
            'cache_domain'      => 'core'
        );
        
        // Get the taxonomy terms
        $tax_terms = get_terms( $taxonomy->taxonomy_slug, $args );
        
        // loop through the terms
        foreach ($tax_terms as $tax_term):
          
          if($tax_term):
            
            // create data object about the term
            $object = new stdClass();
            
            $object->id = $tax_term->term_id;
            
            $object->name = $tax_term->name;
            
            $object->taxonomy_slug = $tax_term->taxonomy;
            
            $object->taxonomy_name = $taxonomy->taxonomy_name;

             // add teh term to the results
            $results[] = $object;
            
          endif;
         
        endforeach;

      
      
    endforeach;
    
    return $results;
    
  }
  
  
  
  // Create Tube Video post (via AJAX)
  function create_tube_video_post_via_ajax(){
  
    $post_id = $this ->  create_tube_video_post( $_POST );

    // make sure we got a result
    if ( is_numeric($post_id) ):
      
      // add the post links to the result
      $results['view'] = get_permalink( $post_id );
      
      $results['edit'] = get_edit_post_link( $post_id );

      $results = json_encode($results);
      
      echo $results;        
        
    else:
      
      echo 'error';
      
    endif;
       
    die();
    
  }
  

  
  // Create Tube Video post  
  function create_tube_video_post( $args ){ // Setting and calling wp_insert_post();
      
      $title = $args['title'];
      
      //$channel_id = $args['channel_id'];
      
      $content = $args['content'];
      
      $id = $args['id'];
	  	  
      $creator_name = ($args['creator_name'] === '') ? 'unknown user':$args['creator_name'];
      
      $author = ( ! array_key_exists('author', $args) ) ? get_option( 'tube_vc_default_import_author') : $args['author']; ;
      
      $image_url = $args['image_url'];
      
      $media_url = $args['media_url'];
      
      $tags = $args['tags'];
      
      $date = $args['date'];
      
      $source_site = $args['site'];
      
      $status = $args['status'];
      
      //$descrContent = $content;
      
      $excerpt = wp_trim_words( $content, 40 );
      
      $auto_imported = ( array_key_exists('auto_imported', $args) ) ? 1 : NULL;
      
      
      $date_gmt_formatted = date('Y-m-d H:i:s',strtotime($date));;
  
      $replacers = array('/%title%/', '/%description%/', '/%embed%/', '/%media_url%/', '/%thumbnail%/', '/%query%/', '/%user%/', '/%date%/');
            
      $post_type = get_option( 'tube_vc_default_import_post_type');
                  
      // Creating new post
      $my_post = array(
        'post_title'    => $title,
        'post_content'  => $content,
        'post_excerpt'  => $excerpt,
        'post_date_gmt' => $date_gmt_formatted,
        'post_status'   => $status,
        'post_author'   => $author,
        'post_type'     => $post_type
      );
      
      
  
      $post_ID = wp_insert_post($my_post);
      //var_dump($post_ID);
      // updating post meta
      // if a media is detected, add its source url
      
      
      // Set the default import term, if provided
      $import_term_id = intval( get_option( 'tube_vc_default_import_term') );
      
      if ( $import_term_id ):
        
        $import_term = get_term( $import_term_id );
        
        if ( $import_term ):
          
          wp_set_object_terms( $post_ID, $import_term_id, $import_term->taxonomy ); 
          
        endif;
        
      endif;        
        
        
      // deal with tag importing  
      if ( $tags ):
        
        //$taxonomy = 'category';
        $taxonomy = get_option( 'tube_vc_default_tag_import_taxonomy');
        
        if ( $taxonomy ):
          
          // create an array from the tags,if it's not an array already
          $tags_array = is_array( $tags ) ? $tags : explode(',', $tags);
          
          $tags_sanitized = array();
          
          // sanitize all the the tags
          foreach ( $tags_array as $tag ):
            
            $tags_sanitized[] = esc_html( $tag );
            
          endforeach;
          
          wp_add_object_terms( $post_ID, $tags_sanitized, $taxonomy ); 
        
        endif;
        
      endif;
        
      // add all the post meta  
      add_post_meta( $post_ID, 'tube_video_oembed_url', $media_url, true );
      
      add_post_meta( $post_ID, 'tube_video_date', $date, true );
      
      add_post_meta( $post_ID, 'tube_video_id', $id, true );
      
      add_post_meta( $post_ID, 'tube_video_site', $source_site, true );
      
      add_post_meta( $post_ID, 'tube_video_creator_name', $creator_name, true );
      
      if ($auto_imported):
        add_post_meta( $post_ID, 'tube_video_auto_imported', $auto_imported, true );
      endif;
      
      
      if( $image_url ):
        
        add_post_meta( $post_ID, 'tube_video_image_url', $image_url, true ) || update_post_meta( $post_ID, 'tube_video_image_url', $image_url ); 
        
        // Create and upload thumbnail 
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $title_sanitized = substr(sanitize_title(stripslashes($title)), -70);
        $filename = $title_sanitized . '-' . $id.basename($image_url);
        $filename = str_replace( array('%','maxresdefault'),'', $filename);
   
        if(wp_mkdir_p($upload_dir['path']))
            $file = $upload_dir['path'] . '/' . $filename;
        else
            $file = $upload_dir['basedir'] . '/' . $filename;
        file_put_contents($file, $image_data);
    
        $wp_filetype = wp_check_filetype($filename, null );
    
        
        $caption = '<a href="'.$media_url.'" target="_blank">' . _x('Source', 'Text of source link in image caption', 'tube-video-curator') . '</a>';
        
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => esc_html($title),
            'post_excerpt' => wp_kses_post($caption),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $file, $post_ID );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
    
        update_post_meta($attach_id, '_wp_attachment_image_alt', esc_html($title));
        
        set_post_thumbnail( $post_ID, $attach_id );
        
      endif;
      
      return $post_ID;
      
  }
  
  
  
  // Create the skipped video post (via AJAX)
  function create_skipped_tube_video_post_via_ajax(){ // Reject post : insert
      
      $title = $_POST['title'];
      
      $content = $_POST['content'];
      
      $creator_name = $_POST['creator_name'];
      
      $id = $_POST['id'];
      
      $date = $_POST['date'];      
      
      $date_gmt_formatted = date('Y-m-d H:i:s',strtotime($date));;
      
      $media_url = $_POST['media_url'];
      
      $source_site = $_POST['site'];
      
      $author = ( ! array_key_exists('author', $_POST) ) ? get_option( 'tube_vc_default_import_author') : $_POST['author']; ;
  
  
      $skipped_video_post_args = array(
        'post_name'     => $source_site . '-' . $id,
        'post_title'    => $title . ' [' . $creator_name . ' | ' . $source_site . ']',
        'post_content'  => $content,
        'post_date_gmt' => $date_gmt_formatted,
        'post_status'   => 'publish',
        //'tags_input'    => $author,
        'post_author'   => $author,
        'post_type'     => 'tube_skipped_video'
      );
  
      $skipped_video_post = wp_insert_post($skipped_video_post_args);
      
      add_post_meta( $skipped_video_post, 'tube_video_oembed_url', $media_url, true );
      add_post_meta( $skipped_video_post, 'tube_video_date', $date, true );
      add_post_meta( $skipped_video_post, 'tube_video_id', $id, true );
      add_post_meta( $skipped_video_post, 'tube_video_site', $source_site, true );
      add_post_meta( $skipped_video_post, 'tube_video_creator_name', $creator_name, true );
      //add_post_meta( $skipped_video_post, 'tube_video_channel_id', $channel_id, true );
      

      if ( is_numeric($skipped_video_post) ):
        
        $results['id'] =  $skipped_video_post ;
        
        $results['view'] = get_permalink( $skipped_video_post );
        
        $results['edit'] = get_edit_post_link( $skipped_video_post );

        $results = json_encode($results);
        
        echo $results;          
          
      else:
        
        echo 'error';
        
      endif;
         
      die();
  }
  
  
  
  // Restore the skipped video post (via AJAX)
  function restore_skipped_tube_video_post_via_ajax(){ // Reject post : insert
      
      $post_id = $_POST['postid'];
  
      $restore_post = wp_delete_post($post_id);
      
      if ( $restore_post ):
        echo 'success';          
      else:
        echo 'error';
      endif;
         
      die();
  }


  // Load the text domain
  function load_tube_vc_textdomain() {
      
    load_plugin_textdomain('tube-video-curator', false, basename( dirname( __FILE__ ) ) . '/i18n' );
    
  }
  
  
  
  // Main Tube Admin CSS
  function load_tube_vc_admin_styles(){
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_tube_vc_admin_styles') );
  }
      
  function enqueue_tube_vc_admin_styles() {
      wp_register_style(
        TUBE_VIDEO_CURATOR_SLUG, 
        plugins_url('/css/style.css', __FILE__), 
        array(), 
        '1.0.0'
     );
     wp_enqueue_style(TUBE_VIDEO_CURATOR_SLUG);
     
  }
  
  
  
  function load_tube_vc_find_videos_scripts(){
    
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_find_videos_scripts') );      
      
  }
  
      
  function enqueue_find_videos_scripts() {
    
      wp_register_script(
        'tube_vc_find_videos', 
        plugins_url('/js/tube-vc-find-videos.js', __FILE__), 
        array(), 
        '1.0.0',
        //rand(50000, 150000), 
        false
      );
      wp_enqueue_script('tube_vc_find_videos');
      
      add_action( 'admin_print_scripts', array(  $this, 'print_find_videos_scripts'), 99 );
      
  }

      
  function print_find_videos_scripts() {    
    ?>
    <script type="text/javascript">
      var pluginDirectory = '<?php echo plugins_url('', __FILE__); ?>';
      Tube_VC_Ajax_Videos.query = '<?php echo get_option( 'tube_vc_find_videos_query', '' ); ?>';
      Tube_VC_Ajax_Videos.queryType = '<?php echo get_option( 'tube_vc_find_videos_query_type', '' ); ?>';
      Tube_VC_Ajax_Videos.number = '<?php echo get_option( 'tube_vc_find_videos_max_results', '' ); ?>';
      Tube_VC_Ajax_Videos.currentCat = '<?php echo get_option( 'tube_vc_default_import_term', '' ); ?>';
      Tube_VC_Ajax_Videos.currentTagImportTax = '<?php echo get_option( 'tube_vc_default_tag_import_taxonomy', '' ); ?>';
      Tube_VC_Ajax_Videos.trans.approve = '<?php _e('Approve', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.deny = '<?php _e('Deny', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.needUpdate = '<?php _e('You have changed the query parameters, remember to click on Save Changes to update the results.', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.apiWrong = '<?php _e('There is an error with your API key, it has been rejected by Google API Service.', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.loading = '<?php _e('Loading...', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.mediaFailed = '<?php _e('Video import failed. Please try again.', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.mediaAdded = '<?php _e('Video added successfully.', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.mediaRejected = '<?php _e('Video skipped successfully.', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.restoreFailed = '<?php _e('Video restore failed. Please try again.', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.mediaRestored = '<?php _e('Video restored successfully.', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.videoPublishedFlag = '<?php _e('PUBLISHED', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.videoDraftFlag = '<?php _e('DRAFT', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.videoPendingFlag = '<?php _e('PENDING', 'tube-video-curator'); ?>';
      Tube_VC_Ajax_Videos.trans.videoSkippedFlag = '<?php _e('SKIPPED', 'tube-video-curator'); ?>';
     </script>
    
    <?php
  }



  

  
  /* -----------------------------------------------------------------------------
   *  Render the admin page
    ---------------------------------------------------------------------------- */
  
  function render_options_page_find_videos() {
      
      require_once(dirname(__FILE__) . '/TOSS-page-find-videos.php');
       
  }
  
  
  
  
  
  // add action links to the Plugins List page
  function add_tube_vc_action_links ( $links ) {
      
     $mylinks = array(
       '<a href="' . TUBE_VIDEO_CURATOR_DASH_URL . '">Dashboard</a>',
       '<a href="' . TUBE_VIDEO_CURATOR_SETTINGS_URL . '">Settings</a>'
       );
       
      return array_merge( $links, $mylinks );
      
  }


  /* -----------------------------------------------------------------------------
   *  Get settings values for sites & autosycn
    ---------------------------------------------------------------------------- */
  
  
  function get_external_site_options( $active_only = false ){
        
     $sites = array();
     
     $sites['youtube'] = 'YouTube';
     
     $sites['vimeo'] = 'Vimeo';
     
     $sites['twitch'] = 'Twitch';
     
     if ( ! $active_only ):
       
        return $sites;
       
     endif;
     
      
    // get the search site value
    $search_site = get_option( 'tube_vc_find_videos_site' );
  
     
     $youtube_api_key = Tube_Video_Curator::$tube_youtube_videos->get_youtube_api_key();  
     
     $vimeo_api_key = Tube_Video_Curator::$tube_vimeo_videos->get_vimeo_api_key();  
     
     $twitch_api_key = Tube_Video_Curator::$tube_twitch_videos->get_twitch_api_key(); 
      
     if ( ! $youtube_api_key ):
       
       // Remove YouTube from search options
       unset( $sites['youtube'] );
       
       // make sure it's not using YouTube as search site
       if ( $search_site == 'youtube' ):
       
          delete_option( 'tube_vc_find_videos_site' );
        
       endif;       
     
        // check if there's a Vimeo key and update the site option if so
       if ( $vimeo_api_key ):
         
         update_option( 'tube_vc_find_videos_site', 'vimeo' );
         
       endif;
     
     endif;
     
     
     if ( ! $vimeo_api_key):       
       
       // Remove Vimeo from search options
       unset( $sites['vimeo'] );
       
       // make sure it's not using Vimeo as search site     
       if ( $search_site == 'vimeo' ):
         
        delete_option( 'tube_vc_find_videos_site' );
         
       endif;
     
        // check if there's a YouTube key and update the site option if so   
       if ( $youtube_api_key ):
         
         update_option( 'tube_vc_find_videos_site', 'youtube' );
         
       endif;
       
     endif;
     
     /*
     
     if ( ! $twitch_api_key):       
       
       // Remove Twitch from search options
       unset( $sites['twitch'] );
       
       // make sure it's not using Twitch as search site     
       if ( $search_site == 'twitch' ):
         
        delete_option( 'tube_vc_find_videos_site' );
         
       endif;
     
        // check if there's a YouTube key and update the site option if so   
       if ( $youtube_api_key ):
         
         update_option( 'tube_vc_find_videos_site', 'youtube' );
         
       endif;
       
     endif;
     */
     
     return $sites;
   
  }
  
  function get_external_site_label( $key ){
        
     $options = $this->get_external_site_options();
     
     if ( ! array_key_exists($key, $options) ):
       return NULL;
     endif;
     
     return $options[$key];
     
  }
  
  
  function get_feed_type_options($require_api = false){
        
     $options = array();
     
     $youtube_api_key = Tube_Video_Curator::$tube_youtube_videos->get_youtube_api_key();
      
     if ($youtube_api_key || ! $require_api):
       
       $options['youtube-channel'] = 'YouTube Channel';
       
       $options['youtube-playlist'] = 'YouTube Playlist';
       
     endif;
     
     $vimeo_api_key = Tube_Video_Curator::$tube_vimeo_videos->get_vimeo_api_key();
     
     if ($vimeo_api_key || ! $require_api):
       
       $options['vimeo-username'] = 'Vimeo User';
       
       $options['vimeo-channel'] = 'Vimeo Channel';
       
     endif;
     
     
     $twitch_api_key = Tube_Video_Curator::$tube_twitch_videos->get_twitch_api_key();
     
     if ($twitch_api_key || ! $require_api):
       
       $options['twitch-channel'] = 'Twitch Channel';
       
     endif;
     
     
     return $options;
     
  }
  
  
  
  function get_feed_type_label( $feed_type ){
        
     $options = $this->get_feed_type_options();
     
     if ( ! array_key_exists( $feed_type, $options) ):
       return NULL;
     endif;
     
     return $options[$feed_type];
     
  }
  
  
  function get_auto_import_status( $video_data ){        
    
    $auto_import_status = get_option( 'tube_vc_default_auto_import_status');
    
    $auto_import_status = apply_filters( 'tube_vc_filter_auto_import_status', $auto_import_status, $video_data );
     
    return $auto_import_status;
     
  }
    
  
  public static function has_api_keys( $required = NULL ){        
    
    
    $api_keys['youtube'] = Tube_Video_Curator::$tube_youtube_videos->get_youtube_api_key();
    
    $api_keys['vimeo'] = Tube_Video_Curator::$tube_vimeo_videos->get_vimeo_api_key();
    
    $api_keys['twitch'] = Tube_Video_Curator::$tube_twitch_videos->get_twitch_api_key();
    
    if ( $required ):
      
      foreach ( $required as $is_required ):
        
        // only need one match to be true
        if ( $api_keys[$is_required] ) return true;
        
      endforeach;
    
    else:      
      
      foreach ( $api_keys as $key => $key_value ):
        
        // only need one match to be true
        if ( $key_value ) return true;
        
      endforeach;
          
    endif;
    
    return false;
     
  }
      
  
  public static function no_api_keys_message(  ){        
    
    $message = sprintf(
      __('Sorry, you must have a <a href="%1$s">YouTube</a>, <a href="%2$s">Vimeo</a>, or <a href="%3$s">Twitch</a> API key to use this feature.', 'tube-video-curator'), 
      TUBE_VIDEO_CURATOR_API_KEYS_URL, 
      TUBE_VIDEO_CURATOR_VIMEO_API_KEYS_URL, 
      TUBE_VIDEO_CURATOR_TWITCH_API_KEYS_URL
    );
    
    echo wp_kses_post( '<p>' . $message . '</p>' );
    
    return;
     
  }
  
  public static function no_youtube_or_vimeo_api_keys_message(  ){        
    
    $message = sprintf(
    __('Sorry, you must have a <a href="%1$s">YouTube</a> or <a href="%2$s">Vimeo</a> API key to use this feature.', 'tube-video-curator'), 
      TUBE_VIDEO_CURATOR_API_KEYS_URL, 
      TUBE_VIDEO_CURATOR_VIMEO_API_KEYS_URL
    );
    
    echo wp_kses_post( '<p>' . $message . '</p>' );
    
    return;
     
  }
    
    
   
    
    
    
}