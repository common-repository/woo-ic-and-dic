<?php
/**
 * @package   Musilda Ico Dic
 * @author    Musilda.com
 * @license   GPL-2.0+
 * @link      https://musilda.com
 * @copyright 2022 Musilda.com
 */

class Wid {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.1.5';

	/**
	 * Plugin slug
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'musilda-vat-number';

	/**
	 * Is plugin active
	 * @since    1.1.5
	 *
	 * @var      array
	 */
	protected $is_active = false;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		$this->plugin_init();

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

    	//Checkout fields           
    	add_filter( 'woocommerce_checkout_fields' , array( $this, 'billing_fields' ), 10 );
    	add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'my_account_fields' ), 10, 3 );
		add_filter( 'woocommerce_localisation_address_formats', array( $this, 'localisation_address_formats' ) );
    	add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'formatted_address_replacements' ), 10, 2 );
    	add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'order_formatted_billing_address' ), 10, 2 );

    	add_action( 'woocommerce_checkout_process', array( $this, 'checkout_field_process' ), 10, 2);
        
    	add_action( 'init', array( $this, 'output_buffer' ) );
    
    
 	}

	/**
	 * Init plugin variables
	 *
	 * @since    1.0.0
	 *
	 * @return    void
	 */
	public function plugin_init() {
		
		$activate_option = wc_clean( maybe_unserialize( get_option('woocommerce_checkout_vat_number') ) );
		if ( !empty( $activate_option ) && 'yes' == $activate_option ) {
			$this->is_active = $activate_option;
		}


	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 * "Network Activate" action, false if
	 * WPMU is disabled or plugin is
	 * activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {

	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain,  WIDDIR . 'languages/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
    	if( is_checkout() ) {
  			wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
    	}  
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if( is_checkout() ) {
        	wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
    	}
	}

  	/**
  	 * Add billing fields
  	 *
  	 * @since 1.0.0  
  	 */        
  	public function billing_fields( $fields ) {    	

    	if( false !== $this->is_active ){

    		$fields['billing']['billing_company_buy_on'] = array(
       		'type'        => 'checkbox',
		    	'label'       => __('Buy for the company', $this->plugin_slug),
		    	'placeholder' => __('Buy for the company', $this->plugin_slug),
	    		'required'    => false,
	    		'class'       => array('form-row-wide input-checkbox billing_company_buy_on input-checkbox'),
				'priority' 	  => 32
			);
   
    		$fields['billing']['billing_company_number'] = array(
		    	'label'       => __('Company number', $this->plugin_slug),
        		'type'        => 'text',
		    	'placeholder' => '',
		    	'required'    => false,
		    	'class'       => array('form-row-wide'),
		    	'clear'       => true,
				'priority' 	  => 33
			);
		
			$fields['billing']['billing_vat_number'] = array(
		    	'label'       => __('VAT number', $this->plugin_slug),
		    	'placeholder' => '',
		    	'required'    => false,
		    	'class'       => array('form-row-wide'),
		    	'clear'       => true,
				'priority' 	  => 34
			);
     
    		$fields['billing']['billing_vat_number_2'] = array(
			   'label'       => __('VAT number 2', $this->plugin_slug),
			   'placeholder' => '',
			   'required'    => false,
			   'class'       => array('form-row-wide'),
			   'clear'       => true,
			   'priority' 	  => 35
			);

    	} 
     
     	return $fields;
		
	}

  

  	/**
  	 * Add billing fields
  	 *
  	 * @since 1.0.0  
  	 */ 
  	public function my_account_fields( $fields, $customer_id, $name ) {
		
		$fields['company_number'] = get_user_meta( $customer_id, $name . '_company_number', true );
		$fields['vat_number']     = get_user_meta( $customer_id, $name . '_vat_number', true );
		$fields['vat_number_2']   = get_user_meta( $customer_id, $name . '_vat_number_2', true );
                                                                
    	return $fields;                                                                   
      
	} 
  
  	/**
  	 * Localisation address formats
  	 *
  	 * @since 1.0.0  
  	 */
  	public function localisation_address_formats( $address_formats ) {
		
    	$address_formats['CZ'] .= "\n{company_number}\n{vat_number}";
		$address_formats['SK'] .= "\n{company_number}\n{vat_number}\n{vat_number_2}";
		
    	return $address_formats;
    
	}
  
  	/**
  	 * Formated address replacement
  	 *
  	 * @since 1.0.0  
  	 */
	public function formatted_address_replacements( $replace, $args ) {
		
		if( !empty( $args['company_number'] ) ) {  
			$replace['{company_number}']       = $this->get_args_replacement($args['company_number'], __('Company number: ', $this->plugin_slug));
    	}else{ $replace['{company_number}'] = ''; }  
    	
    	if(!empty($args['vat_number'])){
     		$replace['{vat_number}']           = $this->get_args_replacement($args['vat_number'], __('VAT number: ', $this->plugin_slug));
    	}else{ $replace['{vat_number}'] = ''; }  
    
    	if(!empty($args['vat_number_2'])){  
			$replace['{vat_number_2}']         = $this->get_args_replacement($args['vat_number_2'], __('VAT number 2: ', $this->plugin_slug));
    	}else{ $replace['{vat_number_2}'] = ''; }  
    
    	if(!empty($args['company_number'])){  
			$replace['{company_number_upper}'] = strtoupper($this->get_args_replacement($args['company_number'], __('Company number: ', $this->plugin_slug)));
    	}else{ $replace['{company_number}'] = ''; }  
    
    	if(!empty($args['vat_number'])){  
			$replace['{vat_number_upper}']     = strtoupper($this->get_args_replacement($args['vat_number'], __('VAT number: ', $this->plugin_slug)));
    	}else{ $replace['{vat_number}'] = ''; }  
    
    	if(!empty($args['vat_number_2'])){  
			$replace['{vat_number_upper_2}']   = strtoupper($this->get_args_replacement($args['vat_number_2'], __('VAT number 2: ', $this->plugin_slug)));
    	}else{ $replace['{vat_number_2}'] = ''; }  
		
    	return $replace;
    
	}
  
  	/**
  	 * Get args replacements
  	 *
  	 * @since 1.0.0  
  	 */        
  	private function get_args_replacement($value, $label){
  
     	return (isset( $value ) && $value != '' ) ?  __($label, $this->plugin_slug) .$value : '';
     
  	} 
  
  	/**
  	 * Fotrmated billing address new fields
  	 *
  	 * @since 1.1.0  
  	 */ 
  	public function order_formatted_billing_address($address, $order) { 
		
		$address['company_number'] = $order->get_meta( '_billing_company_number' );
		$address['vat_number']	   = $order->get_meta( '_billing_vat_number' );
		$address['vat_number_2']   = $order->get_meta( '_billing_vat_number_2' );
        
        return $address;
          
	}

	/**
  	 * Formated billing address new fields
  	 *
  	 * @since 1.1.0  
  	 */ 
  	public function checkout_field_process() {

		if ( !isset( $_POST['billing_country'] ) ) {
			return false;
		}

		$country = sanitize_text_field( $_POST['billing_country'] );
	
		if ( !empty( $_POST['billing_company_number'] ) ) {	

			$billing_company_number = preg_replace('/\s+/', '', $_POST['billing_company_number']);
			$billing_company_number = sanitize_text_field( $billing_company_number );

			if ( $country == 'CZ' ) {
			
				//Ares
				$check_ic = $this->check_ic_from_ares( $billing_company_number );
				if ( 'ok' != $check_ic['error'] ) {
					wc_add_notice( $check_ic['stav'], 'error' );
				}

			} elseif ( $country == 'SK' ) {
				
				if ( ! $this->verify_ic( $billing_company_number ) ) {
					wc_add_notice( __( 'Enter a valid Business ID', $this->plugin_slug  ), 'error' );
				}

			}			

		}

		if ( !empty( $_POST['billing_vat_number'] ) ) {

			$billing_vat_number = preg_replace('/\s+/', '', $_POST['billing_vat_number']);
			$billing_vat_number = sanitize_text_field( $billing_vat_number );

			if ( $country == 'CZ' ) {

				if ( ! ( $this->verify_rc( substr( $billing_vat_number, 2 )) || $this->verify_ic( substr( $billing_vat_number, 2 ) ) ) || substr( $billing_vat_number, 0, 2) != "CZ") {
					wc_add_notice( __( 'Enter a valid VAT number', $this->plugin_slug ), 'error' );
				}

			} elseif ( $country == 'SK' ) {

				if ( ! $this->verify_dic_sk( $billing_vat_number ) ) {
					wc_add_notice( __( 'Enter a valid Tax ID', 'woolab-ic-dic' ), 'error' );
				}

			}

			//TODO - VIES control for EU countries except SK

		}			

		if ( !empty( $_POST['billing_vat_number_2'] ) ) {
			if ( $country == 'SK' ) {

				//TODO - VIES control for SK

			}
		}

  	}

	/**
  	 * Control data from ARES
  	 * Source: https://webtrh.cz/279860-script-nacitani-dat-ares-jquery
  	 *
  	 * @since 1.1.0  
  	 */ 
  	public function check_ic_from_ares( $ico ) { 

  		$url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico='; 
		$url = $url . $ico; 

		$curl = curl_init(); 
		curl_setopt( $curl, CURLOPT_URL, $url ); 
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); 
		curl_setopt( $curl, CURLOPT_HEADER, false ); 
		$data = curl_exec( $curl ); 
		curl_close( $curl ); 

		if ( $data ) $xml = simplexml_load_string( $data ); 

		$response = array(); 

		if ( isset( $xml ) ) { 
    		
    		$ns = $xml->getDocNamespaces(); 
    		$data = $xml->children($ns['are']); 
    		$el = $data->children($ns['D'])->VBAS; 
    		if (strval($el->ICO) == $ico) { 
        		$response['ico']     = strval($el->ICO); 
        		$response['dic']     = strval($el->DIC); 
        		$response['firma'] = strval($el->OF); 
        		$response['jmeno'] = ""; 
        		$response['prijmeni'] = ""; 
        		
        		// detekce jména a firmy .. 
        		
        		$firma = $response['firma']; 
        		$roz = explode(" ",$firma); 
        		$match = preg_match("/(s\.r\.o\.|s\. r\. o\.|spol\. s r\.o\.|a\.s\.|a\. s\.|v\.o\.s|v\. o\. s\.|o\.s\.|k\.s\.|kom\.spol\.)/",$firma); 
        		
        		if (count($roz) == 2 AND !$match) { 
            		// nenašli jsme shodu s firmou, pravděpodobně se jedná o živnostníka se jménem .. 
            		$response['jmeno'] = $roz[0]; 
            		$response['prijmeni'] = $roz[1]; 
        		} 

        		$response['ulice']    = strval($el->AA->NU); 
        		if (!empty($el->AA->CO) OR !empty($el->AA->CD)) { 
            		// detekování popisného a orientačního čísla 
            		$response['ulice'] .= " "; 
            		if (!empty($el->AA->CD)) $response['ulice'] .= strval($el->AA->CD); 
            		if (!empty($el->AA->CO) AND !empty($el->AA->CD)) $response['ulice'] .= "/"; 
            		if (!empty($el->AA->CO)) $response['ulice'] .= strval($el->AA->CO); 
        		} 

        		$response['mesto']    = strval($el->AA->N); 
        		$response['psc']      = strval($el->AA->PSC); 
        		$response['stav']     = 'ok'; 
        		$response['error']    = 'ok'; 

    		} else { 
        		
        		$response['stav']     = __( 'Company number was not found in ARES database', $this->plugin_slug ); 
        		$response['error']    = 'not_found'; 
    		
    		} 
		} else { 
    		
			//Todo - resolve when ARES is unvailable - user must be able to finish order
			//Maybe show this in admin - user is not checked in register
    		$response['stav']     = __( 'ARES database isnt available.', $this->plugin_slug ); 
			$response['error']    = 'ok'; 

		} 

		return $response;

	}

	/**
  	 * Verify CZ RČ
  	 * Source Kybernaut: https://github.com/vyskoczilova/kybernaut-ic-dic/blob/master/includes/helpers.php
  	 *
  	 * @since 1.1.0  
  	 */
	public function verify_rc( $rc ){
    	// be liberal in what you receive
    	if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) {
        	return FALSE;
    	}
    	
    	list(, $year, $month, $day, $ext, $c) = $matches;
    	
    	if ($c === '') {
	        $year += $year < 54 ? 1900 : 1800;
	    } else {
        	// controll number
        	$mod = ($year . $month . $day . $ext) % 11;
        	if ($mod === 10) $mod = 0;
        	if ($mod !== (int) $c) {
	            return FALSE;
        	}
        	$year += $year < 54 ? 2000 : 1900;
    	}
    
    	// there can be added 20, 50 or 70 to the month
    	if ($month > 70 && $year > 2003) {
        	$month -= 70;
    	} elseif ($month > 50) {
	        $month -= 50;
	    } elseif ($month > 20 && $year > 2003) {
        	$month -= 20;
    	}
    	// check date
    	if (!checkdate($month, $day, $year)) {
	        return FALSE;
	    }
	    return TRUE;
	}

	/**
  	 * Verify CZ IČ
  	 * Source Kybernaut: https://github.com/vyskoczilova/kybernaut-ic-dic/blob/master/includes/helpers.php
  	 *
  	 * @since 1.1.0  
  	 */
	public function verify_ic( $ic ){
   
    	// be liberal in what you receive
    	$ic = preg_replace('#\s+#', '', $ic);
    	
    	// check required format
    	if (!preg_match('#^\d{8}$#', $ic)) {
        	return FALSE;
    	}
    	// controll sum
    	$a = 0;
    	for ($i = 0; $i < 7; $i++) {
	        $a += $ic[$i] * (8 - $i);
	    }
	    $a = $a % 11;
	    if ($a === 0) {
        	$c = 1;
    	} elseif ($a === 1) {
        	$c = 0;
    	} else {
	        $c = 11 - $a;
	    }
	    return (int) $ic[7] === $c;
	}
 
	/**
	 * Headers allready sent fix
	 *
	 */        
  	public function output_buffer() {
		ob_start();
  	} 


	/**
  	 * Verify SK DIČ
  	 * Source Kybernaut: https://github.com/vyskoczilova/kybernaut-ic-dic/blob/master/includes/helpers.php
  	 *
  	 * @since 1.1.0  
  	 */
	  public function verify_dic_sk( $dic ){

		// be liberal in what you receive
		$dic = preg_replace('#\s+#', '', $dic);
	
		// check required format
		if (!preg_match('#^\d{10}$#', $dic)) {
			return FALSE;
		}
	
		return (int) $dic;
	}
 
}//End class

