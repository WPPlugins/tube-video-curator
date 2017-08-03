<?php
/**
 * Tube_VC_Source_YouTube_Channel
 * 
 * Find Videos from a YouTube Channel / User
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Source_YouTube_Channel Extends Tube_VC_Source {
  
  function __construct( $id ) {
    
    parent::__construct( $id ); 
    
  }



  function get_source_videos(  $search_args ){        
        
    // Get the guid for this source
    $youtube_channel_id = $this -> guid;
    
    // Get the vidoes for this source
    $channel_videos = Tube_Video_Curator::$tube_youtube_videos->get_youtube_channel_videos_via_api( $youtube_channel_id, $search_args );
    
    // Return the videos
    return $channel_videos;
    
  }

  
  
  function get_source_videos_since_last_import(  $last_import_date ) {
 
    $search_args['publishedBefore'] = NULL;
    $search_args['publishedAfter'] = $last_import_date;
    $search_args['maxResults'] = 50;
    $search_args['get_all'] = true;
    
    $youtube_channel_id = $this -> guid;
    
    $channel_videos = Tube_Video_Curator::$tube_youtube_videos->get_youtube_channel_videos_via_api(
    
      $youtube_channel_id, $search_args
      //array( 'publishedBefore' => '2015-02-12T20:16:15.000Z' )
      
    );
    
    //print_r($channel_videos['items']);
    return $channel_videos;
    
  }
  
  
  
}