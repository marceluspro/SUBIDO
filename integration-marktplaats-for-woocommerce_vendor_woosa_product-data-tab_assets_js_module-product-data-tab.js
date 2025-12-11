( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var moduleProductDataTab = {

      init: function(){

         this.render();
         this.render_errors();
         this.refresh();
         this.toggle_items();
         this.load_page();

      },


      /**
       * Displays the panel content according to product type
       */
      render: function(){

         var variable_visibility = $( '.'+Prefix+'_variable_tab' ).is(":visible");

         $('#product-type').on('change', this, function(e){

            var product_type = $(this).val();

            if ( product_type === 'simple' ) {

               e.data.render_content( Prefix + '_simple' );

            } else {

               if ( product_type === 'variable' && variable_visibility ) {
                  e.data.render_content( Prefix + '_variable' );
               } else {
                  e.data.render_content( null );
               }

            }

         });

      },


      /**
       * Displays the panel content.
       *
       * @param {string} tab_key
       * @param {string} load_page
       */
      render_content: function(tab_key, load_page = ''){

         let product_id  = $('#post_ID').val(),
            product_type = $('#product-type').val(),
            page         = '' === load_page ? $('#'+Prefix+'-current-page').val() : load_page,
            tab          = product_type == 'variable' ? $('#'+Prefix+'_variable_data') : $('#'+Prefix+'_simple_data');

         if(product_type != 'variable' && product_type != 'simple') return;

         return $.ajax({
            url: woosa.ajax.url,
            method: "POST",
            data: {
               action: Prefix+'_render_product_data_panel',
               security: woosa.ajax.nonce,
               product_id,
               tab_key,
               page,
            },
            beforeSend: function(){
               $('#wpcontent').block({
                  message: null,
                  overlayCSS: {
                     background: '#fff',
                     opacity: 0.6
                  }
               });
            },
            success: function(res) {
               if(res.success){
                  tab.html(res.data.html);
               }
            },
            complete: function(){

               $('#wpcontent').unblock();

               $('#'+Prefix+'_simple_data .woocommerce-help-tip','#'+Prefix+'_variable_data .woocommerce-help-tip').tipTip({
                  'attribute': 'data-tip',
                  'fadeIn':    50,
                  'fadeOut':   50,
                  'delay':     200
               });
            }
         });
      },


      /**
       * Displays the errors.
       */
      render_errors: function(){

         let elems = $('[data-'+Prefix+'-error-args]'),
            args = [];

         if(elems.length > 0){

            elems.each(function(index, item){
               let data = JSON.parse($(item).attr('data-'+Prefix+'-error-args'));
               args.push(data);
            });

            $.ajax({
               url: woosa.ajax.url,
               method: "POST",
               data: {
                  action: Prefix+'_render_product_data_panel_errors',
                  security: woosa.ajax.nonce,
                  args: args,
               },
               success: function(res) {

                  if(res.success){

                     let entries = Object.entries(res.data.errors);

                     entries.forEach(function(item){

                        let elem = $('[data-'+Prefix+'-error-tab="'+item[0]+'"]');

                        if(elem.length > 0){
                           elem.html(item[1]);
                        }
                     });
                  }

               },
            });
         }
      },


      /**
       * Shows/hides the product variations.
       */
      toggle_items: function(){

         $(document).on('click', '.'+Prefix+'-variation__name', function(){

            let _this = $(this),
               content = _this.closest('.'+Prefix+'-variation').find('.'+Prefix+'-variation__content');

            _this.toggleClass('active');

            $('.'+Prefix+'-variation__content').not(content).slideUp().closest($('.'+Prefix+'-variation')).find($('.'+Prefix+'-variation__name')).removeClass('active');
            content.slideToggle();
         });
      },


      /**
       * Refreshes the data panel content.
       */
      refresh: function(){

         $(document).on('click', '#'+Prefix+'-refresh-action', this, function(e){

            let btn     = $(this),
               _this     = e.data,
               panel      = btn.closest('[data-'+Prefix+'-tabkey]'),
               page       = parseInt(panel.find('[data-'+Prefix+'-current-page]').val()),
               tab_key    = panel.attr('data-'+Prefix+'-tabkey'),
               render_tpl = _this.render_content(tab_key, page);

            btn.attr('disabled', true);

            render_tpl.then(function(){
               _this.render_errors();
            });
         });
      },


      /**
       * Loads next/prev variations page.
       */
      load_page: function(){

         $(document).on('click', '.'+Prefix+'-load-variations-page', this, function(e){

            let btn  = $(this),
               _this     = e.data,
               panel   = btn.closest('[data-'+Prefix+'-tabkey]'),
               page    = parseInt(panel.find('[data-'+Prefix+'-current-page]').val()),
               tab_key = panel.attr('data-'+Prefix+'-tabkey'),
               mode    = btn.attr('data-load-page');

            page = 'prev' === mode ? page - 1 : page + 1;

            btn.attr('disabled', true);

            let render_tpl = _this.render_content(tab_key, page);

            render_tpl.then(function(){
               _this.render_errors();
            });

         });
      },
   };

   $( document ).ready( function() {
      moduleProductDataTab.init();
   });


})( jQuery, mkt_module_core );