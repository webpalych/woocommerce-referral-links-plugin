<?php
/*
Plugin Name: WC Referral Links
Author: Brykov Pavel
Author URI: http://webpalych.pp.ua
Version: 0.2
Text Domain: wc-refs
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require __DIR__ . '/WCRefCreator.php';

add_action('plugins_loaded', ['WCRefCreator', 'initActions']);
add_action('plugins_loaded', ['WCRefCreator', 'loadPluginTextdomain']);

