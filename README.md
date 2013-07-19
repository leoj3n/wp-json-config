# WordPress JSON Config

The goal of this project is to abstract the wp-config.php file into a more maintainable and reproducible structure.

## Usage

Require `class.wp-json-config.php` in `wp-config.php` like so, replacing any/all default code up to `stop editing`:

```php
define('WP_LOCAL_DEV', true);

require_once(dirname(__FILE__) . '/class.wp-json-config.php');

$config = WP_JSON_Config::getInstance()
            ->parseFile(dirname(__FILE__) . '/wp-config.json')
            ->parse({ "local": { "database": { "user": "localroot" }}})
            ->apply();

/* That's all, stop editing! Happy blogging. */
```

### Notes

* Pass in the JSON you want parsed using `parse` or `parseFile` as demonstrated above.
* A sample JSON configuration file `wp-config.json` is included as a reference.
* `WP_LOCAL_DEV` determines whether or not to use the `local` or `remote` JSON configuration.
* Depending on `WP_LOCAL_DEV`, any `local`/`remote` settings override `global` settings.
* You can chain `parse` (for JSON strings/arrays) and `parseFile` (for `*.json` files).
