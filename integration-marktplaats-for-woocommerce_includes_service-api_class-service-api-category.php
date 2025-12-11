<?php
/**
 * Service API Category
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;

use Rakit\Validation\Validator;

//prevent direct access data leaks
defined('ABSPATH') || exit;


class Service_API_Category extends Service_API {


   /**
    * The API resource.
    *
    * @var string
    */
   protected $resource = 'category';



   /**
    * Retrieves the category.
    *
    * @param string $category_id
    * @return array
    */
   public function get(string $category_id){

      $response = $this->send_request("category/{$category_id}", [], 'GET');

      if(200 == $response->status){
         return Util::obj_to_arr($response->body);
      }

      return [];
   }



   /**
    * Retrieves the category configs.
    *
    * @param string $category_id
    * @return array
    */
   public function get_config(string $category_id){

      $result = [
         'cpc' => [
            'min' => 0,
            'max' => 0,
         ],
         'cpc_total_budget' => [
            'min' => 0,
            'max' => 0,
         ],
      ];

      if(empty($category_id)){
         return $result;
      }

      $category = $this->get($category_id);

      if($category){
         $result = [
            'cpc' => $this->format_cpc(Util::array($category)->get('config/bidMicros')),
            'cpc_total_budget' => $this->format_cpc(Util::array($category)->get('config/totalBudgetMicros')),
         ];
      }

      return $result;
   }



   /**
    * Formats cost per click values.
    *
    * @param string $micros
    * @return array
    */
   public function format_cpc(string $micros){

      $cpc = explode(',', str_replace(['[', ']'], '', $micros));

      return [
         'min' => number_format($cpc[0]/1000000, 2, '.', ''), //micro
         'max' => number_format($cpc[1]/1000000, 2, '.', ''), //micro
      ];
   }



   /**
    * Retrieves a list of categories.
    *
    * @throws \Exception
    * @return array
    */
   public function get_list(){

      $results = array_filter((array) Transient::get('category_list'));
      $query_params = [
         'levels' => '3',
      ];

      if(!empty($results)){
         return $results;
      }

      $response = $this->send_request("/category/0", [], 'GET', [
         'cache' => false,
         'query_params' => $query_params,
      ]);

      if(200 == $response->status){

         $result = Util::obj_to_arr($response->body);
         $children = Util::array($result)->get('children');
         $results = $this->get_children($children);

         //cache the results
         Transient::set('category_list', $results, \MONTH_IN_SECONDS);
      }

      return $results;
   }



   /**
    * Retrieves the category children.
    *
    * @param array $categories
    * @return array
    */
   private function get_children($categories){

      $results = [];

      foreach($categories as $child){
         $results[] = [
            'id'       => Util::array($child)->get('id'),
            'parent_id' => Util::array($child)->get('parentId'),
            'level'    => Util::array($child)->get('level'),
            'name'    => Util::array($child)->get('label/nl_NL'),
            'status'   => Util::array($child)->get('status'),
            'config'   => array_filter([
               'bidMicros'         => Util::array($child)->get('config/bidMicros'),
               'totalBudgetMicros' => Util::array($child)->get('config/totalBudgetMicros'),
               'dailyBudgetMicros' => Util::array($child)->get('config/dailyBudgetMicros'),
               'titleLength'       => Util::array($child)->get('config/titleLength'),
               'descriptionLength' => Util::array($child)->get('config/descriptionLength'),
            ]),
         ];

         if(!empty($child['children'])){
            $results = array_merge($results, $this->get_children($child['children']));
         }
      }

      return $results;
   }
}