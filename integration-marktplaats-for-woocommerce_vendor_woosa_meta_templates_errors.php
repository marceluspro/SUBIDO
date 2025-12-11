<?php
/**
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


?>
<div class="<?php echo PREFIX;?>-style">
   <div class="mb-10 alertbox alertbox--red">
      <ul>
         <?php if ( is_array( $errors ) ) : ?>
            <?php foreach( $errors as $key => $value ) : ?>
               <li>
                  <?php
                  if(is_string($value)){
                     echo $value;
                  }else{
                     echo json_encode($value);
                  }
                  ?>
               </li>
            <?php endforeach; ?>
         <?php else : ?>
            <li>
               <?php
               if(is_string($errors)){
                  echo $errors;
               }else{
                  echo json_encode($errors);
               }
               ?>
            </li>
         <?php endif; ?>
      </ul>
   </div>
</div>