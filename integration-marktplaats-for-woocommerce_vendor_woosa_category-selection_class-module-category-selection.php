<?php
/**
 * Module Category Selection
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Module_Category_Selection{

   const SHOW_ITEMS_LIMIT = 100;


   /**
    * The level of selection. Allows only the leaf or the entire tree.
    *
    * @var string
    */
   public $level;


   /**
    * Source of items.
    *
    * @var string
    */
   public $source;


   /**
    * List of shop items.
    *
    * @var array
    */
   public $shop_items = [];


   /**
    * List of service items.
    *
    * @var array
    */
   public $service_items = [];



   /**
    * Construct of the class
    *
    * @param string $source
    * @param string $level - leaf|tree
    */
   public function __construct($source = 'service', $level = 'leaf'){

      $this->source = $source;
      $this->level = apply_filters(PREFIX . '\module\category_seletion\level', $level, $this);
   }



   /**
    * Retrieves the template for displaying the items.
    *
    * @param array $items
    * @return string
    */
   public function get_list_template($items){

      $path = \dirname(__FILE__).'/templates/list.php';
      $incl_path = DIR_PATH.'/includes/category-selection/templates/list.php';

      if(file_exists($incl_path)){
         $path = $incl_path;
      }

      $html = Util::get_template($path, [
         'items' => $items,
         'level' => $this->level,
      ]);

      return $html;
   }



   /**
    * Retrieves the template for displaying the trail.
    *
    * @param int $item_id
    * @return string
    */
   public function get_trail_template($item_id){

      $results = ['0' => '<span class="dashicons dashicons-admin-home"></span>'] + $this->search_parent_item($item_id, $this->get_items());

      $path = \dirname(__FILE__).'/templates/trail.php';
      $incl_path = DIR_PATH.'/includes/category-selection/templates/trail.php';

      if(file_exists($incl_path)){
         $path = $incl_path;
      }

      $html = Util::get_template($path, [
         'items' => $results
      ]);

      return $html;
   }



   /**
    * Retrieves all items.
    *
    * @return array
    */
   public function get_items(){

      $results = [];

      switch($this->source){

         case 'shop':

            $results = $this->get_shop_items();

            break;

         case 'service':

            $results = $this->get_service_items();

            break;
      }

      return $results;
   }



   /**
    * Retrieves items of a given parent.
    *
    * @param int $item_id
    * @return array
    */
   public function get_subitems($item_id){

      $results = $this->get_items();
      $items = [];

      foreach($results as $result){

         $parent_id = $result['parent_id'];

         //make sure the type is the same if they are empty
         if(empty($parent_id) && empty($item_id)){
            $parent_id = 0;
            $item_id   = 0;
         }

         //make sure the type is integer if they are numeric
         $parent_id = is_numeric($parent_id) ? (int) $parent_id : $parent_id;
         $item_id   = is_numeric($item_id) ? (int) $item_id : $item_id;

         if($parent_id === $item_id){
            $items[] = Util::obj_to_arr($result);
         }
      }

      return $items;
   }



   /**
    * Search for the rest of parents for a given item parent id.
    *
    * @param int $parent_id
    * @param array $items - the list of items
    * @param array $parents
    * @return array
    */
   public function search_parent_item($parent_id, $items, $parents = [], $depth = 0){

      //Prevent infinite recursion
      if ($depth > 100) {
         return array_reverse($parents, true);
      }

      foreach($items as $item){
         if($item['id'] == $parent_id){
            $parents[$item['id']] = $item['name'];
            return $this->search_parent_item($item['parent_id'], $items, $parents, $depth + 1);
         }
      }

      return array_reverse($parents, true);
   }



   /**
    * Retrieves shop items (categories).
    *
    * @return array
    */
   public function get_shop_items(){

      if(empty($this->shop_items)){

         $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
         ]);

         foreach($terms as $term){
            $this->shop_items[] = [
               'id'        => $term->term_id,
               'name'      => $term->name,
               'parent_id' => $term->parent,
            ];
         }

      }

      return $this->shop_items;
   }



   /**
    * Retrieves service items (categories).
    *
    * @return array
    */
   public function get_service_items(){

      if(empty($this->service_items)){
         $this->service_items = apply_filters_deprecated(PREFIX . '\category_selection\service_items', [ $this->service_items ], '1.3.0', PREFIX . '\module\category_selection\service_items');
         $this->service_items = apply_filters(PREFIX . '\module\category_selection\service_items', $this->service_items);
      }

      return $this->service_items;
   }



   /**
    * Displays the selection.
    *
    * @param string $source
    * @param string $level
    * @return string
    */
   public static function render($source, $level){

      echo Util::get_template('general.php', [
         'mcs' => new self($source, $level),
      ], dirname(dirname(__FILE__)), '/category-selection/templates/select');

   }



   /**
    * Displays the selection on product.
    *
    * @param string $source
    * @param string $level
    * @param Module_Meta $meta
    * @return string
    */
   public static function render_on_product($source, $level, $meta){

      echo Util::get_template('product.php', [
         'meta' => $meta,
         'mcs'  => new self($source, $level),
      ], dirname(dirname(__FILE__)), 'category-selection/templates/select');

   }
}
