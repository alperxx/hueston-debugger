# Hueston Debugger

**A WordPress plugin to log PHP timeout errors remotely.**

**Plugin Name:** Hueston Debugger
**Plugin URI:** [https://github.com/alperxx/hueston-debugger](https://github.com/alperxx/hueston-debugger)
**Description:** Logs PHP timeout errors to a log file and optionally sends them to your ks.today account for centralized monitoring.
**Version:** 1.2
**Author:** alperxx
**Author URI:** [https://github.com/alperxx](https://github.com/alperxx)
**License:** GPL2
**License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

This document explains how to set up the Hueston Debugger plugin to log PHP timeout errors remotely.

## Requirements

*   WordPress website
*   Hosting environment with PHP support
*   A ks.today account (if using remote logging to ks.today)

## Installation

1.  Upload the Hueston Debugger plugin files to your WordPress plugin directory (`/wp-content/plugins/`).
2.  Activate the plugin in your WordPress admin panel.

## Configuration

**Recommended Method (WordPress 5.3+ - Using the Secrets API):**

This is the most secure method. It leverages WordPress's built-in Secrets API to store sensitive information.

1.  Create a new file named `config.php` **inside** your Hueston Debugger plugin folder.

2.  Add the following code to `config.php`:

    ```php
    <?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly.
    }
    $Hueston_Debugger = array(
        'apiUrl' => '[https://yoururl.ks.today/api/timeout_logger/](https://yoururl.ks.today/api/timeout_logger/)', // Replace with your API endpoint URL
        'apiKey' => 'your api key', // Replace with your actual API key
    );
    ```
    The `if (!defined('ABSPATH')) { exit; }` line is a security measure to prevent direct access to the config file.
    **Important:**  Remember to replace the placeholder API key with your actual API key. 

## Setting up the timeout_logger table on ks.today

These steps describe how to set up the necessary structure on the ks.today service to receive the error logs.

1.  Sign in to your ks.today account.
2.  Create a new page or data table (the exact terminology might vary depending on the ks.today interface) with the name "Timeout Logger".
3.  Create the following fields (columns) within the "Timeout Logger" table:

    *   **Name:** Time, **Type:** Date Time
    *   **Name:** Domain, **Type:** Text
    *   **Name:** Error, **Type:** Textarea

    These fields will store the timestamp of the error, the domain where it occurred, and the error details, respectively.

## Remote Logging (Optional)

*   If you want to log errors to a remote server (e.g., ks.today), ensure the `apiUrl` in your configuration is set correctly.
*   You will need to set up a separate server or service (e.g. a page in ks.today) to receive and process the logged errors. The steps for setting up the table on ks.today are described above.

## Additional Notes

*   It's **strongly recommended** to use the WordPress Secrets API method (first approach) for improved security.
*   Remember to keep your WordPress secret key and API key confidential.
*   Consider using a more descriptive domain name instead of "ks.today" for clarity.

## Support

For further assistance, please refer to the plugin's official documentation (if available) or contact the plugin developer.