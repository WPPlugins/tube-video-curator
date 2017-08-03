<?php
/**
 * Tube_VC_Source_Twitch_Channel
 * 
 * Find Videos from a Twitch Channel 
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.1.0
 */

class Tube_VC_Source_Twitch_Channel Extends Tube_VC_Source {

  
  function __construct( $id ) {
    
    parent::__construct( $id ); 
    
  }
    
  
  function get_source_videos(  $search_args ){
        
    // Get the guid for this source
    $twitch_channel_guid = strtolower($this -> guid);    
    
    // Get the vidoes for this source
    $channel_videos = Tube_Video_Curator::$tube_twitch_videos->get_twitch_channel_videos_via_api( $twitch_channel_guid, $search_args );
    
    // Return the videos
    return $channel_videos;  
    
  }
  
  
  // TODO: Add "since last import" capability for Twitch
  function get_source_videos_since_last_import(  $last_import_date ) {
    
    return $last_import_date;
    
  }

}

