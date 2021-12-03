<?php
// Exit if accessed directly
// https://github.com/jarkkolaine/personalize-login-tutorial-part-3
if ( ! defined( 'ABSPATH' ) )
	exit;
/**
 * Sa_Core_Users class
 */
class Sa_Core_Users {

	/**
	 * Dashboard message.
	 *
	 * @access private
	 * @var string
	 */
	private $dashboard_message = '';

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;
	/**
	 * Constructor
	 */
	public function __construct() {
		
		add_action( 'init', array( $this, 'submit_my_account_form' ), 10 );
		add_action( 'init', array( $this, 'submit_change_password_form' ), 10 );
		add_action( 'init', array( $this, 'submit_account_settings_form' ), 10 );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_profile_fields' ));
		
		add_action( 'wp_ajax_nopriv_update_user_document_tag', array( $this, 'update_user_document_tag' ) );
		add_action( 'wp_ajax_update_user_document_tag', array( $this, 'update_user_document_tag' ) );
		
		add_shortcode( 'sa_my_account', array( $this, 'my_account' ) );
		add_shortcode( 'sa_dashboard', array( $this, 'sa_dashboard' ) );
		add_shortcode( 'sa_settings', array( $this, 'sa_settings' ) );
		add_shortcode( 'sa_my_documents', array( $this, 'sa_my_documents' ) );
		add_shortcode( 'sa_supplier_documents', array( $this, 'sa_supplier_documents' ) );
		add_shortcode( 'sa_documents_upload', array( $this, 'sa_documents_upload' ) );
		add_shortcode( 'sa_due_documents', array( $this, 'sa_due_documents' ) );
		
		add_shortcode( 'sa_my_coupons', array( $this, 'sa_my_coupons' ) );
		add_shortcode( 'sa_my_warranties', array( $this, 'sa_my_warranties' ) );
		add_shortcode( 'sa_my_insurances', array( $this, 'sa_my_insurances' ) );
		
		add_filter( 'get_avatar', array( $this, 'sa_core_gravatar_filter' ), 10, 6);
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}
	
	/**
	 * add_query_vars()
	 *
	 * Adds query vars for search and display.
	 *
	 * @param integer $vars Post ID
	 *
	 * @since 1.0.0
	 */
	public function add_query_vars($vars) {
		
		$new_vars = array();

        array_push($new_vars, 'status', 'sort', 'order');
	
	    $vars = array_merge( $new_vars, $vars );
		return $vars;

	}
	
	public function my_account( $atts = array() ) {
		$template_loader = new Sa_Core_Template_Loader;
		ob_start();
		if ( is_user_logged_in() ) : 
		$template_loader->get_template_part( 'my-account' ); 
		else :
		$template_loader->get_template_part( 'account/login' ); 
		endif;
		return ob_get_clean();
	}	
	
	/**
	 * User dashboard
	 */
	public function sa_dashboard( $atts ) {

		if ( ! is_user_logged_in() ) {
			
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}

		extract( shortcode_atts( array(
			//'posts_per_page' => '25',
		), $atts ) );

		ob_start();

		$template_loader = new Sa_Core_Template_Loader;		
		$template_loader->set_template_data( 
			array( 
				'message' => $this->dashboard_message, 

			) )->get_template_part( 'account/dashboard' ); 


		return ob_get_clean();
	}	
	
	function save_extra_profile_fields( $user_id ) {

		if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
		if(isset($_POST['sa_core_avatar_id'])) {
			update_user_meta( $user_id, 'sa_core_avatar_id', $_POST['sa_core_avatar_id'] );	
		}
	
	}
	
	function submit_my_account_form() {
		global $blog_id, $wpdb;
		if ( isset( $_POST['my-account-submission'] ) && '1' == $_POST['my-account-submission'] ) {
			$current_user = wp_get_current_user();
			$error = array();  

			if ( !empty( $_POST['url'] ) ) {
		       	wp_update_user( array ('ID' => $current_user->ID, 'user_url' => esc_attr( $_POST['url'] )));
			}

		    if ( isset( $_POST['email'] ) ){

		        if (!is_email(esc_attr( $_POST['email'] ))) {
		            $error = 'error_1'; // __('The Email you entered is not valid.  please try again.', 'profile');
		        	
		        } else {
		        	if(email_exists(esc_attr( $_POST['email'] ) ) ) {
		        		if(email_exists(esc_attr( $_POST['email'] ) ) != $current_user->ID) {
		        			$error = 'error_2'; // __('This email is already used by another user.  try a different one.', 'profile');	
		        		}
		            	
		        	} else {
		            $user_id = wp_update_user( 
		            	array (
		            		'ID' => $current_user->ID, 
		            		'user_email' => esc_attr( $_POST['email'] )
		            	)
		            );
		            }
		        }
		    }
			
			if ( isset( $_POST['alternative_email'] ) ) {
				$filter_emails = array();
				$users = get_users(array(
					'exclude' => array($current_user->ID),
				));
				if (!empty($users)) {
					foreach($users as $user) {
						array_push($filter_emails, $user->user_email);
						$alter_emails = array_filter(get_the_author_meta( 'alternative_email', $user->ID ));
						if (!empty($alter_emails)) {
							array_merge($filter_emails, $alter_emails);
						}
					}
				}
				
				$matched_emails = array_intersect($_POST['alternative_email'], $filter_emails);
				if (!empty($matched_emails)) {
					$error = 'error_3'; // __('This email is already used by another user.  try a different one.', 'profile');	
				}
			}

		    if ( isset( $_POST['first-name'] ) ) {
		        update_user_meta( $current_user->ID, 'first_name', esc_attr( $_POST['first-name'] ) );
		    }
		    
		    if ( isset( $_POST['last-name'] ) ) {
		        update_user_meta( $current_user->ID, 'last_name', esc_attr( $_POST['last-name'] ) );
		    }		    

		    if ( isset( $_POST['phone'] ) ) {
		        update_user_meta( $current_user->ID, 'phone', esc_attr( $_POST['phone'] ) );
		    }	

			if ( isset( $_POST['alternative_email'] ) && !empty( $_POST['alternative_email'] ) ) {
				update_user_meta( $current_user->ID, 'alternative_email', $_POST['alternative_email'] );
			}
		    
		    if ( isset( $_POST['sa_core_avatar_id'] ) ) {
		        update_user_meta( $current_user->ID, 'sa_core_avatar_id', esc_attr( $_POST['sa_core_avatar_id'] ) );
		    }

			if ( count($error) == 0 ) {
		        //action hook for plugins and extra fields saving
		        //do_action('edit_user_profile_update', $current_user->ID);
		        wp_redirect( get_permalink().'?updated=true' ); 
		        exit;
		    } else {
				wp_redirect( get_permalink().'?user_err_pass='.$error ); 
				exit;
				 
			} 
		} // end if

	} // end 
	
	public function submit_change_password_form(){
		$error = false;
		if ( isset( $_POST['sa_core-password-change'] ) && '1' == $_POST['sa_core-password-change'] ) {
			$current_user = wp_get_current_user();
			if ( !empty($_POST['current_pass']) && !empty($_POST['pass1'] ) && !empty( $_POST['pass2'] ) ) {

				if ( !wp_check_password( $_POST['current_pass'], $current_user->user_pass, $current_user->ID) ) {
					/*$error = 'Your current password does not match. Please retry.';*/
					$error = 'error_1';
				} elseif ( $_POST['pass1'] != $_POST['pass2'] ) {
					/*$error = 'The passwords do not match. Please retry.';*/
					$error = 'error_2';
				} elseif ( strlen($_POST['pass1']) < 4 ) {
					/*$error = 'A bit short as a password, don\'t you think?';*/
					$error = 'error_3';
				} elseif ( false !== strpos( wp_unslash($_POST['pass1']), "\\" ) ) {
					/*$error = 'Password may not contain the character "\\" (backslash).';*/
					$error = 'error_4';
				} else {
					$user_id  = wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => esc_attr( $_POST['pass1'] ) ) );
					
					if ( is_wp_error( $user_id ) ) {
						/*$error = 'An error occurred while updating your profile. Please retry.';*/
						$error = 'error_5';
					} else {
						$error = false;
						do_action('edit_user_profile_update', $current_user->ID);
				        wp_redirect( get_permalink().'?updated_pass=true' ); 
				        exit;
					}
				}
			
				if ( $error ) {
					do_action('edit_user_profile_update', $current_user->ID);
			        wp_redirect( get_permalink().'?updated_pass=true' ); 
			        exit;
				} else {
					wp_redirect( get_permalink().'?err_pass='.$error ); 
					exit;
					 
				}
				
			}
		} // end if
	}
	
	function sa_core_gravatar_filter($avatar, $id_or_email, $size, $default, $alt, $args) {
		
		if(is_object($id_or_email)) {
	      // Checks if comment author is registered user by user ID
	      
	      if($id_or_email->user_id != 0) {
	        $email = $id_or_email->user_id;
	      // Checks that comment author isn't anonymous
	      } elseif(!empty($id_or_email->comment_author_email)) {
	        // Checks if comment author is registered user by e-mail address
	        $user = get_user_by('email', $id_or_email->comment_author_email);
	        // Get registered user info from profile, otherwise e-mail address should be value
	        $email = !empty($user) ? $user->ID : $id_or_email->comment_author_email;
	      }
	      $alt = $id_or_email->comment_author;
	    } else {
	      if(!empty($id_or_email)) {
	        // Find user by ID or e-mail address
	        $user = is_numeric($id_or_email) ? get_user_by('id', $id_or_email) : get_user_by('email', $id_or_email);
	      } else {
	        // Find author's name if id_or_email is empty
	        $author_name = get_query_var('author_name');
	        if(is_author()) {
	          // On author page, get user by page slug
	          $user = get_user_by('slug', $author_name);
	        } else {
	          // On post, get user by author meta
	          $user_id = get_the_author_meta('ID');
	          $user = get_user_by('id', $user_id);
	        }
	      }
	      // Set user's ID and name
	      if(!empty($user)) {
	        $email = $user->ID;
	        $alt = $user->display_name;
	      }
	    }
		if( is_email( $email ) && ! email_exists( $email ) ) {
			return $avatar;
		}
	

		$class = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );

		if ( ! $args['found_avatar'] || $args['force_default'] ) {
			$class[] = 'avatar-default';
		}

		if ( $args['class'] ) {
			if ( is_array( $args['class'] ) ) {
				$class = array_merge( $class, $args['class'] );
			} else {
				$class[] = $args['class'];
			}
		}

		$custom_avatar_id = get_user_meta($email, 'sa_core_avatar_id', true); 
		$custom_avatar = wp_get_attachment_image_src($custom_avatar_id,'sa_core-avatar');
		if ($custom_avatar)  {
			$return = '<img src="'.$custom_avatar[0].'" class="'.esc_attr( join( ' ', $class ) ).'" width="'.$size.'" height="'.$size.'" alt="'.$alt.'" />';
		} elseif ($avatar) {
			$return = $avatar;
		} else {
			$return = '<img src="'.$default.'" class="'.esc_attr( join( ' ', $class ) ).'" width="'.$size.'" height="'.$size.'" alt="'.$alt.'" />';
		}
		
		return $return;
		
	}
	
	/**
	 * User account settings
	 */
	public function sa_settings() {
		
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		ob_start();

		$template_loader = new Sa_Core_Template_Loader;		
		$template_loader->get_template_part( 'settings' ); 

		return ob_get_clean();
		
	}
	
	public function submit_account_settings_form() {
		$error = false;
		if ( isset( $_POST['sa_core-settings-change'] ) && '1' == $_POST['sa_core-settings-change'] ) {
			$current_user = wp_get_current_user();
			
			if ( isset( $_POST['reminder'] ) ) {
		        update_user_meta( $current_user->ID, 'reminder', esc_attr( $_POST['reminder'] ) );
		    }
			
			if ( isset( $_POST['reminder_day'] ) ) {
		        update_user_meta( $current_user->ID, 'reminder_day', esc_attr( $_POST['reminder_day'] ) );
		    }
			
			if ( isset( $_POST['reminder_type'] ) ) {
		        update_user_meta( $current_user->ID, 'reminder_type', esc_attr( $_POST['reminder_type'] ) );
		    }
			
			if ( isset( $_POST['tags'] ) ) {
		        update_user_meta( $current_user->ID, 'tags', $_POST['tags'] );
		    }
			
			if ( $error ) {
				wp_redirect( get_permalink().'?err_settings='.$error ); 
				exit;
			} else {
				wp_redirect( get_permalink().'?updated_settings=true' ); 
				exit;
			}
		}
	}
	
	/**
	 * User documents
	 */
	public function sa_my_documents() {
		global $wpdb;
		
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		ob_start();

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$joinTable = $joinCondition = '';
		
		if ( get_query_var( 'status' ) ) { 
			$status = get_query_var( 'status' ); 
			if ($status == 'past') {
				$heading = 'Past Documents';
			} else {
				$heading = 'Upcoming Documents';
			}
		} else { 
			$status = 'upcoming'; 
			$heading = 'Upcoming Documents';
		}
		
		if ( isset( $_GET['document_paged'] ) ) { 
			$page = $_GET['document_paged']; 
		} else { 
			$page = 1; 
		}
		
		$tag_filter = NULL;
		if ( get_query_var( 'tag' ) ) { 
			$tag_filter = get_query_var( 'tag' ); 
			if (!empty($tag_filter)) {
				$joinTable = ' INNER JOIN `'. $wpdb->prefix . 'user_document_tags` As doc_tag ON doc_tag.email_attachment_id = doc.email_attachment_id ';
				$joinCondition = ' AND doc_tag.`tag` = "'. $tag_filter .'"';
			}
		}
		
		$sort = 'doc.`invoice_dueDate`';
		$sort_param = null;
		if ( get_query_var( 'sort' ) ) { 
			$sort_param = $sort = get_query_var( 'sort' );
			if ($sort == 'supplier') {
				$sort = 'doc.`supplier_name`';
			}
			if ($sort == 'amount_due') {
				$sort = 'doc.`invoice_amountDue`';
			}
		}
		$order = null;
		if ( get_query_var( 'order' ) ) { 
			$order = get_query_var( 'order' );
		}		
		
		$records_per_page = 20;
		$offset = ( $page * $records_per_page ) - $records_per_page;
		
		if ($status == 'past') {
			$total_records = $wpdb -> get_var( "SELECT COUNT(*) FROM `" . $wpdb->prefix . "user_email_data_new` As doc $joinTable WHERE doc.`user_id` = '$user_id' AND doc.`invoice_dueDate` < NOW() $joinCondition ORDER BY $sort $order" );
			$records = $wpdb -> get_results( "SELECT * FROM `" . $wpdb->prefix . "user_email_data_new` As doc $joinTable WHERE doc.`user_id` = '$user_id' AND doc.`invoice_dueDate` < NOW() $joinCondition ORDER BY $sort $order LIMIT $offset, $records_per_page", "ARRAY_A" );
		} else {
			$total_records = $wpdb -> get_var( "SELECT COUNT(*) FROM `" . $wpdb->prefix . "user_email_data_new` As doc $joinTable WHERE doc.`user_id` = '$user_id' AND doc.`invoice_dueDate` > NOW() $joinCondition ORDER BY $sort $order" );
			$records = $wpdb -> get_results( "SELECT * FROM `" . $wpdb->prefix . "user_email_data_new` As doc $joinTable WHERE doc.`user_id` = '$user_id' AND doc.`invoice_dueDate` > NOW() $joinCondition ORDER BY $sort $order LIMIT $offset, $records_per_page", "ARRAY_A" );
		}
		$max_num_pages = ceil($total_records / $records_per_page);
		
		$tags = get_user_meta( $user_id, 'tags', true );
		if (!empty($tags)) {
			$tags = array_column(json_decode($tags, true), 'value');
		}
		
		$userDocumentTag = $wpdb -> get_results( "SELECT `email_attachment_id`,`tag` FROM `" . $wpdb->prefix . "user_document_tags` WHERE user_id = '$user_id'", "ARRAY_A" );
		$userDocumentTag = array_combine(array_column($userDocumentTag, 'email_attachment_id'), array_column($userDocumentTag, 'tag'));
	
		$template_loader = new Sa_Core_Template_Loader;		
		$template_loader->set_template_data( 
			array( 
				'heading' => $heading,
				'records' => $records, 
				'total_pages' => $max_num_pages,
				'record_index' => $offset,
				'tags' => $tags,
				'filter_tag' => $tag_filter,
				'user_document_tag' => $userDocumentTag,
				'status' => $status,
				'sort' => $sort_param,
				'order' => $order
			) )->get_template_part( 'my-documents' );

		return ob_get_clean();
	}
	
	public function update_user_document_tag() {
		global $wpdb;
		$result = NULL;
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		if (isset($_POST['documentID']) && $user_id != 0) {
			$table = $wpdb->prefix . 'user_document_tags';
			$documentID = $_POST['documentID'];
			$userDocumentTag = $wpdb -> get_results( "SELECT * FROM `" . $wpdb->prefix . "user_document_tags` WHERE user_id = '$user_id' AND email_attachment_id= '$documentID'", "ARRAY_A" );
			if (!empty($userDocumentTag)) {
				$data = array('tag' => $_POST['tag']);
				$where = array('user_id' => $user_id, 'email_attachment_id' => $documentID);
				$wpdb->update( $table, $data, $where );
				$result = array(
					'success' => 1,
					'error' => 0,
					'message' => 'Success',
					'data' => NULL
				);
			} else {
				$data = array('user_id' => $user_id, 'email_attachment_id' => $documentID, 'tag' => $_POST['tag']);
				$wpdb->insert( $table, $data );
				$result = array(
					'success' => 1,
					'error' => 0,
					'message' => 'Success',
					'data' => NULL
				);
			}
		}
		echo json_encode($result);
		exit;
	}
	
	/**
	 * Supplier documents
	 */
	public function sa_supplier_documents() {
		global $wpdb;
		
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		if ( !isset( $_GET['supplier'] ) || empty($_GET['supplier']) ) {
			return __( 'You must pass supplier.', 'sa_core' );
		}
		
		$supplier = $_GET['supplier'];
		
		ob_start();

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
				
		if ( isset( $_GET['document_paged'] ) ) { 
			$page = $_GET['document_paged']; 
		} else { 
			$page = 1; 
		}
		
		$records_per_page = 20;
		$offset = ( $page * $records_per_page ) - $records_per_page;
		
		$total_records = $wpdb -> get_var( "SELECT COUNT(*) FROM `" . $wpdb->prefix . "user_email_data_new` As doc WHERE doc.`user_id` = '$user_id' AND doc.`document_supplierABN` = '$supplier' ORDER BY doc.`invoice_dueDate` DESC" );
		$records = $wpdb -> get_results( "SELECT * FROM `" . $wpdb->prefix . "user_email_data_new` As doc WHERE doc.`user_id` = '$user_id' AND doc.`document_supplierABN` = '$supplier' ORDER BY doc.`invoice_dueDate` DESC LIMIT $offset, $records_per_page", "ARRAY_A" );
		$max_num_pages = ceil($total_records / $records_per_page);
		
		$heading = NULL;
		if (!empty($records)) {
			$first_rec = current($records);
			$heading = $first_rec['supplier_name'];
		}
		
		$template_loader = new Sa_Core_Template_Loader;		
		$template_loader->set_template_data( 
			array( 
				'heading' => $heading,
				'records' => $records, 
				'total_pages' => $max_num_pages,
				'record_index' => $offset,
			) )->get_template_part( 'supplier-documents' );

		return ob_get_clean();
		
	}
	
	/**
	 * Documents upload
	 */
	public function sa_documents_upload() {
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		ob_start();

		$template_loader = new Sa_Core_Template_Loader;		
		$template_loader->get_template_part( 'upload-documents' ); 

		return ob_get_clean();
	}

	/**
	 * Due Documents
	 */
	public function sa_due_documents() {
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		ob_start();

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;

		global $wpdb;
		$documents = $wpdb -> get_results( "SELECT * FROM `" . $wpdb->prefix . "user_email_data_new` As doc WHERE doc.`user_id` = '$user_id' AND doc.`invoice_dueDate` > NOW() ORDER BY doc.`invoice_dueDate` DESC", "ARRAY_A" );
		//echo '<pre>'; print_r($documents);
		//exit;
		$dueDocuments = array();
		foreach($documents as $key => $document) {
			$dueDocuments[$key]['id'] = $document['id'];
			$dueDocuments[$key]['title'] = $document['supplier_name'] . ' Due, $'. $document['invoice_amountDue'];
			$dueDocuments[$key]['start'] = $document['invoice_dueDate'];
			$dueDocuments[$key]['url'] = $document['email_attachment_url'];
		}
		
		$template_loader = new Sa_Core_Template_Loader;		
		$template_loader->set_template_data(
			array(
				'dueDocuments' => $dueDocuments
			) )->get_template_part( 'due-documents-graph' ); 

		return ob_get_clean();
	}
	
	/**
	 * User coupons
	 */
	public function sa_my_coupons() {
		global $wpdb;
		
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		ob_start();

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		
		$template_loader = new Sa_Core_Template_Loader;	
		$template_loader->set_template_data( 
			array( 
				//'records' => $records, 
				//'total_pages' => $max_num_pages,
				//'record_index' => $offset,
			) )->get_template_part( 'my-coupons' );

		return ob_get_clean();
	}
	
	/**
	 * User coupons
	 */
	public function sa_my_warranties() {
		global $wpdb;
		
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		ob_start();

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		
		$template_loader = new Sa_Core_Template_Loader;	
		$template_loader->set_template_data( 
			array( 
				//'records' => $records, 
				//'total_pages' => $max_num_pages,
				//'record_index' => $offset,
			) )->get_template_part( 'my-warranties' );

		return ob_get_clean();
	}
	
	/**
	 * User insurances
	 */
	public function sa_my_insurances() {
		global $wpdb;
		
		if ( ! is_user_logged_in() ) {
			return __( 'You need to be signed in to access your dashboard.', 'sa_core' );
		}
		
		ob_start();

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		
		$template_loader = new Sa_Core_Template_Loader;	
		$template_loader->set_template_data( 
			array( 
				//'records' => $records, 
				//'total_pages' => $max_num_pages,
				//'record_index' => $offset,
			) )->get_template_part( 'my-insurances' );

		return ob_get_clean();
	}
}