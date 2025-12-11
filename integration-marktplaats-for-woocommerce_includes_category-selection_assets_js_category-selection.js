( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var categorySelection = {

      init: function(){

         this.init_product_cpc();
         this.show_product_cpc();
         this.toggle_cpc_total_budget()
      },

      init_product_cpc: function(){

         var cpc_field = $('.' + Prefix + '_product_cpc_field');

         cpc_field.ionRangeSlider();

      },

      show_product_cpc: function(){

         $(document).on(Prefix + '_cs_item_selected', function(event, params){

            var box = $(params.wrapperElem).closest('.options_group'),
               cpcElem = box.find('#' + Prefix + '_cpc_slider'),
               cpcTotalBudgetElem = box.find('#' + Prefix + '_cpc_total_budget_slider'),
               cpcTotalBudget = cpcTotalBudgetElem.data("ionRangeSlider"),
               cpc = cpcElem.data("ionRangeSlider");

            $.ajax({
               url: Ajax.url,
               method: "POST",
               data: {
                  action: woosa.prefix+'_render_product_cpc_field',
                  security: Ajax.nonce,
                  category_id: params.item_id,
               },
               beforeSend: function(){

                  cpcElem.closest('div').find('.ajax-response').remove();

                  box.block({
                     message: null,
                     overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                     }
                  });
               },
               success: function(res) {

                  if(res.success){

                     cpc.update({
                        disable: cpc.options.disable, //use current disable state
                        min: res.data.cpc.min,
                        max: res.data.cpc.max
                     });

                     cpcTotalBudget.update({
                        min: res.data.cpc_total_budget.min,
                        max: 500//res.data.cpc_total_budget.max
                     });

                  }else{

                     cpc.update({
                        disable: true,
                        from: 0
                     });

                     cpcElem.closest('div').append('<p class="ajax-response error p-0 m-0 mt-20">'+res.data.message+'</p>');
                  }

                  box.unblock();
               }
            });
         });
      },

      toggle_cpc_total_budget: function(){

         $(document).on('change', '[data-' +Prefix+ '-cpc-automatic]', function(){

            var _this = $(this),
               section = _this.closest('[data-' +Prefix+ '-cpc-field]'),
               cpc_cost = section.find('[data-' +Prefix+ '-cpc]').data('ionRangeSlider');

            if (_this.prop('checked')) {
               cpc_cost.update({
                  disable: true
               });

            }else{
               cpc_cost.update({
                  disable: false
               });
            }
         });
      },

   };

   $( document ).ready( function() {
      categorySelection.init();
   });


})( jQuery, mkt_module_core );