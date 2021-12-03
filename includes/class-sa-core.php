<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Sa_Core {

	/**
	 * The single instance of Sa_Core.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;
	
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.2.2' ) {
		$this->_version = $version;
		
		$this->_token = 'sa_core';
		
		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		
		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		define( 'SA_CORE_ASSETS_DIR', trailingslashit( $this->dir ) . 'assets' );
		define( 'SA_CORE_ASSETS_URL', esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) ) );
		
		include( 'class-sa-core-users.php' );
		
		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		
		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		
		add_action( 'wp_ajax_handle_dropped_media', array( $this, 'sa_core_handle_dropped_media' ));
		add_action( 'wp_ajax_nopriv_handle_dropped_media', array( $this, 'sa_core_handle_dropped_media' ));
		add_action( 'wp_ajax_nopriv_handle_delete_media',  array( $this, 'sa_core_handle_delete_media' ));
		add_action( 'wp_ajax_handle_delete_media',  array( $this, 'sa_core_handle_delete_media' ));
		
		add_action( 'wp_ajax_handle_dropped_document', array( $this, 'sa_core_handle_dropped_document' ));
		add_action( 'wp_ajax_nopriv_handle_dropped_document', array( $this, 'sa_core_handle_dropped_document' ));
		add_action( 'wp_ajax_nopriv_handle_delete_document',  array( $this, 'sa_core_handle_delete_document' ));
		add_action( 'wp_ajax_handle_delete_document',  array( $this, 'sa_core_handle_delete_document' ));
		
		add_action( 'init', array( $this, 'image_size' ) );
		
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		
		$this->users 		= new Sa_Core_Users();
		
	} // End __construct ()
	
	public function include_template_functions() {
		include( REALTEO_PLUGIN_DIR.'/sa-core-template-functions.php' );
	}
	
	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		wp_register_style( $this->_token . '-icons', esc_url( $this->assets_url ) . 'css/icons.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-icons' );
		
		wp_register_style( $this->_token . '-apexcharts', esc_url( $this->assets_url ) . 'apexcharts/css/apexcharts.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-apexcharts' );
		
		wp_register_style( $this->_token . '-bootstrap-grid', esc_url( $this->assets_url ) . 'css/bootstrap-grid.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-bootstrap-grid' );
		
		wp_register_style( $this->_token . '-dashboard', esc_url( $this->assets_url ) . 'css/dashboard-style.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-dashboard' );
		
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
		
		if ( is_page( 'login' ) ) {
			wp_register_style( $this->_token . '-login', esc_url( $this->assets_url ) . 'css/login.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-login' );
		}
		
		
	} // End enqueue_styles ()
	
	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		wp_register_script( 'dropzone', esc_url( $this->assets_url ) . 'js/dropzone.js', array( 'jquery' ), $this->_version );
		wp_register_script( 'chosen-min', esc_url( $this->assets_url ) . 'js/chosen.min.js', array( 'jquery' ), $this->_version );
		wp_register_script( 'apexcharts', esc_url( $this->assets_url ) . 'apexcharts/js/apexcharts.min.js', array( 'jquery' ), $this->_version );
		wp_register_script( 'mmenu', esc_url( $this->assets_url ) . 'js/mmenu.min.js', array( 'jquery' ), $this->_version );
		wp_register_script( 'dashboard', esc_url( $this->assets_url ) . 'js/dashboard.js', array( 'jquery' ), $this->_version );
		
		wp_enqueue_script( 'dropzone' );
		wp_enqueue_script( 'chosen-min' );
		wp_enqueue_script( 'apexcharts' );
		wp_enqueue_script( 'mmenu' );
		wp_enqueue_script( 'dashboard' );
		
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), $this->_version );
		
		$ajax_url = admin_url( 'admin-ajax.php', 'relative' );
		
		$localize_array = array(
				'ajax_url'                	=> $ajax_url,
				'upload'					=> admin_url( 'admin-ajax.php?action=handle_dropped_media' ),
				'delete'					=> admin_url( 'admin-ajax.php?action=handle_delete_media' ),
  				'dictDefaultMessage'		=> esc_html__("Drop files here to upload","sa_core"),
				'dictFallbackMessage' 		=> esc_html__("Your browser does not support drag'n'drop file uploads.","sa_core"),
				'dictFallbackText' 			=> esc_html__("Please use the fallback form below to upload your files like in the olden days.","sa_core"),
				'dictFileTooBig' 			=> esc_html__("File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.","sa_core"),
				'dictInvalidFileType' 		=> esc_html__("You can't upload files of this type.","sa_core"),
				'dictResponseError'		 	=> esc_html__("Server responded with {{statusCode}} code.","sa_core"),
				'dictCancelUpload' 			=> esc_html__("Cancel upload","sa_core"),
				'dictCancelUploadConfirmation' => esc_html__("Are you sure you want to cancel this upload?","sa_core"),
				'dictRemoveFile' 			=> esc_html__("Remove file","sa_core"),
				'dictMaxFilesExceeded' 		=> esc_html__("You can not upload any more files.","sa_core"),
				'areyousure' 				=> esc_html__("Are you sure?","sa_core"),
				'maxFiles' 					=> get_option('listeo_max_files',10),
				'maxFilesize' 				=> get_option('listeo_max_filesize',2),
				'no_results_text'           => esc_html__("No results match","sa_core"),
				'placeholder_text_single'   => esc_html__("Select an Option","sa_core"),
				'placeholder_text_multiple' => esc_html__("Select Some Options","sa_core"),
				// settings array for documents upload
				'document_upload'					=> admin_url( 'admin-ajax.php?action=handle_dropped_document' ),
				'document_delete'					=> admin_url( 'admin-ajax.php?action=handle_delete_document' ),
  			);
			
		wp_localize_script(  $this->_token . '-frontend', 'sa_core', $localize_array);
		
		wp_enqueue_script( $this->_token . '-frontend' );
		
	} // End enqueue_scripts ()
	
	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()
	
	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		wp_register_script( $this->_token . '-settings', esc_url( $this->assets_url ) . 'js/settings' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-settings' );
		
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.js', array( 'jquery' ), $this->_version );
		
		$ajax_url = admin_url( 'admin-ajax.php', 'relative' );
		
		$localize_array = array(
				'ajax_url'                	=> $ajax_url,
			);
		wp_localize_script( $this->_token . '-admin', 'sa_core', $localize_array);
		
		wp_enqueue_script( $this->_token . '-admin' );	
		
	} // End admin_enqueue_scripts ()
	
	/**
	 * Adds image sizes
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function image_size () {
		add_image_size('sa_core-avatar', 590, 590, true);
		add_image_size('sa_core-preview', 200, 200, true);

	} // End image_size ()
	
	public function sa_core_handle_delete_media() {

	    if( isset($_REQUEST['media_id']) ){
	        $post_id = absint( $_REQUEST['media_id'] );
	        $status = wp_delete_attachment($post_id, true);
	        if( $status )
	            echo json_encode(array('status' => 'OK'));
	        else
	            echo json_encode(array('status' => 'FAILED'));
	    }
	    wp_die();
	}

	public function sa_core_handle_dropped_media() {
	    status_header(200);

	    $upload_dir = wp_upload_dir();
	    $upload_path = $upload_dir['path'] . DIRECTORY_SEPARATOR;
	    $num_files = count($_FILES['file']['tmp_name']);

	    $newupload = 0;

	    if ( !empty($_FILES) ) {
	        $files = $_FILES;
	        foreach($files as $file) {
	            $newfile = array (
	                    'name' => $file['name'],
	                    'type' => $file['type'],
	                    'tmp_name' => $file['tmp_name'],
	                    'error' => $file['error'],
	                    'size' => $file['size']
	            );

	            $_FILES = array('upload'=>$newfile);
	            foreach($_FILES as $file => $array) {
	                $newupload = media_handle_upload( $file, 0 );
	            }
	        }
	    }

	    echo $newupload;    
	    wp_die();
	}
	
	public function sa_core_handle_dropped_document() {
	    status_header(200);
		
		$upload_dir = wp_upload_dir();
	    $upload_path = $upload_dir['path'] . DIRECTORY_SEPARATOR;
	    $num_files = count($_FILES['file']['tmp_name']);

	    $newupload = 0;
		if ( !empty($_FILES) ) {
	        $files = $_FILES;
	        foreach($files as $file) {
	            $newfile = array (
	                    'name' => $file['name'],
	                    'type' => $file['type'],
	                    'tmp_name' => $file['tmp_name'],
	                    'error' => $file['error'],
	                    'size' => $file['size']
	            );

	            $_FILES = array('upload'=>$newfile);
	            foreach($_FILES as $file => $array) {
	                $media_id = media_handle_upload( $file, 0 );
					$media = get_attached_file($media_id);
					$response = $this->upload_on_sypht($media);
					if(isset($response['code']) && $response['code'] == 1) {
						$this->extract_data_from_sypht_and_save($response['fileId'], $media_id);
					}
	            }
	        }
	    }

	    echo $media_id;    
	    wp_die();
	}
	
	public function sa_core_handle_delete_document() {
		if( isset($_REQUEST['media_id']) ){
	        $post_id = absint( $_REQUEST['media_id'] );
	        $status = wp_delete_attachment($post_id, true);
	        if( $status )
	            echo json_encode(array('status' => 'OK'));
	        else
	            echo json_encode(array('status' => 'FAILED'));
	    }
	    wp_die();
	}
	
	public function upload_on_sypht($media_path = null) {
		if ($media_path == null || !file_exists($media_path)) {
			return false;
		}
		$syphtObj = new SyphtClass();
		$response = $syphtObj->upload_documents($media_path);
		return $response;
	}
	
	public function extract_data_from_sypht_and_save($file_id = null, $media_id = null) {
		if ($file_id == null) {
			return false;
		}
		global $wpdb;
		$syphtObj = new SyphtClass();
		$response = $syphtObj->get_document_extract_data($file_id);
		if (isset($response['code']) && $response['code'] == 1 && isset($response['data']['results']['fields'])) {
            if (!empty( $response['data']['results']['fields'])) {
				$field_arr = $response['data']['results']['fields'];
				
				$crn = "";
				$billerCode = "";
				$billingPeriodFromDate = "";
				$billingPeriodNumberOfDays = "";
				$billingPeriodToDate = "";
				$supplierGSTN = "";
				$supplierABN = "";
				$supplierName = "";
				$tax = "";
				$dueDate = "";
				$amountPaid = "";
				$purchaseOrderNo = "";
				$amountDue = "";
				$subTotal = "";
				$total = "";
				$gst = "";
				$accountNo = "";
				$bsb = "";
				$referenceNo = "";
				$date = "";
				
				foreach ($field_arr as $key => $value) {
					// If field is document skip this field
					if( is_array( $value['value'] ) ){
						continue;
					}
					
					if (strcmp($value['name'], "bpay.crn") == 0)
						$crn = $value['value'];
					if (strcmp($value['name'], "bpay.billerCode") == 0)
						$billerCode = $value['value'];
					if (strcmp($value['name'], "bill.billingPeriodFromDate") == 0)
						$billingPeriodFromDate = $value['value'];
					if (strcmp($value['name'], "bill.billingPeriodNumberOfDays") == 0)
						$billingPeriodNumberOfDays = $value['value'];
					if (strcmp($value['name'], "bill.billingPeriodToDate") == 0)
						$billingPeriodToDate = $value['value'];
					if (strcmp($value['name'], "document.supplierGSTN") == 0)
						$supplierGSTN = $value['value'];
					if (strcmp($value['name'], "document.supplierABN") == 0)
						$supplierABN = $value['value'];
					if (strcmp($value['name'], "invoice.tax") == 0)
						$tax = $value['value'];
					if (strcmp($value['name'], "invoice.dueDate") == 0)
						$dueDate = $value['value'];
					if (strcmp($value['name'], "invoice.amountPaid") == 0)
						$amountPaid = $value['value'];
					if (strcmp($value['name'], "invoice.purchaseOrderNo") == 0)
						$purchaseOrderNo = $value['value'];
					if (strcmp($value['name'], "invoice.paymentDate") == 0)
						$paymentDate = $value['value'];
					if (strcmp($value['name'], "invoice.amountDue") == 0)
						$amountDue = $value['value'];
					if (strcmp($value['name'], "invoice.subTotal") == 0)
						$subTotal = $value['value'];
					if (strcmp($value['name'], "invoice.total") == 0)
						$total = $value['value'];
					if (strcmp($value['name'], "invoice.gst") == 0)
						$gst = $value['value'];
					if (strcmp($value['name'], "bank.accountNo") == 0)
						$accountNo = $value['value'];
					if (strcmp($value['name'], "bank.bsb") == 0)
						$bsb = $value['value'];
					if (strcmp($value['name'], "document.referenceNo") == 0)
						$referenceNo = $value['value'];
					if (strcmp($value['name'], "document.date") == 0)
						$date = $value['value'];
				}
				
				$user_id = get_current_user_id();
				$is_found = $wpdb->get_var( 'SELECT COUNT(*) FROM `' . $wpdb->prefix . 'user_email_data_new` WHERE user_id = "'. $user_id .'" AND document_supplierABN = "'. $supplierABN .'" AND document_date = "'. $date .'"' );
				if ($is_found == 0) {
					$supplierName = $this->get_supplier_name($supplierABN);
					$wpdb->insert($wpdb->prefix . 'user_email_data_new', array(
                            "gsuite_emails_id"              => 0,
                            "user_id"                       => $user_id,
                            "bpay_crn"                      => $crn,
                            "bpay_billerCode"               => $billerCode,
                            "bill_billingPeriodFromDate"    => $billingPeriodFromDate,
                            "bill_billingPeriodNumberOfDays"=> $billingPeriodNumberOfDays,
                            "bill_billingPeriodToDate"      => $bill_billingPeriodToDate,
                            "document_supplierGSTN"         => $supplierGSTN,
                            "document_supplierABN"          => $supplierABN,
                            "supplier_name"					=> $supplierName,
                            "invoice_tax"                   => $tax,
                            "invoice_dueDate"               => $dueDate,
                            "invoice_amountPaid"            => $amountPaid,
                            "invoice_purchaseOrderNo"       => $purchaseOrderNo,
                            "invoice_amountDue"             => $amountDue,
                            "invoice_subTotal"              => $subTotal,
                            "invoice_total"                 => $total,
                            "invoice_gst"                   => $gst,
                            "invoice_paymentDate"           => $paymentDate,
                            "bank_accountNo"                => $accountNo,
                            "bank_bsb"                      => $bsb,
                            "document_referenceNo"          => $referenceNo,
                            "document_date"                 => $date,
                            "email_attachment_id"           => $media_id,
                            "email_attachment_url"          => wp_get_attachment_url($media_id),
							"source"                        => 1
                        ));
				}
			}	
		}
	}
	
	public function get_supplier_name($supplierABN) {
		$url = "https://abr.business.gov.au/json/AbnDetails.aspx?abn=".$supplierABN."&guid=1343a7aa-5dc1-45eb-af8d-b36bb0b8fa21";
		$curl = curl_init();
			
		curl_setopt_array($curl, array(
			CURLOPT_URL 				=> $url,
			CURLOPT_RETURNTRANSFER 		=> true,
			CURLOPT_ENCODING 			=> "",
			CURLOPT_MAXREDIRS 			=> 10,
			CURLOPT_TIMEOUT 			=> 30,
			CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 		=> "GET",
			CURLOPT_HTTPHEADER 			=> array(
											"cache-control: no-cache"
										),
		));
			
		$response = curl_exec($curl);
		$err = curl_error($curl);
			
		curl_close($curl);
			
		if ($err) {
			echo "cURL Error #:" . $err;
			$entity_name = '';
		} else {
			$response = str_replace("callback(","",$response);
			$response = str_replace(")","",$response);
			$response = json_decode($response);
			$entity_name_uppercase = strtolower($response->EntityName);
			$entity_name = ucwords($entity_name_uppercase);
		}
		return $entity_name;
	}
	
	/**
	 * Main Sa_Core Instance
	 *
	 * Ensures only one instance of Sa_Core is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Sa_Core()
	 * @return Main Sa_Core instance
	 */
	public static function instance ( $file = '', $version = '1.2.1' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()
}