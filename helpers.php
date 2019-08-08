<?php

/**
 * Function for rendering html template
 *
 * @param string $template
 * @param array $data
 * @return string
 */
function wc_ref_render_template($template, $data = []) {
    extract($data, EXTR_SKIP);
    ob_start();
    include sprintf(__DIR__ . '/templates/%s.php', $template);
    return ob_get_clean();
}