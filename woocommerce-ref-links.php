<?php
/*
Plugin Name: WC Referral Links
Author: Brykov Pavel
Author URI: http://webpalych.pp.ua
Version: 0.3
Text Domain: wc-refs
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('WC_REF_SOURCE_META_FIELD', 'referrer_source');
define('WC_REF_USER_META_FIELD', 'referrer_user');

require __DIR__ . '/WCRefCreator.php';
require __DIR__ . '/WCRefAdminFilter.php';
require __DIR__ . '/helpers.php';

add_action('plugins_loaded', ['WCRefCreator', 'initActions']);
add_action('plugins_loaded', ['WCRefAdminFilter', 'initActions']);
add_action('plugins_loaded', ['WCRefCreator', 'loadPluginTextdomain']);

