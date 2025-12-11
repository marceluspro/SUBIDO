<?php
/**
 * Module Field Generator
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

class Module_Field_Generator {


   /**
    * The context in which the fields are rendered.
    *
    * @var string
    */
   protected $context = '';


   /**
    * List of fields.
    *
    * @var array
    */
   protected $fields = [];


   /**
    * The array name that each field will be added to.
    *
    * @var string
    */
   protected $array_name = '';



   /**
    * Sets the list of fields and the context.
    * [
    *    [
    *       'id'                => 'id_of_the_field',
    *       'title'             => 'label/title of the field',
    *       'name'              => 'label/title of the field',
    *       'type'              => 'text',
    *       'required'          => 1|0
    *       'options'           => [] //for `select`, `radio`
    *       'custom_attributes' => [],
    *    ]
    * ]
    * @param array $fields
    * @param string $context
    * @return void
    */
   public function set_fields( array $fields, string $context = '' ){

      $this->fields  = $fields;
      $this->context = $context;

   }



   /**
    * Sets the array name where the field to be add to.
    *
    * @param string $array_name
    * @return void
    */
   public function set_array_name(string $array_name){
      $this->array_name = $array_name;
   }



   /**
    * Retrieves the list of fields.
    *
    * @return array
    */
   public function get_fields(){
      return apply_filters(PREFIX . '\field_generator\fields', $this->fields, $this->context);
   }



   /**
    * Gets the context.
    *
    * @return string
    */
   public function get_context(){
      return $this->context;
   }



   /**
    * Helper function to get the formatted description for a
    * given form field.
    *
    * @param  array $field The field value array.
    * @return string The field description.
    */
   public function get_field_description( array $field ) {

      $description  = Util::array($field)->get('desc');

      if( ! empty($description) ){

         if ( $description && in_array( $field[ 'type' ], [ 'textarea', 'radio' ], true ) ) {
            $description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
         } elseif ( $description && in_array( $field['type'], [ 'checkbox' ], true ) ) {
            $description = wp_kses_post( $description );
         } elseif ( $description ) {
            $description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
         }
      }

      $description = apply_filters( PREFIX . '\field_generator\field_description', $description, $field, $this->context );

      return $description;

   }



   /**
    * Gets the field desc_tip.
    *
    * @param string $desc_tip
    * @return string
    */
   public function get_field_tooltip(string $desc_tip){

      if( ! empty($desc_tip) ){
         $desc_tip = '<span class="woocommerce-help-tip" tabindex="0" aria-label="' . esc_attr( wp_strip_all_tags( $desc_tip ) ) . '" data-tip="' . esc_attr( $desc_tip ) . '"></span>';
      }

      return $desc_tip;
   }



   /**
    * Gets the field id.
    *
    * @param string $id
    * @return string
    */
   public function get_field_id(string $id){

      if( ! empty($this->array_name) ){
         $id = md5($this->array_name) . '-' . $id;
      }

      return esc_attr($id);
   }



   /**
    * Gets the field name.
    *
    * @param string $name
    * @return string
    */
   public function get_field_name(string $name){

      if( ! empty($this->array_name) ){
         $name = "{$this->array_name}[$name]";
      }

      return esc_attr($name);
   }



   /**
    * Gets the field type.
    *
    * @param string $type
    * @return string
    */
   public function get_field_type(string $type){
      return esc_attr($type);
   }



   /**
    * Gets the field custom attributes.
    *
    * @param array $custom_attributes
    * @return string
    */
   public function get_field_custom_attributes(array $custom_attributes){

      $attrs = '';

      foreach ( $custom_attributes as $attribute => $attribute_value ) {
         $attrs .= esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
      }

      return $attrs;
   }



   /**
    * Check if the field global_unique_id is present (use WC version)
    *
    * @return bool
    */
   public function is_option_global_unique_id() {

      if (defined('WC_VERSION')) {

         if(version_compare( \WC_VERSION, '9.2', '<' )) {

            return false;

         } else {

            return true;

         }
      }

      return false;

   }



   /**
    * Displays the attribute responsible to show/hide the field conditionally.
    *
    * @param array $field
    * @return void
    */
   public function render_visibility(array $field){

      $show_if    = Util::array($field)->get('show_if');
      $attr_value = 'yes';
      $attr_id    = $show_if;

      if(is_array($show_if)){

         if(isset($show_if['id']) && isset($show_if['value'])){

            $attr_id      = $show_if['id'];
            $attr_value   = $show_if['value'];
            $parent_field = Util::array($this->fields)->get(array_search($attr_id, array_column($this->fields, 'id')));

            if(is_array($attr_value)){

               $attribute = [];

               foreach($attr_value as $value){
                  $attribute[] = "data-" . PREFIX . "-extra-field-{$this->get_field_id($attr_id)}--{$value}='{$value}' data-" . PREFIX . "-extra-field-values";
               }
               $attribute = implode(' ', $attribute) . " style=\"".Util::css_display(true, in_array(Util::array($parent_field)->get('value'), $attr_value))."\"";

            }else{

               $attribute = empty($attr_id) ? '' : "data-" . PREFIX . "-extra-field-{$this->get_field_id($attr_id)}='{$attr_value}' style=\"".Util::css_display($attr_value, Util::array($parent_field)->get('value'))."\"";
            }

            echo $attribute;
         }

      }else{

         $parent_field = Util::array($this->fields)->get(array_search($attr_id, array_column($this->fields, 'id')));
         $attribute    = empty($attr_id) ? '' : "data-" . PREFIX . "-extra-field-{$this->get_field_id($attr_id)}='{$attr_value}' style=\"".Util::css_display($attr_value, Util::array($parent_field)->get('value'))."\"";

         echo $attribute;
      }
   }



   /**
    * Set the default keys in the field before output it.
    *
    * @param array $field
    * @return array|false
    */
   protected function pre_process( array $field ) {

      $field = apply_filters( PREFIX . '\field_generator\pre_process\field', $field, $this->context );

      if(is_array($field)){

         if ( ! isset( $field['type'] ) ) {

            $field = false;

         }else{

            $id = Util::array($field)->get('id');

            $custom_attributes = Util::array($field)->get('custom_attributes', []);

            if(in_array($field['type'], ['select', 'checkbox', 'toggle'])){
               $custom_attributes['data-' . PREFIX . '-has-extra-field'] = $this->get_field_id($id);
            }

            $field['id']                = $id;
            $field['title']             = Util::array($field)->get('title', Util::array($field)->get('name'));
            $field['class']             = 'widefat ' . Util::array($field)->get('class');
            $field['css']               = Util::array($field)->get('css');
            $field['default']           = Util::array($field)->get('default');
            $field['desc']              = Util::array($field)->get('desc');
            $field['desc_tip']          = Util::array($field)->get('desc_tip', '');
            $field['placeholder']       = Util::array($field)->get('placeholder');
            $field['suffix']            = Util::array($field)->get('suffix');
            $field['required']          = Util::array($field)->get('required');
            $field['custom_attributes'] = $custom_attributes;
            $field['value']             = Util::array($field)->get('value', Option::get($field['id'], $field['default']));
         }

      }else{

         $field = false;
      }

      return $field;
   }



   /**
    * Displays the output of the given fields.
    * Loops through the array and outputs each field.
    *
    * @return void
    */
   public function render() {

      foreach ( $this->get_fields() as $field ) {

         $field = $this->pre_process( $field );
         $type  = Util::array($field)->get('type');

         if ( false === $field ) {
            continue;
         }

         $is_disabled = strpos($this->get_field_custom_attributes($field['custom_attributes']), 'disabled') !== false ? 'is-field-disabled' : '';

         switch ( $type ) {

            case 'title':

               $section_id = Util::unprefix( $this->get_field_id($field['id']) );

               echo '<div class="page-section ' . $section_id . '">';

               if ( ! empty( $field['title'] ) ) {
                  echo '<h2>' . esc_html( $field['title'] ) . '</h2>';
               }

               if ( ! empty( $field['desc'] ) ) {
                  echo '<div id="' . $section_id . '-description">';
                  echo wp_kses_post( wpautop( wptexturize( $field['desc'] ) ) );
                  echo '</div>';
               }

               echo '<div class="page-section-content ' . $section_id . '">';
               echo '<table class="form-table">';

               break;

            // Section Ends.
            case 'sectionend':
               echo '</table>';
               echo '</div>';//.field-section-content
               echo '</div>';//.page-section
               break;

            // Standard text inputs and subtypes like 'number'.
            case 'text':
            case 'password':
            case 'datetime':
            case 'datetime-local':
            case 'date':
            case 'month':
            case 'time':
            case 'week':
            case 'number':
            case 'email':
            case 'url':
            case 'tel':

               echo Util::get_template('text.php', [
                  'field'    => $field,
                  'is_disabled' => $is_disabled,
                  'instance' => $this,
               ], dirname(dirname(__FILE__)), 'field-generator/templates/fields');

               break;

            default:

               $output = Util::get_template("{$type}.php", [
                  'field'    => $field,
                  'is_disabled' => $is_disabled,
                  'instance' => $this,
               ], dirname(dirname(__FILE__)), 'field-generator/templates/fields');

               if(empty($output)){

                  do_action( PREFIX . '\field_generator\render\\' . $field['type'], $field, $this->context );

               }else{

                  echo $output;
               }
         }
      }

   }
}