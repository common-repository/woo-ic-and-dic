(function ( $ ) {
	"use strict";

	$(function () {

		 jQuery('.wrap').on('click', '.set_custom_images', function(e) {
            e.preventDefault();
            var button = jQuery(this);
            var id = button.prev();
            wp.media.editor.send.attachment = function(props, attachment) {
                id.val(attachment.url);
            };
            wp.media.editor.open(button);
            return false;
      });
      
      //Remove carousel
      jQuery('.table-bordered').on('click', '.remove-carousel', function(){
         jQuery(this).closest('.carousel-item').remove();
      });
      
      
      //CZ carousel
      jQuery('#add-carousel-cz').on('click', function(){
         jQuery('#carousel-cz-table').append('<tr class="carousel-item"><td><input type="text" name="home-carousel-cz[]" value="" /><span class="set_custom_images btn">Add Image</span></td><td><span class="btn btn-info remove-carousel">Remove</span></td></tr>');
      });
      //EN carousel
      jQuery('#add-carousel-en').on('click', function(){
         jQuery('#carousel-en-table').append('<tr class="carousel-item"><td><input type="text" name="home-carousel-en[]" value="" /><span class="set_custom_images btn">Add Image</span></td><td><span class="btn btn-info remove-carousel">Remove</span></td></tr>');
      });
      //DE carousel
      jQuery('#add-carousel-de').on('click', function(){
         jQuery('#carousel-de-table').append('<tr class="carousel-item"><td><input type="text" name="home-carousel-de[]" value="" /><span class="set_custom_images btn">Add Image</span></td><td><span class="btn btn-info remove-carousel">Remove</span></td></tr>');
      });
      //PL carousel
      jQuery('#add-carousel-pl').on('click', function(){
         jQuery('#carousel-pl-table').append('<tr class="carousel-item"><td><input type="text" name="home-carousel-pl[]" value="" /><span class="set_custom_images btn">Add Image</span></td><td><span class="btn btn-info remove-carousel">Remove</span></td></tr>');
      });
      //SK carousel
      jQuery('#add-carousel-sk').on('click', function(){
         jQuery('#carousel-sk-table').append('<tr class="carousel-item"><td><input type="text" name="home-carousel-sk[]" value="" /><span class="set_custom_images btn">Add Image</span></td><td><span class="btn btn-info remove-carousel">Remove</span></td></tr>');
      });
      
      

	});

}(jQuery));