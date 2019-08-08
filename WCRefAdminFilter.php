<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WCRefAdminFilter')) :

    class WCRefAdminFilter
    {
        const REQUEST_ATTR = 'wc_ref';

        public function __construct(){}

        /**
         * Add plugin actions and filters
         */
        public static function initActions()
        {
            add_action('restrict_manage_posts', [__CLASS__, 'addFilterDropdown'], 10);
            add_filter('parse_query', [__CLASS__, 'filterOrdersByRef'], 10);
        }

        /**
         * Create dropdown in filters section
         *
         * @param string $post_type
         */
        public static function addFilterDropdown($post_type)
        {
            if('shop_order' !== $post_type){
                return; //filter your post
            }
            $selected = '';
            if (isset($_REQUEST[self::REQUEST_ATTR])) {
                $selected = sanitize_text_field($_REQUEST[self::REQUEST_ATTR]);
            }

            $referrers = self::getReferrersValues();

            //build a custom dropdown list of values to filter by
            echo wc_ref_render_template('filter-dropdown', compact('referrers', 'selected'));
        }

        /**
         * @param WP_Query $query
         * @return WP_Query
         */
        public static function filterOrdersByRef($query)
        {
            //modify the query only if it admin and main query.
            if(!(is_admin() && $query->is_main_query())){
                return $query;
            }

            if (isset($_REQUEST[self::REQUEST_ATTR])) {
                $selected = sanitize_text_field($_REQUEST[self::REQUEST_ATTR]);
            }

            //we want to modify the query for the targeted custom post and filter option
            if(!('shop_order' === $query->query['post_type'] && isset($selected))){
                return $query;
            }

            //for the default value of our filter no modification is required
            if('0' == $selected){
                return $query;
            }

            //modify the query_vars.
            $query->query_vars['meta_query'][] = [
                [
                    'field' => WC_REF_SOURCE_META_FIELD,
                    'value' => $selected,
                    'compare' => '=',
                    'type' => 'CHAR'
                ]
            ];

            return $query;
        }

        /**
         * Get unique values of the referrers to filer by.
         *
         * @return array
         */
        protected static function getReferrersValues()
        {
            global $wpdb;
            $meta_key = WC_REF_SOURCE_META_FIELD;
            $referrers = $wpdb->get_col(
                $wpdb->prepare( "
                    SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
                    WHERE pm.meta_key = '%s'
                    ORDER BY pm.meta_value",
                    $meta_key
                )
            );

            return $referrers;
        }
    }

endif;