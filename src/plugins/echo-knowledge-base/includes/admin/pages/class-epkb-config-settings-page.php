<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display KB configuration menu and pages
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPKB_Config_Settings_Page {

	private $kb_config;
	private $kb_config_specs;
	private $elay_enabled;
	private $asea_enabled;
	private $eprf_enabled;
	private $is_basic_layout;
	private $is_tabs_layout;
	private $is_categories_layout;
	private $is_grid_layout;
	private $is_sidebar_layout;
	private $is_kb_templates;

	public function __construct( $kb_config ) {
		$this->kb_config = apply_filters( 'eckb_kb_config', $kb_config );
		$this->kb_config_specs = EPKB_Core_Utilities::retrieve_all_kb_specs( $this->kb_config['id'] );
		$this->elay_enabled = EPKB_Utilities::is_elegant_layouts_enabled();
		$this->asea_enabled = EPKB_Utilities::is_advanced_search_enabled();
		$this->eprf_enabled = EPKB_Utilities::is_article_rating_enabled();

		$this->is_basic_layout = EPKB_KB_Config_Layout_Basic::LAYOUT_NAME == $this->kb_config['kb_main_page_layout'];
		$this->is_tabs_layout = EPKB_KB_Config_Layout_Tabs::LAYOUT_NAME == $this->kb_config['kb_main_page_layout'];
		$this->is_categories_layout = EPKB_KB_Config_Layout_Categories::LAYOUT_NAME == $this->kb_config['kb_main_page_layout'];
		$this->is_grid_layout = EPKB_KB_Config_Layouts::GRID_LAYOUT == $this->kb_config['kb_main_page_layout'];
		$this->is_sidebar_layout = EPKB_KB_Config_Layouts::SIDEBAR_LAYOUT == $this->kb_config['kb_main_page_layout'];

		$this->is_kb_templates = $this->kb_config['templates_for_kb'] == 'kb_templates';
	}

	/**
	 * Return configuration array of vertical Tabs for Settings top-level tab
	 *
	 * @return array
	 */
	public function get_vertical_tabs_config() {

		$contents_configs = self::get_contents_configs();
        $helpful_info_box_configs = $this->get_helpful_info_box_config();
		$access_to_get_started = EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_need_help_read' ) || EPKB_Admin_UI_Access::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' );

		$tabs_config = [];

		// Get Started
		if ( $access_to_get_started ) {
			$tabs_config['get-started'] = array(
				'title'     => __( 'Get Started', 'echo-knowledge-base' ),
				'icon'      => 'epkbfa epkbfa-rocket',
				'key'       => 'about-kb',
				'active'    => true,
				'contents'  => array(
					array(
						'title'             => __( 'About KB Settings', 'echo-knowledge-base' ),
						'desc'              => __( 'Configure your Knowledge Base features and colors on the left. For advanced styling and features, use our Full Page Editor.', 'echo-knowledge-base' ),
						'body_html'         => '',
						'read_more_url'     => '#',
						'read_more_text'    => __( 'Click here', 'echo-knowledge-base' ),
					),
					array(
						'title'             => __( 'Quick Links', 'echo-knowledge-base' ),
						'body_html'         => $this->get_quick_links_box(),
					),
					array(
						'title'             => __( 'Helpful Information', 'echo-knowledge-base' ),
						'body_html'         => $this->get_helpful_info_box( $helpful_info_box_configs ),
					),
				),
			);
		}

		// Main Page
		$tabs_config['main-page'] = array(
			'title'     => __( 'Main Page', 'echo-knowledge-base' ),
			'icon'      => 'epkb-main-page-icon',
			'key'       => 'main-page',
			'active'    => ! $access_to_get_started,
		);

		// Articles Page
		$tabs_config['article-page'] = array(
			'title'     => __( 'Article Page', 'echo-knowledge-base' ),
			'icon'      => 'epkb-article-page-icon',
			'key'       => 'article-page',
			'active'    => false,
		);

		// Archive Page
		$tabs_config['archive-page'] = array(
			'title'     => __( 'Archive Page', 'echo-knowledge-base' ),
			'icon'      => 'epkb-archive-page-icon',
			'key'       => 'archive-page',
			'active'    => false,
		);

		// Search Box
		$tabs_config['search-box'] = array(
			'title'     => __( 'Search Box', 'echo-knowledge-base' ),
			'icon'      => 'epkb-search-box-icon',
			'key'       => 'search-box',
			'active'    => false,
		);

		// TOC - 'fields' is not used for this tab
		$tabs_config['toc'] = array(
			'title'     => __( 'TOC', 'echo-knowledge-base' ),
			'icon'      => 'epkb-toc-icon',
			'key'       => 'toc',
			'active'    => false,
		);

		// Sidebar
		$tabs_config['sidebar'] = array(
			'title'     => __( 'Sidebar', 'echo-knowledge-base' ),
			'icon'      => 'epkb-sidebar-icon',
			'key'       => 'sidebar',
			'active'    => false,
		);

		// Labels
		$tabs_config['labels'] = array(
			'title'     => __( 'Labels', 'echo-knowledge-base' ),
			'icon'      => 'epkbfa ep_font_icon_tag',
			'key'       => 'labels',
			'active'    => false,
		);

		// General
		$tabs_config['general'] = array(
			'title'     => __( 'General' ),
			'icon'      => 'epkb-toggle-icon',
			'key'       => 'general',
			'active'    => false,
		);

		if ( $this->eprf_enabled ) {
			// Article Rating
			$tabs_config['ratings'] = array(
				'title'           => __( 'Rating and Feedback' ),
				'icon'            => 'epkbfa epkbfa-star-half-empty',
				'key'             => 'ratings',
				'requirement'     => 'eprf',
				'active'          => false,
			);
		}

		// Editor - 'fields' is not used for this tab
		$tabs_config['editor'] = array(
			'title'     => __( 'Full Editor', 'echo-knowledge-base' ),
			'icon'      => 'epkb-edit-icon',
			'key'       => 'editor',
			'active'    => false,
			'contents'  => array(
				array(
					'title'             => __( 'Visual Editor', 'echo-knowledge-base' ),
					'desc'              => '',
					'body_html'         => $this->show_frontend_editor_links(),
					'read_more_url'     => '',
					'read_more_text'    => '',
				),
			),
		);

		// Show first special content, then generated for settings 
		foreach ( $tabs_config as $key => $config ) {

			if ( empty( $config['contents'] ) ) {
				$tabs_config[$key]['contents'] = [];
			}

			if ( empty( $contents_configs[$config['key']] ) ) {
				continue;
			}

			$tabs_config[$key]['contents'] = empty( $config['contents'] )
				? $this->apply_fields_in_contents_config( $contents_configs[$config['key']] )
				: array_merge( $config['contents'], $this->apply_fields_in_contents_config( $contents_configs[$config['key']] ) );
		}

		return $tabs_config;
	}

	/**
	 * Convert fields in contents configuration array into HTML for each group of fields
	 *
	 * @param $contents_config
	 * @return array
	 */
	private function apply_fields_in_contents_config( $contents_config ) {

		foreach ( $contents_config as $tab => $tab_config ) {

			// leave only corresponding settings (depends on requirements for each field)
			$settings_list = $this->filter_settings( $tab_config['fields'] );

			// unset tab if it has empty fields set
			if ( empty( $settings_list ) ) {
				unset( $contents_config[$tab] );
				continue;
			}

			$contents_config[$tab]['body_html'] = $this->get_settings_html( $settings_list );

			if ( isset( $contents_config[$tab]['dependency'] ) ) {
                $contents_config[$tab]['css_class'] = 'eckb-condition-depend__' . implode( ' eckb-condition-depend__', $tab_config['dependency'] );
				$contents_config[$tab]['data'] = array(
					'dependency-ids' => implode( ' ', $tab_config['dependency'] ),
					'enable-on-values' => implode( ' ', $tab_config['enable_on'] )
				);
			}

			if ( ! empty( $tab_config['learn_more_links'] ) ) {
				$contents_config[$tab]['body_html'] .= $this->learn_more_block( $tab_config['learn_more_links'] );
			}
		}

		return $contents_config;
	}

	/**
	 * Get HTML list of specific KB config settings for given tab
	 *
	 * @param $settings_list
	 * @return false|string
	 */
	private function get_settings_html( $settings_list ) {
		ob_start();     ?>
		<div class="epkb-admin__kb__form">  <?php
			foreach ( $settings_list as $setting_name => $requirement ) {
				$this->show_kb_setting_html( $setting_name );
			} ?>
		</div>  <?php
		return ob_get_clean();
	}

	/**
	 * Display HTML for single KB config setting by its name
	 *
	 * @param $setting_name
	 */
	private function show_kb_setting_html( $setting_name ) {

		// handle custom display of certain fields
		if ( in_array( $setting_name, [ 'toc_locations', 'toc_left', 'toc_content', 'toc_right', 'advanced_search_presets', 'editor_backend_mode',
			'nav_sidebar_left', 'nav_sidebar_right', 'kb_sidebar_left', 'kb_sidebar_right', 'theme_compatibility_mode', 'kb_sidebar_left_toggler', 'kb_sidebar_right_toggler' ] ) ) {
			$this->show_custom_display_fields( $setting_name );
			return;
		}

		if ( ! isset( $this->kb_config_specs[$setting_name] ) ) {
			return;
		}

		$field_spec = $this->kb_config_specs[$setting_name];
		$field_spec = wp_parse_args( $field_spec, EPKB_KB_Config_Specs::get_defaults() );
		$field_spec = $this->set_custom_field_specs( $field_spec );

		$input_group_class = empty( $field_spec['input_group_class'] ) ? '' : $field_spec['input_group_class'];

		// display fields based on type
		$type = empty( $field_spec['type'] ) ? '' : $field_spec['type'];

		$input_args = array(
			'specs' => $setting_name,
			'desc'  => empty( $field_spec['desc'] ) ? '' : $field_spec['desc'],
		);

		// fields with dependency to other field values
		if ( isset( $field_spec['dependency'] ) ) {
			$input_group_class .= 'eckb-condition-depend__' . implode( ' eckb-condition-depend__', $field_spec['dependency'] );
			$input_args['group_data'] = array(
				'dependency-ids' => implode( ' ', $field_spec['dependency'] ),
				'enable-on-values' => implode( ' ', $field_spec['enable_on'] )
			);
		}

		switch( $type ) {

			case EPKB_Input_Filter::COLOR_HEX:
				EPKB_HTML_Elements::color( array_merge( $input_args, [
					'value' => $this->kb_config[$setting_name],
					'input_group_class' => $input_group_class,
				] ) );
				break;

			case EPKB_Input_Filter::SELECTION:

                // icon selection
				if ( in_array( $setting_name, [ 'expand_articles_icon', 'breadcrumb_icon_separator', 'sidebar_expand_articles_icon', 'section_head_category_icon_location', 'width',
					'rating_like_style', 'article_nav_sidebar_type_left', 'article_nav_sidebar_type_right', 'rating_mode' ] ) ) {
					EPKB_HTML_Elements::radio_buttons_icon_selection( array_merge( $input_args, [
						'value'             => $this->kb_config[$setting_name],
						'input_group_class' => $input_group_class,
					] ) );
					break;
				}

				if ( in_array( $setting_name, ['article_toc_hx_level', 'article_toc_hy_level'] ) ) {
					EPKB_HTML_Elements::radio_buttons_horizontal( array_merge( $input_args, [
						'value'             => $this->kb_config[$setting_name],
						'input_group_class' => 'epkb-radio-horizontal-button-group-container--small-btn ' . $input_group_class,
					] ) );
					break;
				}

				if ( in_array( $setting_name, ['template_category_archive_page_style', 'articles_comments_global',  'rating_feedback_trigger_stars', 'rating_feedback_required_stars'] ) ) {
					EPKB_HTML_Elements::dropdown( array_merge( $input_args, [
						'value' => $this->kb_config[$setting_name],
						'input_group_class' => $input_group_class,
					] ) );
					break;
				}

				if ( ! empty( $field_spec['options'] ) && count( $field_spec['options'] ) == 4 ) {
					EPKB_HTML_Elements::radio_buttons_horizontal( array_merge( $input_args, [
						'value'             => $this->kb_config[ $setting_name ],
						'input_group_class' => 'epkb-radio-horizontal-button-group-container--small-btn ' . $input_group_class,
					] ) );
					break;
				}

                EPKB_HTML_Elements::radio_buttons_horizontal( array_merge( $input_args, [
					'value' => $this->kb_config[$setting_name],
	                'input_group_class' => $input_group_class,
				] ) );
				break;

			case EPKB_Input_Filter::CHECKBOX:
				EPKB_HTML_Elements::checkbox_toggle( array_merge( $input_args, [
					'id'        => $setting_name,
					'text'      => $field_spec['label'],
					'checked'   => $this->kb_config[$setting_name] == 'on',
					'name'      => $setting_name,
					'input_group_class' => $input_group_class,
				] ) );
				break;

			case EPKB_Input_Filter::WP_EDITOR:
				EPKB_HTML_Elements::wp_editor( array_merge( $input_args, [
					'value'             => $this->kb_config[$setting_name],
					'editor_options'    => [ 'teeny' => 1, 'media_buttons' => false ],
					'input_group_class' => $input_group_class,
				] ) );
				break;

			case 'textarea':
				EPKB_HTML_Elements::textarea( array_merge( $input_args, [
					'value'             => $this->kb_config[$setting_name],
					'main_tag'          => 'div',
					'input_group_class' => 'epkb-input-group epkb-admin__input-field epkb-admin__textarea-field ' . $input_group_class,
				] ) );
				break;

			case EPKB_Input_Filter::TEXT:
				if ( in_array( $setting_name, [ 'advanced_search_mp_description_below_input', 'advanced_search_mp_description_below_title', 'advanced_search_ap_description_below_input', 'advanced_search_ap_description_below_title' ] ) ) {
					EPKB_HTML_Elements::textarea( [
						'specs'             => $setting_name,
						'value'             => $this->kb_config[$setting_name],
						'main_tag'          => 'div',
						'input_group_class' => 'epkb-input-group epkb-admin__input-field epkb-admin__textarea-field ' . $input_group_class,
					] );
				} else {
					EPKB_HTML_Elements::text( array_merge( $input_args, [
						'value' => $this->kb_config[$setting_name],
						'input_group_class' => $input_group_class,
					] ) );
				}
				break;

			default:
				EPKB_HTML_Elements::text( array_merge( $input_args, [
					'value' => $this->kb_config[$setting_name],
					'input_group_class' => $input_group_class,
				] ) );
		}
	}

	private function show_custom_display_fields( $setting_name ) {

		if ( $setting_name == 'toc_locations' ) {
			EPKB_HTML_Elements::checkboxes_as_icons_selection( [
				'name' => $setting_name,
				'label' => __( 'TOC Location', 'echo-knowledge-base' ),
				'values' => [
					( empty( $this->kb_config['article_sidebar_component_priority']['toc_left'] ) ? null : 'toc_left' ),
					( empty( $this->kb_config['article_sidebar_component_priority']['toc_content'] ) ? null : 'toc_content' ),
					( empty( $this->kb_config['article_sidebar_component_priority']['toc_right'] ) ? null : 'toc_right' ),
				],
				'options' => array(
					'toc_left' => __( 'Left', 'echo-knowledge-base' ),
					'toc_content' => __( 'Top', 'echo-knowledge-base' ),
					'toc_right' => __( 'Right', 'echo-knowledge-base' ),
				),
				'default' => '0',
			]);
		}

		if ( $setting_name == 'toc_left' ) {
			EPKB_HTML_Elements::radio_buttons_icon_selection( [
				'name' => $setting_name,
				'label' => is_rtl() ? __( 'Display on the Right', 'echo-knowledge-base' ) : __( 'Display on the Left', 'echo-knowledge-base' ),
				'value' => $this->kb_config['article_sidebar_component_priority'][$setting_name],
				'input_group_class' => 'epkb-radio-horizontal-button-group-container--small-btn ',
				'input_class' => 'epkb-radio-buttons-icon-selection',
				'options' => array(
					'1' => __( 'Position', 'echo-knowledge-base' ) . ' 1',
					'2' => __( 'Position', 'echo-knowledge-base' ) . ' 2',
					'3' => __( 'Position', 'echo-knowledge-base' ) . ' 3',
					'0' => __( 'None', 'echo-knowledge-base' ),
				),
				'default' => '0',
			]);
		}

		if ( $setting_name == 'toc_content' ) {
			EPKB_HTML_Elements::radio_buttons_icon_selection( [
				'name' => $setting_name,
				'label' => __( 'Display Above the Article', 'echo-knowledge-base' ),
				'value' => $this->kb_config['article_sidebar_component_priority'][$setting_name],
				'input_group_class' => 'epkb-radio-horizontal-button-group-container--small-btn ',
				'input_class' => 'epkb-radio-buttons-icon-selection',
				'options' => array(
					'1' => __( 'Displayed', 'echo-knowledge-base' ),
					'0' => __( 'Not displayed', 'echo-knowledge-base' ),
				),
				'default' => '0',
			]);
		}

		if ( $setting_name == 'toc_right' ) {
			EPKB_HTML_Elements::radio_buttons_icon_selection( [
				'name'  => $setting_name,
				'label' => is_rtl() ? __( 'Display on the Left', 'echo-knowledge-base' ) : __( 'Display on the Right', 'echo-knowledge-base' ),
				'value' => $this->kb_config['article_sidebar_component_priority'][$setting_name],
				'input_group_class' => 'epkb-radio-horizontal-button-group-container--small-btn ',
				'input_class' => 'epkb-radio-buttons-icon-selection',
				'options'    => array(
					'1' => __( 'Position', 'echo-knowledge-base' ) . ' 1',
					'2' => __( 'Position', 'echo-knowledge-base' ) . ' 2',
					'3' => __( 'Position', 'echo-knowledge-base' ) . ' 3',
					'0' => __( 'None', 'echo-knowledge-base' ),
				),
				'default' => '0',
			] );
		}

		if ( $setting_name == 'advanced_search_presets' && $this->asea_enabled ) { ?>

			<p><i><?php esc_html_e( 'Change Search Presets in the full Editor', 'echo-knowledge-base' ); ?></i></p>
			<div class="epkb-kb__btn-wrap">
				<a href="<?php echo add_query_arg( array( 'action' => 'epkb_load_editor', 'preopen_zone' => 'search_box_zone' ), EPKB_KB_Handler::get_first_kb_main_page_url( $this->kb_config ) ); ?>" data-open-editor-link="<?php echo EPKB_Core_Utilities::is_kb_flag( 'editor_backend_mode' ) ? 'back' : 'front'; ?>">
					<?php echo __( 'Edit Search Presets', 'echo-knowledge-base' );  ?>
				</a>
			</div>  <?php
		}

		if ( $setting_name == 'editor_backend_mode' ) {
			EPKB_HTML_Elements::radio_buttons_horizontal( [
				'label'     => __( 'Launch the Editor', 'echo-knowledge-base' ),
				'value'     => EPKB_Core_Utilities::is_kb_flag( $setting_name ) ? '1' : '0',
				'name'      => $setting_name,
				'options'   => [
					'0'  => __( 'On the Frontend', 'echo-knowledge-base' ),
					'1'   => __( 'On the Backend', 'echo-knowledge-base' ),
				],
				'default'   => '0',
			] );
		}

		if ( $setting_name == 'theme_compatibility_mode' ) { ?>
			<p><?php esc_html_e( 'You have two options for displaying the Knowledge Base: with or without your theme structure.', 'echo-knowledge-base' ); ?></p>
			<p><?php esc_html_e( 'Choose how KB is displayed with your theme:', 'echo-knowledge-base' ); ?></p>
			<p>
				<?php esc_html_e( 'a) Use KB Template', 'echo-knowledge-base' ); ?><br>
				<?php esc_html_e( 'b) Use your current theme template', 'echo-knowledge-base' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'To change this option, follow the instructions ', 'echo-knowledge-base' ); ?>
				<a href="https://www.echoknowledgebase.com/documentation/current-theme-template-vs-kb-template/" target="_blank"><?php echo esc_html_e( 'here', 'echo-knowledge-base' );  ?></a> <span class="epkb-kbnh__link__icon epkbfa epkbfa-external-link"></span>
			</p> <?php
		}

		if ( in_array( $setting_name, [ 'nav_sidebar_left', 'nav_sidebar_right' ] ) ) {
			EPKB_HTML_Elements::radio_buttons_icon_selection( array_merge( [
				'name'  => $setting_name,
				'label' => __( 'Sidebar Navigation Location', 'echo-knowledge-base' ),
				'value' => $this->kb_config['article_sidebar_component_priority'][$setting_name],
				'input_class' => 'epkb-radio-buttons-icon-selection',
				'options'    => array(
					'1' => __( 'Position', 'echo-knowledge-base' ) . ' 1',
					'2' => __( 'Position', 'echo-knowledge-base' ) . ' 2',
					'3' => __( 'Position', 'echo-knowledge-base' ) . ' 3',
					'0' => __( 'Hide', 'echo-knowledge-base' ),
				),
				'default' => '0',
			] ) );
		}

		if ( $setting_name == 'kb_sidebar_left_toggler' ) {

			EPKB_HTML_Elements::checkbox_toggle( [
				'id'        => $setting_name,
				'text'      => __( 'Show Widget ', 'echo-knowledge-base' ),
				'checked'   => $this->kb_config['article_sidebar_component_priority']['kb_sidebar_left'] != '0',
				'name'      => $setting_name,
				'group_data' => [ 'control-toggler' => 'kb_sidebar_left', 'control-disabled-value' => '0' ] ,
			] );
		}

		if ( $setting_name == 'kb_sidebar_right_toggler' ) {

			EPKB_HTML_Elements::checkbox_toggle( [
				'id'        => $setting_name,
				'text'      => __( 'Show Widget ', 'echo-knowledge-base' ),
				'checked'   => $this->kb_config['article_sidebar_component_priority']['kb_sidebar_right'] != '0',
				'name'      => $setting_name,
				'group_data' => [ 'control-toggler' => 'kb_sidebar_right', 'control-disabled-value' => '0' ] ,
			] );
		}

		if ( in_array( $setting_name, [ 'kb_sidebar_left', 'kb_sidebar_right' ] ) ) {
			EPKB_HTML_Elements::radio_buttons_icon_selection( array_merge( [
				'name'  => $setting_name,
				'label' => __( 'Widget Location', 'echo-knowledge-base' ),
				'value' => $this->kb_config['article_sidebar_component_priority'][$setting_name],
				'input_group_class' => 'epkb-radio-buttons-last-hidden',
				'input_class' => 'epkb-radio-buttons-icon-selection',
				'options'    => array(
					'1' => __( 'Position', 'echo-knowledge-base' ) . ' 1',
					'2' => __( 'Position', 'echo-knowledge-base' ) . ' 2',
					'3' => __( 'Position', 'echo-knowledge-base' ) . ' 3',
					'0' => __( 'Hide', 'echo-knowledge-base' ),
				),
				'default' => '0',
			] ) );
		}
	}

	/**
	 * Get Quick Links box
	 *
	 * @return false|string
	 */
	private function get_quick_links_box() {

		ob_start(); ?>

        <div class="epkb-kb__btn-wrap"> <?php
			echo EPKB_Core_Utilities::get_current_kb_main_page_link( $this->kb_config, __( 'View My Knowledge Base', 'echo-knowledge-base' ), '' ); ?>
            <span class="epkb-kbnh__link__icon epkbfa epkbfa-external-link"></span>
        </div>

        <div class="epkb-kb__btn-wrap"> <?php
	        echo EPKB_Core_Utilities::get_kb_admin_page_link( '', __( 'Edit Articles', 'echo-knowledge-base' ), false ); ?>
            <span class="epkb-kbnh__link__icon epkbfa epkbfa-external-link"></span>
        </div>

        <div class="epkb-kb__btn-wrap">
	        <a href="<?php echo esc_url( admin_url( '/edit-tags.php?taxonomy=' . EPKB_KB_Handler::get_category_taxonomy_name( $this->kb_config['id'] ) . '&post_type=' . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] ) ) ); ?>">
	            <?php echo __( 'Edit Categories', 'echo-knowledge-base' ); ?>
            </a>
            <span class="epkb-kbnh__link__icon epkbfa epkbfa-external-link"></span>
        </div>  <?php

		if ( current_user_can( EPKB_Admin_UI_Access::get_context_required_capability( 'admin_eckb_access_frontend_editor_write' ) ) ) {   ?>
			<div class="epkb-kb__btn-wrap"> <?php
				echo EPKB_Core_Utilities::get_kb_admin_page_link( 'page=epkb-kb-configuration&setup-wizard-on', __( 'Setup Wizard', 'echo-knowledge-base' ), false );  ?>
				<span class="epkb-kbnh__link__icon epkbfa epkbfa-external-link"></span>
			</div>  <?php
		}

		return ob_get_clean();
	}

	/**
	 * Get Helpful Information box
	 *
	 * @param $helpful_info_box_config
	 * @return false|string
	 */
    private function get_helpful_info_box( $helpful_info_box_config ) {

	    ob_start(); ?>

        <div class="epkb-admin__helpful-info-wrap"> <?php
            foreach ( $helpful_info_box_config as $item ) {    ?>
                <div class="epkb-admin__helpful-info-box">
                    <div class="epkb-admin__helpful-info-box__title"><?php echo esc_html( $item['title'] ); ?></div>
                    <div class="epkb-admin__helpful-info-box__icon-container">
                        <img src="<?php echo esc_url( Echo_Knowledge_Base::$plugin_url . esc_attr( $item['icon'] ) ); ?>">
                    </div>
                    <div class="epkb-admin__helpful-info-box__desc"><?php echo esc_html( $item['desc'] ); ?></div>
                    <div class="epkb-admin__helpful-info-box__link-container">
                        <a href="<?php echo esc_attr( $item['btn_url'] ); ?>" target="_blank"><?php echo esc_html( $item['btn_text'] ); ?></a>
                    </div>
                </div>  <?php
            }   ?>
        </div>  <?php

        return ob_get_clean();
    }

	/**
	 * KB Design: Box Editors List
	 *
	 * @return false|string
	 */
	private function show_frontend_editor_links() {

		$editor_urls = EPKB_Editor_Utilities::get_editor_urls( $this->kb_config, '', '', '', false );

		ob_start();

		// Main page link to editor
		if ( $editor_urls['main_page_url'] != '' ) {
			EPKB_HTML_Forms::call_to_action_box(array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/basic-layout-light.jpg',
				'title' => __('Main Page', 'echo-knowledge-base' ),
				'btn_text' => __('Change Style', 'echo-knowledge-base' ),
				'btn_url' => $editor_urls['main_page_url'],
				'btn_target' => "_blank",
				'container_class' => 'epkb-main-page-editor-link'
			) );
		} else {
			EPKB_HTML_Forms::call_to_action_box( array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/basic-layout-light.jpg',
				'title'         => __( 'Main Page', 'echo-knowledge-base' ),
				'content'       => __( 'No Main Page Found', 'echo-knowledge-base' ),
				'btn_text'      => __( 'Add Shortcode', 'echo-knowledge-base' ),
				'btn_url'       => admin_url( "edit.php?post_type=" . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] ) . "&page=epkb-kb-configuration&wizard-global" ),
				'btn_target'	  => "_blank",
			) );
		}

		// Article page link to editor
		if ( $editor_urls['article_page_url'] != '' ) {
			EPKB_HTML_Forms::call_to_action_box( array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/article-page.jpg',
				'title'         => __( 'Article Page', 'echo-knowledge-base' ),
				'btn_text'      => __( 'Change Style', 'echo-knowledge-base' ),
				'btn_url'       => $editor_urls['article_page_url'],
				'btn_target'    => "_blank",
				'container_class' => 'epkb-article-page-editor-link'
			) );
		} else {
			EPKB_HTML_Forms::call_to_action_box( array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/article-page.jpg',
				'title'         => __( 'Article Page', 'echo-knowledge-base' ),
				'content'       => __( 'All articles have no Category. Please assign your article to categories.', 'echo-knowledge-base' ),
				'btn_text'      => __( 'Add New Article', 'echo-knowledge-base' ),
				'btn_url'       => admin_url( "post-new.php?post_type=" . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] ) ),
				'btn_target'    => "_blank",
			) );
		}

		// Archive page link to editor
		if ( $this->kb_config['templates_for_kb'] == 'current_theme_templates' ) {
			EPKB_HTML_Forms::call_to_action_box(array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/category-archive-page.jpg',
				'title' => __( 'Category Archive Page', 'echo-knowledge-base' ),
				'content' => sprintf(  __( 'The KB template option is set to the Current Theme. You need to configure your Archive Page template in ' .
				                           'your theme settings. For details about the KB template option see %s', 'echo-knowledge-base' ),
					' <a href="https://www.echoknowledgebase.com/documentation/current-theme-template-vs-kb-template/" target="_blank">' . esc_html__( 'here', 'echo-knowledge-base' ) . '.' . '</a> ' )
			) );
		} else if ( $editor_urls['archive_url'] != '' ) {
			EPKB_HTML_Forms::call_to_action_box(array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/category-archive-page.jpg',
				'title' => __('Category Archive Page', 'echo-knowledge-base'),
				'btn_text' => __('Change Style', 'echo-knowledge-base'),
				'btn_url' => $editor_urls['archive_url'],
				'btn_target' => "_blank",
				'container_class' => 'epkb-archive-page-editor-link'
			) );
		} else {
			EPKB_HTML_Forms::call_to_action_box(array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/category-archive-page.jpg',
				'title' => __('Category Archive Page', 'echo-knowledge-base'),
				'content' => __('No Categories Found', 'echo-knowledge-base'),
				'btn_text' => __('Add New Category', 'echo-knowledge-base'),
				'btn_url' => admin_url('edit-tags.php?taxonomy=' . EPKB_KB_Handler::get_category_taxonomy_name( $this->kb_config['id'] ) .'&post_type=' . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] )),
				'btn_target' => "_blank",
			) );
		}

		// Advanced Search Page
		if ( EPKB_Utilities::is_advanced_search_enabled() && $editor_urls['search_page_url'] != '' ) {
			EPKB_HTML_Forms::call_to_action_box( array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/search-result-page.png',
				'title'         => __( 'Search Results Page', 'echo-knowledge-base' ),
				'btn_text'      => __( 'Change Style', 'echo-knowledge-base' ),
				'btn_url'       => $editor_urls['search_page_url'],
				'btn_target'    => "_blank",
				'container_class' => 'epkb-search-page-editor-link'
			) );
		} else if ( EPKB_Utilities::is_advanced_search_enabled() ) {
			EPKB_HTML_Forms::call_to_action_box( array(
				'style' => 'style-1',
				'icon_img_url'  => 'img/editor/basic-layout-light.jpg',
				'title'         => __( 'Search Results Page', 'echo-knowledge-base' ),
				'content'       => __( 'To edit the Search Results page, be sure you have a KB Main Page.', 'echo-knowledge-base' ),
				'btn_text'      => __( 'Configure KB Main Page', 'echo-knowledge-base' ),
				'btn_url'       => admin_url( "edit.php?post_type=" . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] ) . "&page=epkb-kb-configuration#settings__kb-urls" ),
				'btn_target'	  => "_blank",
			) );
		}

		return ob_get_clean();
	}

	/**
	 * Return configuration array of settings fields
	 *
	 * @return array
	 */
	private static function get_contents_configs() {

		// Main Page
		$contents_configs['main-page'] = array(
			array(
				'title'         => __( 'Page', 'echo-knowledge-base' ),
				'fields'        => [
					'template_main_page_display_title' => 'only_kb_templates',
					'grid_nof_columns' => 'only_grid',
					'nof_columns' => [ 'not_grid', 'not_sidebar' ],
				],
			),
			array(
				'title'         => __( 'Tabs', 'echo-knowledge-base' ),
				'fields'        => [
					'tab_nav_font_color' => 'only_tabs',
					'tab_nav_active_font_color' => 'only_tabs',
					'tab_nav_active_background_color' => 'only_tabs',
					'tab_nav_background_color' => 'only_tabs',
				],
			),
			array(
				'title'         => __( 'Categories Box', 'echo-knowledge-base' ),
				'fields'        => [
					'grid_section_box_height_mode' => 'only_grid',
					'grid_section_body_height' => 'only_grid',
					'grid_background_color' => 'only_grid',
					'grid_section_box_shadow' => 'only_grid',
					'section_box_height_mode' => [ 'not_grid', 'not_sidebar' ],
					'section_body_height' => [ 'not_grid', 'not_sidebar' ],
					'background_color' => [ 'not_grid', 'not_sidebar' ],
				],
			),
			array(
				'title'     => __( 'Category Header', 'echo-knowledge-base' ),
				'fields'    => [
					'grid_section_head_alignment' => 'only_grid',
					'grid_section_divider' => 'only_grid',
					'grid_section_divider_color' => 'only_grid',
					'section_hyperlink_text_on' => 'only_grid',
					'grid_section_desc_text_on' => 'only_grid',
					'grid_section_head_icon_color' => 'only_grid',
					'grid_section_head_font_color' => 'only_grid',
					'grid_section_head_background_color' => 'only_grid',
					'section_head_alignment' => [ 'not_grid', 'not_sidebar' ],
					'section_hyperlink_on' => [ 'not_grid', 'not_sidebar' ],
					'section_divider_color' => [ 'not_grid', 'not_sidebar' ],
					'section_head_font_color' => [ 'not_grid', 'not_sidebar' ],
					'section_head_background_color' => [ 'not_grid', 'not_sidebar' ],
				],
			),
			array(
				'title'     => __( 'Category Body', 'echo-knowledge-base' ),
				'fields'    => [
					'grid_section_article_count' => 'only_grid',
					'grid_section_body_text_color' => 'only_grid',
					'grid_section_body_background_color' => 'only_grid',
					'section_body_background_color' => [ 'not_grid', 'not_sidebar' ],
					'section_desc_text_on' => [ 'not_grid', 'not_sidebar' ],
					'section_head_description_font_color' => [ 'not_grid', 'not_sidebar' ],
				],
			),
			array(
				'title'         => __( 'Category Icons', 'echo-knowledge-base' ),
				'fields'        => [
					'grid_category_icon_location' => 'only_grid',
					'grid_section_icon_size' => 'only_grid',
					'section_head_category_icon_location' => [ 'not_grid', 'not_sidebar' ],
					'section_head_category_icon_size' => [ 'not_grid', 'not_sidebar' ],
					'section_head_category_icon_color' => [ 'not_grid', 'not_sidebar' ],
					'section_category_icon_color' => [ 'not_grid', 'not_sidebar' ],
					'section_category_font_color' => [ 'not_grid', 'not_sidebar' ],
				],
			),
			array(
				'title'         => __( 'List of Articles', 'echo-knowledge-base' ),
				'fields'        => [
					'nof_articles_displayed' => [ 'not_grid', 'not_sidebar' ],
					'section_box_shadow' => [ 'not_grid', 'not_sidebar' ],
					'expand_articles_icon' => [ 'not_grid', 'not_sidebar' ],
					'article_icon_color' => [ 'not_grid', 'not_sidebar' ],
					'article_font_color' => [ 'not_grid', 'not_sidebar' ],
				],
			),
		);

		// Articles Page
		$contents_configs['article-page'] = array(
			array(
				'title'     => __( 'Article Features', 'echo-knowledge-base' ),
				'fields'    => [
					'article_content_enable_article_title' => 'core',
					'article_content_enable_author' => 'core',
					'article_content_enable_last_updated_date' => 'core',
					'article_content_enable_created_date' => 'core',
					'articles_comments_global' => 'core',
				],
				'learn_more_links' => [ // title => url
					__( 'Top Author and Dates', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/created-on-updated-on-author-meta/',
					__( 'Article Title', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/article-title/',
					__( 'Article Comments', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/wordpress-article-comments/',
					__( 'Author', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/created-on-updated-on-author-meta/#articleTOC_3',
					__( 'Created Date', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/created-on-updated-on-author-meta/#articleTOC_1',
					__( 'Last Updated Date', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/created-on-updated-on-author-meta/#articleTOC_2',
				]
			),
			array(
				'title'     => __( 'Breadcrumbs', 'echo-knowledge-base' ),
				'fields'    => [
					'breadcrumb_enable' => 'core',
					'breadcrumb_icon_separator' => 'core',
				],
				'learn_more_links' => [ // title => url
					__( 'Breadcrumb', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/article-breadcrumb/',
				]
			),
			array(
				'title'     => __( 'Back Navigation', 'echo-knowledge-base' ),
				'fields'    => [
					'article_content_enable_back_navigation' => 'core',
					'back_navigation_mode' => 'core',
				],
			),
			array(
				'title'     => __( 'Prev/Next Navigation', 'echo-knowledge-base' ),
				'fields'    => [
					'prev_next_navigation_enable' => 'core',
					'prev_next_navigation_bg_color' => 'core',
					'prev_next_navigation_hover_text_color' => 'core',
					'prev_next_navigation_hover_bg_color' => 'core',
				],
				'learn_more_links' => [ // title => url
					__( 'Prev/Next Navigation', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/previous-next-page-navigation/',
				]
			),
			array(
				'title'         => __( 'Grid Layout', 'echo-knowledge-base' ),
				'fields'        => [
					'grid_section_article_count' => 'only_grid',
				],
			)
		);

		// Archive Page
		$contents_configs['archive-page'] = array(
			array(
				'title'     => __( 'Archive Settings', 'echo-knowledge-base' ),
				'fields'    => [
					'template_category_archive_page_style' => 'core',
					'archive-content-width-v2' => 'core',
					'archive-show-sub-categories' => 'core',
					'archive-container-width-v2' => 'core',
					'archive-container-width-units-v2' => 'core',
				],
			),
		);

		// Labels
		$contents_configs['labels'] = array(
			array(
				'title'         => __( 'Search Title - Main Page', 'echo-knowledge-base' ),
				'fields'        => [
					'search_title' => '!asea',
					'search_title_html_tag' => '!asea',
					'search_button_name' => '!asea',
				],
			),
			array(
				'title'     => __( 'Search Input Box - Main Page', 'echo-knowledge-base' ),
				'fields'    => [
					'search_box_hint' => 'core',
					'search_results_msg' => 'core',
					'no_results_found' => 'core',
					'min_search_word_size_msg' => 'core',
				],
			),
			array(
				'title'         => __( 'Search Title - Article Page', 'echo-knowledge-base' ),
				'fields'        => [
					'article_search_title' => '!asea',
					'article_search_title_html_tag' => '!asea',
					'article_search_button_name' => '!asea',
				],
			),
			array(
				'title'     => __( 'Search Input Box - Article Page', 'echo-knowledge-base' ),
				'fields'    => [
					'article_search_box_hint' => 'core',
					'article_search_results_msg' => 'core',
				],
			),
			array(
				'title'         => __( 'Tabs Layout Drop Down', 'echo-knowledge-base' ),
				'fields'    => [
					'choose_main_topic' => 'only_tabs',
				],
			),
			array(
				'title'     => __( 'Category Body', 'echo-knowledge-base' ),
				'fields'    => [
					'category_empty_msg' => 'not_grid',
					'grid_category_empty_msg' => 'only_grid',
					'grid_category_link_text' => 'only_grid',
					'grid_article_count_text' => 'only_grid',
					'grid_article_count_plural_text' => 'only_grid',
				],
			),
			array(
				'title'     => __( 'Articles', 'echo-knowledge-base' ),
				'fields'    => [
					'collapse_articles_msg' => 'core',
					'show_all_articles_msg' => 'core',
				],
			),
			array(
				'title'     => __( 'Sidebar Articles', 'echo-knowledge-base' ),
				'fields'    => [
					'sidebar_collapse_articles_msg' => 'core',
					'sidebar_show_all_articles_msg' => 'core',
				],
			),
			array(
				'title'     => __( 'Navigation', 'echo-knowledge-base' ),
				'fields'    => [
					'sidebar_category_empty_msg' => 'core',
				],
			),
			array(
				'title'         => __( 'Categories List', 'echo-knowledge-base' ),
				'fields'        => [
					'category_focused_menu_heading_text' => 'only_categories',
				],
			),
			array(
				'title'     => __( 'TOC', 'echo-knowledge-base' ),
				'fields'    => [
					'article_toc_title' => 'core',
				],
			),
			array(
				'title'     => __( 'Breadcrumb', 'echo-knowledge-base' ),
				'fields'    => [
					'breadcrumb_description_text' => 'core',
					'breadcrumb_home_text' => 'core',
				],
			),
			array(
				'title'     => __( 'Back Navigation', 'echo-knowledge-base' ),
				'fields'    => [
					'back_navigation_text' => 'core',
				],
			),
			array(
				'title'     => __( 'Print Button', 'echo-knowledge-base' ),
				'fields'    => [
					'print_button_text' => 'core',
				],
			),
			array(
				'title'     => __( 'Created Date', 'echo-knowledge-base' ),
				'fields'    => [
					'created_on_text' => 'core',
				],
			),
			array(
				'title'     => __( 'Last Updated Date', 'echo-knowledge-base' ),
				'fields'    => [
					'last_updated_on_text' => 'core',
				],
			),
			array(
				'title'     => __( 'Author', 'echo-knowledge-base' ),
				'fields'    => [
					'author_text' => 'core',
				],
			),
			array(
				'title'     => __( 'Prev/Next Navigation', 'echo-knowledge-base' ),
				'fields'    => [
					'prev_navigation_text' => 'core',
					'next_navigation_text' => 'core',
				],
			),
			array(
				'title'     => __( 'Archive', 'echo-knowledge-base' ),
				'fields'    => [
					'template_category_archive_page_heading_description' => 'core',
					'template_category_archive_read_more' => 'core',
				],
			),
			array(
				'title'     => __( 'Archive Meta Data', 'echo-knowledge-base' ),
				'fields'    => [
					'template_category_archive_date' => 'core',
					'template_category_archive_author' => 'core',
					'template_category_archive_categories' => 'core',
				],
			),
			array(
				'title'         => __( 'Rating and Feedback', 'echo-knowledge-base' ),
				'fields'        => [
					'rating_text_value' => 'eprf',
					'rating_stars_text' => 'eprf',
					'rating_stars_text_1' => 'eprf',
					'rating_stars_text_2' => 'eprf',
					'rating_stars_text_3' => 'eprf',
					'rating_stars_text_4' => 'eprf',
					'rating_stars_text_5' => 'eprf',
					'rating_out_of_stars_text' => 'eprf',
					'rating_confirmation_positive' => 'eprf',
					'rating_confirmation_negative' => 'eprf',
					'rating_feedback_title' => 'eprf',
					'rating_feedback_required_title' => 'eprf',
					'rating_feedback_name' => 'eprf',
					'rating_feedback_email' => 'eprf',
					'rating_feedback_description' => 'eprf',
					'rating_feedback_support_link_text' => 'eprf',
					'rating_feedback_support_link_url' => 'eprf',
					'rating_feedback_button_text' => 'eprf',
				],
			),
			array(
				'title'         => __( 'Sidebar Intro', 'echo-knowledge-base' ),
				'fields'        => [
					'sidebar_main_page_intro_text' => 'only_sidebar',
				],
			)
		);

		// Search Box
		$contents_configs['search-box'] = array(
			array(
				'title'         => __( 'Search Box Presets', 'echo-knowledge-base' ),
				'fields'        => [
					'advanced_search_presets' => 'asea',
				],
			),
			array(
				'title'     => __( 'Main Page Search Box', 'echo-knowledge-base' ),
				'fields'    => [
					'width' => 'core',

					'advanced_search_mp_box_visibility' => 'asea',
					'advanced_search_mp_input_box_search_icon_placement' => 'asea',
					'advanced_search_mp_filter_toggle' => 'asea',

					'search_box_input_width' => '!asea',
					'search_layout' => '!asea',
					'search_title_font_color' => '!asea',
					'search_background_color' => '!asea',
					'search_text_input_background_color' => '!asea',
					'search_btn_background_color' => '!asea',

					'advanced_search_mp_title_toggle' => 'asea',
					'advanced_search_mp_title' => 'asea',
					'advanced_search_mp_title_font_color' => 'asea',
					'advanced_search_mp_description_below_title_toggle' => 'asea',
					'advanced_search_mp_description_below_title' => 'asea',
					'advanced_search_mp_description_below_input_toggle' => 'asea',
					'advanced_search_mp_description_below_input' => 'asea',
					'advanced_search_mp_filter_category_level' => 'asea',
					'advanced_search_mp_show_top_category' => 'asea',
					'advanced_search_mp_link_font_color' => 'asea',
					'advanced_search_mp_background_color' => 'asea',

					'advanced_search_mp_background_gradient_toggle' => 'asea',
					'advanced_search_mp_background_gradient_from_color' => 'asea',
					'advanced_search_mp_background_gradient_to_color' => 'asea',
					'advanced_search_mp_background_gradient_degree' => 'asea',
					'advanced_search_mp_background_gradient_opacity' => 'asea',
				],
			),
			array(
				'title'     => __( 'Article Page Search Box', 'echo-knowledge-base' ),
				'fields'    => [

					'advanced_search_ap_box_visibility' => 'asea',
					'advanced_search_ap_input_box_search_icon_placement' => 'asea',
					'advanced_search_ap_filter_toggle' => 'asea',

					'article_search_box_input_width' => '!asea',
					'article_search_layout' => '!asea',
					'article_search_title_font_color' => '!asea',
					'article_search_background_color' => '!asea',
					'article_search_text_input_background_color' => '!asea',
					'article_search_btn_background_color' => '!asea',

					'advanced_search_ap_title_toggle' => 'asea',
					'advanced_search_ap_title' => 'asea',
					'advanced_search_ap_title_font_color' => 'asea',
					'advanced_search_ap_description_below_title_toggle' => 'asea',
					'advanced_search_ap_description_below_title' => 'asea',
					'advanced_search_ap_description_below_input_toggle' => 'asea',
					'advanced_search_ap_description_below_input' => 'asea',
					'advanced_search_ap_filter_category_level' => 'asea',
					'advanced_search_ap_show_top_category' => 'asea',
					'advanced_search_ap_link_font_color' => 'asea',
					'advanced_search_ap_background_color' => 'asea',

					'advanced_search_ap_background_gradient_toggle' => 'asea',
					'advanced_search_ap_background_gradient_from_color' => 'asea',
					'advanced_search_ap_background_gradient_to_color' => 'asea',
					'advanced_search_ap_background_gradient_degree' => 'asea',
					'advanced_search_ap_background_gradient_opacity' => 'asea',
				],
			),

			array(
				'title'         => __( 'Search Results Page', 'echo-knowledge-base' ),
				'fields'        => [
					'advanced_search_mp_results_page_size' => 'asea',
					'advanced_search_results_meta_created_on_toggle' => 'asea',
					'advanced_search_results_meta_author_toggle' => 'asea',
					'advanced_search_results_meta_categories_toggle' => 'asea'
				],
			),
		);

		// TOC
		$contents_configs['toc'] = array(
			array(
				'title'     => __( 'Location', 'echo-knowledge-base' ),
				'desc'      => __( 'Number 1 places the element at the top, 2 below it, and so on.', 'echo-knowledge-base' ),
				'fields'    => [
					'toc_locations' => 'core',  // is internally using by Settings UI
					'toc_left'      => 'core',
					'toc_content'   => 'core',
					'toc_right'     => 'core',
				],
				'learn_more_links' => [ // title => url
					__( 'Table of Content', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/table-of-content/',
				]
			),
			array(
				'title'     => __( 'Header Range', 'echo-knowledge-base' ),
				'fields'    => [
					'article_toc_hx_level' => 'core',
					'article_toc_hy_level' => 'core',
				],
			),
			array(
				'title'     => __( 'Title', 'echo-knowledge-base' ),
				'fields'    => [
					'article_toc_title_color' => 'core',
					'article_toc_background_color' => 'core',
					'article_toc_border_color' => 'core',
				],
			),
			array(
				'title'     => __( 'Headings', 'echo-knowledge-base' ),
				'fields'    => [
					'article_toc_text_color' => 'core',
					'article_toc_active_bg_color' => 'core',
					'article_toc_active_text_color' => 'core',
					'article_toc_cursor_hover_bg_color' => 'core',
					'article_toc_cursor_hover_text_color' => 'core',
				],
			),
		);

		// Sidebar
		$contents_configs['sidebar'] = array(
			array(
				'title'     => __( 'Left Sidebar', 'echo-knowledge-base' ),
				'fields'    => [
					'article-left-sidebar-toggle' => 'core',
					'article_nav_sidebar_type_left' => 'core',
					'nav_sidebar_left' => 'core',
					'kb_sidebar_left_toggler' => 'core',
					'kb_sidebar_left' => 'core',
				],
				'learn_more_links' => [ // title => url
					__( 'Sidebar', 'echo-knowledge-base' ) => 'https://www.echoknowledgebase.com/documentation/article-sidebars/',
				]
			),
			array(
				'title'     => __( 'Right Sidebar', 'echo-knowledge-base' ),
				'fields'    => [
					'article-right-sidebar-toggle' => 'core',
					'article_nav_sidebar_type_right' => 'core',
					'nav_sidebar_right' => 'core',
					'kb_sidebar_right_toggler' => 'core',
					'kb_sidebar_right' => 'core',
				]
			),
			array(
				'title'     => __( 'Categories and Articles Navigation', 'echo-knowledge-base' ),
				'fields'    => [
					'sidebar_top_categories_collapsed' => 'core',
					'sidebar_expand_articles_icon' => 'core',
					'sidebar_section_head_font_color' => 'core',
					'sidebar_section_category_icon_color' => 'core',
					'sidebar_section_category_font_color' => 'core',
					'sidebar_side_bar_height_mode' => 'core',
					'sidebar_side_bar_height' => 'core',
				],
				'dependency'    => [ 'article_nav_sidebar_type_left', 'article_nav_sidebar_type_right' ],
				'enable_on'     => [ 'eckb-nav-sidebar-v1' ],
			),
			array(
				'title'     => __( 'Top Categories Navigation', 'echo-knowledge-base' ),
				'fields'    => [
					'category_box_title_text_color' => 'only_categories',
                    'category_box_container_background_color' => 'core',
                    'category_box_category_text_color' => 'only_categories',
                    'category_box_count_background_color' => 'core',
                    'category_box_count_text_color' => 'core',
                    'category_box_count_border_color' => 'core',
				],
				'dependency'    => [ 'article_nav_sidebar_type_left', 'article_nav_sidebar_type_right' ],
				'enable_on'     => [ 'eckb-nav-sidebar-categories' ],
			),
			array(
				'title'         => __( 'Categories List', 'echo-knowledge-base' ),
				'fields'        => [
					'categories_layout_list_mode' => 'only_categories',
				],
				'dependency'    => [ 'article_nav_sidebar_type_left', 'article_nav_sidebar_type_right' ],
				'enable_on'     => [ 'eckb-nav-sidebar-categories' ],
			),
		);

		$contents_configs['general'] = array(
			array(
				'title'     => __( 'Theme Compatibility Mode', 'echo-knowledge-base' ),
				'desc'      => '',
				'fields'    => [
					'theme_compatibility_mode' => 'core',   // is internally using by Settings UI
				],
			),
			array(
				'title'     => __( 'KB Nickname', 'echo-knowledge-base' ),
				'desc'      => __( 'Give your Knowledge Base a name. The name will show when we refer to it or when you see a list of post types.', 'echo-knowledge-base' ),
				'fields'    => [
					'kb_name' => 'core',
				],
			),
		);

		// Rating and Feedback
		$contents_configs['ratings'] = array(
			array(
				'title'     => __( 'General', 'echo-knowledge-base' ),
				'fields'    => [
					'article_content_enable_rating_element' => 'eprf',
					'rating_mode' => 'eprf',
					'rating_element_alignment' => 'eprf',
					'rating_feedback_name_prompt' => 'eprf',
					'rating_feedback_email_prompt' => 'eprf',
					'rating_text_color' => 'eprf',
					'rating_element_color' => 'eprf',
					'rating_feedback_button_color' => 'eprf',
				],
			),
			array(
				'title'     => __( 'Stars Mode', 'echo-knowledge-base' ),
				'fields'    => [
					'rating_layout' => 'eprf',
					'rating_feedback_trigger_stars' => 'eprf',
					'rating_feedback_required_stars' => 'eprf',
					'rating_element_size' => 'eprf',
					'rating_element_color' => 'eprf',
				],
				'dependency'    => [ 'rating_mode' ],
				'enable_on'     => [ 'eprf-rating-mode-five-stars' ],
			),
			array(
				'title'     => __( 'Like/Dislike Mode', 'echo-knowledge-base' ),
				'fields'    => [
					'rating_like_style' => 'eprf',
					'rating_feedback_trigger_like' => 'eprf',
					'rating_feedback_required_like' => 'eprf',
					'rating_like_color' => 'eprf',
				],
				'dependency'    => [ 'rating_mode' ],
				'enable_on'     => [ 'eprf-rating-mode-like-dislike' ],
			)
		);

		// Full Editor
		$contents_configs['editor'] = array(
			array(
				'title'     => __( 'Visual Editor Launch Mode', 'echo-knowledge-base' ),
				'desc'      => __( 'This toggle controls how the buttons above open the Editor. The Editor can be shown either on the frontend or backend. ' .
				                   'If you experience compatibility issues on the front end, switch the Editor to the back end or vice-versa.', 'echo-knowledge-base' ),
				'fields'    => [
					'editor_backend_mode' => 'core',    // is storing in KB flags
				],
			),
		);

		return $contents_configs;
	}

	/**
	 * Return configuration array of helpful information box
     *
	 * @return array
	 */
	private function get_helpful_info_box_config() {

        $list_configs = array();

        $list_configs[] = array(
	        'title'    => __( 'Getting Started', 'echo-knowledge-base' ),
	        'desc'     => __( 'Set up your Knowledge Base name, url, and design', 'echo-knowledge-base' ),
	        'icon'     => 'img/need-help/rocket-2.jpg',
	        'btn_text' => __( 'Learn More', 'echo-knowledge-base' ),
	        'btn_url'  => admin_url( '/edit.php?post_type=' . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] ) . '&page=epkb-kb-need-help' ),
        );
        $list_configs[] = array(
	        'title'    => __( 'Explore Features', 'echo-knowledge-base' ),
	        'desc'     => __( 'Get familiar with features and how they function', 'echo-knowledge-base' ),
	        'icon'     => 'img/need-help/mountain-flag.jpg',
	        'btn_text' => __( 'Learn More', 'echo-knowledge-base' ),
	        'btn_url'  => admin_url( '/edit.php?post_type=' . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] ) . '&page=epkb-kb-need-help#features__design' ),
        );
		$list_configs[] = array(
			'title'    => __( 'Online Documentation', 'echo-knowledge-base' ),
			'desc'     => __( 'Get familiar with features and how they function', 'echo-knowledge-base' ),
			'icon'     => 'img/need-help/education-hat.jpg',
			'btn_text' => __( 'Learn More', 'echo-knowledge-base' ),
			'btn_url'  => 'https://www.echoknowledgebase.com/documentation/',
		);
		$list_configs[] = array(
			'title'    => __( 'Contact Us', 'echo-knowledge-base' ),
			'desc'     => __( 'Support question for something that is not working correctly', 'echo-knowledge-base' ),
			'icon'     => 'img/need-help/mail.jpg',
			'btn_text' => __( 'Ask a Question', 'echo-knowledge-base' ),
			'btn_url'  => admin_url( '/edit.php?post_type=' . EPKB_KB_Handler::get_post_type( $this->kb_config['id'] ) . '&page=epkb-kb-need-help#contact-us' ),
		);

        return $list_configs;
    }

	/**
	 * Adjust field specification when display it on Settings
	 *
	 * @param $field_specs
	 *
	 * @return mixed|string|void
	 * @noinspection PhpUnusedParameterInspection*/
	private function set_custom_field_specs( $field_specs ) {

		// overwrite existing or add additional data
		$current_kb_id = EPKB_KB_Handler::get_current_kb_id();
		$kb_config = epkb_get_instance()->kb_config_obj->get_kb_config_or_default( $current_kb_id );

		switch ( $field_specs['name'] ) {

			case 'section_head_category_icon_size':
				$field_specs['dependency'] = ['section_head_category_icon_location'];
				$field_specs['enable_on'] = ['top', 'left', 'right'];
				$field_specs['desc'] = '<a class="epkb-admin__input-field-desc" href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=' . EPKB_KB_Handler::get_category_taxonomy_name( $kb_config['id'] ) .
				                                                                                                     '&post_type=' . EPKB_KB_Handler::get_post_type( $kb_config['id'] ) ) ) . '" target="_blank">' . esc_html__( 'Edit Categories Icons', 'echo-knowledge-base' ) . '</a>';
				break;

			case 'width':
				if ( $kb_config['templates_for_kb'] ==  'current_theme_templates' ) {
					$field_specs['desc'] = '<div class="epkb-editor__info"><span class="epkbfa epkbfa-info-circle"></span> ' .
										   esc_html__( 'We have detected that you are using the Current Theme Template option. If your width is not expanding the way you want, it is because the theme is controlling the total width. ' .
													   'You have two options: either switch to the KB Template option or check your theme settings to expand the width.', 'echo-knowledge-base' ) .
										   ' <a href="https://www.echoknowledgebase.com/documentation/current-theme-template-vs-kb-template/" target="_blank"><span class="epkbfa epkbfa-external-link"></span></a></div>';
				}
				break;

			case 'section_head_category_icon_color':
				$field_specs['dependency'] = ['section_head_category_icon_location'];
				$field_specs['enable_on'] = ['top', 'left', 'right'];
				break;

			case 'section_head_description_font_color':
				$field_specs['dependency'] = ['section_desc_text_on'];
				$field_specs['enable_on'] = ['on'];
				break;

			case 'article_nav_sidebar_type_left':
			case 'article_nav_sidebar_type_right':
			case 'rating_mode':
			case 'section_head_category_icon_location':
			case 'section_desc_text_on':
				$field_specs['input_group_class'] = 'eckb-conditional-setting-input';
				break;

			default:
				break;
		}

		return $field_specs;
	}

	/**
	 * Return only corresponding settings in the given list
	 *
	 * @param $settings_list
	 * @return mixed
	 */
	private function filter_settings( $settings_list ) {

		foreach ( $settings_list as $setting_name => $requirement ) {

			// normalize requirement
			$requirement = is_array( $requirement ) ? $requirement : [ $requirement ];

			// a field can be disabled at certain times
			if ( in_array( 'none', $requirement ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}

			// a field might require an add-on plugins
			if ( ( in_array( '!elay', $requirement ) && $this->elay_enabled ) || ( in_array( 'elay', $requirement ) && ! $this->elay_enabled ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}
			if ( ( in_array( '!asea', $requirement ) && $this->asea_enabled ) || ( in_array( 'asea', $requirement ) && ! $this->asea_enabled ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}
			if ( ( in_array( '!eprf', $requirement ) && $this->eprf_enabled ) || ( in_array( 'eprf', $requirement ) && ! $this->eprf_enabled ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}

			// a field might require certain layout
			if ( ( in_array( 'not_basic', $requirement ) && $this->is_basic_layout ) || ( in_array( 'only_basic', $requirement ) && ! $this->is_basic_layout ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}
			if ( ( in_array( 'not_tabs', $requirement ) && $this->is_tabs_layout ) || ( in_array( 'only_tabs', $requirement ) && ! $this->is_tabs_layout ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}
			if ( ( in_array( 'not_categories', $requirement ) && $this->is_categories_layout ) || ( in_array( 'only_categories', $requirement ) && ! $this->is_categories_layout ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}
			if ( ( in_array( 'not_grid', $requirement ) && $this->is_grid_layout ) || ( in_array( 'only_grid', $requirement ) && ! $this->is_grid_layout ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}
			if ( ( in_array( 'not_sidebar', $requirement ) && $this->is_sidebar_layout ) || ( in_array( 'only_sidebar', $requirement ) && ! $this->is_sidebar_layout ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}

			// a field can require KB Templates enabled
			if ( ( in_array( 'not_kb_templates', $requirement ) && $this->is_kb_templates ) || ( in_array( 'only_kb_templates', $requirement ) && ! $this->is_kb_templates ) ) {
				unset( $settings_list[$setting_name] );
				continue;
			}
		}

		return $settings_list;
	}

	/**
	 * Generate html for more information drop down for the block
	 * @param array $links
	 * @return string
	 */
	private function learn_more_block( $links = [] ) {
		ob_start(); ?>

		<div class="epkb-admin__form-tab-content-learn-more">
			<div class="epkb-admin__form-tab-content-lm__header">
				<button class="epkb-admin__form-tab-content-lm__toggler"><?php esc_html_e( 'Learn More', 'echo-knowledge-base' ); ?></button>
			</div>
			<div class="epkb-admin__form-tab-content-lm__body">
				<div class="epkb-admin__form-tab-content-lm__description">
					<?php echo esc_html__( 'Learn more about these settings in the following articles', 'echo-knowledge-base' ) . ':'; ?>
				</div>
				<div class="epkb-admin__form-tab-content-lm__links"><?php
					foreach ( $links as $link_title => $link_url ) { ?>
						<div class="epkb-admin__form-tab-content-lm__link">
							<span class="eckb-article-title__icon ep_font_icon_document" style="color: #b3b3b3;"></span>
							<a href="<?php echo $link_url; ?>" target="_blank"><?php echo $link_title; ?></a>
						</div><?php
					} ?>
				</div>
				<div class="epkb-admin__form-tab-content-lm__footer">
					<a href="https://www.echoknowledgebase.com/documentation/" target="_blank"><?php esc_html_e( 'For all other Articles Browser Our Documentation.', 'echo-knowledge-base' ); ?></a>
				</div>
			</div>
		</div><?php

		return ob_get_clean();
	}
}