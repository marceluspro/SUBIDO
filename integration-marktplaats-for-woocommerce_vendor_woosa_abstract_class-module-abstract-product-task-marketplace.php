<?php
/**
 * Module Abstract Product Task Marketplace
 *
 * This is dedicated for processing a shop product to the marketplace entity.
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


/**
 * @property string $id
 * @property string $offer_id
 * @property string $account_id
 * @property array $account
 * @property string $type
 * @property Module_Meta $meta
 * @property string $ean
 * @property string $reference
 * @property string $on_hold
 * @property string $price
 * @property int|null $stock
 * @property string $condition
 * @property string $force_pause
 */
abstract class Module_Abstract_Product_Task_Marketplace{


   /**
    * Throw exception code for general
    */
   const GENERAL_ERROR_CODE = 10;


   /**
    * Throw exception code for account
    */
   const ACCOUNT_ERROR_CODE = 20;


   /**
    * The product id in shop.
    *
    * @var string
    */
   protected string $id;


   /**
    * The offer (e.g. product, unit, etc) id in markeplace.
    *
    * @var string|null
    */
   protected ?string $offer_id;


   /**
    * The Marketplace account id.
    *
    * @var string|null
    */
   protected ?string $account_id;


   /**
    * The Marketplace account.
    *
    * @var array
    */
   protected array $account;


   /**
    * The product type in shop.
    *
    * @var string|null
    */
   protected ?string $type;


   /**
    * @var Module_Meta
    */
   protected Module_Meta $meta;


   /**
    * The product ean in shop.
    *
    * @var string|null
    */
   protected ?string $ean;


   /**
    * The product reference.
    *
    * @var string|null
    */
   protected ?string $reference;


   /**
    * Whether or not the product is on-hold.
    *
    * @var string|null
    */
   protected ?string $on_hold;


   /**
    * The product price.
    *
    * @var string|null
    */
   protected ?string $price;


   /**
    * Whether or not the product stock is manageable.
    *
    * @var bool|null
    */
   protected ?bool $manage_stock;


   /**
    * The product stock.
    *
    * @var int|null
    */
   protected ?int $stock;


   /**
    * The product stock status.
    *
    * @var string|null
    */
   protected ?string $stock_status;


   /**
    * The product condition.
    *
    * @var string|null
    */
   protected ?string $condition;


   /**
    * Whether or not to force pausing the offer.
    *
    * @var string|null
    */
   protected ?string $force_pause;



   /**
    * Construct of this class.
    *
    * @param array $data
    * @param array $account
    * @throws \Exception
    */
   public function __construct(array $data, array $account = []) {

      $this->id         = Util::array($data)->get('id');
      $this->account_id = Util::array($account)->get('account_id');
      $this->account    = $account;

      $this->meta = new Module_Meta($this->id);
      $this->meta->set_account_id($this->account_id);

      $this->type         = Util::array($data)->get('type');
      $this->manage_stock = Util::array($data)->get('meta_data/_manage_stock');
      $this->stock        = Util::array($data)->get('meta_data/_stock');
      $this->stock_status = Util::array($data)->get('meta_data/_stock_status');
      $this->ean          = Util::array($data)->get('meta_data/' . Util::prefix('ean'));
      $this->reference    = Util::array($data)->get('meta_data/' . Util::prefix('reference'));
      $this->condition    = Util::array($data)->get('meta_data/' . Util::prefix('condition'));
      $this->offer_id     = Util::array($data)->get('meta_data/' . Util::prefix($this->account_id . '_product_id'));
      $this->on_hold      = Util::array($data)->get('meta_data/' . Util::prefix($this->account_id . '_on_hold'));
      $this->price        = Util::array($data)->get('meta_data/' . Util::prefix($this->account_id . '_price'));
      $this->force_pause  = Util::array($data)->get('custom_data/force_pause', 'no');
   }


   /**
    * List of supported actions.

    * @return array
    */
   public static function action_list() {

      return [
         'create_or_update_product',
         'pause_or_unpause_product',
         'delete_or_trash_product'
      ];
   }



   /**
    * Checks whether or not the shop product type is supported.
    *
    * @return boolean
    */
   public function is_supported_type(){

      $list = ['simple', 'variation'];

      return in_array($this->type, $list);
   }



   /**
    * Checks whether or not the shop product is valid.
    *
    * @return boolean
    */
   public function is_valid(){
      return $this->is_supported_type() && wc_get_product($this->id) instanceof \WC_Product;
   }



   /**
    * Checks whether or not the given shop product EAN/GTN belongs to an existing product in the marketplace.
    *
    * @return boolean
    */
   abstract public function is_product_in_catalog();



   /**
    * Checks whether or not the offer is paused.
    *
    * @return bool
    */
   public function is_on_hold(){
      return Util::string_to_bool($this->on_hold);
   }



   /**
    * Checks whether or not the offer pause is forced.
    *
    * @return bool
    */
   public function is_pause_forced(){
      return Util::string_to_bool($this->force_pause);
   }



   /**
    * Retrieves the shop product id.
    *
    * @return string|int
    */
   public function get_id() {
      return $this->id;
   }



   /**
    * Retrieves the marketplace product id.
    *
    * @return string|int
    */
   public function get_remote_id() {
      return $this->offer_id;
   }



   /**
    * Retrieves the shop product type.
    *
    * @return string
    */
   public function get_type() {
      return $this->type;
   }



   /**
    * Retrieves the price to be sent to the marketplace.
    *
    * @return string
    */
   abstract public function get_price();



   /**
    * Retrieves the stock to be sent to the marketplace.
    *
    * @return int
    */
   public function get_stock(){

      //set a default in case is not enabled
      if(!$this->manage_stock){
         $this->stock = 99;
      }

      //fix in case for some reasons the stock is below 0
      if($this->stock < 0){
         $this->stock = 0;
      }

      $preserve_stock_offset = (int) Option::get('preserve_stock_offset', 0);

      if($this->stock > $preserve_stock_offset){
         $this->stock = (int) $this->stock - $preserve_stock_offset;
      }

      return (int) $this->stock;
   }



   /**
    * Retrieves the status to be sent to the marketplace.
    *
    * @return string
    */
   abstract public function get_status();



   /**
    * Handles the exception errors.
    *
    * @param \Exception $e
    * @return void
    */
   public function handle_errors(\Exception $e){

      //set status error always per account
      $this->meta->set_account_id($this->account_id);
      $this->meta->set_status('error');

      //remove account id to set the errors as general
      if(self::GENERAL_ERROR_CODE == $e->getCode()){
         $this->meta->remove_account_id();
      }

      //set account id to set the errors per account
      if(self::ACCOUNT_ERROR_CODE == $e->getCode()){
         $this->meta->set_account_id($this->account_id);
      }

      $errors = Util::is_json($e->getMessage()) ? json_decode($e->getMessage()) : $e->getMessage();

      if(is_array($errors)){

         foreach($errors as $error){
            $this->meta->set_error($error);
         }

      }else{
         $this->meta->set_error($errors);
      }

      $this->meta->save();
   }



   /**
    * Creates the marketplace product.
    *
    * @return void
    */
   abstract public function create();



   /**
    * Updates the marketplace product.
    *
    * @return void
    */
   abstract public function update();



   /**
    * Deletes the marketplace product.
    *
    * @return void
    */
   abstract public function delete();

}