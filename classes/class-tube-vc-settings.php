<?php
/**
 * Tube_VC_Settings
 * 
 * Create the main plugin Settings page
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Settings {
  
  public static $instance;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Settings();
      return self::$instance;
  }
  
  
  
  // Constructor  
  
  function __construct() {    
    
    // register the import settings
    add_action('admin_init', array( $this, 'register_plugin_import_settings') );  
    
    // register the display settings
    add_action('admin_init', array( $this, 'register_plugin_display_settings') );  

  }
  
 
  // loader function called from main tube-video-curator.php file
  function load_tube_vc_settings_scripts(){
    
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_scripts') );      
      
  }    
  
  
  // Register and enque JS associated with the Settings page    
  function enqueue_settings_scripts() {      
      
      global $tube_video_curator;
    
      // register the main settings page JS
      wp_register_script(
        'tube_vc_settings', 
        TUBE_VIDEO_CURATOR_URL . 'js/tube-vc-settings.js', 
        array(), 
        '1.0.0',
        //rand(50000, 150000), 
        false
      );
      
      // enque the main settings page JS
      wp_enqueue_script('tube_vc_settings');
      
      // additional scripts that get printed to the page
      add_action( 'admin_print_scripts', array(  $this, 'print_settings_scripts'), 99 );
      
  }
  
      
  // Additional scripts that get printed to the head
  function print_settings_scripts() {    
    ?>
    <script type="text/javascript">
    var pluginDirectory = '<?php echo plugins_url('', __FILE__); ?>';
    var Tube_VC_Ajax_Videos = {};
    Tube_VC_Ajax_Videos.currentCat = '<?php echo get_option( 'tube_vc_default_import_term', '' ); ?>';
    Tube_VC_Ajax_Videos.currentTagImportTax = '<?php echo get_option( 'tube_vc_default_tag_import_taxonomy', '' ); ?>';
    </script>    
    <?php
  }
  
  
  // Display the Settings page
  function render_options_page_settings(){    
   
    // get active tab, or default to youtube-api    
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'display';
    
    // clean the URL for use in tabs
    $url = remove_query_arg('settings-updated');
    
    // Set up the tabs data
    $tabs = array(
      'display' => array(
        'name' => __('Display', 'tube-video-curator'), 
        'url' => add_query_arg('tab', 'display', $url)
      ),
      'import' => array(
        'name' => __('Import', 'tube-video-curator'), 
        'url' => add_query_arg('tab', 'import', $url)
      ),
    )
    
  
    ?>
    <div class="wrap">
      
      <h1>.TUBE Video Curator Settings</h1>
      <?php settings_errors(); ?>
      
      <h2 class="nav-tab-wrapper">
        <?php foreach ( $tabs as $tab_slug => $tab ): ?>
            <a href="<?php echo $tab['url']; ?>" class="nav-tab <?php if ( $active_tab == $tab_slug ) echo 'nav-tab-active'; ?>"><?php echo $tab['name']; ?></a>
        <?php endforeach; ?>
      </h2>
      <form action='options.php' method='post' class="section-divider">
        
        
        <?php  
        switch ($active_tab) {
          
          case 'import': // IMPORT
          
            settings_fields( 'tube_vc_import_settings' );
            do_settings_sections( 'tube_vc_import_settings' );
            submit_button(__('Save Import Settings', 'tube-video-curator'));   
            break;
          
          default: // DISPLAY         
            
            settings_fields( 'tube_vc_display_settings' );
            do_settings_sections( 'tube_vc_display_settings' );
            submit_button(__('Save Display Settings', 'tube-video-curator'));
            break;
        }    
  
        ?>
        
        <?php
        ?>
        
      </form>
    
    </div> <!-- .wrap -->
    <?php
    //require_once(dirname(__FILE__) . '/page-import-options.php');
  
  }
  
  
  // Register the Import Settings, add sections, fields, etc  
  function register_plugin_import_settings() { //register our settings
    
    // register all of the settings
    register_setting( 'tube_vc_import_settings', 'tube_vc_default_import_post_type' );
    register_setting( 'tube_vc_import_settings', 'tube_vc_default_import_term' );
    register_setting( 'tube_vc_import_settings', 'tube_vc_default_tag_import_taxonomy' );
    register_setting( 'tube_vc_import_settings', 'tube_vc_default_import_author' );
    register_setting( 'tube_vc_import_settings', 'tube_vc_default_auto_import_status' );

    // Add the settings section
     add_settings_section(
      'tube_vc_import_settings_section', 
      __( 'Choose how videos get imported into your site.', 'tube-video-curator' ), 
      array( $this, 'show_import_settings_section'), 
      'tube_vc_import_settings'
    );
    
    // Default import post type    
    add_settings_field( 
      'tube_vc_default_import_post_type', 
      __( 'Default Post Type', 'tube-video-curator' ), 
      array( $this, 'render_import_post_type_setting'), 
      'tube_vc_import_settings', 
      'tube_vc_import_settings_section' 
    );
    
    // Default import author
    add_settings_field( 
      'tube_vc_default_import_author', 
      __( 'Default Author', 'tube-video-curator' ), 
      array( $this, 'render_import_author_setting'), 
      'tube_vc_import_settings', 
      'tube_vc_import_settings_section' 
    );
    
    // Default import "term"
    add_settings_field( 
      'tube_vc_default_import_term', 
      __( 'Default Term', 'tube-video-curator' ), 
      array( $this, 'render_default_import_term_setting'), 
      'tube_vc_import_settings', 
      'tube_vc_import_settings_section' 
    );
        
    // Default tag import taxonomy
    add_settings_field( 
      'tube_vc_default_tag_import_taxonomy', 
      __( 'Tag Import Taxonomy', 'tube-video-curator' ), 
      array( $this, 'render_default_tag_import_taxonomy_setting'), 
      'tube_vc_import_settings', 
      'tube_vc_import_settings_section' 
    );
    
    // Default auto import status (e.g. Publish, Draft)
    add_settings_field( 
      'tube_vc_default_auto_import_status', 
      __( 'Auto-import Status', 'tube-video-curator' ), 
      array( $this, 'render_auto_import_status_setting'), 
      'tube_vc_import_settings', 
      'tube_vc_import_settings_section' 
    );
      
  }

  
  // display the intro to the import section
  function show_import_settings_section() { 
    
    //echo __( 'Choose how videos get imported into your site.', 'tube-video-curator' );
    
  }
  
  
  // Show the post type setting field
  function render_import_post_type_setting(  ) {
    
    // get the import post type value
    $import_post_type = get_option( 'tube_vc_default_import_post_type' );
    
    // get the importable types
    $types = $this -> get_importable_post_types();
    
    if ( ! $types ) :
      _e('Sorry, there are no public post types that support post thumbnails.', 'tube-video-curator');
      return;
    endif;
    
    _e('The default post type for new posts will be ', 'tube-video-curator');
    
    if ( count($types) == 1 ):
       // only one so no need for selector
      
    
      list($post_type, $post_type_name) = each($types);
      
      ?>
      
      <strong><?php echo $post_type_name; ?></strong>.
      <input type="hidden" name="tube_vc_default_import_post_type" value="<?php echo $post_type; ?>" />
      
    <?php  else: ?>
      
      <select id="updateCatOnChange" name="tube_vc_default_import_post_type">
        <?php foreach ( $types as $post_type => $post_type_name ): ?>
          <option value="<?php echo $post_type; ?>" <?php selected( $import_post_type, $post_type ); ?> >
            <?php echo $post_type_name; ?>
          </option>        
        <?php endforeach; ?>
      </select>
    <!--
    <p class="description"><?php _e('If you select a custom post type, be sure it supports Post Thumbnails.', 'tube-

plugin') ?></p>
    -->    
          
  <?php  
    endif;
  }
  
  
  
  
  // Returns an array of post types that can accept video imports
  
  function get_importable_post_types(){
        
    // get all public post types
    $post_types = get_post_types( array('public'   => true), 'objects' );
    
    // container for importable types
    $importable_post_types = array();
    
    // loop through the types
    foreach ($post_types as $type):
       
      // ensure thumbnail support
      if( ! post_type_supports( $type->name, 'thumbnail' ) ):
        continue;
      endif;
      
      // ignore pages, attachments, and skipped videos         
      if( $type->name === 'page' || $type->name === 'attachment' ):
        continue;
      endif;              
      
      // add the type and name to importable post types     
      $importable_post_types[$type->name] = $type->labels->singular_name;
     
    endforeach;
    
    // make sure there were importable types
    if ( count($importable_post_types) == 0 ):
     return NULL;
    endif;
     
    // return the importable types
    return $importable_post_types;
 
  }
  


  
  // Show the import author setting field
  function render_import_author_setting(  ) {
         
    // get the import post type value, set to YouTube if NULL  
    
    $import_author = intval(get_option( 'tube_vc_default_import_author'));
    
    
    $authors = $this -> get_importable_authors();
     
    
    if ( ! $authors ) :
      _e('Sorry, there are no authors to import into.', 'tube-video-curator');
      return;
    endif;
    
    _e('The default author for new posts will be ', 'tube-video-curator');
        
    if ( count($authors) == 1 ):
      
       // only one so no need for selector
      list($author_id, $author_name) = each($authors);      
      ?>
      
      <strong><?php echo $author_name; ?></strong>.
      <input type="hidden" name="tube_vc_default_import_author" value="<?php echo $author_id; ?>" />
      
    <?php  else: ?>
      
      <select name="tube_vc_default_import_author" >
        <?php foreach ($authors as $author_id => $author_name): ?>
          <option value="<?php echo $author_id; ?>" <?php if( $import_author == $author_id){ echo 'selected'; } ?> >
            <?php echo $author_name; ?>    
          </option>
        <?php endforeach; ?>
      </select>
         
    <?php endif;
    
  }
  
  
  
  
  
  // Returns an array of post types that can accept video imports
  
  function get_importable_authors(){
    
    $args = array( 
      'who' => 'authors', 
      'fields' => array( 'ID', 'display_name' ) 
    );    
    
    // get all public post types
    $authors = get_users( $args );
    
    // container for importable types
    $importable_authors = array();
    
    // loop through the types
    foreach ($authors as $author):
      
      // add the type and name to importable post types     
      $importable_authors[$author->ID] = $author->display_name;
     
    endforeach;
    
    // make sure there were importable types
    if ( count($importable_authors) == 0 ):
     return NULL;
    endif;
     
    // return the importable types
    return $importable_authors;
 
  }
  
    
  
  // Show the import term setting field
  function render_default_import_term_setting( ) {        
    
    global $tube_video_curator;
    
    
    // get the default import post term ID
    $import_term = intval( get_option( 'tube_vc_default_import_term') );
    
    // get the default import post type slug
    $import_post_type = get_option( 'tube_vc_default_import_post_type' );
    
    // make sure there's a post type
    if ( !$import_post_type ):
       return;
    endif;
    
    // get all possible terms for that post type
    $terms = $tube_video_curator -> get_all_terms_for_tube_video_post_type( $import_post_type );
    
    
    // Sorry, no terms
    if ( ! $terms ) :
      _e( 'Sorry, there are no terms to import into.', 'tube-video-curator' );
      return;
    endif;
    
    // help text for default term selector
    _e( 'The default term added to new posts will be ', 'tube-video-curator' );

    if ( count($terms) == 1 ):
      
      ?>
      
      <strong><?php echo esc_html( $terms[0] -> name ); ?></strong> <?php _e( 'from the taxonomy ', 'tube-video-curator' ); ?> <?php echo esc_html( $terms[0] -> taxonomy_name ); ?>.
      
      <input type="hidden" name="tube_vc_default_import_term" value="<?php echo intval($terms[0] -> id); ?>" />
      
    <?php  else: 
      
      $this -> show_importable_terms_dropdown( 'catsSelect', 'tube_vc_default_import_term', $terms, $import_term );
      
      
    endif;
  }
  
  
  // Show the dropdown of terms used in the import term setting
  function show_importable_terms_dropdown( $id, $name, $terms, $curr_term ) {
  ?>
      
      <select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" >
        
          <option value="" <?php selected( 0, $curr_term ); ?> >
           <?php esc_html_e( 'None', 'tube-video-curator' ); ?>    
          </option>
            
        <?php 
        
        $prev_taxonomy = NULL;
        
        foreach ($terms as $term): 
          
          $term_id = $term -> id;
          
          $taxonomy = $term -> taxonomy_slug;
          
          if ( $prev_taxonomy != $taxonomy ):
          
            if ( $prev_taxonomy != NULL ):
              ?></optgroup><?php
            endif;
            
            ?><optgroup label="<?php echo esc_attr( $term -> taxonomy_name ); ?> "><?php
          
          endif;
          ?>
            <option value="<?php echo $term_id; ?>" <?php selected( $term_id, $curr_term ); ?> >
             <?php echo esc_html( $term -> name ); ?>    
            </option>          
          <?php 
        
          $prev_taxonomy = $taxonomy;
          
        endforeach; 
        
        ?>
        </optgroup>
      </select>  
         
    <?php

  }
  
  
  // Show the default tag import taxonomy setting field
  function render_default_tag_import_taxonomy_setting(  ) {        
    
    global $tube_video_curator;
    
    
    // get the default taxonomy
    $import_taxonomy = get_option( 'tube_vc_default_tag_import_taxonomy');
    

    
    // get the default import post type slug
    $import_post_type = get_option( 'tube_vc_default_import_post_type' );
    
    // make sure there's a post type
    if ( !$import_post_type ):
       return;
    endif;
    
    // get all possible taxonomies for that post type  
    $taxonomies = $tube_video_curator -> get_all_taxonomies_for_tube_video_post_type( $import_post_type );
            
    // make sure there are taxonomies, or do nothing
    if ( ! $taxonomies || ( 0 == count($taxonomies) ) ):
      
      _e( 'Sorry, there are no taxonomies to import into.', 'tube-video-curator' );
      return;
      
    endif;
    
    //unset( $taxonomies[0] );
    
    // help text for default term selector
    _e( 'If the video has tags, the tags will be imported into the taxonomy ', 'tube-video-curator' );

     ?>
      
      <select id="tube_vc_default_tag_import_taxonomy" name="tube_vc_default_tag_import_taxonomy" >
        
          <option value="" <?php selected( false, $import_taxonomy ); ?> >
           <?php esc_html_e( 'None (Do NOT import tags)', 'tube-video-curator' ); ?>    
          </option>
            
        <?php 
        
        foreach ($taxonomies as $taxonomy): 
          
          $taxonomy_slug = $taxonomy -> taxonomy_slug;
          
          $taxonomy_name = $taxonomy -> taxonomy_name;
          ?>
            <option value="<?php echo $taxonomy_slug; ?>" <?php selected( $taxonomy_slug, $import_taxonomy ); ?> >
             <?php echo esc_html( $taxonomy_name); ?>    
            </option>          
          <?php 
          
        endforeach; 
        
        ?>
      </select> 
         
    <?php
    
  }
  
  
  
  // Show the auto import status setting field
  function render_auto_import_status_setting(  ) {
         
    // get the import post type value, set to YouTube if NULL  
    
    $auto_import_status = get_option( 'tube_vc_default_auto_import_status');
    
    $statuses = $this -> get_importable_statuses();
     
    
    if ( ! $statuses ) :
      _e('Sorry, there are no statuses to choose from. WordPress will use &#8220;draft&#8221; by default.', 'tube-video-curator');
      return;
    endif;
    
    _e('The default status for &#8220;auto-imported&#8221; posts will be ', 'tube-video-curator');
    
    if ( count($statuses) == 1 ):
      
      // only one so no need for selector   
      list($auto_import_status_id, $auto_import_status_name) = each($statuses);
      ?>
      
      <strong><?php echo $auto_import_status_name; ?></strong>.
      
      <input type="hidden" name="tube_vc_default_auto_import_status" value="<?php echo $auto_import_status_id; ?>" />
      
    <?php  else: ?>
      
      <select name="tube_vc_default_auto_import_status" >
        <?php foreach ($statuses as $auto_import_status_id => $auto_import_status_name): ?>
          <option value="<?php echo $auto_import_status_id; ?>" <?php if( $auto_import_status == $auto_import_status_id){ echo 'selected'; } ?> 

>
            <?php echo $auto_import_status_name; ?>    
          </option>
        <?php endforeach; ?>
      </select>  
         
    <?php
    endif;
  }
  
  
  // Returns an array of status that can be used for auto imports  
  function get_importable_statuses(){
    
    // get all statuses
    $statuses = get_post_statuses( );
    
    // allow statuses to be filtered
    $statuses = apply_filters( 'tube_vc_filter_importable_statuses', $statuses );    
     
    // return the importable statuses
    return $statuses;
 
  }
  
  
  // Register Display Settings, sections, fields, etc  
  function register_plugin_display_settings() {
    
    // Register all the settings
    register_setting( 'tube_vc_display_settings', 'tube_vc_video_placement' );  
    register_setting( 'tube_vc_display_settings', 'tube_vc_player_autoplay' );   
    register_setting( 'tube_vc_display_settings', 'tube_vc_player_related' );   
    register_setting( 'tube_vc_display_settings', 'tube_vc_player_showinfo' );   
    register_setting( 'tube_vc_display_settings', 'tube_vc_player_autohide' );   
    register_setting( 'tube_vc_display_settings', 'tube_vc_player_controls' );  
    register_setting( 'tube_vc_display_settings', 'tube_vc_player_fullscreen' );  
    register_setting( 'tube_vc_display_settings', 'tube_vc_player_theme' );  
     
     // Add the display settings section
     add_settings_section(
      'tube_vc_display_settings_section', 
      __( 'Choose how videos get displayed on your site.', 'tube-video-curator' ), 
      array( $this, 'show_display_settings_section'), 
      'tube_vc_display_settings'
    );
    
     // Add the display placement field   
    add_settings_field( 
      'display_placement', 
      __( 'Video Placement', 'tube-video-curator' ), 
      array( $this, 'render_display_placement_setting'), 
      'tube_vc_display_settings', 
      'tube_vc_display_settings_section' 
    );
    
    // Add the player options field
    add_settings_field( 
      'player_options', 
      __( 'Player Options', 'tube-video-curator' ), 
      array( $this, 'render_player_options_setting'), 
      'tube_vc_display_settings', 
      'tube_vc_display_settings_section' 
    );
        
      
  }
  
  // display the intro to the display section
  function show_display_settings_section() { 
    
    //echo __( 'Choose how videos get displayed on your site.', 'tube-video-curator' );
    
  }
                    
  
  // Show the display placement setting field        
  function render_display_placement_setting(  ) {
    
    // get the import display placement value
    $video_placement_setting = get_option( 'tube_vc_video_placement' );
    
    // get the placements
    $placements = $this -> get_video_placement_options();
    ?>
    <fieldset>
    <?php 
    
    $last = count($placements);
    $count = 0;
    
    foreach ( $placements as $video_placement => $video_placement_name ): 
      
      $count++;
      ?>
      
      <label for="tube_vc_video_placement_<?php echo $video_placement; ?>">
        <input type="radio" id="tube_vc_video_placement_<?php echo $video_placement; ?>" 

name="tube_vc_video_placement" value="<?php echo $video_placement; ?>" <?php checked( 

$video_placement_setting, $video_placement ); ?> /> <?php echo $video_placement_name; ?>
      </label>
        
      <?php       
      if ( $count != $last ) echo '<br />';
      
    endforeach; 
    ?>
    </fieldset>        
    
    <?php if ( array_key_exists( 'none', $placements )  ): ?>
      <p class="description">
        <?php _e('If you choose none, you can use the shortcode <code>[tube_video]</code> to include the video in your 
  
  posts.', 'tube-video-curator') ?>
      </p>
    <?php endif; ?>
    
    <p class="description">
      <?php _e('Please see the documentation for more advanced examples and template hooks.', 'tube-video-curator') ?>
    </p>
 
  <?php  
  }
  
          
          
  // Returns an array of video placement options  
  function get_video_placement_options(){
    
    $placements = array(
      'above' => __('<strong>Above:</strong> Insert video above the post content', 'tube-video-curator'),
      'below' => __('<strong>Below:</strong> Insert video below the post content', 'tube-video-curator'),
      'none' => __('<strong>None:</strong> Do not auto-insert videos into posts', 'tube-video-curator'),
    );      
      
    $placements = apply_filters( 'tube_vc_filter_video_placement_options', $placements );
    
    // return the importable categories
    return $placements;
 
  }
  
  
  
  
   
  // Show the player options setting field            
  function render_player_options_setting(  ) {
        
    // get the player_options
    $player_options = $this -> get_player_options();
    
    ?>
      <fieldset>
      <?php 
      
      $last = count($player_options);
      $count = 0;
      
      foreach ( $player_options as $player_option => $player_option_name ): 
        
        $player_option_setting = get_option( $player_option ); 
        
        $count++;
        ?>
        
        <label for="<?php echo $player_option; ?>">
          <input type="checkbox" id="<?php echo $player_option; ?>" name="<?php echo $player_option; ?>" value="1" 

<?php checked( $player_option_setting, true ); ?> /> <?php echo $player_option_name; ?>
        </label>
          
        <?php       
        if ( $count != $last ) echo '<br />';
        
      endforeach; 
      ?>
      </fieldset>   
      <p class="description">
        <?php _e('Please note that some options may not apply to all video sources.', 'tube-video-curator') ?>
      </p>   
      <p class="description">
        <?php 
        echo sprintf( 
          __( 'Here is a complete list of supported parameters for <a href="%1$s" target="_blank">YouTube</a> and <a href="%2$s" target="_blank">Vimeo</a>.', 'tube-video-curator' ), 
          esc_url( 'https://developers.google.com/youtube/player_parameters#Parameters' ), 
          esc_url( 'https://developer.vimeo.com/player/embedding' ) 
        ); 
        ?>
         &nbsp; 
        <button class="settings-help-toggle">
          <i class="dashicons dashicons-editor-help"></i> <?php _e('Using Extra Params', 'tube-plugin') ?>          
        </button>
      </p>   
      <div class="card settings-help hidden">
        <p>
          <?php _e('If you are familiar with PHP, you can add or modify parameters using the <code>tube_vc_filter_oembed_args</code> filter in your theme&rsquo;s functions.php file.', 'tube-video-curator') ?>  
        </p>
        <p>
          <?php _e('Here&rsquo;s an example:', 'tube-video-curator') ?> 
        </p>
        <pre><code>function my_custom_oembed_args( $args ) {
  $args['loop'] = 1; // add parameter to loop video
  $args['iv_load_policy'] = 3; // add parameter to hide annotations
  return $args;
}

add_filter( 'tube_vc_filter_oembed_args', 'my_custom_oembed_args', 10, 1 );</code> </pre>

        <p>
          <?php _e('Additional source-specific filters are available as well:', 'tube-video-curator') ?><br />
          - <code>tube_vc_filter_youtube_oembed_args</code><br />
          - <code>tube_vc_filter_vimeo_oembed_args</code>          
        </p>

      </div>      
  <?php  
  }
  
  
  // Returns an array of player setting options  
  function get_player_options(){
    
    $player_settings = array(
      'tube_vc_player_autoplay' => __('<strong>Autoplay:</strong> Videos start automatically when the player loads', 'tube-video-curator' ),
      'tube_vc_player_showinfo' => __('<strong>Show Info:</strong> Display the video title &amp; other info in the player', 'tube-video-curator' ),
      'tube_vc_player_related' => __('<strong>Related:</strong> Show related videos when the video ends', 'tube-video-curator' ),
      'tube_vc_player_autohide' => __('<strong>Auto-hide:</strong> Video controls will &#8220;hide&#8221; after a video begins', 'tube-video-curator' ),
      'tube_vc_player_controls' => __('<strong>Controls:</strong> Show controls (play, pause, timeline, etc) in the player', 'tube-video-curator' ),
      'tube_vc_player_fullscreen' => __('<strong>Fullscreen:</strong> Allow the user to play the video fullscreen', 'tube-video-curator' )
    );      
      
    // return the importable categories
    return $player_settings;
 
  }
     
   
   
  // Set default values for the plugin settings
  function set_default_plugin_settings() {
    
    add_option('tube_vc_default_import_author', 1);
    
    add_option('tube_vc_default_import_post_type', 'post');
    
    add_option('tube_vc_default_auto_import_status', 'publish');
    
    add_option('tube_vc_video_placement', 'above');
    
    add_option('tube_vc_player_autoplay', 0);
    
    add_option('tube_vc_player_showinfo', 0);
    
    add_option('tube_vc_player_related', 1);
    
    add_option('tube_vc_player_autohide', 1);
    
    add_option('tube_vc_player_controls', 1); 
    
    add_option('tube_vc_player_fullscreen', 1);       
    
  }   
      
    
}