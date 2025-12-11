( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var moduleSynchronization = {

      init: function(){

         this.save_sync_settings();
         this.import_products();

      },

      save_sync_settings: function(){

         $(document).on('click', '[data-' + Prefix + '_save_sync_settings]', function(e){

            var _this     = $(this),
               section    = _this.parent().parent().find('.' + Prefix + '-panel'),
               input      = section.find('select, textarea, input, button'),
               btn_parent = _this.parent();

            $.ajax({
               url: Ajax.url,
               method: "POST",
               data: {
                  action: Prefix + '_save_sync_settings',
                  security: Ajax.nonce,
                  fields: input.serialize(),
               },
               beforeSend: function(){

                  $('#wpcontent').block({
                     message: null,
                     overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                     }
                  });

                  btn_parent.next('.ajax-response').remove();

                  section.find(':input').prop('disabled', true);

                  _this.data('label', _this.text()).text(Translation.processing);

               },
               success: function(res) {

                  if(res.success){
                     // window.location.reload();
                  }else{
                     $('<p class="ajax-response error">'+res.data.message+'</p>').insertAfter(btn_parent);
                  }

                  $('#wpcontent').unblock();

                  section.find(':input').not('.always_disabled').prop('disabled', false);

                  _this.text( _this.data('label') );
               }
            });
         });
      },

      import_products: function(){

         $(document).on('click', '[data-' + Prefix + '_import_products]', function(e){

            var _this     = $(this),
               btn_parent = _this.parent();

            $.ajax({
               url: Ajax.url,
               method: "POST",
               data: {
                  action: Prefix + '_import_products',
                  security: Ajax.nonce
               },
               beforeSend: function(){

                  $('#wpcontent').block({
                     message: null,
                     overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                     }
                  });

                  btn_parent.next('.ajax-response').remove();

                  _this.prop('disabled', true);

                  _this.data('label', _this.text()).text(Translation.processing);

               },
               success: function(res) {

                  if(res.success){
                     // window.location.reload();
                  }else{
                     $('<p class="ajax-response error">'+res.data.message+'</p>').insertAfter(btn_parent);
                  }

                  $('#wpcontent').unblock();

               }
            });
         });

      }

   };

   $( document ).ready( function() {
      moduleSynchronization.init();
   });


})( jQuery, mkt_module_core );