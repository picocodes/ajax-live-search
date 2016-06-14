<?php
/**
 * Displays the searching settings tab
 *
 * @author   Picocodes
 * @package  Ajax Live Search Lite
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Used to display the searching tab of our settings page
 * @since      1.0.0
 *
 * @package    Ajax Live Search Lite
 * @subpackage Als/admin
 */
class Als_Settings_searching extends Als_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'searching';
		$this->label = __( 'Searching', 'als-lite' );

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

		$settings = apply_filters( 'als_searching_settings', array(

			array( 'title' => __( 'Search Autocomplete', 'als-lite' ), 'type' => 'title', 'desc' => '', 'id' => 'autocomplete_options' ),

			array(
				'title'    => __( 'Display autocomplete suggestions.', 'als-lite' ),
				'desc'     =>'',
				'id'       => 'als_display_autocomplete',
				'css'      => '',
				'default'  => 'checked',
				'type'     => 'checkbox',
				'desc_tip' =>  true,
			),

			array(
				'title'    => __( 'Database table to use for autocompletes.', 'als-lite' ),
				'desc'     =>'',
				'id'       => 'als_autocomplete_table',
				'css'      => '',
				'default'  => 'posts',
				'type'     => 'radio',
				'options'  => array(
					'posts'      => __( 'WP Posts Table', 'als-lite' ),
					'searches'      => __( 'Previous Searches Table', 'als-lite' )
					),
				'desc_tip' =>  true,
			),
			
			array(
				'title'    => __( 'Number Of Suggestions', 'als' ),
				'desc'     =>'',
				'id'       => 'als_autocomplete_count',
				'css'      => 'width: 48px;',
				'default'  => '5',
				'type'     => 'text',
				'desc_tip' =>  true,
			),
			
			array(
					'type' 	=> 'sectionend',
					'id' 	=> 'autocomplete_options'
				),
				
			array( 'title' => __( 'Indexing', 'als-lite' ), 'type' => 'title', 'desc' => '', 'id' => 'searching_options' ),
			
			array(
				'title'    => __( 'Post Types to Index', 'als-lite' ),
				'desc'     =>'Separate with a comma',
				'id'       => 'als_post_types',
				'css'      => '',
				'default'  => als_post_types(),
				'type'     => 'textarea',
				'desc_tip' =>  true,
			),
			
			array(
				'title'    => __( 'Reindex a post on publish', 'als-lite' ),
				'id'       => 'als_index_on_publish',
				'css'      => '',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'desc_tip' =>  true,
			),
			
			array(
				'title'    => __( 'Title Weight', 'als-lite' ),
				'id'       => 'als_title_weight',
				'css'      => 'width: 48px;',
				'default'  => '1.5',
				'type'     => 'text',
				'desc_tip' =>  true,
			),

			array(
				'title'    => __( 'URL Weight', 'als-lite' ),
				'id'       => 'als_url_weight',
				'css'      => 'width: 48px;',
				'default'  => '2.5',
				'type'     => 'text',
				'desc_tip' =>  true,
			),

			array(
				'title'    => __( 'Content Weight', 'als-lite' ),
				'id'       => 'als_content_weight',
				'css'      => 'width: 48px;',
				'default'  => '1',
				'type'     => 'text',
				'desc_tip' =>  true,
			),
			
			array(
				'title'    => __( 'Excerpt Weight', 'als-lite' ),
				'id'       => 'als_excerpt_weight',
				'css'      => 'width: 48px;',
				'default'  => '1.25',
				'type'     => 'text',
				'desc_tip' =>  true,
			),


			array(
					'type' 	=> 'sectionend',
					'id' 	=> 'searching_options'
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

return new Als_Settings_searching();
