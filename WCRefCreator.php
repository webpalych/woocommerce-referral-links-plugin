<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WCRefCreator')) :

    class WCRefCreator
    {
        public function __construct(){}

        /**
         * Add plugin actions and filters
         */
        public static function initActions()
        {
            add_action('wp_enqueue_scripts', [__CLASS__, 'registerScripts']);
            add_action('init', [__CLASS__, 'setRefCookie']);
            add_filter('manage_edit-shop_order_columns', [__CLASS__, 'addOrdersSourceColumn']);
            add_action('manage_shop_order_posts_custom_column', [__CLASS__, 'fillOrdersSourceColumn']);
            add_action('admin_bar_menu', [__CLASS__, 'addAdminBarDropdown'], 100);
            add_action('woocommerce_checkout_update_order_meta', [__CLASS__, 'addOrderReferrer'], 100, 1);
        }

        /**
         * Load plugin textdomain
         */
        public static function loadPluginTextdomain()
        {
            load_plugin_textdomain(
                'wc-refs',
                FALSE,
                basename( dirname( __FILE__ ) ) . '/languages/'
            );
        }

        /**
         * Register plugin's scripts
         */
        public static function registerScripts()
        {
            if (is_admin_bar_showing() && !is_admin()) {
                wp_enqueue_script(
                    'wc-ref-adminbar-script',
                    plugin_dir_url( __FILE__ ) . 'assets/wc-refs.js',
                    ['jquery'],
                    '1.0.0',
                    true
                );

                wp_localize_script( 'wc-ref-adminbar-script', 'wcRefLocalize', array(
                    'alertMessage' => __( 'Copied to clipboard', 'wc-refs' ),
                ) );
            }
        }


        /**
         * Add referrer column to orders list
         *
         * @param array $columns
         * @return array
         */
        public static function addOrdersSourceColumn($columns)
        {
            $new_columns = array();

            foreach ( $columns as $column_name => $column_info ) {
                $new_columns[ $column_name ] = $column_info;
                if ( 'order_date' === $column_name ) {
                    $new_columns['order_referrer'] = __('Source', 'wc-refs');
                }
            }

            return $new_columns;
        }

        /**
         * Fill referrer column in orders list
         *
         * @param string $column
         */
        public static function fillOrdersSourceColumn($column)
        {
            global $post;

            if ('order_referrer' === $column) {

                $referrer = get_post_meta($post->ID, 'referrer_source', true);

                echo $referrer;
            }
        }

        /**
         * Set cookies based on ref link
         */
        public static function setRefCookie()
        {
            if (isset($_GET['utm_ref'])) {
                $ref = htmlspecialchars($_GET['utm_ref']);
                setcookie('_referrer', $ref, time() + (10 * 365 * 24 * 60 * 60), '/');
            }
        }

        /**
         * Add metadata and notes to the order
         *
         * @param $order_id
         */
        public static function addOrderReferrer($order_id)
        {
            if (isset($_COOKIE['_referrer'])) {
                $user_id = null;
                $ref = sanitize_text_field($_COOKIE['_referrer']);

                if (preg_match('/user_([0-9]+)/', $ref, $matches)) {
                    $user_id = $matches[1];
                    $username = self::getUsernameById($user_id);
                    if ($username) {
                        $ref = $username;
                    }
                }

                self::addOrderNote($order_id, $ref);
                self::addOrderMeta($order_id, $ref, $user_id);
            }
        }

        /**
         * Add note to the order
         *
         * @param int $order_id
         * @param string $ref
         */
        public static function addOrderNote($order_id, $ref)
        {
            $order = wc_get_order($order_id);
            $order->add_order_note(__('Source:', 'wc-refs') . ' ' . $ref);
        }

        /**
         * Add meta field to the order
         *
         * @param int $order_id
         * @param string $ref
         * @param mixed|null $user_id
         */
        public static function addOrderMeta($order_id, $ref, $user_id = null)
        {
            update_post_meta($order_id, WC_REF_SOURCE_META_FIELD, wp_slash($ref));
            if ($user_id) {
                update_post_meta($order_id, WC_REF_USER_META_FIELD, $user_id);
            }
        }

        /**
         * Create list in adminbar
         *
         * @param $admin_bar
         */
        public static function addAdminBarDropdown($admin_bar)
        {
            if (!is_admin()) {
                $current_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

                $admin_bar->add_menu([
                    'id' => 'ref-link-root',
                    'title' => __('Get reference link', 'wc-refs'),
                    'href' => '',
                    'meta' => [
                        'title' => __('Get reference link', 'wc-refs'),
                    ],
                ]);
                $admin_bar->add_menu([
                    'id' => 'ref-link-fb',
                    'parent' => 'ref-link-root',
                    'title' => 'Facebook',
                    'href' => $current_link . '?utm_ref=facebook',
                    'meta' => [
                        'title' => 'Facebook',
                        'target' => '_blank',
                        'class' => 'ref_link_admin'
                    ],
                ]);
                $admin_bar->add_menu([
                    'id' => 'ref-link-inst',
                    'parent' => 'ref-link-root',
                    'title' => 'Instagram',
                    'href' => $current_link . '?utm_ref=instagram',
                    'meta' => [
                        'title' => 'Instagram',
                        'target' => '_blank',
                        'class' => 'ref_link_admin'
                    ],
                ]);
                $admin_bar->add_menu([
                    'id' => 'ref-link-user',
                    'parent' => 'ref-link-root',
                    'title' => __('User link', 'wc-refs'),
                    'href' => $current_link . '?utm_ref=user_' . wp_get_current_user()->ID,
                    'meta' => [
                        'title' => __('User link', 'wc-refs'),
                        'target' => '_blank',
                        'class' => 'ref_link_admin'
                    ],
                ]);
            }
        }

        /**
         * Get username from DB
         *
         * @param $user_id
         * @return string|null
         */
        protected static function getUsernameById($user_id)
        {
            global $wpdb;

            return $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT $wpdb->users.user_login FROM $wpdb->users WHERE $wpdb->users.id = %d",
                    $user_id
                )
            );
        }

    }

endif;