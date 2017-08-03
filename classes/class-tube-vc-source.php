<?php
/**
 * Tube_VC_Source
 * 
 * Shared Source functions for setting source-level properties
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 


class Tube_VC_Source {
  
  public static $instance;
  
  public $ID;
  public $autosync;
  public $guid;
  public $site;
  public $feed_type;
  public $title;
  public $image;
  public $external_url;
  
  public static function init() {
      if ( is_null( $this -> instance ) )
          $this -> instance = new Tube_VC_Source();
      return $this -> instance;
  }
  
  function __construct( $id = NULL ) {  
    
    if ( $id ):
      $post_id = $id;
    elseif ( is_array($_POST) && array_key_exists('post_ID', $_POST) ):
      $post_id = $_POST['post_ID'];
    elseif ( is_array($_GET) && array_key_exists('post', $_GET) ):
      $post_id = $_GET['post'];
    endif;
    
    if ( isset($post_id) ):
      $this -> set_source_properties( $post_id );
    endif;
    
  }


  function set_source_properties( $post_id ){
    
    //print_r($post_id);
    
    // Title (channel name)
    $this -> ID = $post_id;
    
    // Title (channel name)
    $this -> title = get_the_title( $post_id );
    
    $post_meta = get_post_meta( $post_id );
    
    // Site
    $this -> site = $post_meta['tube_source_site'][0];
    
    // Feed Type
    if ( array_key_exists('tube_feed_type', $post_meta) ):
      $this -> feed_type = $post_meta['tube_feed_type'][0];
    endif;
    
    if ( array_key_exists('tube_source_external_guid', $post_meta) ):
      
      // Channel GUID
      $this -> guid =  $post_meta['tube_source_external_guid'][0];
    
    endif;
    
    //print_r($post_meta);
    
    // Image
    $this -> image = get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'img-responsive' ) );
    
    
    if ( array_key_exists('tube_source_external_url', $post_meta) ):
      
      // Channel GUID
      $this -> external_url =  $post_meta['tube_source_external_url'][0];
    
    endif;
    
    // Channel Autosync Setting
    $this -> autosync = array_key_exists( 'tube_source_autosync', $post_meta );
    
  }
  
  
 
  // Attempt to get new videos for import based on last import date
  // This is part of the "Auto Sync" capability
  
  function get_new_videos_for_import() {
    
   // wp_mail( EMAILADDRESSHERE, 'importnew', 'importnew');
    
    // Set timezone to LA for YouTube HQ
    //$timezone = 'America/Los_Angeles';      
    $timezone = 'UTC';     
    
    // THIS IS JUST FOR TESTING
    //$date_old = new DateTime( "30 days ago", new DateTimeZone($timezone) );
    //update_post_meta( $this -> ID, 'tube_source_last_import_date', $date_old);    
    
    
    // get the current time
    $current_date_obj = new DateTime( "now", new DateTimeZone($timezone) );    
    
    // Switch to ATOM format per YouTube specs
    //$current_date = $current_date_obj->format($current_date_obj::RFC3339);
    
    $current_date = $current_date_obj->format('Y-m-d\TH:i:s\Z'); // force solid hour, no mins secs
     
    //print_r( $current_date_obj );
    //print_r( $current_date );
     
    
    // get last import date    
    $last_import_date_obj = get_post_meta( $this -> ID, 'tube_source_last_import_date', true);
    
    
    // no previous imports, so set last import attempt to current and return
    if ( ! $last_import_date_obj ):  
        
      // update the last import date to current date
      update_post_meta( $this -> ID, 'tube_source_last_import_date', $current_date_obj);
    
      return;
      
    endif;
    
    // Switch to ATOM format per YouTube specs
    //$last_import_date = $last_import_date_obj->format($last_import_date_obj::RFC3339); 
    $last_import_date = $last_import_date_obj->format('Y-m-d\TH:i:s\Z'); 
    
    // calculate the hours since last import
    $date_diff = $current_date_obj->diff($last_import_date_obj);

    $hours_diff = $date_diff->h;
    
    $hours_diff = $hours_diff + ($date_diff->days * 24);    
    
    // less than an hour, so do nothing
    if ( $hours_diff < 1 ):
      //print_r(' $last_import_date == $current_date ');
      return;
    endif;
            
        
    //$last_import_date = str_replace('+00:00', 'Z', $last_import_date);
      
    // get the new videos
    $new_videos = $this -> get_source_videos_since_last_import( $last_import_date );
    
    //print_r( $new_videos );
    //print_r( $new_videos );
        
    // update the last import date to current date
    update_post_meta( $this -> ID, 'tube_source_last_import_date', $current_date_obj);
    
    return $new_videos;
          
  }

  

}