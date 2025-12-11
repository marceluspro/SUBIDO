( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var moduleProductColumnStatus = {

      init: function(){

         this.render_table_column();

      },


      /**
       * Displays the status on product table column.
       */
      render_table_column: function(){

         //at initial page load
         $('[data-'+Prefix+'-product-table-column-status]').each(function(){
            moduleProductColumnStatus.render_table_column_status($(this));
         });

         //at quick edit action
         $(document).on('click', '.row-actions .editinline', function(e){

            var _this   = $(this),
               row_id   = _this.closest('tr').attr('id'),
               interval = setInterval(function(){

               if($('#'+row_id).is(':visible')){
                  $('#'+row_id).find('[data-'+Prefix+'-product-table-column-status]').each(function(){
                     moduleProductColumnStatus.render_table_column_status($(this));
                  });
                  clearInterval(interval);
               }

            }, 500);

         });
      },


      /**
       * Inserts the status HTML to the given element
       *
       * @param {object} elem
       */
      render_table_column_status: function(elem){

         var args = JSON.parse(elem.attr('data-'+Prefix+'-product-table-column-status'));

         $.ajax({
            url: Ajax.url,
            method: "POST",
            data: {
               action: Prefix+'_render_product_table_column_status',
               security: Ajax.nonce,
               args: args,
            },
            success: function(res) {

               if(res.success){
                  elem.html(res.data.template);

                  elem.find('.woocommerce-help-tip').tipTip({
                     'attribute': 'data-tip',
                     'fadeIn':    50,
                     'fadeOut':   50,
                     'delay':     200
                  });
               }

            },
         });

      },
   };

   $( document ).ready( function() {
      moduleProductColumnStatus.init();
   });


})( jQuery, mkt_module_core );