<?php
/**
 * Util File
 *
 * @author Woosa Team
 */

namespace Woosa\Marktplaats;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Util_File{


   /**
    * Checks the status of a given remote URL
    *
    * @param string $url
    * @return int
    */
   public static function check_remote_status( $url ) {

      $ch = curl_init( $url );

      curl_setopt( $ch, CURLOPT_NOBODY, true );
      curl_exec( $ch );
      $code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
      curl_close( $ch );

      return $code;

   }



   /**
    * Retrieves the size of a remote file.
    *
    * @param string $url
    * @return false|int
    */
   public static function get_remote_filesize( string $url ) {

      $size = false;

      if ( function_exists( 'curl_init' ) ) {

         $ch = curl_init( $url );
         curl_setopt_array( $ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => Module_Core::config('plugin.name'),
         ]);

         curl_exec( $ch );

         $errno    = curl_errno( $ch );
         $error    = curl_error( $ch );
         $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

         if ( $errno ) {
            Util::log()->error( [
               'message' => 'cURL error when checking remote filesize: ' . $error,
               'url'     => $url
            ], __FILE__, __LINE__ );
         } elseif ( $http_code < 400 ) {
            $size = curl_getinfo( $ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
         }

         curl_close( $ch );
      }

      if ( ! $size && ini_get( 'allow_url_fopen' ) ) {

         $headers = @get_headers( $url, 1 );
         if ( $headers && isset( $headers['Content-Length'] ) ) {
            if ( is_array( $headers['Content-Length'] ) ) {
               // If there were redirects, pick the last one
               $size = end( $headers['Content-Length'] );
            } else {
               $size = $headers['Content-Length'];
            }
         }
      }

      if ( $size && is_numeric( $size ) ) {
         return (int) $size;
      }

      return false;
   }



   /**
    * Downloads a remote file locally.
    *
    * @param string $url - remote url
    * @param string $local_file - local file path
    * @return array
    */
   public static function remote_download( $url, $local_file ) {

      $status = self::check_remote_status( $url );

      if ( $status >= 400 ) {
         return [
            'status'  => 'error',
            'message' => 'The remote file cannot be accessed, please try again. Status: ' . $status . '. File url: ' . $url,
         ];
      }

      if ( function_exists( 'curl_init' ) ) {

         $fileHandle = fopen( $local_file, 'wb' );

         if ( ! $fileHandle ) {
            return [
               'status'  => 'error',
               'message' => 'Unable to open file for writing at the following location: ' . $local_file,
            ];
         }

         $ch = curl_init( $url );
         $options = [
            CURLOPT_FILE              => $fileHandle,
            CURLOPT_FOLLOWLOCATION    => true,
            CURLOPT_MAXREDIRS         => 5,
            CURLOPT_FAILONERROR       => false, // we handle it ourselves
            CURLOPT_CONNECTTIMEOUT    => 10,
            CURLOPT_TIMEOUT           => 120,
            CURLOPT_LOW_SPEED_LIMIT   => 1024,
            CURLOPT_LOW_SPEED_TIME    => 30,
            CURLOPT_USERAGENT         => Module_Core::config('plugin.name'),
            CURLOPT_HTTP_VERSION      => CURL_HTTP_VERSION_1_1,
         ];

         curl_setopt_array( $ch, $options );
         curl_exec( $ch );

         $errno    = curl_errno( $ch );
         $error    = curl_error( $ch );
         $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

         curl_close( $ch );
         fclose( $fileHandle );

         if ( $errno ) {
            unlink( $local_file );
            return [
               'status'  => 'error',
               'message' => $error,
            ];
         }

         if ( $http_code >= 400 ) {
            unlink( $local_file );
            return [
               'status'  => 'error',
               'message' => 'The remote server responded with an error (HTTP ' . $http_code . ').',
            ];
         }

         // Verify file size if possible
         $remote_size = self::get_remote_filesize( $url );

         if ( $remote_size && filesize( $local_file ) < $remote_size ) {
            unlink( $local_file );
            return [
               'status'  => 'error',
               'message' => 'The file was not fully downloaded (incomplete transfer).',
            ];
         }

      } elseif ( ini_get( 'allow_url_fopen' ) ) {

         $fp_remote = @fopen( $url, 'rb' );

         if ( ! $fp_remote ) {
            return [
               'status'  => 'error',
               'message' => error_get_last(),
            ];
         }

         $fp_local = @fopen( $local_file, 'wb' );

         if ( ! $fp_local ) {
            fclose( $fp_remote );
            return [
               'status'  => 'error',
               'message' => 'Unable to open file for writing at the following location: ' . $local_file,
            ];
         }

         while ( ! feof( $fp_remote ) ) {
            $buffer = fread( $fp_remote, 8192 );
            if ( $buffer === false || fwrite( $fp_local, $buffer ) === false ) {
               fclose( $fp_remote );
               fclose( $fp_local );
               unlink( $local_file );
               return [
                  'status'  => 'error',
                  'message' => 'An error occurred while reading or writing during the download process.',
               ];
            }
         }

         fclose( $fp_remote );
         fclose( $fp_local );

         $remote_size = self::get_remote_filesize( $url );

         if ( $remote_size && filesize( $local_file ) < $remote_size ) {
            unlink( $local_file );
            return [
               'status'  => 'error',
               'message' => 'The file was not fully downloaded (incomplete transfer).',
            ];
         }

      } else {

         return [
            'status'  => 'error',
            'message' => 'Neither cURL nor allow_url_fopen is available on this server to download remote files. Please check with your hosting provider.',
         ];
      }

      return [
         'status' => 'success',
      ];

   }



   /**
    * Download image from url and create the wp media library attachment
    *
    * @param string $url The file url
    * @param int $post_id
    * @return int|\WP_Error
    */
   public static function download_image_from_url(string $url, $post_id = 0) {

      if ( ! function_exists( 'download_url' ) ) {
         require_once ABSPATH . 'wp-admin/includes/file.php';
      }

      if ( ! function_exists( 'wp_read_image_metadata' ) ) {
         require_once ABSPATH . 'wp-admin/includes/image.php';
      }

      if ( ! function_exists( 'media_handle_sideload' ) ) {
         require_once ABSPATH . 'wp-admin/includes/media.php';
      }

      $url = Util::strip_url($url);

      $tmp = download_url( $url );

      $file_array = array(
         'name' => basename( $url ),
         'tmp_name' => $tmp
      );

      if ( is_wp_error( $tmp ) ) {
         return $tmp;
      }

      $attachment_id = media_handle_sideload( $file_array, $post_id );

      if ( ! is_wp_error( $attachment_id ) ) {
         add_post_meta($attachment_id, Util::prefix('plugin_version'), VERSION, true);
      }

      return $attachment_id;
   }



   /**
    * Check if the attachment was downloaded from url
    *
    * @param $attachment_id
    * @return bool
    */
   public static function has_downloaded_attachment($attachment_id) {
      return metadata_exists('post', $attachment_id, Util::prefix('plugin_version'));
   }



   /**
    * Builds the path by the given fragments.
    *
    * @param array $fragments
    * @return string
    */
    public static function build_path($fragments){
      return is_array($fragments) ? join(DIRECTORY_SEPARATOR, $fragments) : str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fragments);
   }
}