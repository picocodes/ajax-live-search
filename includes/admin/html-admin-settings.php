<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="wrap als">
	<form method="<?php echo esc_attr( apply_filters( 'als_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		
		<h2 class="nav-tab-wrapper als-nav-tab-wrapper">
			<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . admin_url( 'admin.php?page=als-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
				}

				do_action( 'als_settings_tabs' );
			?>
		</h2>

		<?php
			self::show_messages();

			do_action( 'als_sections_' . $current_tab );
			do_action( 'als_settings_' . $current_tab );
			do_action( 'als_settings_tabs_' . $current_tab ); 
		?>

		<p class="submit">
			<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary" type="submit" value="<?php esc_attr_e( 'Save changes', 'als' ); ?>" />
			<?php endif; ?>
			<input type="hidden" name="subtab" id="last_tab" />
			<?php wp_nonce_field( 'als-settings' ); ?>
		</p>
	</form>
</div>
