<?php
/**
 * @package   Musilda Ico Dic
 * @author    Musilda.com
 * @license   GPL-2.0+
 * @link      https://musilda.com
 * @copyright 2022 Musilda.com
 */
class Wid_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = Wid::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		add_action( 'admin_init', array( $this, 'output_buffer' ) );   
	   	add_filter( 'woocommerce_get_settings_checkout', array( $this, 'wid_setting' ), 10, 1 );
    	add_filter( 'woocommerce_admin_billing_fields', array( $this, 'billing_admin_fields' ), 10, 1 );
    	add_filter( 'woocommerce_customer_meta_fields', array( $this, 'customer_meta_fields' ), 10, 1 );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
  
  	/**
  	 * Display plugin settings 
  	 * 
	 * @since 1.0.0
	 * 
  	 */
  	public function wid_setting ( $settings ) {
    
    	$settings[] = array(
			'title'			=> __( 'VAT number setting', $this->plugin_slug ), 
            'type'  		=> 'title',
            'id'    		=> 'checkout__display_vat_title' 
        );
                    
    	$settings[] = array(
			'title'         => __( 'Company number on checkout', $this->plugin_slug ),
            'desc'          => __( 'Active company number on checkout', $this->plugin_slug ),
			'id'            => 'woocommerce_checkout_vat_number',
			'default'       => 'no',
			'type'          => 'checkbox',
			'desc_tip'      => __( 'If is active, on checkout will be new fields for companuny number.', $this->plugin_slug ),
			'checkboxgroup' => 'start',
			'autoload'      => false
		);
    	$settings[] = array( 
			'type' => 'sectionend',
			'id' => 'checkout_display_vat'
		);
  
    	return $settings;
    
  	}        

	/**
	 * 
	 * 
	 * @since 1.0.0
	 * 
	 */
  	public function billing_admin_fields($fields){

  		$fields['company_number'] = array(
				'label' => __( 'Company number', $this->plugin_slug ),
				'show'  => true
			);
		$fields['vat_number'] = array(
				'label' => __( 'VAT number', $this->plugin_slug ),
				'show'  => true
			);
		$fields['vat_number_2'] = array(
				'label' => __( 'VAT number 2', $this->plugin_slug ),
				'show'  => true
			);

  		return $fields;

	}

	/**
	 * 
	 * 
	 * @since 1.0.0
	 * 
	 */
	public function customer_meta_fields( $fields ){

  		$fields['billing']['fields']['billing_company_number'] = array(
				'label' => __( 'Company number', $this->plugin_slug ),
				'description' => '',
			);
		$fields['billing']['fields']['billing_vat_number'] = array(
				'label' => __( 'VAT number', $this->plugin_slug ),
				'description' => '',
			);
		$fields['billing']['fields']['billing_vat_number_2'] = array(
				'label' => __( 'VAT number 2', $this->plugin_slug ),
				'description' => '',
			);

  		return $fields;
	}

	/**
	 * Headers allready sent fix
	 *
	 */        
  	public function output_buffer() {
		ob_start();
  	} 
  
}//End class
