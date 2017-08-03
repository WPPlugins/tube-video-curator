<?php
/**
 * Tube_VC_Source_Vimeo_User
 * 
 * Find Videos from a Vimeo User
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */

class Tube_VC_Source_Vimeo_User Extends Tube_VC_Source {

  
  function __construct( $id ) {
    
    parent::__construct( $id ); 
    
  }
  
  
  
  function get_source_videos(  $search_args ){
        
    // Get the guid for this source
    $vimeo_user_guid = strtolower($this -> guid);    
    
    // Get the videos for this source
    $user_videos = Tube_Video_Curator::$tube_vimeo_videos->get_vimeo_user_videos_via_api( $vimeo_user_guid, $search_args );
    
    // Return the source's videos
    return $user_videos;
    

    
  }
  
  
  // TODO: Add "since last import" capability for Vimeo
  function get_source_videos_since_last_import(  $last_import_date ) {
    /*
    $search_args['publishedBefore'] = NULL;
    $search_args['publishedAfter'] = $last_import_date;
    $search_args['maxResults'] = 50;
    $search_args['get_all'] = true;
    
    $channel_youtube_id = $this -> guid;
    
    $channel_videos = Tube_Video_Curator::$tube_youtube_videos->get_youtube_channel_videos_via_api(
    
      $channel_youtube_id, $search_args
      //array( 'publishedBefore' => '2015-02-12T20:16:15.000Z' )
      
    );
    */
    //print_r($channel_videos['items']);
    return $last_import_date;
    
  }

}

