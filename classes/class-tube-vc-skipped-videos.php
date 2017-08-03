<?php
/**
 * Tube_VC_Skipped_Videos
 * 
 * Create a post type to manage skipped videos
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Skipped_Videos {
  
  public static $instance;
  
  public static $youtube;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Skipped_Videos();
      return self::$instance;
  }
  
  // Constructor  
  
  function __construct() {
    
    // Register the skipped videos post type
    add_action( 'init', array( $this, 'register_skipped_videos_post_type') );
      
    // If Yoast installed, remove the metabox for this post type
    add_action( 'add_meta_boxes', array( $this, 'remove_yoast_metabox_skipped_video') ,11 );   
      
    // Sample custom post type, used for testing
    // add_action( 'init', array( $this, 'register_test_post_type') );
    
  }
  
  
  // Register private post type for Skipped Videos 
  function register_skipped_videos_post_type() {
        
    register_post_type( 'tube_skipped_video',
      array(
        'labels' => array(
          'name' => __( 'Skipped Videos' ),
          'singular_name' => __( 'Skipped Video' )
        ),
        'show_in_menu'       => TUBE_VIDEO_CURATOR_SLUG,
         'show_in_nav_menus'  => false,
        'public' => false,
        'show_ui' => true,
        'has_archive' => false,
        'supports' => array( 'title', 'editor', 'custom-fields' )
      )
    );
    
    
  }
  
 
 
  // If Yoast installed, remove the metabox here
  function remove_yoast_metabox_skipped_video(){
      remove_meta_box('wpseo_meta', 'tube_skipped_video', 'normal');
  }
  
  
  
  // Register private post type for testing only
  function register_test_post_type() {
    
    register_post_type( 'tube_test_cpt',
      array(
        'labels' => array(
          'name' => __( 'Sample Custom Posts' ),
          'singular_name' => __( 'Sample Custom Post' )
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => array( 'title', 'author', 'editor', 'custom-fields', 'thumbnail' ),
        //'taxonomies' => array('category')
      )
    );
    
    register_taxonomy(
      'sampletax',
      'tube_test_cpt',
      array(
        'label' => __( 'Sample Taxonomy' ),
        'rewrite' => array( 'slug' => 'sampletax' ),
        'hierarchical' => true,
      )
    );
      
    
  }


  


    
}
