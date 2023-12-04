<?php
/**
 * Plugin Name: WooCommerce MobiCred
 * Description: Enable MobiCred as a payment method at checkout and display an installment calculator on product pages for enhanced customer decision-making.
 * Version: 1.2
 * Author: Byron Jacobs
 * Author URI: https://byronjacobs.co.za
 * License: GPLv2 or later
 * Text Domain: woocommerce-mobicred
 */

// Include necessary WP functions
include_once ABSPATH . 'wp-admin/includes/plugin.php';

function wc_mobicred_init() {
	if (!is_plugin_active('woocommerce/woocommerce.php')) {
		add_action('admin_notices', 'wc_mobicred_missing_wc_notice');
	}
}
add_action('plugins_loaded', 'wc_mobicred_init');

if (is_plugin_active('woocommerce/woocommerce.php')) {
	class WooCommerceMobiCred {
		public function __construct() {
			add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
			add_action('woocommerce_settings_tabs_mobicred', array($this, 'settings_page_content'));
			add_action('woocommerce_update_options_mobicred', array($this, 'update_settings'));
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action('init', array($this, 'init_hooks'));
			add_shortcode('mobicred_info', array($this, 'shortcode_mobicred_info')); // Adding shortcode
		}

		public function add_settings_tab($tabs) {
			$tabs['mobicred'] = __('MobiCred', 'woocommerce-mobicred');
			return $tabs;
		}

		public function settings_page_content() {
			woocommerce_admin_fields($this->get_settings());
		}

		public function update_settings() {
			woocommerce_update_options($this->get_settings());
		}

		public function get_settings() {
			return array(
				array(
					'title' => __('MobiCred Settings', 'mobicred-woocommerce'),
					'type' => 'title',
					'desc' => '',
					'id' => 'wc_settings_mobicred_section_title',
				),
				array(
					'title' => 'Number of months',
					'id' => 'mobicred_number_of_months',
					'type' => 'select',
					'options' => array('2' => '2', '4' => '4', '6' => '6'),
					'default' => '6',
				),
				array(
					'title' => 'Inject location',
					'id' => 'mobicred_inject_location',
					'type' => 'select',
					'options' => array(
						'shortcode' => 'Shortcode',
						'woocommerce_before_single_product' => 'Before single product',
						'woocommerce_before_single_product_summary' => 'Before single product summary',
						'woocommerce_product_thumbnails' => 'Product thumbnails',
						'woocommerce_single_product_summary' => 'Inside single product summary',
						'woocommerce_before_add_to_cart_form' => 'Before add to cart form',
						'woocommerce_before_variations_form' => 'Before variations form',
						'woocommerce_before_add_to_cart_button' => 'Before add to cart button',
						'woocommerce_before_single_variation' => 'Before single variation',
						'woocommerce_single_variation' => 'Single variation',
						'woocommerce_before_add_to_cart_quantity' => 'Before add to cart quantity',
						'woocommerce_after_add_to_cart_quantity' => 'After add to cart quantity',
						'woocommerce_after_single_variation' => 'After single variation',
						'woocommerce_after_add_to_cart_button' => 'After add to cart button',
						'woocommerce_after_variations_form' => 'After variations form',
						'woocommerce_after_add_to_cart_form' => 'After add to cart form',
						'woocommerce_product_meta_start' => 'Product meta start',
					),
					'desc' => __('Select the location where the MobiCred installment calculator will be displayed or use the shortcode [mobicred_info].', 'woocommerce-mobicred'),
					'default' => 'woocommerce_single_product_summary',
				),
				array('type' => 'sectionend', 'id' => 'wc_settings_mobicred_section_end'),
			);
		}

		public function enqueue_scripts() {
			wp_enqueue_script('mobicred-custom-js', plugin_dir_url(__FILE__) . 'mobicred-custom.js', array('jquery'), '1.0.0', true);
			wp_enqueue_style('mobicred-custom-css', plugin_dir_url(__FILE__) . 'mobicred-custom.css', array(), '1.0.0');
			wp_localize_script(
				'mobicred-custom-js',
				'mobicredOptions',
				array(
					'numberOfMonths' => get_option('mobicred_number_of_months', 6),
					'logoUrl' => plugin_dir_url(__FILE__) . 'mobicred-pty-ltd-logo-vector.svg',
				)
			);
		}

		public function init_hooks() {
			$inject_location = get_option('mobicred_inject_location', 'woocommerce_single_product_summary');
			// Conditionally add action if the inject location is not set to 'shortcode'
			if ($inject_location !== 'shortcode') {
				add_action($inject_location, array($this, 'inject_mobicred_info'), 25);
			}
		}

		public function inject_mobicred_info() {
			echo $this->get_mobicred_info_content();
		}

		public function shortcode_mobicred_info() {
			return $this->get_mobicred_info_content();
		}

		private function get_mobicred_info_content() {
			return '<div id="mobicred-container" style="display:none;"><span>Pay in <strong id="mobicred-installment"></strong> with MobiCred</span></div>';
		}
	}
	new WooCommerceMobiCred();
} else {
	add_action('admin_notices', 'wc_mobicred_missing_wc_notice');
	deactivate_plugins(plugin_basename(__FILE__));
}

function wc_mobicred_missing_wc_notice() {
	?>
        <div class="error">
            <p><?php _e('WooCommerce MobiCred requires WooCommerce to be installed and active.', 'woocommerce-mobicred');?></p>
        </div>
        <?php
}