<?php
/**
 * Tube_VC_Find_Videos
 * 
 * Functions for the "Search for Videos" page provide a keyword based video search
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Find_Videos {
  
  public static $instance;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Find_Videos();
      return self::$instance;
  }
  
    
  // Constructor    
  function __construct() {    
    
    // Settings and section for Search Settings
    add_action('admin_init', array( $this, 'register_find_videos_search_settings') ); 
     
    // Settings and section for Search Results
    add_action('admin_init', array( $this, 'register_find_videos_results_settings') ); 
    
    add_filter('option_page_capability_tube_vc_find_videos_settings', array( $this, 'tube_vc_find_videos_settings_capability' ) );  
 
  }
    
  
  function tube_vc_find_videos_settings_capability($capability){
    
    return 'publish_posts';
      
      
  }    
    
  // Set default values for the Find Videos search settings
  function set_default_query_options() {    
        
    //add_option('tube_vc_find_videos_site', 'youtube');
    
    //add_option('tube_vc_find_videos_query', __('Live in Concert', 'tube-video-curator')); 
    
  }
  
  
 
  // Display the Find Videos page
  function render_options_page_find_videos(){        
    ?>
    
    <div class="wrap">
  
        
      <h1>Search for Videos</h1>
      
      <?php          
      if ( ! Tube_Video_Curator::has_api_keys( array('youtube','vimeo') ) ):
                  
        Tube_Video_Curator::no_youtube_or_vimeo_api_keys_message();
      
      else:  
      ?>
            
        <form action="options.php" method="post">
          
          <?php
          
          settings_fields( 'tube_vc_find_videos_settings' );
          do_settings_sections( 'tube_vc_find_videos_settings' );
          
          submit_button('Search');
          
          do_settings_sections( 'tube_vc_find_videos_results' );
          
          ?>
          
        </form>
        
      <?php endif; ?>
    </div>
    <?php
  
  }
  
  
  // Register settings / sections / fields for the Find Videos search
  function register_find_videos_search_settings() { 
    
    register_setting( 'tube_vc_find_videos_settings', 'tube_vc_find_videos_site' );
    register_setting( 'tube_vc_find_videos_settings', 'tube_vc_find_videos_query_type' );
    register_setting( 'tube_vc_find_videos_settings', 'tube_vc_find_videos_query' );    
    register_setting( 'tube_vc_find_videos_settings', 'tube_vc_find_videos_max_results' );
     
     add_settings_section(
      'tube_vc_find_videos_settings_section', 
      NULL, //__( 'Search Options', 'tube-video-curator' ), 
      array( $this, 'show_find_videos_settings_section'), 
      'tube_vc_find_videos_settings'
    );
    
  
    add_settings_field( 
      'tube_vc_find_videos_site', 
      __( 'Site', 'tube-video-curator' ), 
      array( $this, 'render_find_videos_site_setting'), 
      'tube_vc_find_videos_settings', 
      'tube_vc_find_videos_settings_section' 
    );    
  
  
    add_settings_field( 
      'tube_vc_find_videos_query', 
      __( 'Query', 'tube-video-curator' ), 
      array( $this, 'render_find_videos_query_setting'), 
      'tube_vc_find_videos_settings', 
      'tube_vc_find_videos_settings_section' 
    );
    
     
  }

  

  // Show the Find Videos settings section
  function show_find_videos_settings_section() { 
    
    //echo __( 'Enter your search settings:', 'tube-video-curator' );
    
  }
  
  // show the Find Videos Site dropdown
  function render_find_videos_site_setting(  ) { 
    
    // get the sites to search    
    global $tube_video_curator;
    
    $sites = $tube_video_curator -> get_external_site_options( true );
    
    unset( $sites['twitch']  ); // video search not supported by twitch
    
    if ( ! $sites ) :
      
      _e('Sorry, there are no sites to search.', 'tube-video-curator');
      
      return;
      
    endif;    
    
    
    if ( count( $sites ) == 1 ):
      
      $site = key($sites);      
      $site_name = reset($sites);// NOTE: reset returns the first value
      
      echo esc_html( $site_name ); 
      ?><input type="hidden" id="tube_vc_find_videos_site" name="tube_vc_find_videos_site" value="<?php echo esc_attr( $site); ?>" /><?php
    else:
      
      
    // get the search site value
    $search_site = get_option( 'tube_vc_find_videos_site' );
    
    ?>
    
    <select name="tube_vc_find_videos_site">
      <?php foreach ( $sites as $site => $site_name ): ?>
        <option value="<?php echo esc_attr($site); ?>" <?php selected( $search_site, $site ); ?> >
          <?php echo esc_html($site_name); ?>
        </option>        
      <?php endforeach; ?>
    </select>
    
    <?php
    endif;
    
  }
  
  

  
  // show the Find Videos Query input
  function render_find_videos_query_setting(  ) { 
  
    $search_query = get_option( 'tube_vc_find_videos_query' );
    
    ?>
    <input type="text" name="tube_vc_find_videos_query" value="<?php echo esc_attr($search_query); ?>" class="regular-text ltr">
    <?php
  
  }
  


  // Add the settings section for the Find Videos Results
  function register_find_videos_results_settings() { //register our settings
    
    add_settings_section(
      'tube_vc_find_videos_results_section', 
      NULL, // NO TITLE, INSTEAD OF CUSTOM ONE__( $title, 'tube-video-curator' ), 
      array( $this, 'show_videos_results_section'), 
      'tube_vc_find_videos_results'
    );    
  
  }
  

  // Show the Find Videos Results section
  function show_videos_results_section() {    
  
    global $tube_video_curator;
    
    // get current search site
    $search_site =  get_option( 'tube_vc_find_videos_site', '' ); 
    
    // get the label for the serach site
    $search_site_label = $tube_video_curator -> get_external_site_label( $search_site );
    
    $query =  get_option( 'tube_vc_find_videos_query', '' ); 
    
      
    
    // TODO: Raise a proper error here
    if ( ! isset($query) || ! $query ):
      
      //_e('<p>Enter a search query to get started.</p>', 'tube-video-curator');
      return;
      
    endif;
     
    
    switch ($search_site):
      
      case 'youtube':
        
        $search_args = Tube_Video_Curator::$tube_youtube_videos -> get_source_search_args_from_querystring();
        
        $max_results = $search_args['maxResults'];
        
        $query_videos = Tube_Video_Curator::$tube_youtube_videos->query_youtube_videos_via_api( $query, $search_args );
        
        break;
        
      case 'vimeo':
        
        $search_args = Tube_Video_Curator::$tube_vimeo_videos -> get_source_search_args_from_querystring();
        
        $max_results = $search_args['per_page'];  
        
        $query_videos = Tube_Video_Curator::$tube_vimeo_videos->query_vimeo_videos_via_api( $query, $search_args );
        
        break;
        
    endswitch;
     
    
    // Show the search results box
    ?>
    
    <div id="poststuff">
    <div class="postbox">
      
      <h2 class="hndle">
        <span>          
         <?php 
         echo sprintf( 
           __('Searching <code>%1$s</code> for <code>%2$s</code>', 'tube-video-curator'), 
           esc_html($search_site_label),
           esc_html($query)
         ); 
         ?>     
        </span>
      </h2>
      
      <div class="inside">
        
        <?php 
        
        
        // TODO: Raise a proper error here
        if ( ! isset($query_videos) || ! $query_videos ):
          ?>      
          <p>
            <?php _e('Sorry, no results matched your search.', 'tube-video-curator'); ?>
          </p>      
          <?php
          return;
    
        else:
          
          Tube_Video_Curator::$tube_source_manager -> show_source_videos_list( $query_videos, $search_site, $max_results ); 
        
        endif;
        ?>        
        
      </div><!-- .inside -->
      
    </div><!-- .postbox -->
    </div><!-- .poststuff -->
    <?php      
  }
  
  
  
}