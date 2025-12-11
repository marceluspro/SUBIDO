( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var moduleCategoryMapping = {

      init: function(){

         this.toggle_connection();
         this.config_category();
         this.save_category_config();
         this.copy_category_config_toggle();
         this.copy_category_config();
      },

      toggle_connection: function(){

         let target = 'data-'+Prefix+'-cm-action';

         $(document).on('click', '['+target+']', function(event){

            event.preventDefault();

            let _this     = $(this),
               action     = _this.attr(target),
               data       = {
                  action  : Prefix+'_cm_'+action,
                  security: Ajax.nonce,
                  url     : window.location.href
               },
               cat        = [],
               trail      = [],
               term_id    = _this.closest('tr').attr('data-'+Prefix+'-cm-term-id'),
               cat_id     = _this.closest('tr').attr('data-'+Prefix+'-cm-category-id'),
               info_sec   = _this.closest('table').find('[data-'+Prefix+'-cm-info]'),
               box        = _this.closest('[data-'+Prefix+'-cm-box]');

            if('connect' === action){
               box.find('[data-'+Prefix+'-cs-input]').each(function(){
                  cat.push($(this).val());
               });
               box.find('[data-'+Prefix+'-cs-trail]').each(function(){
                  trail.push($(this).html());
               });

               Object.assign(data, {cat: cat, trail: trail});

            }else{

               Object.assign(data, {term_id: term_id, cat_id: cat_id});

               if( ! window.confirm('Are you sure you want to remove this category connection?') ) {
                  return;
               }
            }

            $.ajax({
               url: Ajax.url,
               method: "POST",
               data: data,
               beforeSend: function(){

                  _this.attr('disabled', true);

                  info_sec.find('.ajax-response').remove();

                  box.block({
                     message: null,
                     overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                     }
                  });
               },
               success: function(res){

                  if(res.success){

                     window.location.href = res.data.redirect_url;

                  }else if(res.data.message){

                     info_sec.append('<p class="ajax-response error">'+res.data.message+'</p>');

                     _this.attr('disabled', false);

                     box.unblock();

                  }

               },
            });

         });
      },

      config_category: function(){

         let target = 'data-'+Prefix+'-config-category';

         $(document).on('click', '['+target+']', function(event){

            event.preventDefault();

            let _this  = $(this),
               term_id = _this.closest('tr').attr('data-'+Prefix+'-cm-term-id'),
               category_id = _this.closest('tr').attr('data-'+Prefix+'-cm-category-id'),
               url     = Ajax.url+'?action='+Prefix+'_config_category'+'&security='+Ajax.nonce+'&term_id='+term_id+'&category_id='+category_id+'&width=1400&height=700';

            tb_show(Translation.config_category_title, url);

            let tb_loaded = setInterval(() => {

               if($('#' + Prefix + '-ajax-view-content').length > 0){

                  clearInterval(tb_loaded);

                  window.dispatchEvent(new Event('resize'));

                  $(document).trigger(Prefix + '_cm_popup_opened');
               }

            }, 300);

         });
      },


      /**
       * Sends request to save the category configuration.
       */
      save_category_config: function(){

         $(document).on('click', '[data-' + Prefix + '-save-category-config]', function(e){
            e.preventDefault();

            let btn = $(this),
               form = btn.closest('form'),
               fields = form.serialize();

            $.ajax({
               url: Ajax.url,
               method: "POST",
               data: {
                  action: Prefix + '_save_category_config',
                  security: Ajax.nonce,
                  fields: fields,
                  wc_category_id: btn.data(Prefix + '-save-category-config')
               },
               beforeSend: function(){
                  btn.data('label', btn.text()).text(Translation.btn.processing).prop('disabled', true);
                  form.find('[data-'+Prefix+'-ajax-response]').html('');
               },
               success: function(res) {

                  let res_class = 'text-color--error';

                  if(res.success){
                     res_class = 'text-color--success';
                  }

                  btn.closest('table').find('[data-'+Prefix+'-ajax-response]').html('<div class="'+res_class+'">'+res.data.message+'</div>');

               },
               complete: function(){
                  btn.text(btn.data('label')).prop('disabled', false);
               }
            });
         });
      },

      /**
       * Toggle show of the select copy category and copy button
       *
       * @since 1.2.0
       */
      copy_category_config_toggle: function () {
         $(document).on('click', '[data-' + Prefix + '-copy-category-config-toggle]', function(e){
            e.preventDefault();

            let displayingSelect = $(this).data('displaying-select');
            let categoryId = $(this).data(Prefix + '-copy-category-config-toggle');

            if (displayingSelect) {
               $(this).data('displaying-select', false);
               $('[data-' + Prefix + '-copy-term-config='+categoryId+']').hide();
               $('[data-' + Prefix + '-copy-category-config='+categoryId+']').hide();
            } else {
               $(this).data('displaying-select', true);
               $('[data-' + Prefix + '-copy-term-config='+categoryId+']').show();
               $('[data-' + Prefix + '-copy-category-config='+categoryId+']').show();
            }
         });
      },


      /**
       * Copy the config from selected category
       *
       * @since 1.2.0
       */
      copy_category_config: function () {
         $(document).on('click', '[data-' + Prefix + '-copy-category-config]', function(e){
            e.preventDefault();

            let btn = $(this),
               categoryId = $(this).data(Prefix + '-copy-category-config'),
               term_id = $(this).data(Prefix + '-term-id'),
               category_id = $(this).data(Prefix + '-category');

            let copyCategoryId = $('[data-' + Prefix + '-copy-term-config='+categoryId+']').val(),
               url     = Ajax.url+'?action='+Prefix+'_config_category'+'&security='+Ajax.nonce+'&term_id='+term_id+'&category_id='+category_id+'&width=1400&height=700';

            $.ajax({
               url: Ajax.url,
               method: "POST",
               data: {
                  action: Prefix + '_copy_category_config',
                  security: Ajax.nonce,
                  wc_category_id: categoryId,
                  copy_category: copyCategoryId
               },
               beforeSend: function(){
                  $('[data-' + Prefix + '-copy-term-config='+categoryId+']').prop('disabled', true);
                  $('[data-' + Prefix + '-copy-category-config='+categoryId+']').prop('disabled', true);
               },
               success: function(res) {

                  let res_class = 'text-color--error';

                  if(res.success){
                     res_class = 'text-color--success';

                     tb_remove();
                     let isShowPopup = true;
                     // reload frame
                     $( 'body' ).on( 'thickbox:removed', function () {
                        if (!isShowPopup) {
                           return;
                        }
                        tb_show(Translation.config_category_title, url);

                        let tb_loaded = setInterval(() => {

                           if($('#' + Prefix + '-ajax-view-content').length > 0){

                              clearInterval(tb_loaded);

                              window.dispatchEvent(new Event('resize'));

                              $(document).trigger(Prefix + '_cm_popup_opened');

                              btn.closest('table').find('[data-'+Prefix+'-ajax-response]').html('<div class="'+res_class+'">'+res.data.message+'</div>');
                              isShowPopup = false;
                           }

                        }, 300);
                     } );
                  }

                  btn.closest('table').find('[data-'+Prefix+'-ajax-response]').html('<div class="'+res_class+'">'+res.data.message+'</div>');
               },
               complete: function(){
                  $('[data-' + Prefix + '-copy-term-config='+categoryId+']').prop('disabled', false);
                  $('[data-' + Prefix + '-copy-category-config='+categoryId+']').prop('disabled', false);
               }
            });
         });
      },

   };

   $( document ).ready( function() {
      moduleCategoryMapping.init();
   });


})( jQuery, mkt_module_category_mapping );