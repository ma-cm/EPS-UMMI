<?php

/**
 * Control for KB Configuration admin page
 */
class EPKB_KB_Config_Controller {

	public function __construct() {

		add_action( 'wp_ajax_epkb_wpml_enable', array( $this, 'wpml_enable' ) );
		add_action( 'wp_ajax_nopriv_epkb_wpml_enable', array( 'EPKB_Utilities', 'user_not_logged_in' ) );

		add_action( 'wp_ajax_epkb_save_access_control', array( 'EPKB_Admin_UI_Access', 'save_access_control' ) );
		add_action( 'wp_ajax_nopriv_epkb_save_access_control', array( 'EPKB_Utilities', 'user_not_logged_in' ) );

		add_action( 'wp_ajax_epkb_apply_settings_changes', array( $this, 'apply_settings_changes' ) );
		add_action( 'wp_ajax_nopriv_epkb_apply_settings_changes', array( 'EPKB_Utilities', 'user_not_logged_in' ) );
	}

	/**
	 * Triggered when user clicks to toggle wpml setting.
	 */
	public function wpml_enable() {

		// wp_die if nonce invalid or user does not have admin permission
		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die();

		// get KB ID
		$kb_id = (int)EPKB_Utilities::post( 'epkb_kb_id', 0 );
		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 410 ) );
		}

		$wpml_enable = EPKB_Utilities::post( 'wpml_enable' );
		if ( $wpml_enable != 'on' ) {
			$wpml_enable = 'off';
		}

		$result = epkb_get_instance()->kb_config_obj->set_value( $kb_id, 'wpml_is_enabled', $wpml_enable );
		if ( is_wp_error( $result ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 412, $result ) );
		}

		// we are done here
		if ( $wpml_enable == 'on' ) {
			EPKB_Utilities::ajax_show_info_die( __( 'WPML enabled', 'echo-knowledge-base' ) );
		} else {
			EPKB_Utilities::ajax_show_info_die( __( 'WPML disabled', 'echo-knowledge-base' ) );
		}
	}

	/**
	 * Handle actions that need reload of the page - KB Configuration page and other from addons
	 */
	public static function handle_form_actions() {

		$action = EPKB_Utilities::post( 'action' );
		if ( empty( $action ) ) {
			return [];
		}

		// verify that request is authentic
		if ( ! isset( $_REQUEST['_wpnonce_epkb_ajax_action'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_epkb_ajax_action'], '_wpnonce_epkb_ajax_action' ) ) {
			return [ 'error' => EPKB_Utilities::report_generic_error( 1 ) ];
		}

		// only admin user can handle these actions
		if ( ! current_user_can( 'manage_options' ) ) {
			return [ 'error' => __( 'You do not have permission.', 'echo-knowledge-base' ) ];
		}

		if ( $action == 'enable_editor_backend_mode' ) {

			// check addons that are updated
			$issues_found = EPKB_Core_Utilities::is_backend_editor_hidden();
			if ( $issues_found ) {
				EPKB_Core_Utilities::update_kb_flag( 'editor_backend_mode', false );
				return [ 'error' => EPKB_Utilities::report_generic_error( '', $issues_found ) ];
			}

			$result = EPKB_Core_Utilities::update_kb_flag( 'editor_backend_mode', true );
			if ( is_wp_error( $result ) ) {
				return [ 'error' => EPKB_Utilities::report_generic_error( 1 ) ];
			}

			return [ 'success' => __( 'Backend visual Editor enabled', 'echo-knowledge-base' ) ];
		}

		// retrieve KB ID we are saving
		$kb_id = empty( $_REQUEST['emkb_kb_id'] )
			? EPKB_Utilities::sanitize_get_id( $_REQUEST['kb_id'] )
			: EPKB_Utilities::sanitize_get_id( $_REQUEST['emkb_kb_id'] );
		if ( empty( $kb_id ) || is_wp_error( $kb_id ) ) {
			EPKB_Logging::add_log( "received invalid kb_id for action " . $action, $kb_id );
			return [ 'error' => EPKB_Utilities::report_generic_error( 2 ) ];
		}

		// retrieve current KB configuration
		$current_config = epkb_get_instance()->kb_config_obj->get_kb_config( $kb_id, true );
		if ( is_wp_error( $current_config ) ) {
			EPKB_Logging::add_log("Could not retrieve KB config when manage KB", $kb_id );
			return [ 'error' => EPKB_Utilities::report_generic_error( 5, $current_config ) ];
		}

		// handle user interactions
		if ( $action == 'epkb_update_article_v2' ) {
			return self::switch_user_to_article_v2( $current_config );
		}

		// EXPORT CONFIG
		if ( $action == 'epkb_export_knowledge_base' ) {
			$export = new EPKB_Export_Import();
			$message = $export->download_export_file( $kb_id );

			// stop php because we sent the file
			if ( empty( $message ) ) {
				exit;
			}
			return $message;
		}

		// IMPORT CONFIG
		if ( $action == 'epkb_import_knowledge_base' ) {
			$import = new EPKB_Export_Import();
			return $import->import_kb_config( $kb_id );
		}

		$message = apply_filters( 'eckb_handle_manage_kb_actions', [], $kb_id, $current_config );

		return is_array( $message ) ? $message : [];
	}

	/***
	 * Handle Form Action
	 *
	 * @param $kb_config
	 * @return array
	 */
	private static function switch_user_to_article_v2( $kb_config ) {

		// convert article structure to version 2
		$result = epkb_get_instance()->kb_config_obj->set_value( $kb_config['id'], 'article-structure-version', 'version-2' );
		if ( is_wp_error( $result ) ) {
			return [ 'error' => __( 'Something went wrong', 'echo-knowledge-base' ) . ' (64)' ];
		}

		if ( $kb_config['article_toc_enable'] == 'on' ) {

			if ( $kb_config['article_toc_position'] == 'left' ) {
				$kb_config['article_sidebar_component_priority']['toc_left'] = 1;
				$kb_config['article-right-sidebar-toggle'] = 'on';
			} else if ( $kb_config['article_toc_position'] == 'right' ) {
				$kb_config['article_sidebar_component_priority']['toc_right'] = 1;
				$kb_config['article-right-sidebar-toggle'] = 'on';
			} else if ( $kb_config['article_toc_position'] == 'middle' ) {
				$kb_config['article_sidebar_component_priority']['toc_content'] = 1;
				$kb_config['article-right-sidebar-toggle'] = 'on';
			}
		}

		$kb_config['article-structure-version'] = 'version-2';

		$new_config = EPKB_Editor_Controller::reset_layout( $kb_config, $kb_config );
		$result = epkb_get_instance()->kb_config_obj->update_kb_configuration( $new_config['id'], $new_config );
		if ( is_wp_error( $result ) ) {

			/* @var $result WP_Error */
			$message = $result->get_error_message();
			if ( empty($message) ) {
				return [ 'error' => __( 'Could not save the new configuration', 'echo-knowledge-base' ) . '(3)' ];
			} else {
				return [ 'error' => __( 'Configuration NOT saved due to following problem:' . $message, 'echo-knowledge-base' ) ];
			}
		}

		return [];
	}

	/**
	 * Handle update for KB Config Options
	 */
	public function apply_settings_changes() {

		// wp_die if nonce invalid or user does not have correct permission
		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die( 'admin_eckb_access_frontend_editor_write' );

		// ensure that user has correct permissions
		if ( ! EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' ) );
		}

		$kb_id = (int)EPKB_Utilities::post( 'epkb_kb_id', 0 );
		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 414 ) );
		}

		// get new KB configuration
		$new_config = EPKB_Utilities::post( 'kb_config', [], 'db-config' );
		if ( empty($new_config) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'Invalid parameters. Please refresh your page.', 'echo-knowledge-base' ) );
		}

		// validate TOC Hy, Hx levels: Hy cannot be less than Hx
		if ( $new_config['article_toc_hy_level'] < $new_config['article_toc_hx_level'] ) {
			EPKB_Utilities::ajax_show_error_die( __( 'HTML Header range is invalid', 'echo-knowledge-base' ) );
		}

		// get current KB configuration
		$orig_config = epkb_get_instance()->kb_config_obj->get_kb_config( $kb_id, true );
		if ( is_wp_error( $orig_config ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 8, $orig_config ) );
		}

		// get current KB configuration from add-ons
		$orig_config = apply_filters( 'eckb_all_editors_get_current_config', $orig_config, $kb_id );
		if ( empty($orig_config) || count($orig_config) < 3 ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 149 ) );
		}

		// set sidebar priority
		$article_sidebar_component_priority = array();
		foreach( EPKB_Editor_Controller::get_sidear_component_priority() as $component ) {
			if ( isset($new_config[$component]) ) {
				$article_sidebar_component_priority[$component] = $new_config[$component];
			}
		}

		// sanitize sidebar
		foreach( $article_sidebar_component_priority as $key => $value ) {
			if ( ! in_array($key, EPKB_KB_Config_Specs::get_sidebar_component_priority_names() ) ) {
				unset($article_sidebar_component_priority[$key]);
			}
			$article_sidebar_component_priority[$key] = sanitize_text_field($value);
		}

		if ( $article_sidebar_component_priority ) {
			$article_sidebar_component_priority = EPKB_KB_Config_Specs::add_sidebar_component_priority_defaults( $article_sidebar_component_priority );

			$article_sidebar_component_priority = array_merge($orig_config['article_sidebar_component_priority'], $article_sidebar_component_priority);

			$new_config['article_sidebar_component_priority'] = $article_sidebar_component_priority;
		}

		// overwrite current KB configuration with new configuration from this editor
		$new_config = array_merge($orig_config, $new_config);

		// reset sidebars if need
		$new_config = EPKB_Editor_Controller::reset_sidebar_widths( $new_config );

		// prevent new config to overwrite essential fields
		$new_config['id'] = $orig_config['id'];
		$new_config['status'] = $orig_config['status'];
		$new_config['kb_main_pages'] = $orig_config['kb_main_pages'];
		$new_config['kb_articles_common_path'] = $orig_config['kb_articles_common_path'];

		// update KB and add-ons configuration
		$update_kb_msg = self::update_kb_configuration( $kb_id, $orig_config, $new_config );
		if ( ! empty($update_kb_msg) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'Could not save the new configuration.', 'echo-knowledge-base' ) . $update_kb_msg . '(32) ' . EPKB_Utilities::contact_us_for_support() );
		}

		if ( $article_sidebar_component_priority ) {
			$result = epkb_get_instance()->kb_config_obj->set_value( $orig_config['id'], 'article_sidebar_component_priority', $new_config['article_sidebar_component_priority'] );
			if ( is_wp_error( $result ) ) {
				EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 37, $result ) );
			}
		}

		// flag for Editor backend mode
		$issues_found = EPKB_Core_Utilities::is_backend_editor_hidden();
		if ( $issues_found ) {
			EPKB_Core_Utilities::update_kb_flag( 'editor_backend_mode', false );
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( '', $issues_found ) );
		}

		$editor_backend_mode = $new_config['editor_backend_mode'] == '1' ? true : false;
		$result = EPKB_Core_Utilities::update_kb_flag( 'editor_backend_mode', $editor_backend_mode );
		if ( is_wp_error( $result ) ) {
			EPKB_Utilities::ajax_show_error_die( EPKB_Utilities::report_generic_error( 417, $result ) );
		}

		// we are done here
		EPKB_Utilities::ajax_show_info_die( __( 'KB Settings Updated', 'echo-knowledge-base' ) );
	}

	/**
	 * Triggered when user submits changes to KB configuration
	 *
	 * @param $kb_id
	 * @param $orig_config
	 * @param $new_config
	 * @return string
	 */
	private static function update_kb_configuration( $kb_id, $orig_config, $new_config ) {

		// core handles only default KB
		if ( $kb_id != EPKB_KB_Config_DB::DEFAULT_KB_ID && ! EPKB_Utilities::is_multiple_kbs_enabled() ) {
			return __('Ensure that Multiple KB add-on is active and refresh this page', 'echo-knowledge-base');
		}

		// sanitize all fields in POST message
		$field_specs = EPKB_Core_Utilities::retrieve_all_kb_specs( $kb_id );
		if ( empty( $field_specs ) ) {
			return __( 'Error occurred. Please refresh your browser and try again.', 'echo-knowledge-base' ) . ' (91)';
		}

		$form_fields = EPKB_Utilities::retrieve_and_sanitize_form( $new_config, $field_specs );
		if ( empty($form_fields) ) {
			EPKB_Logging::add_log("form fields missing");
			return __( 'Error occurred. Please refresh your browser and try again.', 'echo-knowledge-base' ) . ' (92)';
		} else if ( count($form_fields) < 100 ) {
			return __( 'Error occurred. Please refresh your browser and try again.', 'echo-knowledge-base' ) . ' (93)';
		}

		// sanitize fields based on each field type
		$input_handler = new EPKB_Input_Filter();
		$new_kb_config = $input_handler->retrieve_and_sanitize_form_fields( $form_fields, $field_specs, $orig_config );

		// save add-ons configuration
		$result = apply_filters( 'epkb_kb_config_save_input_v2', '', $kb_id, $form_fields, $new_kb_config['kb_main_page_layout'] );
		if ( is_wp_error( $result ) ) {
			/* @var $result WP_Error */
			$message = $result->get_error_message();
			if ( empty($message) ) {
				return __( 'Could not save the new configuration', 'echo-knowledge-base' ) . '(4)';
			} else {
				return __( 'Configuration NOT saved due to following problem:' . $message, 'echo-knowledge-base' );
			}
		}

		// ensure kb id is preserved
		$new_kb_config['id'] = $kb_id;

		// save KB core configuration
		$result = epkb_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $new_kb_config );
		if ( is_wp_error( $result ) ) {

			/* @var $result WP_Error */
			$message = $result->get_error_message();
			if ( empty($message) ) {
				return __( 'Could not save the new configuration', 'echo-knowledge-base' ) . '(31)';
			} else {
				return __( 'Configuration NOT saved due to following problem:' . $message, 'echo-knowledge-base' );
			}
		}

		// we are done here
		return '';
	}
}