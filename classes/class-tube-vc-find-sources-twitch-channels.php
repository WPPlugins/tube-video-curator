<?php
/**
 * Tube_VC_Find_Sources_Twitch_Channels
 * 
 * Used by Tube_VC_Find_Sources to search for Twitch Channels
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.1.0
 */


class Tube_VC_Find_Sources_Twitch_Channels {
  
  function __construct() {
    
  }
    
  
  function find_sources( $query ){   
    
    // get the normalized search arguments for Twitch
    $args = Tube_Video_Curator::$tube_twitch_videos -> get_source_search_args_from_querystring();
    
    // add the query to the arguments
    $args['query'] = $query;    
    
     // create an array to store data about sources (i.e. Twitch Users)
    $sources_data = array();
        
    // Try to get any users that match the query   
    $twitch_response = $this -> get_twitch_channels_from_query( $args );
        
    // doh, no sources found
    if ( ! $twitch_response || count($twitch_response['channels']) == 0 ):

      //echo 'Sorry, no channels matching that search could not be found.';
      return NULL;
      
    endif;
     
    $sources_data = $twitch_response['channels'];
    
    // normalize the source data
     foreach ($sources_data as $source):
       
       $source_data['title'] = $source['display_name'];
       $source_data['external_guid'] = $source['name'];
       $source_data['external_url'] = $source['url'];
       $source_data['description'] = $source['game'];
      
       $source_data['image_url'] = $source['logo'];
       $source_data['thumbnail_image_url'] = $source['logo'];
       
       $source_data['subscribers']= $source['followers'];
       $source_data['videos']= NULL;
       
       
       $sources['sources'][] = $source_data;
       
     endforeach;     
     
       
     
     $sources['feed_type'] = 'twitch-channel';
     
     $sources['per_page'] = $args['limit'];
     
     $sources['site'] = 'twitch';     
     
     $sources['total_results'] = $twitch_response['_total'];     
     
     // get any prev/next arguments
      $prev_next = Tube_Video_Curator::$tube_twitch_videos -> get_twitch_prev_next_vals( $twitch_response );
               
      $sources['prev_page_token'] = $prev_next[0];
      
      $sources['next_page_token'] = $prev_next[1];
   
        
    // return the sources
    return $sources;
    
  }


  function get_twitch_channels_from_query( $args ){    
    
    // get the twitch API client
    $twitch = Tube_Video_Curator::$tube_twitch_videos -> get_twitch_client();   
    
    if ( ! $twitch ):
      _e( 'Error retrieving Twitch API client.', 'tube-video-curator' );
      return;
    endif;
    
    $transient_key = 'tube-twtch-fnd-chnl-' . md5( serialize($args) );
    
    $twitch_channels = get_transient( $transient_key ); 
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $twitch_channels ) || $flush_transient ):  
      
      // Get the response from the API
      $twitch_channels =  $twitch -> search_channels( $args );
      
      // Check for errors    
      if ( ! array_key_exists('channels', $twitch_channels) ):
        
        _e( 'Error retrieving Twitch results. ', 'tube-video-curator' );
        
        return;
        
      endif;
      
      // TODO: Figure out what an error looks like
      //if (  array_key_exists('error', $twitch_channels ) ):
      
      //  echo esc_html( $twitch_channels['error'] );
      
      //  return;
      
     // endif;      
      
      // Save the API response so we don't have to call again for half an hour
      set_transient( $transient_key, $twitch_channels, HOUR_IN_SECONDS / 2 );
            
    endif;
      
    return $twitch_channels;    
    
  }
  

}
