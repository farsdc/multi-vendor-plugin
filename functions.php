<?php 

function redirect_vendor_dashboard() {
    $user = wp_get_current_user();
    
    if (in_array('vendor', (array) $user->roles)) {
        wp_redirect(home_url('/vendor-dashboard/'));
        exit;
    }
}
add_action('admin_init', 'redirect_vendor_dashboard');

function block_vendor_wp_admin_access() {
    $user = wp_get_current_user();
    
    if (in_array('vendor', (array) $user->roles) && is_admin()) {
        wp_redirect(home_url('/vendor-dashboard/'));
        exit;
    }
}
add_action('admin_init', 'block_vendor_wp_admin_access');

function hide_admin_bar_for_vendor($show) {
    $user = wp_get_current_user();
    
    if (in_array('vendor', (array) $user->roles)) {
        return false;
    }

    return $show;
}
add_filter('show_admin_bar', 'hide_admin_bar_for_vendor');

?>