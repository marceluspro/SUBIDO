( function($, woosa){

   if ( ! woosa ) {
      return;
   }

   var Ajax = woosa.ajax;
   var Translation = woosa.translation;
   var Prefix = woosa.prefix;

   var moduleWorker = {

      init: function(){

         this.render();

      },


      /**
       * Displays the status on product table column.
       */
      render: function(){

         //at initial page load
         $('[data-'+Prefix+'-worker-action]').each(function(){
            moduleWorker.insert_template($(this));
         });
      },


      /**
       * Inserts the status HTML to the given element
       *
       * @param {object} elem
       */
      insert_template: function(elem){

         var worker_action = JSON.parse(elem.attr('data-'+Prefix+'-worker-action'));

         $.ajax({
            url: Ajax.url,
            method: "POST",
            data: {
               action: Prefix+'_render_total_worker_action_tasks',
               security: Ajax.nonce,
               worker_action: worker_action,
            },
            success: function(res) {

               if(res.success){
                  elem.html(res.data.template);
               }

            },
         });

      }

   };

   $( document ).ready( function() {
      moduleWorker.init();
   });


})( jQuery, mkt_module_core );