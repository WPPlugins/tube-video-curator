<?php
/**
 * Tube_VC_Import_Cron
 * 
 * ADD STUFF HERE
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Import_Cron {
  
  public static $instance;
  
  public static function init() {
    
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Import_Cron();
      return self::$instance;
  }
  
  // Constructor  
  
  function __construct() {
    
    // THIS NEXT LINE FORCES IT TO RUN EVERY LOAD, FOR DEBUGGING
    if ( array_key_exists( 'clear_tube_vc_import_cron', $_GET ) ):
      wp_clear_scheduled_hook('tube_vc_autosync');
      return;
    endif;
    
    if ( ! wp_next_scheduled( 'tube_vc_autosync' ) ) {
      wp_schedule_event( time(), 'hourly', 'tube_vc_autosync');
    }
    
    add_action( 'tube_vc_autosync', array( $this, 'do_autosync') );

  } 
    

  //Autosync function that gets attached to CRON
  function do_autosync(){
    
    // get any Sources with AutoSync
    $autosycn_sources = $this -> get_autosync_sources();  
    
    // if no autosync sources, we're done here
    if ( ! is_array($autosycn_sources) || count( $autosycn_sources ) == 0 ):
      
      return;
      
    endif;       
      
    // get reference to $tube_video_curator
    global $tube_video_curator;
    
    // loop through the sources
    foreach ( $autosycn_sources as $source_post_id ):
      
      // get the source manager
      $tube_source_manager = $tube_video_curator::$tube_source_manager;
      
      // set the current source
      $tube_source_manager -> set_current_source_by_post_id( $source_post_id );
      
      // get new importable videos for the source
      $new_videos_for_source = $tube_source_manager::$current_source -> get_new_videos_for_import( );
      
      //ob_start();
      //$output = ob_get_clean();
      
      //wp_mail( EMAILADDRESSHERE, 'tubeSourceManager 23', $output);
      
      //ob_start();
      
      //print_r( $tube_source_manager::$current_source );
          
      //$output = ob_get_clean();
      
     // wp_mail( EMAILADDRESSHERE, ' $tube_source_manager::$current_source 24', 'asdf ' . $output);
      
      //ob_start();
      
      //print_r($external_video_ids);
          
      //$output = ob_get_clean();
      
      //wp_mail( EMAILADDRESSHERE, 'external_video_ids 25', 'asdf ' . $output);
      
      // make sure new videos returned an array
      if ( ! is_array( $new_videos_for_source ) ):
        continue;
      endif;  
      
      // make sure there's an items index
      if ( ! array_key_exists('items', $new_videos_for_source)  ):
        continue;
      endif;  
      
      // make sure new videos has items
      if ( count( $new_videos_for_source['items' ] ) == 0 ):
        continue;
      endif;  
    
      // get existing video ideas
      $external_video_ids = $tube_video_curator -> get_external_video_ids ( $tube_source_manager::$current_source -> site );
      
    
      
        //ob_start();
        
        //print_r($external_video_ids);
            
        //$output = ob_get_clean();
        
        //wp_mail( EMAILADDRESSHERE, '$external_video_ids 001', $output);
        
  
      // loop through the videos
      foreach ( $new_videos_for_source['items' ] as $video_data ):
       
        // make sure it's not already imported
        if ( is_array($external_video_ids) && array_key_exists($video_data['id'], $external_video_ids) ):
          
          //wp_mail( EMAILADDRESSHERE, 'skip autosync 002' . $video_data['title'], 'asdf ' . $video_data['id']);
          continue;
          
        endif;
        
        // get the setting for auto import status and add to video data
        $video_data['status'] = $tube_video_curator -> get_auto_import_status( $video_data );
        $video_data['auto_imported'] = 1;
        
        // insert the new post
        $inserted_post = $tube_video_curator -> create_tube_video_post( $video_data );
        
      
        //ob_start();
        
        //print_r($video_data);
            
        //$output = ob_get_clean();
        
        //wp_mail( EMAILADDRESSHERE, $video_data['title'], $output);
     
        //$this -> auto_import_video( $video_data );
        
      endforeach;  
    
    endforeach;
    
    
  }


  // get post objects for all Tube Sources with autosync
  function get_autosync_sources() {
        
    $args = array( 
      'posts_per_page' => -1,
      'post_type' => 'tube_source',
      'fields' => 'ids',
      'meta_query' => array(
        array(
          'key'     => 'tube_source_autosync',
          'compare' => '=',
          'value' => 1,
        ),
      ),
    );
    
    $autosync_sources_query = new WP_Query($args);    
    
   /*     
    ob_start();
    
    print_r($args);
    
    print_r($posts_query -> posts);
    
    $output = ob_get_clean();
    * 
    //wp_mail( EMAILADDRESSHERE, 'get_autosync_sources 8', $output);
    */
    
    if ( ! $autosync_sources_query->have_posts() ):
      return false;
    endif;   
    
    
    return $autosync_sources_query->posts;
        
    
  }
  
  
  // clear the schedule hook for autosync
  function clear_scheduled_autosync_hook() {
    wp_clear_scheduled_hook('tube_vc_autosync');
  }

    
}
