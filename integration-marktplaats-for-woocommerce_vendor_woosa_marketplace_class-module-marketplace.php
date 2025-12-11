<?php
/**
 * Module Marketplace
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Marketplace{


   /**
    * Gets the list of service accounts.
    *
    * @return array
    */
   public static function get_accounts(){
      return apply_filters(PREFIX . '\module\marketplace\service_accounts', []);
   }



   /**
    * When the fulfilment is by marketplace then the stock is considered already reduced.
    *
    * @param array $meta_data
    * @return boolean
    */
   public static function is_stock_already_reduced(array $meta_data){
      return in_array(Util::array($meta_data)->get('fulfilment_method'), [
         'FBB', //Bol
         'fulfilled_by_kaufland', //Kaufland
      ]);
   }
}