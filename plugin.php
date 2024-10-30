<?php

/**
 * Plugin Name: Cached localization
 * Description: Caches the global variable $l10n, which stores all translations for considerable speed improvement. Be sure to regenerate the cache if a .mo changes.
 * Version: 0.2.2
 * Author: seb@wemakecustom.com
 * Author URI: http://www.wemakecustom.com
 */

class Cached_L10n {
    /**
     * 0: not loaded, will load at next 'load_textdomain'
     * 1: first time loading, before the import.
     * 2: import done
     * 
     * If it stays at 1, file does not exist or is corrupted.
     */
    public static $status  = 0;

    public static $enabled = null;
    public static $file    = null;

    public static function init() {
        $plugin = plugin_basename(__FILE__);
        self::$enabled = !WP_DEBUG;
        self::$file    = WP_CONTENT_DIR . '/uploads/l10n.pson';

        // Admin hooks
        add_filter("plugin_action_links_$plugin", array(__CLASS__, 'settings_link'), 10, 2);
        add_action('admin_menu', array(__CLASS__, 'menu'));
        add_action('admin_notices', array(__CLASS__, 'warnings'));

        if (self::$enabled && !self::is_admin_page()) {
            add_filter('override_load_textdomain', array(__CLASS__, 'override'));

            // Admin hooks
            add_filter('pre_update_option_active_plugins', array(__CLASS__, 'pre_update_option'), 9999, 2);
            add_filter('pre_update_option_current_theme', array(__CLASS__, 'pre_update_option'), 9999, 2);
            add_action('admin_init', array(__CLASS__, 'save_index'), 9999);
        }
    }

    public static function export() {
        global $l10n;

        foreach ($l10n as $mo) {
            $mo->_gettext_select_plural_form = null;
        }

        file_put_contents(self::$file, serialize($l10n));
        update_option(__CLASS__ . '.updated', false);
        self::save_index();
    }

    public static function import() {
        if (is_readable(self::$file)) {
            $l10n = unserialize(file_get_contents(self::$file));
            if ($l10n) {
                self::$status = 2;
                $GLOBALS['l10n'] = $l10n;
                return true;
            }
        }

        return false;
    }

    public static function index() {
        global $l10n;

        $index = array();
        foreach ($l10n as $domain => $mo) {
            if ($mo->entries) {
                $mo->_gettext_select_plural_form = null;
                $index[$domain] = md5(serialize($mo));
            }
        }

        return $index;
    }

    /**
     * filter: override_load_textdomain
     *
     * Cancels load_textdomain and upon the first load, will import the cached data if it exists
     * If import fails, it will not try again
     */
    public static function override($override) {
        if (self::$status == 2) {
            return true;
        } elseif (self::$status == 0) {
            self::$status = 1; // First time loading
            return self::import();
        }

        return $override;
    }

    public static function delete() {
        delete_option(__CLASS__ . '.index');

        if (file_exists(self::$file)) {
            return unlink(self::$file);
        }

        return true;
    }

    public static function save_index() {
        update_option(__CLASS__ . '.index', self::index());
    }

    public static function cached_index() {
        return get_option(__CLASS__ . '.index', array());
    }

    // Admin functions
    /**
     * action: admin_menu
     */
    public static function menu() {
        add_management_page(
            'Cached l10n',
            'Cached l10n',
            'manage_options',
            'cached-l10n',
            array(__CLASS__, 'admin_page')
        );
    }

    public static function admin_page() {
        $plugin = get_plugin_data(__FILE__);

        require __DIR__ . '/admin_page.php';
        return;
    }

    private static function is_admin_page() {
        return strpos($_SERVER['REQUEST_URI'], 'tools.php?page=cached-l10n') !== false;
    }

    /**
     * action: plugin_action_links_$plugin
     */
    public static function settings_link($links) { 
      $links[] = '<a href="'.admin_url('tools.php?page=cached-l10n').'">'.__('Settings').'</a>'; 

      return $links; 
    }

    /**
     * filter: pre_update_option_$name
     * 
     * if active_plugins or current_theme changes, text_domains will probably change
     */
    public static function pre_update_option($new_value, $old_value) {
        if ($new_value !== $old_value) {
            update_option(__CLASS__ . '.updated', true);
        }

        return $new_value;
    }

    /**
     * action: admin_notices
     */
    public static function warnings() {
        global $_wp_using_ext_object_cache;
        $plugin = get_plugin_data(__FILE__);

        if (!self::is_admin_page()) {
            if (self::$status != 2) {
                echo '
                <div class="error">
                    <p>'.$plugin['Name'].' is not loaded, go to the <a href="'.admin_url('tools.php?page=cached-l10n').'">plugin\'s page</a> to generate it.</p>
                </div>';
            }
            if (get_option(__CLASS__ . '.updated', false)) {
                echo '
                <div class="updated">
                    <p>'.$plugin['Name'].' may need to regenerated, go to the <a href="'.admin_url('tools.php?page=cached-l10n').'">plugin\'s page</a> to generate it.</p>
                </div>';
            }
        }
    }
}

Cached_L10n::init();
