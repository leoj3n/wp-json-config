<?php
/**
 * Load JSON configuration
 *
 * WP_LOCAL_DEV used by WP_JSON_Config to load local/remote JSON
 */
define('WP_LOCAL_DEV', true);

require_once(dirname(__FILE__) . '/class.wp-json-config.php');

$config = WP_JSON_Config::getInstance()
            ->parseFile(dirname(__FILE__) . '/wp-config.json')
            ->apply();

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
