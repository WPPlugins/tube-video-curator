<?php
/**
 * Tube_VC_Find_Sources
 * 
 * Used to create the "Add Chanels & Playlists" page
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Find_Sources {
  
  public static $instance;
  
  public static $current_finder;
  
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Find_Sources();
      return self::$instance;
  }
  
  
  
  // Constructor    
  function __construct() {
      
    add_action( 'admin_init', array( $this, 'set_current_finder') );
      
    add_action( 'admin_init', array( $this, 'register_find_sources_settings') );  
    
    add_action( 'admin_init', array( $this, 'register_plugin_sources_results_settings') );  

    add_action( 'wp_ajax_tube_vc_save_source_via_ajax', array( $this, 'save_source_via_ajax' ) );
    add_action( 'wp_ajax_nopriv_tube_vc_save_source_via_ajax', array( $this, 'save_source_via_ajax' ) );
    
  }
  
    
  
  
  function load_tube_vc_find_sources_scripts(){
    
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_find_sources_scripts') );
      
      
  }    
      
  function enqueue_find_sources_scripts() {
    
      wp_register_script(
        'tube_vc_find_sources', 
        TUBE_VIDEO_CURATOR_URL . 'js/tube-vc-find-sources.js',
        array(), 
        '1.0.0',
         //rand(50000, 150000), 
        false
      );
      wp_enqueue_script('tube_vc_find_sources');
      
      add_action( 'admin_print_scripts', array(  $this, 'print_find_sources_scripts'), 99 );
      
  }
    

  // Print out JS variables for the Find Sources JS     
  function print_find_sources_scripts() {    
    ?>
    <script type="text/javascript">
    Tube_VC_Ajax_Sources.trans.sourceAdded = '<?php _e('Source saved successfully.', 'tube-video-curator'); ?>';
    Tube_VC_Ajax_Sources.trans.sourceAddError = '<?php _e('Source could not be saved. Try again', 'tube-video-curator'); ?>';
    Tube_VC_Ajax_Sources.trans.hintYouTubeChannel = '<?php _e('Enter a YouTube Username, Channel Name, or Channel ID', 'tube-video-curator'); ?>';
    Tube_VC_Ajax_Sources.trans.hintYouTubePlaylist = '<?php _e('Enter a keyword or Playlist ID to search YouTube playlists', 'tube-video-curator'); ?>';   
    Tube_VC_Ajax_Sources.trans.hintVimeoUsername = '<?php _e('Enter a Vimeo Username', 'tube-video-curator'); ?>'; 
    Tube_VC_Ajax_Sources.trans.hintVimeoChannel = '<?php _e('Enter a keyword to search Vimeo channels (i.e. playlists)', 'tube-video-curator'); ?>';
    Tube_VC_Ajax_Sources.trans.sourcePublishedFlag = '<?php _e('SAVED', 'tube-video-curator'); ?>';
  </script>
    
    <?php
  }


  // Set the default query options for Find Sources
  function set_default_query_options() {    
        
    //add_option('tube_vc_find_sources_feed_type', 'youtube-channel');
    
    //add_option('tube_vc_find_sources_query', __('Concerts', 'tube-video-curator')); 
    
  }
  
  
  
  // Set the local finder object based on the search type
  function set_current_finder() {    
     
    $feed_type =  get_option( 'tube_vc_find_sources_feed_type' );   
       
    switch ( $feed_type ) :
      
      case 'youtube-channel':        
        
        require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-find-sources-youtube-channels.php'; 
        
        self::$current_finder = new Tube_VC_Find_Sources_YouTube_Channels;
        
        break;
        
      
      case 'youtube-playlist':
      
        require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-find-sources-youtube-playlists.php'; 
        
        self::$current_finder = new Tube_VC_Find_Sources_YouTube_Playlists;
        
        break;
      
      case 'vimeo-username':
      
        require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-find-sources-vimeo-users.php'; 
        
        self::$current_finder = new Tube_VC_Find_Sources_Vimeo_Users;
        
        break;
      
      case 'vimeo-channel':
      
        require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-find-sources-vimeo-channels.php'; 
        
        self::$current_finder = new Tube_VC_Find_Sources_Vimeo_Channels;
        
        break;
      
      case 'twitch-channel':
      
        require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-find-sources-twitch-channels.php'; 
        
        self::$current_finder = new Tube_VC_Find_Sources_Twitch_Channels;
        
        break;
        
        
    endswitch; 
    
  }


 
  
  // Create the overall Find Sources pages
  function render_options_page_find_sources(){    
    ?>
    <div class="wrap">
        
      <h1>Add New Channel / Playlist</h1>
      
      <?php       
      if ( ! Tube_Video_Curator::has_api_keys() ):
                  
        Tube_Video_Curator::no_api_keys_message();
          
      else:  
      ?>
        <form action="options.php" method="post">
          
          <?php
          settings_fields( 'tube_vc_find_sources_settings' );
          do_settings_sections( 'tube_vc_find_sources_settings' );        
            
          $button_text = __( 'Search', 'tube-video-curator' );  
                
          submit_button( $button_text );   
          
          do_settings_sections( 'tube_vc_find_sources_results' );
          ?>
          
        </form>
      <?php endif; ?>
    </div>
    <?php
  
  }
  
  
  // Register settings for Find Sources page
  function register_find_sources_settings() {
    
    register_setting( 'tube_vc_find_sources_settings', 'tube_vc_find_sources_feed_type' );
    
    register_setting( 'tube_vc_find_sources_settings', 'tube_vc_find_sources_query' );    

     
     add_settings_section(
      'tube_vc_find_sources_settings_section', 
      NULL, //     __( 'Search Options', 'tube-video-curator' ), 
      array( $this, 'show_find_sources_settings_section'), 
      'tube_vc_find_sources_settings'
    );
  
    add_settings_field( 
      'tube_vc_find_sources_feed_type', 
      __( 'Query Type', 'tube-video-curator' ), 
      array( $this, 'render_find_sources_feed_type_setting'), 
      'tube_vc_find_sources_settings', 
      'tube_vc_find_sources_settings_section' 
    );
  
    add_settings_field( 
      'tube_vc_find_sources_query', 
      __( 'Query', 'tube-video-curator' ), 
      array( $this, 'render_find_sources_query_setting'), 
      'tube_vc_find_sources_settings', 
      'tube_vc_find_sources_settings_section' 
    );
    
  }

  

  // Show the header for the settings section
  function show_find_sources_settings_section() { 
    
    // _e( 'Enter your search settings:', 'tube-video-curator' );
    
  }
  
  
  // Callback to show the Feed Type Setting
  function render_find_sources_feed_type_setting(  ) { 
        
    // get the query type value
    $feed_type_setting = get_option( 'tube_vc_find_sources_feed_type' );
    
    // get the query types to  
    global $tube_video_curator;
    
    $feed_types = $tube_video_curator -> get_feed_type_options( true );
    
    ?>
    <select name="tube_vc_find_sources_feed_type">
      <?php foreach ( $feed_types as $feed_type => $feed_type_name ): ?>
        <option value="<?php echo $feed_type; ?>" <?php selected( $feed_type_setting, $feed_type ); ?> >
          <?php echo $feed_type_name; ?>
        </option>        
      <?php endforeach; ?>
    </select>
    
    <?php
  
  }
    
  

  // Callback to show the Query Setting
  function render_find_sources_query_setting(  ) { 
  
    $search_query = esc_attr( get_option( 'tube_vc_find_sources_query' ) );
    
    ?>
    <input type="text" name="tube_vc_find_sources_query" value="<?php echo $search_query; ?>" class="regular-text ltr">
    <p id="source-query-hint" class="description">&nbsp;</p>
    <?php
  
  }




  // Add the Results "settings" section 
  function register_plugin_sources_results_settings() { //register our settings
      
    add_settings_section(
      'tube_vc_find_sources_results_section', 
      NULL, // no title
      array( $this, 'show_find_sources_results_section'), 
      'tube_vc_find_sources_results'
    );    
  
  }
  
  
  // Display the Results "settings" section 
  function show_find_sources_results_section() { 
   
   global $tube_video_curator;
    
    $query =  get_option( 'tube_vc_find_sources_query' );          
    
    // TODO: Raise a proper error here
    if ( ! isset($query) || ! $query ):
      
      //_e('<p>Enter a search query to get started.</p>', 'tube-video-curator');
      return;
      
    endif;
    
    // this is rare, but could occur for example if a source was set and then 
    // that API key got deleted
    if ( ! self::$current_finder ):
      ?>      
      <p><?php _e('There was an error searching. Please try again.', 'tube-video-curator'); ?></p>      
      <?php
      return;
    endif;
        
    
    // Current finder is specific to the feed type (e.g. YouTube Channel, Vimeo User, etc)
    $sources = self::$current_finder -> find_sources($query); 
          
    // Get the feed type
    $feed_type =  get_option( 'tube_vc_find_sources_feed_type' ); 
    
    // Get the label for the feed type
    $feed_type_label =  $tube_video_curator -> get_feed_type_label( $feed_type ); 
    
    ?>      
    <div id="poststuff">
    <div class="postbox">
      
      <h2 class="hndle">
        <span>          
         <?php 
         echo sprintf( 
           __('Searching <code>%1$s</code> for <code>%2$s</code>', 'tube-video-curator'), 
           esc_html($feed_type_label),
           esc_html($query)
         ); 
         ?>     
        </span>
      </h2>
      
      <div class="inside">
        <?php 
    
        // TODO: Raise a proper error here
        if ( ! $sources ):
          ?>      
          <p><?php _e('Sorry, no results matched your search.', 'tube-video-curator'); ?></p>      
          <?php
          
        else:        
            
            $this->show_sources_list( $sources ); ?>      
          
            <?php
            
            $base_url = remove_query_arg( array('prev','next') );
            
            if ( $sources['prev_page_token'] != '' ):
              
              $prev_uri = add_query_arg( 'prev', $sources['prev_page_token'], $base_url );
              
              ?>
              <a href="<?php echo esc_url($prev_uri);; ?>" class="button button-primary">
                <?php esc_html_e('Prev', 'tube-video-curator'); ?>
              </a>
              <?php
            endif;
            
            
        
            if ( $sources['next_page_token'] != '' ):
              
              $next_uri = add_query_arg( 'next', $sources['next_page_token'], $base_url);
              
              ?>
              <a href="<?php echo esc_url($next_uri); ?>" class="button button-primary">
                <?php esc_html_e('Next', 'tube-video-curator'); ?>
              </a>
              <?php
            endif;
        endif;
        ?>
      </div><!-- /.inside -->
    </div><!-- /.postbox -->
    </div><!-- /#poststuff -->
    <?php 
  }
  
  
  // Display a list of sources using normalized data
  function show_sources_list( $sources ){
      
    global $tube_video_curator;
    
    $site = $sources['site'];
    
    $feed_type = $sources['feed_type'];
    
    $feed_type_label =  $tube_video_curator -> get_feed_type_label($feed_type); 
    
    $results_per_page = $sources['per_page'];
    
    
    $existing_ids = $this -> get_external_source_ids($feed_type) ;
    
    $results_per_page_links = array(
      10 => __('10', 'tube-video-curator' ),
      20 => __('20', 'tube-video-curator' ),
      30 => __('30', 'tube-video-curator' ),
      40 => __('40', 'tube-video-curator' ),
      50 => __('50', 'tube-video-curator' ),
    );
       
    
    $sorces_total_results_label =  _n(' source', ' sources', $sources['total_results'], 'tube-video-curator');
  
    ?>
    
    
        <div class="tube-admin-list-videos-heading">
          
          <p style="float:left;">
            <?php echo number_format ($sources['total_results'] ) . $sorces_total_results_label; ?>
          </p> 
          <ul class="max-results-links" style="float:right;">
            <?php      
            foreach ( $results_per_page_links as $results_per_page_id => $results_per_page_label ):          
              
              if ( $results_per_page_id != $results_per_page ):
                
                $link_url = add_query_arg( 'results', $results_per_page_id ); 
                
                $link_url = remove_query_arg( array('next','prev'), $link_url);
                
                $link_url = esc_url( $link_url );
                
                ?><li><a href="<?php echo $link_url; ?>"><?php echo $results_per_page_label; ?></a></li><?php
                
              else:
                
                ?><li><strong><?php echo $results_per_page_label; ?></strong></li><?php
                
              endif;
            endforeach;
            ?>
            </ul>
        </div>
    
    
        <div class="notice notice-info" id="tube-video-curator-notice" style="display:none;">
          <p><?php _e('Processing...', 'tube-video-curator'); ?></p>
        </div>
        
        <hr />
    
        <div class="tube-admin-list-videos">
          <?php
          foreach ($sources['sources'] as $source): 
          
           $existing_source_data = array();
           
           $source_label = NULL; 
           
           $existing_source_edit_link = NULL; 
                     
           if ( is_array($existing_ids) && ( array_key_exists($source['external_guid'], $existing_ids) ) ): 
                
                $existing_source_edit_link = $existing_ids[ $source['external_guid'] ]['edit'];
                
                //print_r($existing_source_edit_link);
                
                $source_label = 'SAVED'; 
                
                $source_label_class = 'text-notification';
             
            endif;
            
            
               $source_description_truncated =  wp_trim_words( $source['description'], 60 );
               
               $source_videos =  number_format( $source['videos'] );             
               
               $source_videos_label =  _n(' video', ' videos', $source['videos']);
               
               $source_subscribers = NULL;
               
               if ( array_key_exists('subscribers', $source) ):
                 $source_subscribers =  number_format( $source['subscribers'] );
                 
                 $source_subscribers_label =  _n(' subscriber', ' subscribers', $source['subscribers'], 'tube-video-curator');
               endif;
              //print_r($source);
              ?>
              <div class="result">
                
                <div class="tube-video-thumbnail" >
                  <?php if ( $source['thumbnail_image_url'] ): ?>
                    <img src="<?php echo $source['thumbnail_image_url']; ?>" alt="" width="150" />
                  <?php endif; ?>
                </div>
                
                <div class="tube-video-summary">
                    
      
                    
                  <h3 class="video-title">  
                    <?php if (  $source_label ): ?>
                      <strong class="wp-ui-<?php echo sanitize_html_class($source_label_class); ?>">[<?php echo esc_html($source_label); ?>]</strong>                
                    <?php endif; ?>
                      <a href="<?php echo $source['external_url']; ?>" target="_blank"><?php echo esc_html($source['title']); ?></a>
                  </h3>
                    
                  <p class="video-meta description">
                    <?php if ($source_videos) echo esc_html($source_videos . $source_videos_label); ?>
                    <?php if ($source_subscribers && $source_videos) echo ' | ' ; ?> 
                    <?php if ($source_subscribers) echo esc_html($source_subscribers . $source_subscribers_label); ?> 
                  </p>
                  <p class="video-description">
                    <?php echo esc_html($source_description_truncated); ?>
                  </p>
                  
                </div>
                
                <div class="tube-video-buttons tube-source-new-buttons">                
                  
              
                <?php 
                if (  isset($existing_source_edit_link) ): ?>
                
                  <a  class="btn-view button" href="<?php echo esc_url($existing_source_edit_link); ?>">
                    <?php _e( 'Find Videos', 'tube-video-curator' ); ?>
                  </a>
              
              <?php else: ?>
                    <a data-site= "<?php echo esc_attr($site); ?>"  data-feed-type= "<?php echo esc_attr($feed_type); ?>"  data-title= "<?php echo esc_attr($source['title']); ?>" data-external-guid="<?php echo $source['external_guid']; ?>"  data-external-url="<?php echo $source['external_url']; ?>" data-content="<?php echo esc_attr($source['description']); ?>" data-image-url="<?php echo $source['image_url']; ?>" class="btn-save-source button button-primary" href="#"><?php _e( 'Save ' . $feed_type_label , 'tube-video-curator'); ?></a>
                    
                    <?php if ($feed_type == 'youtube-channel' ): ?>
                          
                          <label for="source_<?php echo esc_attr($source['external_guid']); ?>_autosync" style="display:block;margin-top:5px;">
                            <input type="checkbox" id="source_<?php echo esc_attr($source['external_guid']); ?>_autosync" name="source_<?php echo esc_attr($source['external_guid']); ?>_autosync" value="1" checked="checked" class="autosync-toggle"> 
                            <?php _e( 'Autosync&nbsp;new&nbsp;vids' , 'tube-video-curator'); ?>   
                          </label>
                    <?php endif; ?>
                    
              <?php endif; ?>
                  
                </div>
                
              </div>   <!-- .result -->
                  
          <?php endforeach; ?>  
          
        </div>
    
    
    <?php      
    }



  // Ajax wrapper for the save_source function
  function save_source_via_ajax(){ 
  
    $post_id = $this -> save_source( $_POST );
    
    if ( is_numeric($post_id) ):
      echo get_edit_post_link( $post_id );
    else:
      echo 'error';
    endif;
       
    die();
      
  }
  
  
  // Save a new Source (Channel or Playlist)
  function save_source( $args ){ 
          
    
      $title = $args['title'];
      
      $content = $args['content'];
      
      $external_guid = $args['external_guid'];
      
      $image_url = $args['image_url'];
      
      $external_url = $args['external_url'];
      
      $source_site = $args['site'];
      
      $feed_type = $args['feed_type'];
      
      $autosync = $args['autosync'];
      
      $excerpt = wp_trim_words( $content, 40 );
      
            
      // Creating new post
      $my_post = array(
        'post_title'    => $title,
        'post_content'  => $content,
        'post_excerpt'  => $excerpt,
        'post_status'   => 'publish',
        'post_type'     => 'tube_source'
      );
  
      $post_ID = wp_insert_post($my_post);
      //var_dump($post_ID);
      // updating post meta
      // if a media is detected, add its source url
      
      if($image_url){
        add_post_meta( $post_ID, 'tube_source_image_url', $image_url, true ) || update_post_meta( $post_ID, 'tube_source_image_url', $image_url ); 
      }

      add_post_meta( $post_ID, 'tube_source_external_url', $external_url, true );
      add_post_meta( $post_ID, 'tube_source_site', $source_site, true );
      add_post_meta( $post_ID, 'tube_source_external_guid', $external_guid, true );
      add_post_meta( $post_ID, 'tube_feed_type', $feed_type, true );
      
      if ($autosync) :
        add_post_meta( $post_ID, 'tube_source_autosync', 1, true );
      endif;
        
      // Create and upload thumbnail 
      $upload_dir = wp_upload_dir();
      
      // TODO: Check for 404
      $image_data = file_get_contents($image_url);
      $filename = $external_guid.basename($image_url);
      if(wp_mkdir_p($upload_dir['path']))
          $file = $upload_dir['path'] . '/' . $filename;
      else
          $file = $upload_dir['basedir'] . '/' . $filename;
      file_put_contents($file, $image_data);
  
      $wp_filetype = wp_check_filetype($filename, null );
      $attachment = array(
          'post_mime_type' => $wp_filetype['type'],
          'post_title' => sanitize_file_name($filename),
          'post_content' => '',
          'post_status' => 'inherit'
      );
      $attach_id = wp_insert_attachment( $attachment, $file, $post_ID );
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
      wp_update_attachment_metadata( $attach_id, $attach_data );
  
      set_post_thumbnail( $post_ID, $attach_id );
      
      return $post_ID;
      
  }
  
  
  // function to get a list of guids for all existing sources
  function get_external_source_ids( $feed_type ) {

    $args = array( 
      'posts_per_page' => -1,
      'post_type' => 'tube_source',
      'meta_query' => array(
        array(
          'key'     => 'tube_source_external_guid',
          'compare' => 'EXISTS',
        ),
        array(
          'key'     => 'tube_feed_type',
          'compare' => '=',
          'value' => $feed_type,
        ),
      ),
    );
    
    
    $posts_query = new WP_Query($args);
    
    // make sure we've got some results
    if ( ! $posts_query->have_posts() ):
      return NULL;
    endif;
    
    // placeholder for the IDs
    $source_ids = array();
      
    foreach ($posts_query->posts as $post):
      
      if( $post->tube_source_external_guid != "" ):    
          
          // add an edit (i.e. Find Videos) link for the Source
          $source_ids[$post->tube_source_external_guid]['edit'] = get_edit_post_link($post->ID);        
        
      endif;
           
    endforeach;
    
    return $source_ids;
    
  }
  
}