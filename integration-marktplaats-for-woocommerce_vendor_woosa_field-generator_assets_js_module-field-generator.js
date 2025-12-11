( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var moduleFieldGenerator = {

      init: function(){

         this.init_select2();
         this.init_quill();
         this.init_shipping_rules();
         this.init_color_picker();
         this.init_media_uploader();
      },


      /**
       * Init the select2.
       */
      init_select2(){
         if(jQuery.fn.select2){
            $('[data-'+Prefix+'-select2="yes"]').select2();
         }
      },


      /**
       * Init the Quill editor.
       */
      init_quill: function(){

         if (typeof Quill !== 'undefined') {

            $('[data-'+Prefix+'-editor-input]').each(function(){

               let _this    = $(this),
                  source_id = _this.attr('data-'+Prefix+'-editor-input'),
                  textarea  = $('[data-'+Prefix+'-editor-value="'+source_id+'"]');

               let editor = new Quill(_this.get(0), {
                  modules: {
                     toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }]
                     ]
                  },
                  theme: 'snow'  // or 'bubble'
               });

               var editor_value = editor.root.innerHTML;

               if('' === editor.getText().trim()){
                  editor_value = '';
               }

               textarea.html(editor_value);

               editor.on('text-change', function() {

                  var editor_value = editor.root.innerHTML;

                  if('' === editor.getText().trim()){
                     editor_value = '';
                  }

                  textarea.html(editor_value);
               });
            });
         }

      },

      init_shipping_rules: function(){

         //add rule
         $(document).on('click', '[data-'+Prefix+'-add-shipping-rule]', function(){
            let _this = $(this),
               rule_data = JSON.parse(_this.attr('data-'+Prefix+'-add-shipping-rule')),
               rules_elem = _this.closest('[data-'+Prefix+'-rule-list]').find('[data-'+Prefix+'-shipping-rules]'),
               rule_elems = rules_elem.find('> tr[data-'+Prefix+'-shipping-rule]'),
               rule_template = rule_data.rule_template;

            if(rule_elems.length == 15){
               alert(wp.i18n.__('Sorry, you cannot add more than 15 shipping cost rules.', 'integration-marktplaats-for-woocommerce'));
            }else{

               rule_template = rule_template.replaceAll('__index__', rule_elems.length);
               rule_template = rule_template.replaceAll('__number__', rule_elems.length + 1);

               if(rule_elems.length > 0){
                  rules_elem.append(rule_template);
               }else{
                  rules_elem.html(rule_template);
               }
            }
         });

         //remove rule
         $(document).on('click', '[data-'+Prefix+'-remove-shipping-rule]', function(){

            let _this = $(this),
               rules_elem = _this.closest('[data-'+Prefix+'-rule-list]').find('[data-'+Prefix+'-shipping-rules]');

            _this.closest('tr').remove();

            if(rules_elem.find('> tr[data-'+Prefix+'-shipping-rule]').length == 0){
               rules_elem.html('<tr><td colspan="5">' + wp.i18n.__('No rules available.', 'integration-marktplaats-for-woocommerce') +'</td></tr>');
            }else{
               rules_elem.find('> tr[data-'+Prefix+'-shipping-rule]').each(function(index){
                  $(this).find('td:first').text(index + 1);

                  $(this).find('input').each(function() {
                  const name = $(this).attr('name');
                  if (name) {
                     const newName = name.replace(/\[rules]\[\d+]/, `[rules][${index}]`);
                     $(this).attr('name', newName);
                  }
               });
               });
            }
         });
      },

      init_color_picker: function(){
         $('[data-' + Prefix + '-colorpicker]').wpColorPicker();
      },

      init_media_uploader: function(){

         let wkMedia;
         $('[data-'+Prefix+'-media-file-selector]').click(function(e) {
            e.preventDefault();
            // If the upload object has already been created, reopen the dialog
            if (wkMedia) {
               wkMedia.open();
               return;
            }

            let field_id = $(this).attr('data-'+Prefix+'-media-file-selector');

            wkMedia = wp.media.frames.file_frame = wp.media({
               title: 'Select media',
               button: {
               text: 'Select media'
            }, multiple: false });

            // When a file is selected, grab the URL and set it as the text field's value
            wkMedia.on('select', function() {
               let attachment = wkMedia.state().get('selection').first().toJSON();

               $('#' + field_id).val(attachment.url);

               $('#' + field_id + '_preview').html('<img src="'+attachment.url+'" style="max-width:100px; max-height: 150px;" />');
            });

            wkMedia.open();
         });
      },

   };

   woosa.init_select2 = function(){
      moduleFieldGenerator.init_select2()
   };
   woosa.init_quill = function(){
      moduleFieldGenerator.init_quill()
   };
   woosa.init_color_picker = function(){
      moduleFieldGenerator.init_color_picker()
   };

   $( document ).ready( function() {
      moduleFieldGenerator.init();
   });


})( jQuery, mkt_module_field_generator );