( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Util = woosa.util;
   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var moduleOrderDetails = {

      init: function(){

         this.handle_open_popup();
         this.handle_submit_popup();
      },

      interval: null,

      handle_open_popup: function(){

         var target = 'data-'+Prefix+'-open-popup-order';

         $(document).on('click', '['+target+']', this, function(event){
            var _this = $(event.target),
               data   = JSON.parse(_this.attr(target)),
               url    = Ajax.url+'?action='+Prefix+'_order_details_handle_open_popup&security='+Ajax.nonce+'&order_id='+data.order_id+'&width=1000&height=700';

            tb_show(data.popup_title, url);

            event.data.interval = setInterval(function(){
               jQuery( window ).trigger( 'resize' )
            }, 30);
         });

         jQuery(window).on('resize', this, function(event){
            Util.resize_tb(event.data.interval);
         });

      },


      handle_submit_popup: function(){

         var target = 'data-'+Prefix+'-submit-popup';

         $(document).on('click', '[data-'+Prefix+'-popup="module_order_details"] ['+target+']', this, function(event){
            var _this     = $(event.target),
               mode       = _this.attr(target);
               section    = _this.closest('[data-'+Prefix+'-popup="module_order_details"]'),
               input      = section.find('select, textarea, input, button'),
               btn_parent = _this.parent();

            if( ! window.confirm('Are you sure you want to perform this action?') ) {
               return;
            }

            $.ajax({
               url: Ajax.url,
               method: "POST",
               data: {
                  action: Prefix + '_order_details_handle_' + mode,
                  security: Ajax.nonce,
                  fields: input.serialize(),
               },
               beforeSend: function(){

                  section.block({
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

                     if(res.data.reload_page){

                        location.reload();

                     }else if(res.data.template){

                        _this.closest('#TB_ajaxContent').html(res.data.template);
                     }

                  }else{
                     $('<p class="ajax-response error">'+res.data.message+'</p>').insertAfter(btn_parent);
                  }

                  section.unblock();

                  section.find(':input').not('.always_disabled').prop('disabled', false);

                  _this.text( _this.data('label') );
               }
            });

         });

      },

   };

   $( document ).ready( function() {
      moduleOrderDetails.init();
   });


})( jQuery, mkt_module_core );