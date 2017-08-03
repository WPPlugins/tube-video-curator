<?php
/**
 * Tube_VC_Dashboard
 * 
 * Custom plugin dashboard shows different functionality based on user status
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Dashboard {
  
  public static $instance;
  
  public static $youtube_api_key;
  
  public static $vimeo_api_key;
  
  public static $twitch_api_key;
  
  public static $sources;
  
  
  public static function init()
  {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Dashboard();
      return self::$instance;
  }
  
  
  // Constructor  
  
  function __construct() {    
        
    if ( isset( $_GET['page'] ) && ( $_GET['page'] == TUBE_VIDEO_CURATOR_SLUG) ):    
      
      // action to set local storage of API key values
      add_action( 'init', array( $this, 'set_local_api_key_values' ) );
      
      // Add Sections
      add_action('admin_init', array( $this, 'add_dashboard_sections' ) );
      
    endif;
    
  }
  
  
  
  
  // Store API key values here in this object for quick access
  function set_local_api_key_values(){  
    
    self::$youtube_api_key = Tube_Video_Curator::$tube_youtube_videos->get_youtube_api_key();
    
    self::$vimeo_api_key = Tube_Video_Curator::$tube_vimeo_videos->get_vimeo_api_key();
    
    self::$twitch_api_key = Tube_Video_Curator::$tube_twitch_videos->get_twitch_api_key();
  
  }
  
  
  // add the different settions sections for the dashboard
  function add_dashboard_sections() { 

     // Get started section
     add_settings_section(
      'tube_vc_dashboard_get_started_section', 
      NULL, 
      array( $this, 'show_dashboard_get_started_section'), 
      'tube_vc_dashboard_get_started_section'
    );
        
    // YouTube API Section
     add_settings_section(
      'tube_vc_dashboard_youtube_api_key_section', 
      __( 'How to get a YouTube API key:', 'tube-video-curator' ), 
      array( $this, 'show_dashboard_youtube_api_key_section'), 
      'tube_vc_dashboard_youtube_api_key_section'
    );    
            
    // Vimeo API Section
     add_settings_section(
      'tube_vc_dashboard_vimeo_api_key_section', 
      __( 'How to get a Vimeo API key:', 'tube-video-curator' ), 
      array( $this, 'show_dashboard_vimeo_api_key_section'), 
      'tube_vc_dashboard_vimeo_api_key_section'
    );    
            
    // Twitch API Section
     add_settings_section(
      'tube_vc_dashboard_twitch_api_key_section', 
      __( 'How to get a Twitch API key:', 'tube-video-curator' ), 
      array( $this, 'show_dashboard_twitch_api_key_section'), 
      'tube_vc_dashboard_twitch_api_key_section'
    );    
            
    // Your Channels & Playlists Section
     add_settings_section(
      'tube_vc_dashboard_your_channels_section', 
      __( 'Manage channels and playlists:', 'tube-video-curator' ), 
      array( $this, 'show_dashboard_your_channels_section'), 
      'tube_vc_dashboard_next_steps_section'
    );
            
    // Find Videos
     add_settings_section(
      'tube_vc_dashboard_find_videos_section', 
      __( 'Find and curate new videos:', 'tube-video-curator' ), 
      array( $this, 'show_dashboard_find_videos_section'), 
      'tube_vc_dashboard_next_steps_section'
    );

    // Settings & API Keys
     add_settings_section(
      'tube_vc_dashboard_update_settings_section', 
      __( 'Update your settings:', 'tube-video-curator' ), 
      array( $this, 'show_dashboard_update_settings_section'), 
      'tube_vc_dashboard_next_steps_section'
    );
    
    // Settings & API Keys
     add_settings_section(
      'tube_vc_dashboard_manage_api_keys_section', 
      __( 'Manage your API Keys:', 'tube-video-curator' ), 
      array( $this, 'show_dashboard_manage_api_keys_section'), 
      'tube_vc_dashboard_next_steps_section'
    );
            
            
    // Sidebar Section
     add_settings_section(
      'tube_vc_dashboard_sidebar_section', 
      NULL, 
      array( $this, 'show_dashboard_sidebar_section'), 
      'tube_vc_dashboard_sidebar_section'
    );
    
      
  }
 
 
  // render the Dashboard page
  function render_options_page_dashboard(){
         
    ?>
    <div class="wrap">
      <h1>
        <?php _e('Welcome to the .TUBE Video Curator', 'tube-video-curator'); ?>
      </h1>
      <?php settings_errors(); ?>
      
      
      <div class="tube-vc-settings">
        <div class="tube-vc-settings-content-col">
          <form action='options.php' method='post' class="section-divider">
            <?php
              
              // test if no API keys
              if ( ! self::$youtube_api_key && ! self::$vimeo_api_key ):
                
                // show page to add a new key?
                if ( isset( $_GET['newkey'] ) ):
                  
                  // show specific settings sections based on which type of key
                  switch ( $_GET['newkey'] ):
                    
                    case 'youtube':                    
                      do_settings_sections( 'tube_vc_dashboard_youtube_api_key_section' );
                      break;
                    
                    case 'vimeo':
                      do_settings_sections( 'tube_vc_dashboard_vimeo_api_key_section' );
                      break;
                    
                    case 'twitch':
                      do_settings_sections( 'tube_vc_dashboard_twitch_api_key_section' );
                      break;
                      
                  endswitch;            
                  
                else:
                 
                 // no keys, and not trying to add a key, so show "get started"
                 do_settings_sections( 'tube_vc_dashboard_get_started_section' );
                  
                endif;
                
              else: // has at least one set of API keys
             
                // show the main next sections
                do_settings_sections( 'tube_vc_dashboard_next_steps_section' );
                
              endif; 
              
              ?>
            
          </form>      
        </div>    
        <div class="tube-vc-settings-sidebar-col">
        
          <?php 
          // show the sidebar section
          do_settings_sections( 'tube_vc_dashboard_sidebar_section' );
          ?>
          
        </div>    
      </div>  
    </div>
    <?php
  
  }
  
  // Get started section
  function show_dashboard_get_started_section(){
        
    global $tube_video_curator;
    
    $current_user = wp_get_current_user();    
    
    
    ?>
      <p style="font-size:1.25em;"><?php _e( sprintf( __('Hi %1$s. To curate videos, you&rsquo;ll need an API Key* from YouTube, Vimeo, or Twitch.'), $current_user->first_name ) , 'tube-video-curator'); ?></p>
      <p style="font-size:1.25em;"><?php _e('Which site would you like to set up first?', 'tube-video-curator'); ?></p>
      <ul style="list-style: disc; padding-left:1.75em;">
        <li>
          <h3 style="margin-bottom:0;">
            <a href="<?php echo add_query_arg('newkey','youtube'); ?>"><?php _e('YouTube', 'tube-video-curator'); ?></a>
          </h3>
          <p style="margin-top:5px;">
            <?php _e('This usually takes about 3 or 4 minutes if you already have a Google account.', 'tube-video-curator'); ?>
          </p>
        </li>
        <li>
          <h3 style="margin-bottom:0;">
            <a href="<?php echo add_query_arg('newkey','vimeo'); ?>"><?php _e('Vimeo', 'tube-video-curator'); ?></a>
          </h3>
          <p style="margin-top:5px;">
            <?php _e('This usually takes about 2 minutes if you already have a Vimeo account.', 'tube-video-curator'); ?>
          </p>
        </li>
        <li>
          <h3 style="margin-bottom:0;">
            <a href="<?php echo add_query_arg('newkey','twitch'); ?>"><?php _e('Twitch', 'tube-video-curator'); ?></a>
          </h3>
          <p style="margin-top:5px;">
            <?php _e('This also takes about 2 minutes if you already have a Twitch account.', 'tube-video-curator'); ?>
          </p>
        </li>
      </ul>
      <hr />
      <p><?php _e('* An API Key is a unique code that&rsquo;s required to get videos and other information from these sites.', 'tube-video-curator'); ?></p>
      <?php   
  }
  
  
  // YouTube API Key Section
  function show_dashboard_youtube_api_key_section(){
    global $tube_video_curator;
    $current_user = wp_get_current_user();
    
    
    ?>
      
      <p style="font-size:1.25em;"><?php _e( 'If you already have a YouTube API Key, scroll down and enter it now.' , 'tube-video-curator'); ?></p>
      <p style="font-size:1.25em;"><?php  _e('Otherwise, here&rsquo;s how to get one.' , 'tube-video-curator'); ?></p>
        <?php
        Tube_Video_Curator::$tube_settings_api_keys -> show_youtube_api_howto();
        ?>
      <div class="card">        
        <?php
        settings_fields( 'tube_vc_youtube_api_settings' );
        do_settings_sections( 'tube_vc_youtube_api_settings' );
        submit_button(__('Save API Key', 'tube-video-curator' ) );      
        ?>
      </div>
      <?php
  }
  
  
  
  // Vimeo API Key Section
  function show_dashboard_vimeo_api_key_section(){
    global $tube_video_curator;
    $current_user = wp_get_current_user();
    
    
    ?>
      
      <p style="font-size:1.25em;"><?php _e( 'If you already have a Vimeo API Key, scroll down and enter it now.' , 'tube-video-curator'); ?></p>
      <p style="font-size:1.25em;"><?php  _e('Otherwise, read on to learn how to get one.' , 'tube-video-curator'); ?></p>
      <?php
      Tube_Video_Curator::$tube_settings_api_keys -> show_vimeo_api_howto();
        ?>
      <div class="card">        
        <?php
      settings_fields( 'tube_vc_vimeo_api_settings' );
      do_settings_sections( 'tube_vc_vimeo_api_settings' );
      submit_button(__('Save ID &amp; Secret', 'tube-video-curator' ) );       
        ?>
      </div>
      <?php 
  }
  
  
  
  // Twitch API Key Section
  function show_dashboard_twitch_api_key_section(){
    global $tube_video_curator;
    $current_user = wp_get_current_user();
    
    
    ?>
      
      <p style="font-size:1.25em;"><?php _e( 'If you already have a Twitch API Key, scroll down and enter it now.' , 'tube-video-curator'); ?></p>
      <p style="font-size:1.25em;"><?php  _e('Otherwise, read on to learn how to get one.' , 'tube-video-curator'); ?></p>
      <?php
      Tube_Video_Curator::$tube_settings_api_keys -> show_twitch_api_howto();
        ?>
      <div class="card">        
        <?php
      settings_fields( 'tube_vc_twitch_api_settings' );
      do_settings_sections( 'tube_vc_twitch_api_settings' );
      submit_button(__('Save ID &amp; Secret', 'tube-video-curator' ) );       
        ?>
      </div>
      <?php 
  }
  
  
      
  // Your Channels section
  function show_dashboard_your_channels_section() { 
    
    global $tube_video_curator;
     
    $this::$sources = $tube_video_curator::$tube_source_manager -> get_sources( array( 'posts_per_page' => 6 ) );
       
    ?>
    
    
      <ul style="list-style: disc; padding-left:1.75em;"> 
        <?php if ( count($this::$sources) > 0 ): ?>
          
          <li>
            <h3 style="margin-bottom:0;">
              <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_VIEW_SOURCES_URL ); ?>">
                <?php _e('Your Channels &amp; Playlists', 'tube-video-curator' ); ?>
              </a>
            </h3>
            <p style="margin-top:5px;">
              <?php _e('Find videos from your Channels &amp; Playlists, change auto-sync settings, and more.', 'tube-video-curator'); ?>
            </p>
          </li>
                    
        <?php endif; ?>               
        
        <li>
          
          <h3 style="margin-bottom:0;">
            <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_FIND_SOURCES_URL ); ?>">
              <?php _e('Add Channels &amp; Playlists', 'tube-video-curator'); ?>  
            </a>
          </h3>
              <p style="margin-top:5px;">
                
                <?php _e('Channels and playlists make it&rsquo;s easy to curate the latest videos from your favorite sources.', 'tube-video-curator'); ?>
              </p>
          
        </li>
        
      </ul>

    <hr />
    <?php
      
  }
  
  
      
  // Show the sidebar  
  function show_dashboard_sidebar_section() {
        
    $this -> show_dashboard_recent_channels_module();  
        
    $this -> show_dashboard_tube_ad_module();    
    
  }
  
      
  // Channels List Module
  function show_dashboard_recent_channels_module() {
      
    global $tube_video_curator;
   
      // If there are sources, show a box of recently added
      if ( count($this::$sources) > 0 ): 
      ?>
       <div id="tube-vc-recent-channels-module">
        
        <h3>
          <?php _e( 'Recently added Channels &amp; Playlists', 'tube-video-curator' ); ?>
        </h3>
        
        <ul style="list-style: none; padding-left:.25em;">
          
          <?php 
            
            $count = 0;
            
            foreach ( $this::$sources as $source ):
              
              $count++;
                   
              if ( $count > 5 ):
                continue;
              endif;
                    
              $tube_source_link = get_edit_post_link( $source->ID );
              
              $tube_source_site = get_post_meta( $source->ID, 'tube_source_site', true);
              
              $tube_source_site_label = $tube_video_curator->get_external_site_label($tube_source_site);
              
              $tube_feed_type = get_post_meta( $source->ID, 'tube_feed_type', true);
              
              $tube_feed_type_label = $tube_video_curator->get_feed_type_label($tube_feed_type);
              
               $tube_source_thumbnail = get_the_post_thumbnail( $source->ID,  array(50,80))  ;
              ?>
              <li style="overflow:hidden;">
                
                <div style="float:left; margin-right:1em;">
                  <?php echo $tube_source_thumbnail; ?>
                </div>
                <div style="float:left;">
                  <h3 style="margin:0;">
                    <a href="<?php echo esc_url($tube_source_link); ?>">
                      <?php echo esc_html( $source->post_title ); ?>
                    </a>
                  </h3>
                   
                  <p style="margin-top:5px;">
                    <?php echo esc_html( $tube_feed_type_label ); ?>
                  </p>
                </div>
                
              </li>
            <?php endforeach; ?>
                    
            <?php if ( count($this::$sources) > 5 ): ?>                
              <li>
                <hr />
                <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_VIEW_SOURCES_URL ); ?>">
                <?php _e('Manage Your Channels &amp; Playlists', 'tube-video-curator' ); ?>
              </a>
              </li>
             <?php endif; ?>   
             
        </ul>
        
      </div>
    <?php endif; 
  }
      
  // Tube Ad Module
  function show_dashboard_tube_ad_module() {
    
    ?>
    <div class="tube-vc-find-your-tube-module">
      <a href="https://www.get.tube">
        <img src="<?php echo TUBE_VIDEO_CURATOR_URL; ?>images/find-your-tube-domain-300x250.png" alt="Find Your .TUBE Domain" />
      </a>
    </div>

    <?php
  }
  
  
  // Find Videos section
  function show_dashboard_find_videos_section() {
    
    global $tube_video_curator;
     
    $pending_videos = $tube_video_curator -> get_videos_pending_review();
     
     
    $pending_videos_count = 0;
     
    if ( $pending_videos ):
       
      $pending_videos_count = $pending_videos->found_posts;
       
      $post_type = get_option( 'tube_vc_default_import_post_type' );
       
      $pending_videos_url = admin_url( 'edit.php?post_status=pending&post_type=' . $post_type );

      $pending_videos_label = _n(' Pending Video', ' Pending Videos', $pending_videos_count, 'tube-video-curator');
        
    endif;
 
    ?>
              
        <ul style="list-style: disc; padding-left:1.75em;">
              
              
             <?php if ( $pending_videos_count ): ?>
              <li>
                <h3 style="margin-bottom:0;">
                  <a href="<?php echo $pending_videos_url; ?>">
                    <?php printf(__('Review %1$s %2$s', 'tube-video-curator'), $pending_videos_count, $pending_videos_label); ?>
                  </a>
                </h3>
                <p style="margin-top:5px;">
                  <?php _e('Review and approve videos that are set to &#8220Pending Review.&#8221', 'tube-video-curator'); ?>
                </p>
              </li>
             <?php endif; ?>
          
            <li>
              <h3 style="margin-bottom:0;">
                <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_ADD_VIA_URL_URL ); ?>">
                  <?php _e('Add Video via URL', 'tube-video-curator'); ?>  
                </a>
              </h3>
              <p style="margin-top:5px;">
                <?php _e('Have a specific video you want to turn into a post? This is the tool for you.', 'tube-video-curator'); ?>                  
              </p>
            </li>

            <li>
              <h3 style="margin-bottom:0;">
                <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_FIND_VIDEOS_URL ); ?>">
                  <?php _e('Search for Videos', 'tube-video-curator'); ?>  
                </a>
              </h3>
              <p style="margin-top:5px;">
                <?php _e('Search for the latest videos for any query you can think of, then curate just the videos you like.', 'tube-video-curator'); ?>                  
              </p>
            </li>

            <li>
              <h3 style="margin-bottom:0;">
                <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_SKIPPED_VIDEOS_URL ); ?>">
                  <?php _e('Skipped Videos', 'tube-video-curator'); ?>  
                </a>
              </h3>
              <p style="margin-top:5px;">
                <?php _e('See all the videos you&rsquo;ve passed up, in case you want to &#8220;un-skip / delete&#8221; them.', 'tube-video-curator'); ?>                  
              </p>
            </li>
        </ul>

    <hr />
        <?php
  } 
  
  
   
      
  // Update settings section
  function show_dashboard_update_settings_section() {
    ?>
    <ul style="list-style: disc; padding-left:1.75em;">
      
      <li>
        <h3 style="margin-bottom:0;">
          <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_SETTINGS_URL ); ?>">                    
            <?php _e('Change Display Settings', 'tube-video-curator'); ?>
          </a>
        </h3>
        <p style="margin-top:5px;">
          <?php _e('Customize how videos get displayed on your site.', 'tube-video-curator'); ?>
        </p>
      </li>
    
      <li>
        <h3 style="margin-bottom:0;">
          <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_IMPORT_SETTINGS_URL ); ?>">                    
            <?php _e('Change Import Settings', 'tube-video-curator'); ?>
          </a>
        </h3>
        <p style="margin-top:5px;">
          <?php _e('Set the defaults for how new videos get turned into posts.', 'tube-video-curator'); ?>
        </p>
      </li>
        
    </ul>

    <hr />
  <?php
  } 
  
      
  // Update settings section
  function show_dashboard_manage_api_keys_section() {
    ?>
    <ul style="list-style: disc; padding-left:1.75em;">
      
     
      <li>
        <h3 style="margin-bottom:0;">
          <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_API_KEYS_URL ); ?>">
            <?php
              if ( self::$youtube_api_key ):
                
                  _e('Manage YouTube API Key', 'tube-video-curator');
                
              else:
                
                  _e('Add a YouTube API Key', 'tube-video-curator');
                
              endif;
              ?>
          </a>
         </h3>
        <p style="margin-top:5px;">
          <?php _e('With a YouTube API Key you can search for channels, playlists and more.', 'tube-video-curator'); ?>
        </p>
      </li>
     
      <li>
        <h3 style="margin-bottom:0;">
          <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_VIMEO_API_KEYS_URL ); ?>">                  
            <?php 
            if ( self::$vimeo_api_key ):
              
                _e('Manage Vimeo API Key', 'tube-video-curator');
              
            else:
              
                _e('Add a Vimeo API Key', 'tube-video-curator');
              
            endif;
            ?>
          </a>
        </h3>
        <p style="margin-top:5px;">
          <?php _e('A Vimeo API Key allows you to search for users, channels, and more.', 'tube-video-curator'); ?>
        </p>
      </li>
     
      <li>
        <h3 style="margin-bottom:0;">
          <a href="<?php echo esc_url( TUBE_VIDEO_CURATOR_TWITCH_API_KEYS_URL ); ?>">                  
            <?php 
            if ( self::$twitch_api_key ):
              
                _e('Manage Twitch API Key', 'tube-video-curator');
              
            else:
              
                _e('Add a Twitch API Key', 'tube-video-curator');
              
            endif;
            ?>
          </a>
        </h3>
        <p style="margin-top:5px;">
          <?php _e('Your Twitch API Key allows you to search for channels, videos, and more.', 'tube-video-curator'); ?>
        </p>
      </li>
        
    </ul>
  <?php
  } 
  
    
}