<?php
/**
 * Tube_VC_Find_Sources_YouTube_Playlists
 * 
 * Used by Tube_Find_Sources to search for YouTube Playlists
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
  
 
class Tube_VC_Find_Sources_YouTube_Playlists {
  
  function __construct() {
    
  }
    
  
  function find_sources( $query, $args = NULL ){   
    
    // get the normalized search arguments for YouTube
    $args = Tube_Video_Curator::$tube_youtube_videos -> get_source_search_args_from_querystring();
    
    // add the query to the arguments
    $args['q'] = $query;    
    
    ////print_r($args);
    
    // Try to get any playlist that match the query   
    $playlist_response = $this -> get_youtube_playlists_from_query( $args );
    
    ////print_r($playlist_response);
    
    // check if there are playlist search results
    if ( !$playlist_response || count($playlist_response["items"]) == 0 ):
      
      //echo 'Sorry, no playlists matching that search could not be found.';
      return NULL;
      
    endif;
    
    $sources_data = $playlist_response["items"];
    
    
    
    // normalize the source data
    foreach ($sources_data as $source):
              
      ////print_r($source);
      ////print_r('----------');
      //continue;
      
      $source_data['title']= $source['snippet']['title'];
      
      $source_data['external_guid']= $source['id'];
      
      $source_data['external_url']= 'https://www.youtube.com/playlist?list=' . $source['id'];
      
      $source_data['description']= $source['snippet']['description'];
      
      // TODO: Support for 'standard' (larger) thumbnail
      
      $source_data['image_url']= $source['snippet']['thumbnails']['high']['url'];
      
      $source_data['thumbnail_image_url']= $source['snippet']['thumbnails']['medium']['url'];
      
      //$source_data['subscribers']= $source['statistics']['subscriberCount'];
      
      $source_data['videos']= $source['contentDetails']['itemCount'];
      
      $sources['sources'][] = $source_data;
          
    endforeach;
     
     
    $sources['total_results'] = $playlist_response['pageInfo']['totalResults'];
     
     
    $sources['feed_type'] = 'youtube-playlist';
     
    $sources['per_page'] = $args['maxResults'];
     
    $sources['site'] = 'youtube';
     
    $sources['next_page_token'] = $playlist_response['nextPageToken'];
      
    $sources['prev_page_token'] = $playlist_response['prevPageToken'];
        
    // return the sources
    return $sources;
    
  }



  function get_youtube_playlists_from_query( $args ){    
    
   
    // get the youtube API client
    $youtube = Tube_Video_Curator::$tube_youtube_videos -> get_youtube_client();   
     
    if ( ! $youtube ):      
      
      _e( 'Error retrieving YouTube API client.', 'tube-video-curator' );
      return;
      
    endif;    
     
    //echo date(DATE_ATOM,  mktime(23,59,59));
    
    $defaults =   array(
        'q' => $args['q'],
        'type' => 'playlist',
        'maxResults' => get_option('posts_per_page'),
        'pageToken' => '',
    );
    
    $search_args = wp_parse_args( $args, $defaults );
    
    $sources_data = array();    
      
    
    $transient_key = 'tube-yt-plylst-frm-qry' . md5( serialize($args) );
        
    $playlist_response = get_transient( $transient_key );  
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $playlist_response ) || $flush_transient ):  
        
      // attempt to get playlists for the query
      try {
        
         $playlist_response = $youtube->search->listSearch('id', $search_args );
        
       
      } catch (Google_Service_Exception $e) {
        echo 'Google_Service_Exception ' . $e->getMessage();;
        return;
      } catch (Google_Exception $e) {
        echo 'Google_Exception ' . $e->getMessage();;
        return;
      }
      
      
      $playlist_ids = array();
      
      // check if there are playlist search results
      if ( isset($playlist_response) && count($playlist_response["items"]) != 0 ):
  
          // loop through resulting playlists
          foreach ($playlist_response['items'] as $playlist_data):
      
             // add this playlist's ID to the list of playlist ids
             $playlist_ids[] = $playlist_data['id']['playlistId'];
          
          endforeach;
          
      endif;
        
      
      // see if there are playlist IDs
      if ( count($playlist_ids) != 0 ):
  
        // implode playlist IDs into a string
        $playlist_ids = implode(',', $playlist_ids);
      
        // get playlist details
        try {
          
           $playlist_detail_response = $youtube->playlists->listPlaylists('snippet,contentDetails', array(
            'id' => $playlist_ids
          ));
        
        
        } catch (Google_Service_Exception $e) {
          echo 'Google_Service_Exception ' . $e->getMessage();;
          return;
        } catch (Google_Exception $e) {
          echo 'Google_Exception ' . $e->getMessage();;
          return;
        }
          
      endif;
           
      
      $playlist_response['items'] = $playlist_detail_response['items'];
    
            
      // Save the API response so we don't have to call again for half an hour
      set_transient( $transient_key, $playlist_response, HOUR_IN_SECONDS / 2 );
    
    endif;
        
    return $playlist_response;    
    
    
  }

}


    