<?php
/**
 * Plugin Name: WooCommerce MobiCred
 * Description: Seamlessly integrate MobiCred installment information within your WooCommerce single product pages to improve customer experience and increase conversions.
 * Version: 1.1
 * Author: Byron Jacobs
 * Author URI: https://byronjacobs.co.za
 * License: GPLv2 or later
 * Text Domain: woocommerce-mobicred
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

class WooCommerceMobiCred {
	public function __construct() {
		add_action('admin_menu', array($this, 'add_settings_page'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('plugins_loaded', array($this, 'init_hooks'));
	}

	public function add_settings_page() {
		add_submenu_page(
			'woocommerce',
			'MobiCred Settings',
			'MobiCred',
			'manage_options',
			'woocommerce-mobicred',
			array($this, 'settings_page_content')
		);
	}

	public function register_settings() {
		register_setting('woocommerce-mobicred', 'mobicred_number_of_months', array('default' => 6));
		register_setting('woocommerce-mobicred', 'mobicred_inject_location', array('default' => 'woocommerce_single_product_summary'));
	}

	public function settings_page_content() {
		?>
<div class="wrap">
    <h1>MobiCred Settings</h1>
    <form method="post" action="options.php">
        <?php
settings_fields('woocommerce-mobicred'); // Corrected option group name
		do_settings_sections('woocommerce-mobicred'); // Corrected option group name
		?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Number of months:</th>
                <td>
                    <select name="mobicred_number_of_months">
                        <option value="2" <?php selected(get_option('mobicred_number_of_months'), '2');?>>2</option>
                        <option value="4" <?php selected(get_option('mobicred_number_of_months'), '4');?>>4</option>
                        <option value="6" <?php selected(get_option('mobicred_number_of_months'), '6');?>>6</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Inject location:</th>
                <td>
                    <select name="mobicred_inject_location">
                        <option value="woocommerce_before_single_product_summary"
                            <?php selected(get_option('mobicred_inject_location'), 'woocommerce_before_single_product_summary');?>>
                            Before single product summary</option>
                        <option value="woocommerce_single_product_summary"
                            <?php selected(get_option('mobicred_inject_location'), 'woocommerce_single_product_summary');?>>
                            Inside single product summary</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button();?>
    </form>
</div>
<?php
}

	public function enqueue_scripts() {
		wp_enqueue_script('mobicred-custom-js', plugin_dir_url(__FILE__) . 'mobicred-custom.js', array('jquery'), '1.0.0', true);
		wp_enqueue_style('mobicred-custom-css', plugin_dir_url(__FILE__) . 'mobicred-custom.css', array(), '1.0.0');
		wp_localize_script('mobicred-custom-js', 'mobicredOptions', array(
			'numberOfMonths' => get_option('mobicred_number_of_months', 6),
			'logoUrl' => plugin_dir_url(__FILE__) . 'mobicred-pty-ltd-logo-vector.svg',
		));
	}

	public function init_hooks() {
		$inject_location = get_option('mobicred_inject_location', 'woocommerce_single_product_summary');
		add_action($inject_location, array($this, 'display_mobicred_info'), 25);
	}

	public function display_mobicred_info() {
		echo '<div id="mobicred-container" style="display:none;"><span>Pay in <strong id="mobicred-installment"></strong> with MobiCred</span></div>';
	}
}

new WooCommerceMobiCred();