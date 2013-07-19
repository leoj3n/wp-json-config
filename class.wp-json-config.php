<?php
/**
 * This class translates JSON into named constants
 */
class WP_JSON_Config {

  private $json = array();
  private $map = array(
    'database' => array(
      'name' => 'DB_NAME',
      'host' => 'DB_HOST',
      'user' => 'DB_USER',
      'pass' => 'DB_PASSWORD',
      'prefix' => '$table_prefix', // becomes a global variable
      'charset' => 'DB_CHARSET',
      'collate' => 'DB_COLLATE'
    ),
    'salts' => array(
      'AUTH_KEY',
      'SECURE_AUTH_KEY',
      'LOGGED_IN_KEY',
      'NONCE_KEY',
      'AUTH_SALT',
      'SECURE_AUTH_SALT',
      'LOGGED_IN_SALT',
      'NONCE_SALT'
    ),
    'multisite' => array(
      'allow' => 'WP_ALLOW_MULTISITE',
      'blog' => 'BLOG_ID_CURRENT_SITE',
      'site' => 'SITE_ID_CURRENT_SITE',
      'path' => 'PATH_CURRENT_SITE',
      'subdomain' => 'SUBDOMAIN_INSTALL'
    ),
    'disable' => array(
      'cron' => 'DISABLE_WP_CRON',
      'edits' => 'DISALLOW_FILE_EDIT'
    )
    // @TODO: add every WordPress named constant
    // @TODO: organize sections/keywords more sensibly
    // http://wpengineer.com/2382/wordpress-constants-overview/
    // http://codex.wordpress.org/Editing_wp-config.php
  );

  // singleton
  public static $instance = NULL;
  public static function getInstance() {
    if(!isset(self::$instance))
      self::$instance = new WP_JSON_Config();
    return self::$instance;
  }

  // construct defaults from JSON template
  private function __construct() {
    // @FIXME: expose default template in some other way
    ob_start(); ?>
    {
      "global": {
        "database": {
          "host": "localhost",
          "prefix": "wp_",
          "charset": "utf8",
          "collate": ""
        },
        "salts": [
          "put your unique phrase here",
          "put your unique phrase here",
          "put your unique phrase here",
          "put your unique phrase here",
          "put your unique phrase here",
          "put your unique phrase here",
          "put your unique phrase here",
          "put your unique phrase here"
        ],
        "disable": {
          "cron": false,
          "edits": false
        },
        "define": {}
      },


      "local": {
        "database": {
          "name": "mylocaldb",
          "user": "root",
          "pass": ""
        }
      },


      "remote": {
        "database": {}
      }
    }
    <?php
    $this->parse(ob_get_clean());
  }

  // accepts: (JSON) file path
  public function parseFile($file = NULL) {
    try {

      if(($json = @file_get_contents($file)) === false)
        throw new Exception("could not read file");

      $this->parse($json);

    } catch(Exception $e) {

      echo "JSON File Error: {$e->getMessage()}.\n";

    }

    return self::$instance; // allow chain
  }

  // accepts: (JSON) string|array
  public function parse($json = NULL) {
    try {

      switch(true) {
        case is_string($json):
          if(is_null($json = json_decode($json, true)))
            throw new Exception("invalid json");
          break;
        case is_array($json): break;
        default:
          throw new Exception("must be of type string or array");
          break;
      }

      // feel free to chain multiple parse()->parseFile() commands sequentially
      $this->json = array_replace_recursive($this->json, $json);

    } catch(Exception $e) {

      echo "JSON Parse Error: {$e->getMessage()}.\n";

    }

    return self::$instance; // allow chain
  }

  // apply global, local|remote JSON
  public function apply() {
    // fold global, local|remote JSON into single array
    $json = array_replace_recursive($this->json['global'],
     WP_LOCAL_DEV ? $this->json['local'] : $this->json['remote']);

    try {

      // map defines -> folded values
      foreach($this->map as $section => $arr) {
        foreach($arr as $keyword => $d) {
          // skip null value
          if(is_null($v = $json[$section][$keyword]))
            continue;

          // ensure valid value
          if(is_array($v))
            throw new Exception("arrays not allowed for {$section}.{$keyword}");

          $this->define($d, $json[$section][$keyword]);
        }
      }

      // handle custom defines
      foreach($json['define'] as $d => $v) {
        if(is_array($v))
          throw new Exception("arrays not allowed for {$d}");

        if(in_array($d, call_user_func_array('array_merge', $this->map)))
          throw new Exception("{$d} is reserved");

        $this->define($d, $v);
      }

    } catch(Exception $e) {

      echo "JSON Apply Error: {$e->getMessage()}.\n";

    }

    return $json; // expose composed array
  }

  // define parsed named constants
  private function define($define, $value) {
    $expected = array('string', 'boolean', 'integer');

    if(!in_array(gettype($value), $expected))
      throw new Exception("unexpected value type for {$define}");

    if($define[0] == '$') // detect global variable (f.ex: $table_prefix)
      $GLOBALS[substr($define, 1)] = $value;
    else
      define($define, $value);

    /* @DEBUG: */
    // $v = is_bool($value) ? ($value ? 'true' : 'false') : $value;
    // echo ($define[0] == '$') ? "{$define} = {$v};\n" : "define('{$define}', '{$v}');\n";
  }

} // WP_JSON_Config
