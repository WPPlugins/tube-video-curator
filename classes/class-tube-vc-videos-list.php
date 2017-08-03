<?php
/**
 * Tube_VC_Videos_List
 * 
 * Creates a list of videos within Video Search and Your Channels / Playlists
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
 

class Tube_VC_Videos_List {
  
  public static $instance;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Videos_List();
      return self::$instance;
  }
      
  
  // Constructor    
  function __construct() {    
       
      //add_filter('manage_posts_columns' , array( $this, 'add_video_url_admin_column' ) );
       
      //add_filter('manage_posts_custom_column' , array( $this, 'manage_video_url_admin_column' ), 10, 2 );
      
  }

 
  // Add a column for the Video oEmbed URL in admin (NOT USED)
  function add_video_url_admin_column($columns) {
    return array_merge($columns,
        array(
          'tube_video_oembed_url' => __('.TUBE Video', 'tube-video-curator'),
        )
     );
  }
  
  
   
  // Populate the Video oEmbed URL column in admin (NOT USED)
  function manage_video_url_admin_column( $column, $post_id ) {
    
    switch ( $column ) {
   
      case 'tube_video_oembed_url' :
        
          $tube_video_oembed_url = get_post_meta($post_id, 'tube_video_oembed_url', true);
        
          $tube_video_creator_name = get_post_meta($post_id, 'tube_video_creator_name', true);
          
          if ( $tube_video_oembed_url ):
            
            echo '<a href="' . esc_url($tube_video_oembed_url) . '">' . esc_url($tube_video_oembed_url) . '</a><br />';
            
            echo esc_html($tube_video_creator_name);
            
          endif;
          
          break;
    }
      
  }
  
   
   
   
   function show_videos_list( $videos, $site ){ 
      
    global $tube_video_curator;
      
    $existing_ids = $tube_video_curator -> get_external_video_ids( $site ) ;
    ?>
        
    <div class="tube-admin-list-videos">
      <?php 
      
      foreach ( $videos as $video ): 
        
         $existing_video_data = array();
         $existing_video_data['status'] = 'new';
          
         if ( array_key_exists($video['id'], $existing_ids) ): 
              
              $existing_video_data = $existing_ids[ $video['id'] ];
              
          endif;
          
          $video_date_raw = $video['date'];
          
          $date_format = get_option('date_format');
        
          $time_format = get_option('time_format');          
          
          // uses custom function in functions-formatting
          $wordpress_timezone = new DateTimeZone( $this -> get_timezone_string() );
    
          // create a $dt object with the UTC timezone
          $dt = new DateTime($video_date_raw, new DateTimeZone('UTC'));
          
          $dt->setTimeZone( $wordpress_timezone );
          
          // format the datetime
          $video_date_formatted = $dt->format( $date_format );

          $video_time_formatted = $dt->format( $time_format );
    
          $tags = NULL;
          if ( array_key_exists( 'tags', $video ) && $video['tags'] ):
            
            $tags = implode(',', $video['tags']);
            
          endif;
    
         //$video_date_formatted =  date( get_option( 'date_format' ), strtotime($video['date']) );
         
         $video_description_truncated =  wp_trim_words( $video['content'], 40 );
             
              switch ($existing_video_data['status']) {
                case 'skipped':
                  $video_label = __( 'SKIPPED', 'tube-video-curator' );
                  $video_label_class = 'text-highlight';
                  break;
                  
                case 'pending':
                  $video_label = __( 'PENDING', 'tube-video-curator' );
                  $video_label_class = 'text-highlight';
                  break;
                  
                case 'draft':
                  $video_label = __( 'DRAFT', 'tube-video-curator' );
                  $video_label_class = 'text-highlight';
                  break;
                  
                case 'publish':
                  $video_label = __( 'PUBLISHED', 'tube-video-curator' );
                  $video_label_class = 'text-notification';
                  break;
                
                default:
                  
                  $video_label = NULL;                  
                  break;
              }
              
        ?>
        <div class="result video-status-<?php echo sanitize_html_class($existing_video_data['status']); ?>">
          <div class="tube-video-thumbnail">
            
            <img src="<?php echo esc_url( $video['thumb_image_url'] ); ?>" class="thumbnail" alt="" width="150" />
            
          </div>
          <div class="tube-video-summary">
              
            <h3 class="video-title">  
            
              <?php if (  $video_label ): ?>
                <strong class="wp-ui-<?php echo sanitize_html_class( $video_label_class ); ?>">[<?php echo esc_html( $video_label ); ?>]</strong>                
              <?php endif; ?>
                <a href="<?php echo esc_url( $video['media_url'] ); ?>" target="_blank"><?php echo esc_html( $video['title'] ); ?></a>
            </h3>
              
            <p class="video-meta description">
              <strong><?php echo esc_html( $video['creator_name'] ); ?></strong> | 
              <?php echo esc_html( $video_date_formatted ); ?> | <?php echo esc_html( $video_time_formatted ); ?>
            </p>
            <p class="video-description">
              <?php echo esc_html( $video_description_truncated ); ?>
            </p>
          </div>
          
          
          
            
          <div class="tube-video-buttons tube-video-skipped-buttons">
            <a class="button btn-restore" data-postid="<?php if ( $existing_video_data['status'] == 'skipped' ) echo $existing_ids[ $video['id']]['id']; ?>" href="#">
              <?php _e( 'Restore', 'tube-video-curator' ); ?>
            </a>                  
          </div>
            
              
          <div class="tube-video-buttons tube-video-published-buttons">
                  
            <a  class="btn-view button" href="<?php if ( ( $existing_video_data['status'] == 'publish' ) || ( $existing_video_data['status'] == 'draft' ) ) echo $existing_video_data['permalink']; ?>">
              <?php _e( 'View', 'tube-video-curator' ); ?>                    
            </a>  
                            
            <br /> 
                           
            <a  class="btn-edit" href="<?php if ( ( $existing_video_data['status'] == 'publish' ) || ( $existing_video_data['status'] == 'draft' ) ) echo $existing_video_data['edit']; ?>">
              <?php _e( 'Edit', 'tube-video-curator' ); ?>
            </a>
                 
          </div>
              
                
          <div class="tube-video-buttons tube-video-pending-buttons">
                
            <a  class="btn-view button" href="<?php if (  $existing_video_data['status'] == 'pending' ) echo $existing_video_data['edit']; ?>">
              <?php _e( 'Review/Edit', 'tube-video-curator' ); ?>
            </a>
            
            <br />
          
            <a  class="btn-edit" href="<?php if (  $existing_video_data['status'] == 'pending' ) echo $existing_video_data['permalink']; ?>">
              <?php _e( 'Preview', 'tube-video-curator' ); ?>
            </a>
                 
          </div>
          
              
             <div class="tube-video-buttons tube-video-new-buttons">
               
                <a data-site="<?php echo esc_attr($site); ?>"  data-title="<?php echo esc_attr($video['title']); ?>" data-channel-id="<?php echo esc_attr($video['channel_id']); ?>"  data-id="<?php echo $video['id']; ?>" data-src="<?php echo esc_url($video['media_url']); ?>" data-content="<?php echo esc_attr($video['content']); ?>" data-date="<?php echo $video['date']; ?>" data-embed-url="<?php echo esc_url($video['embed_url']); ?>" data-media-url="<?php echo esc_url($video['media_url']); ?>" data-image-url="<?php echo esc_url($video['image_url']); ?>" data-creator-name="<?php echo esc_attr($video['creator_name']); ?>" data-tags="<?php echo esc_attr($tags); ?>"  data-status="publish" class="btn-import btn-publish button button-primary" href="#"><?php _e( 'Publish', 'tube-video-curator' ); ?></a>
                
                <br />
                
               <a data-site= "<?php echo esc_attr($site); ?>" data-title= "<?php echo esc_attr($video['title']); ?>" data-channel-id="<?php echo esc_attr($video['channel_id']); ?>"  data-id="<?php echo $video['id']; ?>" data-src="<?php echo esc_url($video['media_url']); ?>" data-content="<?php echo esc_attr($video['content']); ?>" data-date="<?php echo $video['date']; ?>" data-embed-url="<?php echo $video['embed_url']; ?>" data-media-url="<?php echo esc_url($video['media_url']); ?>" data-image-url="<?php echo esc_url($video['image_url']); ?>" data-creator-name="<?php echo esc_attr($video['creator_name']); ?>" data-tags="<?php echo esc_attr($tags); ?>" data-status="pending" class="btn-import btn-pending" href="#"><?php _e( 'Pending', 'tube-video-curator' ); ?></a>
                
                <br />
                
               <a data-site= "<?php echo esc_attr($site); ?>" data-title= "<?php echo esc_attr($video['title']); ?>" data-channel-id="<?php echo esc_attr($video['channel_id']); ?>"  data-id="<?php echo $video['id']; ?>" data-src="<?php echo $video['media_url']; ?>" data-content="<?php echo esc_attr($video['content']); ?>" data-date="<?php echo $video['date']; ?>" data-embed-url="<?php echo esc_url($video['embed_url']); ?>" data-media-url="<?php echo esc_url($video['media_url']); ?>" data-image-url="<?php echo $video['image_url']; ?>" data-creator-name="<?php echo esc_attr($video['creator_name']); ?>" class="btn-skip" href="#"><?php _e( 'Skip', 'tube-video-curator' ); ?></a>
               
              </div>
          
        </div> <!-- .result --> 
      <?php endforeach; ?>        
    </div>

  <?php
  }

  

  /**
   * https://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
   *
   * Returns the timezone string for a site, even if it's set to a UTC offset
   *
   * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
   *
   * @return string valid PHP timezone string
   */
  
  function get_timezone_string()
  {
  
    // if site timezone string exists, return it  
    if ($timezone = get_option('timezone_string')) return $timezone;
  
    // get UTC offset, if it isn't set then return UTC  
    if (0 === ($utc_offset = get_option('gmt_offset', 0))) return 'UTC';
  
    // adjust UTC offset from hours to seconds  
    $utc_offset*= 3600;
  
    // attempt to guess the timezone string from the UTC offset  
    if ($timezone = timezone_name_from_abbr('', $utc_offset, 0)) {
      return $timezone;
    }
  
    // last try, guess timezone string manually  
    $is_dst = date('I');
    foreach(timezone_abbreviations_list() as $abbr) {
      foreach($abbr as $city) {
        if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset) return $city['timezone_id'];
      }
    }
  
    // fallback to UTC  
    return 'UTC';
  }


}