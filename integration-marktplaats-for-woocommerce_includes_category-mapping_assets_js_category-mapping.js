( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var categoryMapping = {

      init: function(){

         this.init_category_cpc();
      },


      init_category_cpc: function(){

         $(document).on(Prefix + '_cm_popup_opened', function(){

            var cpc_field = $('.'+Prefix + '_cpc_field');

            cpc_field.ionRangeSlider();
         });
      }

   };

   $( document ).ready( function() {
      categoryMapping.init();
   });


})( jQuery, mkt_module_core );