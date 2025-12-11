( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;

   var moduleCategorySelection = {

      init: function(){

         this.load_items();
         this.search_items();
      },

      load_items: function(){

         let target = 'data-'+woosa.prefix+'-cs-load-items';

         $(document).on('click', '['+target+']', this, function(event){

            event.preventDefault();

            let _this  = $(event.target),
               box     = _this.closest('[data-'+woosa.prefix+'-cs-box]'),
               source  = box.attr('data-'+woosa.prefix+'-cs-box'),
               level   = box.attr('data-'+woosa.prefix+'-cs-level'),
               list    = box.find('[data-'+woosa.prefix+'-cs-list]'),
               trail   = box.find('[data-'+woosa.prefix+'-cs-trail]'),
               input   = box.find('[data-'+woosa.prefix+'-cs-input]'),
               search  = box.find('[data-'+woosa.prefix+'-cs-search]'),
               item_id = _this.attr(target);

            _this.attr('disabled', true);

            input.val('');

            box.block({
               message: null,
               overlayCSS: {
                  background: '#fff',
                  opacity: 0.6
               }
            });

            event.data.get_template(item_id, source, level).then(function(res){

               if(res.success && res.data.list){

                  search.show();

                  _this.hide();

                  trail.html(res.data.trail);

                  list.html(res.data.list).show();

                  if(res.data.last || _this.hasClass('cs-select-item')){
                     list.hide();
                     input.val(item_id);
                     search.hide().find('input').val('');

                     $(document).trigger(woosa.prefix + '_cs_item_selected', {item_id, wrapperElem: box});
                  }

               }else{
                  _this.show();
               }

               _this.attr('disabled', false);

               box.unblock();
            });

         });

      },

      search_items: function(){

         let debounceTimeout;
         let previousValue = '';

         $('[data-'+woosa.prefix+'-cs-search] input[type="text"]').on("keyup", function(){

            let _this  = $(this),
               box     = _this.closest('[data-'+woosa.prefix+'-cs-box]'),
               list    = box.find('[data-'+woosa.prefix+'-cs-list]'),
               source  = box.attr('data-'+woosa.prefix+'-cs-box'),
               level   = box.attr('data-'+woosa.prefix+'-cs-level');

            clearTimeout(debounceTimeout);

            debounceTimeout = setTimeout(function(){
               let currentValue = _this.val();
               if (currentValue !== previousValue) {
                  previousValue = currentValue;

                  $.ajax({
                     url: Ajax.url,
                     method: "POST",
                     data: {
                        action: woosa.prefix+'_cs_search_items',
                        security: Ajax.nonce,
                        source: source,
                        level: level,
                        search: currentValue,
                     },
                     beforeSend: function(){

                        box.block({
                           message: null,
                           overlayCSS: {
                              background: '#fff',
                              opacity: 0.6
                           }
                        });

                        _this.attr('disabled', true);
                     },
                     success: function(res) {

                        list.html(res.data.template).show();

                        _this.attr('disabled', false).focus();

                        box.unblock();
                     }
                  });
               }
            }, 500);
         });

      },


      get_template:function(item_id = 0, source, level){

         return $.ajax({
            url: Ajax.url,
            method: "POST",
            data: {
               action: woosa.prefix+'_cs_load_items',
               security: Ajax.nonce,
               item_id,
               source,
               level,
            },
         });

      },

   };

   $( document ).ready( function() {
      moduleCategorySelection.init();
   });


})( jQuery, mkt_module_category_selection );