
      
!function ($) {       
  

    function tube_vc_set_add_via_url_hint( $query_option ){

        $hint = $('#url-hint');

        switch( $query_option ) {
          case 'youtube':
              $hint.html( Tube_VC_Ajax_Add_Via_Url.trans.hintYouTube );        
              break;
          case 'vimeo':
              $hint.html( Tube_VC_Ajax_Add_Via_Url.trans.hintVimeo );        
              break;
          case 'twitch':
              $hint.html( Tube_VC_Ajax_Add_Via_Url.trans.hintTwitch );        
              break;
          default:
              break;
      }
          
    }
    
  $(function(){         // document ready   
    
    // Adds custom hints to the query box in the "Add via URL" page
    
       $('select[name="tube_vc_add_via_url_site"]').on('change', function(event) {                       
         
         tube_vc_set_add_via_url_hint( $(this).val() );  
          
      });
      
      
      if ( $('#tube_vc_add_via_url_site').length ){
        current_site = $('#tube_vc_add_via_url_site').val();
      }else{
        current_site = $('select[name="tube_vc_add_via_url_site"]').val();
      };
      
      
      tube_vc_set_add_via_url_hint( current_site );       
    
  });
}(window.jQuery);

// Placeholder for the translation strings
var Tube_VC_Ajax_Add_Via_Url = {
    trans: {},
};