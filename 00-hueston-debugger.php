<?php
/**
 * Plugin Name: 00 Hueston Debugger
 * Plugin URI:  https://hueston.co/
 * Description: Logs PHP timeout errors to a log file, including stack trace.
 * Version:     1.1
 * Author:      alperxx
 * Author URI:  https://github.com/alperxx
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: timeout-error-logger
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}
require_once(__DIR__ . '/config.php');

class Hueston_Debugger
{

  protected static $apiUrl = '';
  protected static $apiKey = '';

  private static $log_file, $custom_file;

  public function __construct($config = [])
  {
    self::$apiUrl = $config['apiUrl'] ?? false;
    self::$apiKey = $config['apiKey'] ?? false;

    self::$custom_file = plugin_dir_path(__FILE__) . 'custom_errors.log';
    self::$log_file = plugin_dir_path(__FILE__) . 'timeout_errors.log';
    register_shutdown_function([$this, 'shutdown_function']);
    set_error_handler([$this, 'custom_error_handler']);
  }

  public function shutdown_function()
  {
    global $wp;

    $last_error = error_get_last();
    if ($last_error && $last_error['type'] === E_ERROR) {
      $last_error['trace']  = (new \Exception)->getTraceAsString();
      $last_error['server'] = $_SERVER;
      $error_message = "\nError: Timeout occurred! - " . date('Y-m-d H:i:s') . "\n";
      $error_message .= json_encode($last_error);
      error_log($error_message, 3, self::$log_file);

      if (self::$apiKey ?? false) {
        wp_schedule_single_event(time() + 60, 'hueston_send_api_request', array(
          self::$apiUrl . "edit",
          [
            'postvar'       => '',
            'time_time'     => date('Y-m-d H:i:s'),
            'v_domain'      => $_SERVER['HTTP_HOST'] ?? '',
            'v_error'       => json_encode($last_error, JSON_PRETTY_PRINT),
          ]
        ));
      }
    }
  }

  public static function hueston_send_api_request_handler($url, $post)
  {
    $response = wp_remote_post($url, array(
      'headers' => array(
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . self::$apiKey,
      ),
      'body'    => json_encode($post),
    ));

    if (is_wp_error($response)) {
      error_log("API Request Error: " . $response->get_error_message(), 3, self::$log_file);
    }
  }

  public function custom_error_handler($errno, $errstr, $file, $line)
  {
    global $wp;
    $pass_errors = array('exif_read_data');

    foreach ($pass_errors as $pass_key) {
      if (is_string($errstr) && strpos($errstr, $pass_key) !== false) {
        return true;
      }
    }

    $arr = [
      'title'   => 'Custom Error:' . $errno,
      'message' => $errstr,
      'file'    => $file,
      'line'    => $line,
      'server'  => $_SERVER,
    ];

    if ($errno === 8192) { // 8192 deprecated 
    }

    $arr['trace'] = (new \Exception)->getTraceAsString();
    $message      = str_replace(
      [
        '&lt;?php&nbsp;',
        '?&gt;'
      ],
      '',
      highlight_string('<?php ' . var_export($arr, true) . ' ?>', true)
    );

    $ignore = [
      ' deprecated',
    ];

    foreach ($ignore as $msg) {
      if (str_contains($arr['message'], $msg)) {
        return;
      }
    }

    $error_message = "\nCustom Error! - " . date('Y-m-d H:i:s') . "\n";
    $error_message .= htmlspecialchars_decode(
      strip_tags(
        htmlspecialchars_decode(
          html_entity_decode(
            str_replace(
              ['&nbsp;', "<br />", "<br>", "<br/>", "&gt;"],
              [' ', "\n", "\n", "\n", ">"],
              (var_export($arr, true))
            )
          )
        )
      )
    ) . "\n\n\n\n\n\n";

    //error_log($error_message);
    error_log($error_message, 3, self::$custom_file);
  }

  private function vd()
  {
    if (defined('WP_DEBUG') && WP_DEBUG) {
      if (!headers_sent()) {
        header('Content-type: text/html');
      }
      echo "<pre>";

      $vars = func_get_args();
      $die  = count($vars) <= 1 ||
        (
          isset($vars[count($vars) - 1]) &&
          ($vars[count($vars) - 1] === 1 || $vars[count($vars) - 1] === true)
        );

      if (
        count($vars) > 1 &&
        ($die || ($vars[count($vars) - 1] === 0 || $vars[count($vars) - 1] === false))
      ) {
        unset($vars[count($vars) - 1]);
      } else {
        // default die
        if (!isset($vars[count($vars) - 1]) || !is_bool($vars[count($vars) - 1]) || !in_array(
          $vars[count($vars) - 1],
          [0, 1]
        )) {
          $die = true;
        }
      }

      foreach ($vars as $var) {
        echo "\n";
        var_dump($var);
        /*$output = var_export($var, true);
				$output = trim($output);
				$output = highlight_string("<?php " . $output, true);  // highlight_string() requires opening PHP tag or otherwise it will not colorize the text
				$output = preg_replace("|\\<code\\>|", "<code style='background-color: #000000; padding: 10px; margin: 10px; display: block; font: 12px Consolas;'>", $output, 1);  // edit prefix
				$output = preg_replace("|(\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>)(&lt;\\?php&nbsp;)(.*?)(\\</span\\>)|", "\$1\$3\$4", $output);  // remove custom added "<?php "
				echo $output;*/
      }

      echo "\n\n";
      print_r((new \Exception)->getTraceAsString());

      echo "</pre>";
      echo "<style>body{background-color:#282b25; color:#fff;} </style>";

      if ($die) {
        die;
      }
    }
  }
}

add_action('hueston_send_api_request', array('Hueston_Debugger', 'hueston_send_api_request_handler'), 10, 2);

new Hueston_Debugger($Hueston_Debugger ?? []);
