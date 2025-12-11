<?php
/**
 * Module Logger Table
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;

use WP_List_Table;

//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


if( ! class_exists( 'WP_List_Table' ) ) {
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * @property array _args
 * @property array _column_headers
 * @property array items
 * @method int get_pagenum()
 * @method void set_pagination_args()
 * @method void search_box()
 * @method string display()
 */
class Module_Logger_Table extends WP_List_Table {


   public function __construct(){

	   //Set parent defaults
	   parent::__construct( array(
         'singular'  => PREFIX . '-log',     //singular name of the listed records
	      'plural'    => PREFIX . '-logs',    //plural name of the listed records
	      'ajax'      => false        //does this table support ajax?
      ) );

   }



   /**
    * Retrieves the list of bulk actions available for this table.
    *
    * @return array
    */
   protected function get_bulk_actions() {

      $actions = array(
         'download' => __( 'Download' , 'integration-marktplaats-for-woocommerce'),
         'delete' => __( 'Delete' , 'integration-marktplaats-for-woocommerce'),
      );

      return $actions;

   }



   /**
    * The checkbox callback
    *
    * @param $item
    * @return string|void
    */
   function column_cb($item){

      return sprintf(
         '<input type="checkbox" name="%1$s[]" value="%2$s" />',
         /*$1%s*/ $this->_args['singular'],
         /*$2%s*/ $item['file']
      );

   }



   /**
    * Prepare the items for the table to process
    *
    * @return Void
    */
   public function prepare_items() {

      $columns = $this->get_columns();
      $hidden = $this->get_hidden_columns();
      $sortable = $this->get_sortable_columns();

      $data = $this->table_data();
      $data = $this->search($data);

      usort( $data, array( &$this, 'sort_data' ) );

      $perPage = 20;
      $currentPage = $this->get_pagenum();
      $totalItems = count($data);

      $this->set_pagination_args( array(
         'total_items' => $totalItems,
         'per_page'    => $perPage
      ) );

      $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

      $this->_column_headers = array($columns, $hidden, $sortable);
      $this->items = $data;

   }



   /**
    * Override the parent columns method. Defines the columns to use in your listing table
    *
    * @return array
    */
   public function get_columns() {

      $columns = array(
         'cb' => '<input type="checkbox">',
         'source' => __('Source', 'integration-marktplaats-for-woocommerce'),
         'file_date' => __('Date Created', 'integration-marktplaats-for-woocommerce'),
         'file_size' => __('File Size', 'integration-marktplaats-for-woocommerce'),
      );

      return $columns;

   }



   /**
    * Define which columns are hidden
    *
    * @return array
    */
   public function get_hidden_columns() {
      return [];
   }



   /**
    * Define the sortable columns
    *
    * @return array
    */
   public function get_sortable_columns() {

      return array(
         'source' => array('source', false),
         'file_date' => array('file_date', false),
         'file_size' => array('file_bytes', false),
      );

   }



   /**
    * Get the logs data
    *
    * @return array
    */
   public function table_data() {
      return Module_Logger::get_entries();
   }



   /**
    * Search the items
    *
    * @param array $data
    * @return array
    */
   public function search($data) {

      $search = '';

      if (isset($_REQUEST['s'])) {
         $search = $_REQUEST['s'];
      }

      if (!empty($search)) {

         $results = [];

         foreach ($data as $item) {
            if (preg_match('/'.$search.'/i', Util::array($item)->get('file_name'))) {
               $results[] = $item;
            }
         }

         return $results;
      }

      return $data;
   }



   /**
    * Define what data to show on each column of the table
    *
    * @param  array $item
    * @param  string $column_name
    *
    * @return Mixed
    */
   public function column_default( $item, $column_name ) {

      switch( $column_name ) {
         case 'source':
            return sprintf(
               '<a href="%s">%s</a>',
               add_query_arg([
                     'paged' => Util::array($_GET)->get('paged', 1),
                     'log_file' => base64_encode($item['file_name']),
                  ],
                  Module_Settings::get_tab_url(['slug' => Module_Logger_Hook_Settings::id()])
               ),
               $item[ $column_name ]
            );
         case 'file_date':
            return date( 'Y-m-d', $item[ $column_name ]);
         case 'file_size':
            return $item[ $column_name ];

         default:
            return print_r( $item, true ) ;
      }

   }



   /**
    * Allows you to sort the data by the variables set in the $_GET
    *
    * @return Mixed
    */
   private function sort_data( $a, $b ) {

      $orderby = 'file_date';
      $order = 'desc';

      if(!empty($_GET['orderby'])) {
         $orderby = $_GET['orderby'];
      }

      if(!empty($_GET['order'])) {
         $order = $_GET['order'];
      }

      if($order === 'asc')  {
         return ($a[$orderby] < $b[$orderby]) ? -1 : 1;
      }

      return ($a[$orderby] > $b[$orderby]) ? -1 : 1;

   }

}
