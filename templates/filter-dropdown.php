<?php
/**
 * @var array $referrers
 * @var string $selected
 */
?>
<select id="wc-ref-dropdown" name="wc_ref">
    <option value="0"><?php _e( 'All referrers', 'wc-refs' ); ?></option>
    <?php
    foreach($referrers as $referrer) :
        $select = ($referrer == $selected) ? 'selected="selected"':'';
        echo "<option value=\"$referrer\" $select>$referrer</option>";
    endforeach; ?>
</select>