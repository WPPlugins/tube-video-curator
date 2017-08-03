<?php
/**
 * Tube_VC_Add_Via_Url
 * 
 * Supporting for curating videos from a specific single-video URL.
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
 

class Tube_VC_Add_Via_Url {
  
  public static $instance;
  
  public static $video_data;
  
  public static $search_site;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Add_Via_Url();
      return self::$instance;
  }
    
  
  // Constructor    
  function __construct() {    
    
    
    // See if we're NOT on the Add Via URL page
    if ( isset( $_GET['page'] ) && ( $_GET['page'] != TUBE_VIDEO_CURATOR_SLUG . '-add-via-url') ):  
        
      // delete the URL to clean the field so it's gone when we come back
      delete_option( 'tube_vc_add_via_url_url' ); 
      
    endif;
    
    add_action('admin_init', array( $this, 'register_add_via_url_search_settings') ); 
    
    add_action('admin_init', array( $this, 'register_add_via_url_results_settings' ) );  
    
    add_filter('option_page_capability_tube_vc_add_via_url_settings', array( $this, 'tube_vc_add_via_url_settings_capability' ) );  
 
  }
    
  
  function tube_vc_add_via_url_settings_capability($capability){
    
    return 'publish_posts';
      
      
  }    
  
  // loader function called from main tube-video-curator.php file
  function load_tube_vc_add_via_url_scripts(){
    
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_add_via_url_scripts') );
      
      
  }    
      
  function enqueue_add_via_url_scripts() {
    
      wp_register_script(
        'tube_vc_add_via_url', 
        TUBE_VIDEO_CURATOR_URL . 'js/tube-vc-add-via-url.js', 
        array(), 
        '1.0.0',
        //rand(50000, 150000), 
        false
      );
      wp_enqueue_script('tube_vc_add_via_url');
      
      add_action( 'admin_print_scripts', array(  $this, 'print_add_via_url_scripts'), 99 );
      
  }
  
  

  function print_add_via_url_scripts() {    
    ?>
    <script type="text/javascript">
    Tube_VC_Ajax_Add_Via_Url.trans.hintYouTube = '<?php _e('Use standard YouTube URL for single videos only. e.g. https://www.youtube.com/watch?v=-C_jPcUkVrM', 'tube-video-curator'); ?>';
    Tube_VC_Ajax_Add_Via_Url.trans.hintVimeo = '<?php _e('Use standard Vimeo URL for single videos only. e.g. https://vimeo.com/26783966', 'tube-video-curator'); ?>';
    Tube_VC_Ajax_Add_Via_Url.trans.hintTwitch = '<?php _e('Use standard Twitch URL for single videos only. e.g. https://www.twitch.tv/bungie/v/79073494', 'tube-video-curator'); ?>';
  </script>    
    <?php
  }
  
  
  
  // render the options page for Add Via URL
  function render_options_page_add_via_url(){
    ?>
    
    <div class="wrap">  
        
      <h1><?php _e( 'Add Video via URL', 'tube-video-curator' ); ?></h1>
      
      <?php        
      if ( ! Tube_Video_Curator::has_api_keys() ):
                  
        Tube_Video_Curator::no_api_keys_message();
        
      else:  
      ?>
    
        <form action="options.php" method="post">
          
          <?php        
          
          settings_fields( 'tube_vc_add_via_url_settings' );
          do_settings_sections( 'tube_vc_add_via_url_settings' );  
          
          $button_text = __( 'Find Video', 'tube-video-curator' );  
                
          submit_button( $button_text );          
          
          do_settings_sections( 'tube_vc_add_via_url_results' );
          
          ?>
        </form>
        
        
      <?php endif; ?>
      
    </div>
    <?php
  
  }
  
  
  // Register Add via URL settings and add sections and fields
  
  function register_add_via_url_search_settings() { //register our settings
     
    register_setting( 'tube_vc_add_via_url_settings', 'tube_vc_add_via_url_site' );
    
    register_setting( 'tube_vc_add_via_url_settings', 'tube_vc_add_via_url_url' );
    
     add_settings_section(
      'tube_vc_add_via_url_settings_section', 
      NULL, //__( 'Search Options', 'tube-video-curator' ), 
      array( $this, 'show_add_via_url_settings_section'), 
      'tube_vc_add_via_url_settings'
    );
    
    add_settings_field( 
      'tube_vc_add_via_url_site', 
      __( 'Site', 'tube-video-curator' ), 
      array( $this, 'render_add_via_url_site_setting'), 
      'tube_vc_add_via_url_settings', 
      'tube_vc_add_via_url_settings_section' 
    ); 
    
    add_settings_field( 
      'tube_vc_add_via_url_url', 
      __( 'Video URL', 'tube-video-curator' ), 
      array( $this, 'render_add_via_url_url_setting'), 
      'tube_vc_add_via_url_settings', 
      'tube_vc_add_via_url_settings_section' 
    );    
    
  
  }

  
  // function to show the settings section stuff above the fields
  function show_add_via_url_settings_section() { 
    
    //echo __( 'Enter your search settings:', 'tube-video-curator' );
    
  }
  
  // show the Find Videos Site dropdown
  function render_add_via_url_site_setting(  ) { 
    
    // get the sites to search    
    global $tube_video_curator;
    
    $sites = $tube_video_curator -> get_external_site_options( true );
    
    if ( ! $sites ) :
      
      _e('Sorry, there are no sites to search.', 'tube-video-curator');
      
      return;
      
    endif;    
    
    
    if ( count( $sites ) == 1 ):
      
      $site = key($sites);      
      $site_name = reset($sites);// NOTE: reset returns the first value
      
      echo esc_html( $site_name ); 
      ?><input type="hidden" id="tube_vc_add_via_url_site" name="tube_vc_add_via_url_site" value="<?php echo esc_attr( $site); ?>" /><?php
    else:
      
      
    // get the search site value
    $search_site = get_option( 'tube_vc_add_via_url_site' );
    
    ?>
    
    <select name="tube_vc_add_via_url_site">
      <?php foreach ( $sites as $site => $site_name ): ?>
        <option value="<?php echo esc_attr($site); ?>" <?php selected( $search_site, $site ); ?> >
          <?php echo esc_html($site_name); ?>
        </option>        
      <?php endforeach; ?>
    </select>
    
    <?php
    endif;
    
  }

  // callback to show the URL setting
  function render_add_via_url_url_setting(  ) { 
      
  
    $add_via_url_url = get_option( 'tube_vc_add_via_url_url' );
    
    ?>
    <input type="text" name="tube_vc_add_via_url_url" value="<?php echo $add_via_url_url; ?>" class="regular-text ltr">
    
    <p id="url-hint" class="description">&nbsp;</p>
    
    <?php
  
  }
  
  
  
  // Admin message if user enters an invald URL
  function invalid_url(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Please use a valid video URL.', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }


  // Settings for the results section
  function register_add_via_url_results_settings() {
     
    $search_site = get_option( 'tube_vc_add_via_url_site' );
    
    // If no Search Site, do nothing
    if ( ! $search_site ):
      
      return;
      
    endif;
    
    // get the requested URL
    $requested_url = get_option( 'tube_vc_add_via_url_url' );
    
    // If no URL, do nothing
    if ( ! $requested_url ):
      
      return;
      
    endif;
    
    $url_parts = parse_url( $requested_url );   
    
    if ( ! array_key_exists( 'host', $url_parts ) ):
      
      add_action( 'admin_notices', array( $this, 'invalid_url') );     
      return NULL;
      
    endif;
    
    //get_vimeo_video_via_api
    
    //print_r( strpos( $url_parts['host'], 'youtube.com' ) );
    
    switch ($search_site) :
      
      case 'youtube':
      
        $video_data = Tube_Video_Curator::$tube_youtube_videos -> get_youtube_video_data_via_url( $requested_url );
        
        break;
        
      case 'vimeo':
        
        $video_data = Tube_Video_Curator::$tube_vimeo_videos -> get_vimeo_video_data_via_url( $requested_url ); 
        
        break;
        
      case 'twitch':
        
        $video_data = Tube_Video_Curator::$tube_twitch_videos -> get_twitch_video_data_via_url( $requested_url );  
            
        break;
        
    endswitch;
          
   
    
    // If no video data, do nothing
    if ( ! isset($video_data) || ! $video_data ):
      
      return;

    endif;
    
    // store the data in a var for ise om the results section
    self::$video_data = $video_data;         
    
    self::$search_site = $search_site;         
          
    // add a section for the results
    add_settings_section(
      'tube_vc_add_via_url_results_section', 
      NULL, // NO TITLE, INSTEAD OF CUSTOM ONE__( $title, 'tube-video-curator' ), 
      array( $this, 'show_add_via_url_results_section'), 
      'tube_vc_add_via_url_results'
    );    
    
  
  }


  function show_add_via_url_results_section() {
    ?>
      
    
    <div class="notice notice-info" id="tube-video-curator-notice" style="display:none;">
      <p><?php _e('Media added!', 'tube-video-curator'); ?></p>
    </div>

    <div id="poststuff">
    <div class="postbox">
      
      <h2 class="hndle"><span><?php _e('Selected Video', 'tube-video-curator'); ?></span></h2>
      
      <div class="inside">        
        
        <?php 
        Tube_Video_Curator::$tube_videos_list -> show_videos_list( self::$video_data, self::$search_site ); 
        ?>      
        
      </div><!-- /.inside -->
      
      
    </div><!-- /.postbox -->
    </div><!-- /#poststuff -->
    <?php
  }
    
}