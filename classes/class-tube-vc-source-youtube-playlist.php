<?php
/**
 * Tube_VC_Source_YouTube_Playlist
 * 
 * Find Videos from a YouTube Playlist
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */

class Tube_VC_Source_YouTube_Playlist Extends Tube_VC_Source {
  
  function __construct( $id ) {
    
    parent::__construct( $id ); 
    
  }


  function get_source_videos(  $search_args ){       
        
    // Get the guid for this source
    $youtube_playlist_id = $this -> guid;    
    
    // Get the vidoes for this source
    $playlist_videos = Tube_Video_Curator::$tube_youtube_videos->get_youtube_playlist_videos_via_api( $youtube_playlist_id, $search_args );
        
    // Return the videos
    return $playlist_videos;
    
  }
  
  
}