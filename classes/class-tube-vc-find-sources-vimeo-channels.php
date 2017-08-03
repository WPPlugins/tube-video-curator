<?php
/**
 * Tube_VC_Find_Sources_Vimeo_Channels
 * 
 * Used by Tube_VC_Find_Sources to search for Vimeo Channels
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */


class Tube_VC_Find_Sources_Vimeo_Channels {
  
  function __construct() {
    
  }
    
  
  function find_sources( $query ){   
    
    // get the normalized search arguments for Vimeo
    $args = Tube_Video_Curator::$tube_vimeo_videos -> get_source_search_args_from_querystring();
    
    // add the query to the arguments
    $args['query'] = $query;    
    
     // create an array to store data about sources (i.e. Vimeo Users)
    $sources_data = array();
        
    // Try to get any users that match the query   
    $vimeo_response = $this -> get_vimeo_channels_from_query( $args );
        
    // doh, no sources found
    if ( ! $vimeo_response || count($vimeo_response['body']['data']) == 0 ):

      //echo 'Sorry, no channels matching that search could not be found.';
      return NULL;
      
    endif;
     
    $sources_data = $vimeo_response['body']['data'];
    
    // normalize the source data
     foreach ($sources_data as $source):
        
       $user_uri_slug = $source['uri'];
       $user_uri_slug = explode('/', $user_uri_slug);
       
       $source_data['title']= $source['name'];
       $source_data['external_guid']= $user_uri_slug[2];
       $source_data['external_url']= $source['link'];
       $source_data['description']= $source['description'];
       
       //print_r($source['pictures']);
       
    
       if ( $source['pictures'] && array_key_exists('sizes', $source['pictures']) ):
          $largest = count( $source['pictures']['sizes']) - 1;
          $source_data['image_url']= remove_query_arg('r', $source['pictures']['sizes'][$largest]['link']);
          $source_data['thumbnail_image_url']= remove_query_arg('r', $source['pictures']['sizes'][2]['link']);
       else:
          // no pictures for this source
          $source_data['image_url']= NULL;
          $source_data['thumbnail_image_url']= NULL;
       endif;
       
       $source_data['subscribers']= $source['metadata']['connections']['users']['total'];
       $source_data['videos']= $source['metadata']['connections']['videos']['total'];
       
       
       $sources['sources'][] = $source_data;
       
     endforeach;     
     
     
     $sources['feed_type'] = 'vimeo-channel';
     
     $sources['per_page'] = $args['per_page'];
     
     $sources['site'] = 'vimeo';     
     
     $sources['total_results'] = $vimeo_response['body']['total'];     
     
     // get any prev/next arguments
      $prev_next = Tube_Video_Curator::$tube_vimeo_videos -> get_vimeo_prev_next_vals( $vimeo_response );
               
      $sources['prev_page_token'] = $prev_next[0];
      
      $sources['next_page_token'] = $prev_next[1];
   
        
    // return the sources
    return $sources;
    
  }


  function get_vimeo_channels_from_query( $args ){    
    
    // get the vimeo API client
    $vimeo = Tube_Video_Curator::$tube_vimeo_videos -> get_vimeo_client();   
    
    if ( ! $vimeo ):
      _e( 'Error retrieving Vimeo API client.', 'tube-video-curator' );
      return;
    endif;
    
    $transient_key = 'tube-vim-fnd-chnl-' . md5( serialize($args) );
    
    $vimeo_channels = get_transient( $transient_key ); 
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $vimeo_channels ) || $flush_transient ):  
      
      // Get the response from the API
      $vimeo_channels =  $vimeo->request('/channels/', $args, 'GET');
  
      // Check for errors    
      if ( ! array_key_exists('body', $vimeo_channels) ):
        _e( 'Error retrieving Vimeo results. ', 'tube-video-curator' );
        return;
      endif;
      
      if (  array_key_exists('error', $vimeo_channels['body']) ):
        echo esc_html( $vimeo_channels['body']['error'] );
        return;
      endif;
      
      
      // Save the API response so we don't have to call again for half an hour
      set_transient( $transient_key, $vimeo_channels, HOUR_IN_SECONDS / 2 );
            
    endif;
      
    return $vimeo_channels;    
    
  }
  

}
