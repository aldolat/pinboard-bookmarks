<?php
/**
 * This file contains the functions used in the widget's forms
 *
 * @since 1.0
 * @package PinboardBookmarks
 */

/**
 * Prevent direct access to this file.
 *
 * @since 2.0
 */
if ( ! defined( 'WPINC' ) ) {
	exit( 'No script kiddies please!' );
}

/**
 * Create a form label to be used in the widget panel.
 *
 * @since 1.12
 * @param string $label The label to display.
 * @param string $id The id of the label.
 */
function pinboard_bookmarks_form_label( $label, $id ) {
	echo '<label for="' . esc_attr( $id ) . '">' . wp_kses_post( $label ) . '</label>';
}

/**
 * Create a form text input to be used in the widget panel.
 *
 * @since 1.12
 * @param string $label The label to display.
 * @param string $id The id of the label.
 * @param string $name The name of the input form.
 * @param string $value The values of the input form.
 * @param string $placeholder The HTML placeholder for the input form.
 * @param string $comment An optional comment to display. It is displayed below the input form.
 * @param string $style An optional inline style.
 * @param string $class An optional CSS class.
 * @uses pinboard_bookmarks_form_label
 */
function pinboard_bookmarks_form_input_text( $label, $id, $name, $value, $placeholder = '', $comment = '', $style = '', $class = '' ) {
	$class = rtrim( 'widefat pinboard-bookmarks-input ' . $class );

	if ( $style ) {
		echo '<p style="' . esc_attr( $style ) . '">';
	} else {
		echo '<p>';
	}

	pinboard_bookmarks_form_label( $label, $id );

	?>
	<input type="text"
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="<?php echo esc_html( $placeholder ); ?>"
		class="<?php echo esc_attr( $class ); ?>" />
	<?php

	if ( $comment ) {
		echo '<br /><em>' . wp_kses_post( $comment ) . '</em>';
	}

	echo '</p>';
}

/**
 * Create a form textarea to be used in the widget panel.
 *
 * @param string $label The label to display.
 * @param string $id The id of the label.
 * @param string $name The name of the textarea form.
 * @param string $text The text to display.
 * @param string $placeholder The HTML placeholder for the input form.
 * @param string $comment An optional comment to display. It is displayed below the textarea form.
 * @param string $style An optional inline style.
 * @since 1.12
 */
function pinboard_bookmarks_form_textarea( $label, $id, $name, $text, $placeholder = '', $comment = '', $style = '' ) {
	echo '<p>';
	pinboard_bookmarks_form_label( $label, $id );

	?>
	<textarea
	id="<?php echo esc_attr( $id ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	rows="2" cols="10"
	placeholder="<?php echo esc_html( $placeholder ); ?>"
	class="widefat"
	style="<?php echo esc_attr( $style ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
	<?php

	if ( $comment ) {
		echo '<br /><em>' . wp_kses_post( $comment ) . '</em>';
	}
	echo '</p>';
}

/**
 * Create a form checkbox to be used in the widget panel.
 *
 * @param string $label The label to display.
 * @param string $id The id of the label.
 * @param string $name The name of the checkbox form.
 * @param string $checked If the option is checked.
 * @param string $comment An optional comment to display. It is displayed below the checkbox form.
 * @param string $class An optional CSS class.
 * @since 1.12
 */
function pinboard_bookmarks_form_checkbox( $label, $id, $name, $checked, $comment = '', $class = '' ) {
	$class = rtrim( 'checkbox pinboard-bookmarks-checkbox ' . $class );
	?>
	<p>
		<input class="<?php echo esc_attr( $class ); ?>" type="checkbox" <?php checked( $checked ); ?> id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" />
		&nbsp;
		<?php pinboard_bookmarks_form_label( $label, $id ); ?>
		<?php
		if ( $comment ) {
			?>
			<br /><em><?php echo wp_kses_post( $comment ); ?></em>
		<?php } ?>
	</p>
	<?php
}

/**
 * Create a form select to be used in the widget panel.
 *
 * @param string $label The label to display.
 * @param string $id The id of the label.
 * @param string $name The name of the select form.
 * @param string $options The options to display.
 * @param string $value The values of the select form.
 * @param string $comment An optional comment to display. It is displayed below the select form.
 * @param string $class The custom class for the select element.
 * @since 1.12
 */
function pinboard_bookmarks_form_select( $label, $id, $name, $options, $value, $comment = '', $class = '' ) {
	$class = rtrim( 'pinboard-bookmarks-select ' . $class );
	?>
	<p>
		<?php pinboard_bookmarks_form_label( $label, $id ); ?>
		&nbsp;
		<select name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<?php foreach ( $options as $option ) : ?>
			<option <?php selected( $option['value'], $value, true ); ?> value="<?php echo esc_attr( $option['value'] ); ?>">
				<?php echo esc_html( $option['desc'] ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php if ( $comment ) : ?>
		<br /><em><?php echo wp_kses_post( $comment ); ?></em>
		<?php endif; ?>
	</p>
	<?php
}
