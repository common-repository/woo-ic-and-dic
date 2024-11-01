(function ( $ ) {

	"use strict";

	$(function () {
  
        var country = jQuery('body #billing_country').val();
       
        if(jQuery('body #billing_company_buy_on').is(":checked")) {
    
            var country = jQuery('body #billing_country').val();
    
            if(country == 'CZ' || country == 'SK'){
                jQuery('#billing_company_number_field').css('display','block');
                jQuery('#billing_vat_number_field').css('display','block');
            }  
            if(country == 'SK'){
                //jQuery('#billing_vat_number_2_field').css('display','block');
            }else{
                jQuery('#billing_vat_number_2_field').css('display','none');
            } 
        }else{
            jQuery('#billing_vat_number_2_field').css('display','none');
        }   
    
        jQuery( 'body' ).bind( 'country_to_state_changing', function( event, country, wrapper ){
    
            if(country == 'CZ'){
                jQuery('#billing_company_buy_on_field').slideDown( 'slow' );
                //jQuery('#billing_vat_number_2_field').slideUp( 'slow' );
                jQuery('#billing_vat_number_2').val( '' );               
            } else {  
                if(country == 'SK'){
                    if(jQuery('body #billing_company_buy_on').is(":checked")) {
                        jQuery('#billing_company_buy_on_field').slideDown( 'slow' );
                        jQuery('#billing_vat_number_2_field').slideDown( 'slow' );
                    }else{
                        jQuery('#billing_company_buy_on_field').slideDown( 'slow' );
                    }
                } else{
                    jQuery('#billing_company_buy_on_field').slideUp( 'slow' );
                    jQuery('#billing_vat_number_2_field').slideUp( 'slow' );  
                } 
            }
          
        }); 
    

		
        jQuery('body').on('change','#billing_company_buy_on',function(){         
            var country = jQuery('body #billing_country').val();
            if(jQuery('body #billing_company_buy_on').is(":checked")) {
    
                jQuery('#billing_company_number_field').slideDown( 'slow' );
                jQuery('#billing_vat_number_field').slideDown( 'slow' );
                
                if( country == 'SK' ) { 
                    jQuery('#billing_vat_number_2_field').slideDown( 'slow' );
                } 
     
            }else{
        
                jQuery('#billing_company_number_field').slideUp( 'slow' );
                jQuery('#billing_vat_number_field').slideUp( 'slow' );
                jQuery('#billing_vat_number_2_field').slideUp( 'slow' );                
        
            }
        
        });

	});

}(jQuery));