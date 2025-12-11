( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;

   var moduleAuthorization = {

      init: function(){

         //prevent the window which says "the changes may be lost"
         $(document).on('load click change', function(){
            window.onbeforeunload = null;
         });

         this.process_authorization();
      },

      process_authorization: function(){

         $(document).on('click', '[data-'+woosa.prefix+'-authorization-action]', this, function(e) {
            e.data.run_ajax($(this));
         });
      },

      run_ajax: function(button){

         var btn_parent    = button.parent(),
            form           = button.closest('form'),
            section        = button.closest('.field-section'),
            auth_action    = button.attr('data-'+woosa.prefix+'-authorization-action'),
            section_fields = section.find('select, textarea, input, button');
            form_fields    = form.find('select, textarea, input, button');

         $.ajax({
            url: Ajax.url,
            method: "POST",
            data: {
               action: woosa.prefix+'_process_authorization',
               security: Ajax.nonce,
               auth_action: auth_action,
               fields: section_fields.serialize(),
            },
            beforeSend: function(){

               form_fields.prop('disabled', true);

               button.data('label', button.text()).text(Translation.processing);

               jQuery('#wpcontent').block({
                  message: null,
                  overlayCSS: {
                     background: '#fff',
                     opacity: 0.6
                  }
               });

               form_fields.find('.ajax-response').remove();
            },
            success: function(res) {

               if(res.success){

                  if(res.data && res.data.redirect_url){
                     window.location.href = res.data.redirect_url;
                  }else{
                     window.location.reload();
                  }

               }else{

                  let error_msg = res.data && res.data.message ? res.data.message : 'An error occurred.';

                  $('<p class="ajax-response error">'+error_msg+'</p>').insertAfter(btn_parent);

                  button.text( button.data('label') );

                  form_fields.prop('disabled', false);

                  jQuery('#wpcontent').unblock();
               }

            }
         });

      },

      JSON_parse: function(str){
         try{
            return JSON.parse(str);
         }catch (e){
            return str;
         }
      },

   };

   $( document ).ready( function() {
      moduleAuthorization.init();
   });


})( jQuery, mkt_module_core );