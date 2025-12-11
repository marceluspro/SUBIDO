## Introduction

This module adds a section in the plugin settings called `Authorization` and comes with the following:

* It has a UI for inputting the authorization credentials and gives the option to grant or revoke the access

## Installation

* Run composer `require woosa/authorization:<version>` in the plugin's root directory. Alternatively, you can add `"woosa/authorization": "<version>"` directly in `composer.json` and then run `npm start`

## Usage

Example of how to hook on granting access action:

```php
add_filter(PREFIX . '\authorization\connect', 'my_connect_func');

function my_connect_func($output){

   $result = my_logic_here();

   if( ! $result ){

      $output = [
         'success' => false,
         'message' => 'Granting the access has failed',
      ];

   }

   return $output;
}
```

Example of how to hook on revoking access action:

```php
add_filter(PREFIX . '\authorization\disconnect', 'my_disconnect_func');

function my_disconnect_func($output){

   $result = my_logic_here();

   if( ! $result ){

      $output = [
         'success' => false,
         'message' => 'Revoking the access has failed',
      ];

   }

   return $output;
}
```

Example of how to use a redirect URL - useful to initiate OAuth process.

```php
add_action(PREFIX . '\module\authorization\before_process_authorization', 'initiate_oauth_process', 10, 2);

function initiate_oauth_process($action, $ma){

   if('authorize' !== $action){
      return;
   }

   $redirect_url = my_redirect_url_logic_here();

   if(! $redirect_url){
      wp_send_json_error([
         'message' => 'No URL found',
      ]);
   }

   wp_send_json_success([
      'redirect_url' => $redirect_url,
   ]);
}
```