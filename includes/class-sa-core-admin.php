<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Sa_Core_Admin {
	
	/**
     * The single instance of WordPress_Plugin_Template_Settings.
     * @var     object
     * @access  private
     * @since   1.0.0
     */
    private static $_instance = null;
	
	/**
     * The main plugin object.
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $parent = null;
	
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
     * Prefix for plugin settings.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $base = '';
	
	/**
     * Available settings for plugin.
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $settings = array();
	
	public function __construct ( $parent ) {

        $this->parent = $parent;
        $this->_token = 'sa';

        
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

        $this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';



        $this->base = 'sa_';
		
		// Initialise settings
        add_action( 'init', array( $this, 'init_settings' ), 11 );

        // Register plugin settings
        add_action( 'admin_init' , array( $this, 'register_settings' ) );

        // Add settings page to menu
        add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

        //add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 1 );
		
	}
	
	/**
     * Initialise settings
     * @return void
     */
    public function init_settings () {
        $this->settings = $this->settings_fields();
	}
	
	/**
     * Add settings page to admin menu
     * @return void
     */
    public function add_menu_item () {
		$page = add_menu_page( __( 'SA Core ', 'sa_core' ) , __( 'SA Core', 'sa_core' ) , 'manage_options' , $this->_token . '_settings' ,  array( $this, 'settings_page' ) );
     
		add_submenu_page($this->_token . '_settings', 'Pages', 'Pages', 'manage_options', 'sa_settings&tab=pages',  array( $this, 'settings_page' ) ); 
		add_submenu_page($this->_token . '_settings', 'Supplier Colors', 'Supplier Colors', 'manage_options', 'sa_settings&tab=supplier_colors', array( $this, 'settings_page' ) ); 
		add_submenu_page($this->_token . '_settings', 'Export', 'Export', 'manage_options', 'sa_settings&tab=export', array( $this, 'settings_page' ) ); 
	}
	
	 /**
     * Build settings fields
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields () {
		
		$settings['general'] = array(
            'title'                 => __( 'General', 'sa_core' ),
            'description'           => __( 'General SA settings.', 'sa_core' ),
            'fields'                => array(
                array(
                    'label'      => __('Logo image', 'sa_core'),
                    'description'      => __('Upload logo for your website', 'sa_core'),
                    'id'        => 'site_logo',
                    'type'      => 'image'
                ),
				array(
                    'label'      => __('Dashboard Logo image', 'sa_core'),
                    'description'      => __('Upload dashboard logo for your website', 'sa_core'),
                    'id'        => 'dashboard_logo',
                    'type'      => 'image'
                ),
				array(
                    'label'      => __('Retina Logo image', 'sa_core'),
                    'description'      => __('Upload retina logo for your website', 'sa_core'),
                    'id'        => 'retina_logo',
                    'type'      => 'image'
                ),
				array(
					'label' =>  '',
                    'description' =>  __('Dashboard Card Text', 'sa_core'),
                    'type' => 'title',
                    'id'   => 'header_welcome',
                ),
				array(
                    'label'  => __('Suppliers', 'sa_core'),
                    'id'    => 'suppliers_card',
                    'default' =>  'Number of Suppliers',               
                    'type'  => 'text',
                ),
				array(
                    'label'  => __('Documents', 'sa_core'),
                    'id'    => 'documents_card',
                    'default' =>  'Documents Processed',               
                    'type'  => 'text',
                ),
				array(
                    'label'  => __('Total Due', 'sa_core'),
                    'id'    => 'total_due_card',
                    'default' =>  'Total Due',               
                    'type'  => 'text',
                ),
				array(
                    'label'  => __('Average Amount', 'sa_core'),
                    'id'    => 'average_amount_card',
                    'default' =>  'Average Amount',               
                    'type'  => 'text',
                ),
			)
		);
		
		$settings['pages'] = array(
            'title'                 => __( 'Pages', 'sa_core' ),
            'description'           => __( 'Set all pages required in Simple Admin.', 'sa_core' ),
            'fields'                => array(
                array(
                    'id'            => 'dashboard_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'Dashboard Page' , 'sa_core' ),
                    'description'   => __( 'Main Dashboard page for user', 'sa_core' ),
                    'type'          => 'select',
                ),
                array(
                    'id'            => 'documents_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'My Documents Page' , 'sa_core' ),
                    'description'   => __( 'Main page for user documents', 'sa_core' ),
                    'type'          => 'select',
                ),
                array(
                    'id'            => 'due_documents_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'Due Documents Page' , 'sa_core' ),
                    'description'   => __( 'due documents of user', 'sa_core' ),
                    'type'          => 'select',
                ),
				array(
                    'id'            => 'profile_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'My Profile Page' , 'sa_core' ),
                    'description'   => __( 'Displays user profile page', 'sa_core' ),
                    'type'          => 'select',
                ),
				array(
                    'id'            => 'setting_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'Settings Page' , 'sa_core' ),
                    'description'   => __( 'Manage account settings page', 'sa_core' ),
                    'type'          => 'select',
                ),
				array(
                    'id'            => 'documents_upload_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'Documents Upload Page' , 'sa_core' ),
                    'description'   => __( 'Manage documents upload page', 'sa_core' ),
                    'type'          => 'select',
                ),
				array(
                    'id'            => 'coupons_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'My Coupons Page' , 'sa_core' ),
                    'description'   => __( 'Manage coupons page', 'sa_core' ),
                    'type'          => 'select',
                ),
				array(
                    'id'            => 'warranties_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'My Warranties Page' , 'sa_core' ),
                    'description'   => __( 'Manage warranties page', 'sa_core' ),
                    'type'          => 'select',
                ),
				array(
                    'id'            => 'insurances_page',
                    'options'       => sa_core_get_pages_options(),
                    'label'         => __( 'My Insurances Page' , 'sa_core' ),
                    'description'   => __( 'Manage insurances page', 'sa_core' ),
                    'type'          => 'select',
                ),
			)
		);
		
		$settings['supplier_colors'] = array(
            'title'                 => __( 'Supplier Colors', 'sa_core' ),
            'description'           => __( 'You can set the color for supplier to identify using color in graph.', 'sa_core' ),
			'fields'                => array(
					array(
						'id'                    => 'supplier_colors',
						'type'                  => 'input_multi',
						'options'               => $this->supplierColorFields()
					)
				)
 		);
		
		$settings['export'] = array(
            'title'                 => __( 'Export', 'sa_core' ),
            'description'           => __( 'Export the data.', 'sa_core' ),
		);
		
		$settings = apply_filters( $this->_token . '_settings_fields', $settings );

        return $settings;
	}
	
	/**
     * Load supplier colors fields
     * @return array
     */
	public function supplierColorFields() {
		$suppliersColor = get_option('sa_suppliers_color');
		$fields = array(); 
		if (!empty($suppliersColor)) { 
			foreach($suppliersColor as $key => $value) {
				$fields[$key] = array(
                    'label'         => __( $value['supplier'] , 'sa_core' ),
                    'placeholder'   => __( 'Enter color code (Example: #ffffff)', 'sa_core' )
                );
			}
		}
		return $fields;
	}
	
	/**
     * Register plugin settings
     * @return void
     */
    public function register_settings () {
		if ( is_array( $this->settings ) ) {
			
			// Check posted/selected tab
            $current_section = '';
            if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
                $current_section = $_POST['tab'];
            } else {
                if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
                    $current_section = $_GET['tab'];
                }
            }
			
			foreach ( $this->settings as $section => $data ) {

                if ( $current_section && $current_section != $section ) continue;

                // Add section to page
                add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->_token . '_settings' );

                foreach ( $data['fields'] as $field ) {

                    // Validation callback for field
                    $validation = '';
                    if ( isset( $field['callback'] ) ) {
                        $validation = $field['callback'];
                    }

                    // Register field
                    $option_name = $this->base . $field['id'];

                    register_setting( $this->_token . '_settings', $option_name, $validation );

                    // Add field to page

                    add_settings_field( $field['id'], $field['label'], array($this, 'display_field'), $this->_token . '_settings', $section, array( 'field' => $field, 'class' => 'sa_map_settings '.$field['id'],  'prefix' => $this->base ) );
                }

                if ( ! $current_section ) break;
            }
		}
	}
	
	public function settings_section ( $section ) {
        $html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
        echo $html;
    }
	
	/**
     * Load settings page content
     * @return void
     */
    public function settings_page () {

        // Build page HTML
        $html = '<div class="wrap" id="' . $this->_token . '_settings">' . "\n";
            $html .= '<h2>' . __( 'Plugin Settings' , 'sa_core' ) . '</h2>' . "\n";

            $tab = '';
            if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
                $tab .= $_GET['tab'];
            }

            // Show page tabs
            if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

                $html .= '<h2 class="nav-tab-wrapper">' . "\n";

                $c = 0;
                foreach ( $this->settings as $section => $data ) {

                    // Set tab class
                    $class = 'nav-tab';
                    if ( ! isset( $_GET['tab'] ) ) {
                        if ( 0 == $c ) {
                            $class .= ' nav-tab-active';
                        }
                    } else {
                        if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
                            $class .= ' nav-tab-active';
                        }
                    }

                    // Set tab link
                    $tab_link = add_query_arg( array( 'tab' => $section ) );
                    if ( isset( $_GET['settings-updated'] ) ) {
                        $tab_link = remove_query_arg( 'settings-updated', $tab_link );
                    }

                    // Output tab
                    $html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

                    ++$c;
                }

                $html .= '</h2>' . "\n";
            }

            $html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

                // Get settings fields
                ob_start();
                settings_fields( $this->_token . '_settings' );
                do_settings_sections( $this->_token . '_settings' );
                $html .= ob_get_clean();

				if ($tab == 'export') {
					ob_start();
					$html .= $this->export();
					$html .= ob_get_clean();
				} else {
					$html .= '<p class="submit">' . "\n";
						$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
						$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'sa_core' ) ) . '" />' . "\n";
					$html .= '</p>' . "\n";
				}
				
            $html .= '</form>' . "\n";
        $html .= '</div>' . "\n";

        echo $html;
    }
	
	/**
     * Load export page content
     * @return void
     */
	public function export() {
		
		$html = '<table class="form-table" role="presentation">';
			$html .= '<tbody>';
				$html .= '<tr class="sa_map_setting"><th scope="row">Export reminder log of all users</th><td><a href="javascript:void(0);" id="export-documents-reminder" class="button-primary">Download</a></td></tr>';
			$html .= '</tbody>';
		$html .= '</table>';
	
		return $html;
	}
	
	/**
     * Generate HTML for displaying fields
     * @param  array   $field Field data
     * @param  boolean $echo  Whether to echo the field HTML or return it
     * @return void
     */
    public function display_field ( $data = array(), $post = false, $echo = true ) {

        // Get field info
        if ( isset( $data['field'] ) ) {
            $field = $data['field'];
        } else {
            $field = $data;
        }

        // Check for prefix on option name
        $option_name = '';
        if ( isset( $data['prefix'] ) ) {
            $option_name = $data['prefix'];
        }

        // Get saved data
        $data = '';
        if ( $post ) {

            // Get saved field data
            $option_name .= $field['id'];
            $option = get_post_meta( $post->ID, $field['id'], true );

            // Get data to display in field
            if ( isset( $option ) ) {
                $data = $option;
            }

        } else {

            // Get saved option
            $option_name .= $field['id'];
			
            $option = get_option( $option_name );
			
            // Get data to display in field
            if ( isset( $option ) ) {
                $data = $option;
            }

        }

        // Show default data if no option saved and default is supplied
        if ( $data === false && isset( $field['default'] ) ) {
            $data = $field['default'];
        } elseif ( $data === false ) {
            $data = '';
        }
		
        $html = '';

        switch( $field['type'] ) {

            case 'text':
            case 'url':
            case 'email':
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" class="regular-text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( (isset($field['placeholder'])) ? $field['placeholder'] : '' ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
            break;

            case 'password':
            case 'number':
            case 'hidden':
                $min = '';
                if ( isset( $field['min'] ) ) {
                    $min = ' min="' . esc_attr( $field['min'] ) . '"';
                }

                $max = '';
                if ( isset( $field['max'] ) ) {
                    $max = ' max="' . esc_attr( $field['max'] ) . '"';
                }
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
            break;

            case 'text_secret':
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />' . "\n";
            break;

            case 'textarea':
                $html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '">' . $data . '</textarea><br/>'. "\n";
            break;

            case 'checkbox':
                $checked = '';
                if ( $data && 'on' == $data ) {
                    $checked = 'checked="checked"';
                }
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
            break;

            case 'checkbox_multi':
                foreach ( $field['options'] as $k => $v ) {
                    $checked = false;
                    if ( in_array( $k, (array) $data ) ) {
                        $checked = true;
                    }
                    $html .= '<p><label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label></p> ';
                }
            break;

            case 'radio':
                foreach ( $field['options'] as $k => $v ) {
                    $checked = false;
                    if ( $k == $data ) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label><br> ';
                }
            break;

            case 'select':
                $html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
                foreach ( $field['options'] as $k => $v ) {
                    $selected = false;
                    if ( $k == $data ) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
                }
                $html .= '</select> ';
            break;

            case 'select_multi':
                $html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
                foreach ( $field['options'] as $k => $v ) {
                    $selected = false;
                    if ( in_array( $k, (array) $data ) ) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
                }
                $html .= '</select> ';
            break;

            case 'image':
                $image_thumb = '';
                if ( $data ) {
                    $image_thumb = wp_get_attachment_thumb_url( $data );
                }
                $html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
                $html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'listeo_core' ) . '" data-uploader_button_text="' . __( 'Use image' , 'listeo_core' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'listeo_core' ) . '" />' . "\n";
                $html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'listeo_core' ) . '" />' . "\n";
                $html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
            break;

            case 'color':
                ?><div class="color-picker" style="position:relative;">
                    <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
                    <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
                </div>
                <?php
            break;
            
            case 'editor':
                wp_editor($data, $option_name, array(
                    'textarea_name' => $option_name,
                    'editor_height' => 150
                ) );
            break;
			
			case 'input_multi': 
				foreach ( $field['options'] as $k => $v ) {
					$value = '';
					if ( isset( $data[$k] ) ) {
						$value = $data[$k];
					}
                    $html .= '<p><label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="input_multi"><input type="text" name="' . esc_attr( $option_name ) . '['. esc_attr( $k ) .']" value="' . esc_attr( $value ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" placeholder="' . esc_attr( (isset($v['placeholder'])) ? $v['placeholder'] : '' ) . '" class="regular-text" /> ' . $v['label'] . '</label></p> ';
                }
            break;

        }

        switch( $field['type'] ) {

            case 'checkbox_multi':
            case 'radio':
            case 'select_multi':
                $html .= '<br/><span class="description">' . $field['description'] . '</span>';
            break;
            case 'title':
                $html .= '<br/><h3 class="description '.$field['id'].' ">' . $field['description'] . '</h3>';
            break;
			case 'input_multi':
			break;
			
            default:
                if ( ! $post ) {
                    $html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
                }
                if(isset($field['description']) && !empty($field['description'] )) {
                    $html .= '<span class="description">' . $field['description'] . '</span>' . "\n";    
                }
                

                if ( ! $post ) {
                    $html .= '</label>' . "\n";
                }
            break;
        }

        if ( ! $echo ) {
            return $html;
        }

        echo $html;

    }
	
	/**
     * Validate form field
     * @param  string $data Submitted value
     * @param  string $type Type of field to validate
     * @return string       Validated value
     */
    public function validate_field ( $data = '', $type = 'text' ) {

        switch( $type ) {
            case 'text': $data = esc_attr( $data ); break;
            case 'url': $data = esc_url( $data ); break;
            case 'email': $data = is_email( $data ); break;
        }

        return $data;
    }
	
	/**
     * Add meta box to the dashboard
     * @param string $id            Unique ID for metabox
     * @param string $title         Display title of metabox
     * @param array  $post_types    Post types to which this metabox applies
     * @param string $context       Context in which to display this metabox ('advanced' or 'side')
     * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
     * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
     * @return void
     */
    public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {

        // Get post type(s)
        if ( ! is_array( $post_types ) ) {
            $post_types = array( $post_types );
        }

        // Generate each metabox
        foreach ( $post_types as $post_type ) {
            add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
        }
    }

    /**
     * Display metabox content
     * @param  object $post Post object
     * @param  array  $args Arguments unique to this metabox
     * @return void
     */
    public function meta_box_content ( $post, $args ) {

        $fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );

        if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

        echo '<div class="custom-field-panel">' . "\n";

        foreach ( $fields as $field ) {

            if ( ! isset( $field['metabox'] ) ) continue;

            if ( ! is_array( $field['metabox'] ) ) {
                $field['metabox'] = array( $field['metabox'] );
            }

            if ( in_array( $args['id'], $field['metabox'] ) ) {
                $this->display_meta_box_field( $field, $post );
            }

        }

        echo '</div>' . "\n";

    }

    /**
     * Dispay field in metabox
     * @param  array  $field Field data
     * @param  object $post  Post object
     * @return void
     */
    public function display_meta_box_field ( $field = array(), $post ) {

        if ( ! is_array( $field ) || 0 == count( $field ) ) return;

        $field = '<p class="form-field"><label for="' . $field['id'] . '">' . $field['label'] . '</label>' . $this->display_field( $field, $post, false ) . '</p>' . "\n";

        echo $field;
    }

    /**
     * Save metabox fields
     * @param  integer $post_id Post ID
     * @return void
     */
    public function save_meta_boxes ( $post_id = 0 ) {

        if ( ! $post_id ) return;

        $post_type = get_post_type( $post_id );

        $fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );

        if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

        foreach ( $fields as $field ) {
            if ( isset( $_REQUEST[ $field['id'] ] ) ) {
                update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
            } else {
                update_post_meta( $post_id, $field['id'], '' );
            }
        }
    }
	
	/**
     * Main WordPress_Plugin_Template_Settings Instance
     *
     * Ensures only one instance of WordPress_Plugin_Template_Settings is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main WordPress_Plugin_Template_Settings instance
     */
    public static function instance ( $parent ) {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $parent );
        }
        return self::$_instance;
    } // End instance()
	
	/**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone () {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
    } // End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup () {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
    } // End __wakeup()
	
}

$settings = new Sa_Core_Admin( __FILE__ );