<?php
function theme_widget_custom_footer_qrcode(&$setting) {
    $setting['wapurl'] = url::action('topm_ctl_default@index');
    // print_r($setting);echo 33;
    return $setting;
}

?>
