<?php
/**
 * Displays the result pages settings tab
 *
 * @author   Picocodes
 * @package  Ajax Live Search Lite
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Main class to display the Results page settings tab
 *
 */

class Als_Settings_results extends Als_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'results';
		$this->label = __( 'Result Pages', 'als-lite' );

		add_filter( 'als_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'als_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'als_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {


		$settings = apply_filters( 'als_results_settings', array(

			array( 'title' => __( 'Result Page Options', 'als-lite' ), 'type' => 'title', 'desc' => '', 'id' => 'results_options' ),
			
			array(
				'title'    => __( 'Enable Ajax Loading Of Results', 'als-lite' ),
				'desc'     =>__("Results will be shown without reloading the page","als-lite"),
				'id'       => 'als_ajax_results',
				'css'      => '',
				'default'  => 'checked',
				'type'     => 'checkbox',
				'desc_tip' =>  true,
			),

			array(
				'title'    => __( 'Enable Live Search', 'als-lite' ),
				'desc'     =>__("Results will be shown in real time as the user types.","als-lite"),
				'id'       => 'als_live_search',
				'css'      => '',
				'default'  => '',
				'type'     => 'checkbox',
				'desc_tip' =>  true,
			),


			array(
				'title'    => __( 'Result Page Theme', 'als-lite' ),
				'desc'     => __( 'What theme to use on the results page.', 'als-lite' ),
				'id'       => 'als_theme',
				'default'  => 'als',
				'type'     => 'radio',
				'class'    => '',
				'css'      => '',
				'desc_tip' =>  true,
				'options'  => apply_filters('als_search_themes', array(
					'als'      => __( 'Default Theme.', 'als-lite' )))
				),
				
				
			array(
					'type' 	=> 'sectionend',
					'id' 	=> 'results_options'
				),
				
			array( 'title' => __( 'Result Page Excerts', 'als-lite' ), 'type' => 'title', 'desc' => '', 'id' => 'ercerpt_options' ),
			
			array(
				'title'    => __( 'Highlight search terms', 'als-lite' ),
				'id'       => 'als_highlight_term',
				'css'      => '',
				'default'  => 'checked',
				'type'     => 'checkbox',
				'desc_tip' =>  true,
			),
			
			array(
				'title'    => __( 'Excerpt Length', 'als-lite' ),
				'id'       => 'als_excerpt_length',
				'css'      => '',
				'default'  => '200 characters',
				'type'     => 'text',
				'desc_tip' =>  true,
			),
			
			array(
				'title'    => __( 'Allowable Tags in Excerpts', 'als-lite' ),
				'id'       => 'als_excerpt_tags',
				'css'      => 'width: 300px;height: 80px;',
				'default'  => '<strong>,<b>',
				'type'     => 'textarea',
				'desc_tip' =>  true,
			),
			array(
					'type' 	=> 'sectionend',
					'id' 	=> 'ercerpt_options'
				),
				
			array( 'title' => __( 'Result Page Content', 'als-lite' ), 'type' => 'title', 'desc' => '', 'id' => 'content_options' ),
			
			array(
					'title'           => __( 'Main Search Results', 'als-lite' ),
					'desc'            => __( 'Main results', 'als-lite' ),
					'id'              => 'als_content_options_results',
					'default'         => 'yes',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option',
					'autoload'        => false
				),

			array(
					'type' 	=> 'sectionend',
					'id' 	=> 'ercerpt_options'
				)
			
		) );

		return apply_filters( 'als_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		Als_Admin_Settings::save_fields( $settings );
	}

}

return new Als_Settings_results();
