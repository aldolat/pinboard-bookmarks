<?php
/**
 * The widget
 *
 * @package PinboardBookmarks
 * @since 1.0
 */

/**
 * Prevent direct access to this file.
 *
 * @since 1.0
 */
if ( ! defined( 'WPINC' ) ) {
	exit( 'No script kiddies please!' );
}

/**
 * Creates the widget and display it.
 *
 * @since 1.0
 */
class Pinboard_Bookmarks_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$widget_ops  = array(
			'classname'   => 'pinboard_bookmarks_widget',
			'description' => esc_html__( 'Publish a bookmarks list using your Pinboard bookmarks', 'pinboard-bookmarks' ),
		);
		$control_ops = array(
			'width'   => 350,
			'id_base' => 'pinboard_bookmarks_widget',
		);

		parent::__construct(
			'pinboard_bookmarks_widget',
			esc_html__( 'Pinboard Bookmarks', 'pinboard-bookmarks' ),
			$widget_ops,
			$control_ops
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 *                    $args contains:
	 *                        $args['name'];
	 *                        $args['id'];
	 *                        $args['description'];
	 *                        $args['class'];
	 *                        $args['before_widget'];
	 *                        $args['after_widget'];
	 *                        $args['before_title'];
	 *                        $args['after_title'];
	 *                        $args['widget_id'];
	 *                        $args['widget_name'].
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, pinboard_bookmarks_get_defaults() );

		echo "\n" . '<!-- Start Pinboard Bookmarks - ' . $args['widget_id'] . ' -->' . "\n";

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		pinboard_bookmarks_fetch_feed( $instance );

		echo $args['after_widget'];

		echo "\n" . '<!-- End Pinboard Bookmarks - ' . $args['widget_id'] . ' -->' . "\n\n";
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = (array) $old_instance;

		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['intro_text'] = wp_kses_post( $new_instance['intro_text'] );
		$instance['username']   = preg_replace( '([^a-zA-Z0-9\-_])', '', sanitize_text_field( $new_instance['username'] ) );

		$instance['tags'] = sanitize_text_field( $new_instance['tags'] );
		$instance['tags'] = trim( preg_replace( '([\s,]+)', ' ', $instance['tags'] ) );
		$tags             = explode( ' ', $instance['tags'] );
		if ( 4 < count( $tags ) ) {
			$tags = array_slice( $tags, 0, 4 );
		}
		$instance['tags'] = implode( ' ', $tags );

		$instance['source'] = sanitize_text_field( $new_instance['source'] );

		$instance['quantity'] = absint( sanitize_text_field( $new_instance['quantity'] ) );
		if ( '' === $instance['quantity'] || ! is_numeric( $instance['quantity'] ) ) {
			$instance['quantity'] = 5;
		}
		if ( 400 < $instance['quantity'] ) {
			$instance['quantity'] = 400;
		}

		$instance['random']       = isset( $new_instance['random'] ) ? true : false;
		$instance['display_desc'] = isset( $new_instance['display_desc'] ) ? true : false;

		$instance['truncate'] = absint( sanitize_text_field( $new_instance['truncate'] ) );
		if ( '' === $instance['truncate'] || ! is_numeric( $instance['truncate'] ) ) {
			$instance['truncate'] = 0;
		}

		$instance['display_date']    = isset( $new_instance['display_date'] ) ? true : false;
		$instance['display_time']    = isset( $new_instance['display_time'] ) ? true : false;
		$instance['date_text']       = trim( sanitize_text_field( $new_instance['date_text'] ) );
		$instance['display_tags']    = isset( $new_instance['display_tags'] ) ? true : false;
		$instance['tags_text']       = sanitize_text_field( $new_instance['tags_text'] );
		$instance['display_hashtag'] = isset( $new_instance['display_hashtag'] ) ? true : false;
		$instance['use_comma']       = isset( $new_instance['use_comma'] ) ? true : false;
		$instance['display_source']  = isset( $new_instance['display_source'] ) ? true : false;
		$instance['display_arrow']   = isset( $new_instance['display_arrow'] ) ? true : false;

		$instance['time'] = absint( sanitize_text_field( $new_instance['time'] ) );
		if ( '' === $instance['time'] || ! is_numeric( $instance['time'] ) ) {
			$instance['time'] = 1800;
		}
		if ( 1800 > $instance['time'] ) {
			$instance['time'] = 1800;
		}

		$instance['display_archive']  = isset( $new_instance['display_archive'] ) ? true : false;
		$instance['archive_text']     = sanitize_text_field( $new_instance['archive_text'] );
		$instance['list_type']        = sanitize_text_field( $new_instance['list_type'] );
		$instance['display_arch_arr'] = isset( $new_instance['display_arch_arr'] ) ? true : false;
		$instance['new_tab']          = isset( $new_instance['new_tab'] ) ? true : false;
		$instance['nofollow']         = isset( $new_instance['nofollow'] ) ? true : false;
		$instance['items_order']      = pinboard_bookmarks_check_items( $new_instance['items_order'] );
		$instance['admin_only']       = isset( $new_instance['admin_only'] ) ? true : false;
		$instance['debug_options']    = isset( $new_instance['debug_options'] ) ? true : false;
		$instance['debug_urls']       = isset( $new_instance['debug_urls'] ) ? true : false;

		// This option is stored only for debug purposes.
		$instance['widget_id'] = $this->id;

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, pinboard_bookmarks_get_defaults() );

		// *** STARTS PART TO BE REMOVED IN 1.8.1.
		'on' === $instance['random'] ? $instance['random']                     = true : false;
		'on' === $instance['display_desc'] ? $instance['display_desc']         = true : false;
		'on' === $instance['display_date'] ? $instance['display_date']         = true : false;
		'on' === $instance['display_time'] ? $instance['display_time']         = true : false;
		'on' === $instance['display_tags'] ? $instance['display_tags']         = true : false;
		'on' === $instance['display_hashtag'] ? $instance['display_hashtag']   = true : false;
		'on' === $instance['use_comma'] ? $instance['use_comma']               = true : false;
		'on' === $instance['display_source'] ? $instance['display_source']     = true : false;
		'on' === $instance['display_arrow'] ? $instance['display_arrow']       = true : false;
		'on' === $instance['display_archive'] ? $instance['display_archive']   = true : false;
		'on' === $instance['display_arch_arr'] ? $instance['display_arch_arr'] = true : false;
		'on' === $instance['new_tab'] ? $instance['new_tab']                   = true : false;
		'on' === $instance['nofollow'] ? $instance['nofollow']                 = true : false;
		'on' === $instance['admin_only'] ? $instance['admin_only']             = true : false;
		'on' === $instance['debug_options'] ? $instance['debug_options']       = true : false;
		'on' === $instance['debug_urls'] ? $instance['debug_urls']             = true : false;
		// *** ENDS PART TO BE REMOVED IN 1.8.1.
		?>

		<div class="pinboard-bookmarks-widget-content">

			<h4 class="no-border"><?php esc_html_e( 'Introduction', 'pinboard-bookmarks' ); ?></h4>

			<p>
				<?php esc_html_e( 'This widget allows you to publish a list of Pinboard bookmarks in your sidebar. Simply enter a username on Pinboard and/or one or more tags. Then click on Save button.', 'pinboard-bookmarks' ); ?>
			</p>

			<p>
				<?php esc_html_e( 'Please note that a username or one tag is required, at least.', 'pinboard-bookmarks' ); ?>
			</p>

			<h4><?php esc_html_e( 'Title of the widget', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Title.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Title:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'title' ),
				$this->get_field_name( 'title' ),
				esc_attr( $instance['title'] ),
				esc_html__( 'My bookmarks on Pinboard', 'pinboard-bookmarks' )
			);
			?>

			<h4><?php esc_html_e( 'Introductory text', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Introductory text.
			pinboard_bookmarks_form_textarea(
				esc_html__( 'Place this text after the title', 'pinboard-bookmarks' ),
				$this->get_field_id( 'intro_text' ),
				$this->get_field_name( 'intro_text' ),
				$instance['intro_text'],
				esc_html__( 'These are my bookmarks on Pinboard about Italian recipes.', 'pinboard-bookmarks' ),
				esc_html__( 'You can use some HTML, as you would do when writing a post.', 'pinboard-bookmarks' ),
				$style = 'resize: vertical; height: 80px;'
			);
			?>

			<h4><?php esc_html_e( 'Basic Setup', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Username.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Username on Pinboard:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'username' ),
				$this->get_field_name( 'username' ),
				esc_attr( $instance['username'] ),
				esc_html__( 'username', 'pinboard-bookmarks' ),
				esc_html__( 'This is the only mandatory option.', 'pinboard-bookmarks' )
			);

			// Tags.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Tags:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'tags' ),
				$this->get_field_name( 'tags' ),
				esc_attr( $instance['tags'] ),
				esc_html__( 'books reading comics', 'pinboard-bookmarks' ),
				esc_html__( 'Enter a space separated list of tags, up to 4 tags. The plugin will fetch bookmarks from this list of tags.', 'pinboard-bookmarks' )
			);

			// Source.
			$options = array(
				'none'       => array(
					'value' => '',
					'desc'  => esc_html__( 'None', 'pinboard-bookmarks' ),
				),
				'pocket'     => array(
					'value' => 'pocket',
					'desc'  => esc_html__( 'Pocket', 'pinboard-bookmarks' ),
				),
				'instapaper' => array(
					'value' => 'instapaper',
					'desc'  => esc_html__( 'Instapaper', 'pinboard-bookmarks' ),
				),
				/**
				 * Remove support for Twitter.
				 *
				 * The code was:
				 * 'twitter' => array(
				 *     'value' => 'twitter',
				 *     'desc'  => esc_html__( 'Twitter', 'pinboard-bookmarks' )
				 * ),
				 *
				 * @since 1.6.0
				 */
			);
			pinboard_bookmarks_form_select(
				esc_html__( 'Source of the bookmarks', 'pinboard-bookmarks' ),
				$this->get_field_id( 'source' ),
				$this->get_field_name( 'source' ),
				$options,
				$instance['source'],
				// translators: %s is the name of a bookmarking service like Instapaper.
				sprintf( esc_html__( 'Select the source of the bookmarks, like %s. Since Pinboard accepts tags or a source, the tags from the field above will be ignored if you activate this option.', 'pinboard-bookmarks' ), '<code>from:pocket</code>' )
			);

			// Number of items.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Maximum number of items:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'quantity' ),
				$this->get_field_name( 'quantity' ),
				esc_attr( $instance['quantity'] ),
				'5',
				esc_html__( 'Maximum 400 items.', 'pinboard-bookmarks' )
			);

			// Random order.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display items in random order', 'pinboard-bookmarks' ),
				$this->get_field_id( 'random' ),
				$this->get_field_name( 'random' ),
				$instance['random']
			);

			// Fetching time.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Minimum time between two fetchings:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'time' ),
				$this->get_field_name( 'time' ),
				esc_attr( $instance['time'] ),
				'1800',
				esc_html__( 'In seconds. Minimum 1800 seconds (30 minutes).', 'pinboard-bookmarks' )
			);
			?>

			<h4><?php esc_html_e( 'Bookmark description', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Description.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display the bookmark description', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_desc' ),
				$this->get_field_name( 'display_desc' ),
				$instance['display_desc']
			);

			// Description length.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Length of the description:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'truncate' ),
				$this->get_field_name( 'truncate' ),
				esc_attr( $instance['truncate'] ),
				'50',
				// translators: the number of words for the bookmark description.
				sprintf( esc_html__( '(In words. %s means full text)', 'pinboard-bookmarks' ), '<code>0</code>' )
			);
			?>

			<h4><?php esc_html_e( 'Date of the bookmark', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Date.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display the date of the bookmark', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_date' ),
				$this->get_field_name( 'display_date' ),
				$instance['display_date']
			);

			// Time.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Also display the time of the bookmark', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_time' ),
				$this->get_field_name( 'display_time' ),
				$instance['display_time']
			);

			// Text for the date.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Text before the date:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'date_text' ),
				$this->get_field_name( 'date_text' ),
				esc_attr( $instance['date_text'] ),
				esc_html__( 'Stored on', 'pinboard-bookmarks' ),
				esc_html__( 'A space will be added after the text.', 'pinboard-bookmarks' )
			);
			?>

			<h4><?php esc_html_e( 'Tags and source of the bookmark', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Tags.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display tags', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_tags' ),
				$this->get_field_name( 'display_tags' ),
				$instance['display_tags']
			);

			// Text for tags.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Text before tags list:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'tags_text' ),
				$this->get_field_name( 'tags_text' ),
				esc_attr( $instance['tags_text'] ),
				esc_html__( 'Tags:', 'pinboard-bookmarks' ),
				esc_html__( 'A space will be added after the text.', 'pinboard-bookmarks' )
			);

			// Hashtag.
			pinboard_bookmarks_form_checkbox(
				// translators: %s is an hashtag.
				sprintf( esc_html__( 'Display an hashtag %s before each tag', 'pinboard-bookmarks' ), '(<code>#</code>)' ),
				$this->get_field_id( 'display_hashtag' ),
				$this->get_field_name( 'display_hashtag' ),
				$instance['display_hashtag']
			);

			// Comma.
			pinboard_bookmarks_form_checkbox(
				// translators: %s is a comma.
				sprintf( esc_html__( 'Use a comma %s after each tag', 'pinboard-bookmarks' ), '(<code>,</code>)' ),
				$this->get_field_id( 'use_comma' ),
				$this->get_field_name( 'use_comma' ),
				$instance['use_comma']
			);

			// Display source.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display the source of the bookmark', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_source' ),
				$this->get_field_name( 'display_source' ),
				$instance['display_source']
			);
			?>

			<h4><?php esc_html_e( 'Link to the archive', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Archive.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display the link to my bookmarks archive on Pinboard', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_archive' ),
				$this->get_field_name( 'display_archive' ),
				$instance['display_archive']
			);

			// Text for archive.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Text for the archive link:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'archive_text' ),
				$this->get_field_name( 'archive_text' ),
				esc_attr( $instance['archive_text'] ),
				esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' )
			);

			// Archive arrow.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display an arrow after the link to the archive', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_arch_arr' ),
				$this->get_field_name( 'display_arch_arr' ),
				$instance['display_arch_arr']
			);
			?>

			<h4><?php esc_html_e( 'Other options', 'pinboard-bookmarks' ); ?></h4>

			<?php
			// Type of list.
			$options = array(
				'bullet' => array(
					'value' => 'bullet',
					'desc'  => esc_html__( 'Unordered list', 'pinboard-bookmarks' ),
				),
				'number' => array(
					'value' => 'number',
					'desc'  => esc_html__( 'Ordered list', 'pinboard-bookmarks' ),
				),
			);
			pinboard_bookmarks_form_select(
				esc_html__( 'Use this type of list', 'pinboard-bookmarks' ),
				$this->get_field_id( 'list_type' ),
				$this->get_field_name( 'list_type' ),
				$options,
				$instance['list_type']
			);

			// Arrow.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display an arrow after each title', 'pinboard-bookmarks' ),
				$this->get_field_id( 'display_arrow' ),
				$this->get_field_name( 'display_arrow' ),
				$instance['display_arrow']
			);

			// Open links in new tab.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Open links in a new browser tab', 'pinboard-bookmarks' ),
				$this->get_field_id( 'new_tab' ),
				$this->get_field_name( 'new_tab' ),
				$instance['new_tab']
			);

			// No follow.
			pinboard_bookmarks_form_checkbox(
				// translators: %s is the nofollow.
				sprintf( esc_html__( 'Add %s to links', 'pinboard-bookmarks' ), '<code>nofollow</code>' ),
				$this->get_field_id( 'nofollow' ),
				$this->get_field_name( 'nofollow' ),
				$instance['nofollow'],
				__( 'It will be added to all external links.', 'pinboard-bookmarks' )
			);
			?>

			<h4><?php esc_html_e( 'Displaying order', 'pinboard-bookmarks' ); ?></h4>

			<p><?php esc_html_e( 'Define the order in which the elements of each item will be displayed. The available elements are:', 'pinboard-bookmarks' ); ?></p>

			<p><code>title</code> <code>description</code> <code>date</code> <code>tags</code></p>

			<?php
			// Displaying order.
			pinboard_bookmarks_form_input_text(
				esc_html__( 'Order of the elements of each item:', 'pinboard-bookmarks' ),
				$this->get_field_id( 'items_order' ),
				$this->get_field_name( 'items_order' ),
				esc_attr( $instance['items_order'] ),
				esc_html( 'title description date tags' ), // String NOT to be translated.
				esc_html__( 'Enter a space separated list of elements.', 'pinboard-bookmarks' )
			);
			?>

			<h4><?php esc_html_e( 'Debug options', 'pinboard-bookmarks' ); ?></h4>

			<p>
				<?php
				printf(
					// translators: %s is version of the plugin.
					esc_html__( 'You are using Pinboard Bookmarks version %s.', 'pinboard-bookmarks' ),
					'<strong>' . esc_attr( PINBOARD_BOOKMARKS_PLUGIN_VERSION ) . '</strong>'
				);
				?>
			</p>

			<p class="pinboard-bookmarks-alert"><strong><?php esc_html_e( 'Use these options for debugging purposes only.', 'pinboard-bookmarks' ); ?></strong></p>

			<?php
			// Admins only.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display debugging information to admins only', 'pinboard-bookmarks' ),
				$this->get_field_id( 'admin_only' ),
				$this->get_field_name( 'admin_only' ),
				$instance['admin_only']
			);

			// Debugging options.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display parameters', 'pinboard-bookmarks' ),
				$this->get_field_id( 'debug_options' ),
				$this->get_field_name( 'debug_options' ),
				$instance['debug_options']
			);

			// Debugging URLs.
			pinboard_bookmarks_form_checkbox(
				esc_html__( 'Display URLs', 'pinboard-bookmarks' ),
				$this->get_field_id( 'debug_urls' ),
				$this->get_field_name( 'debug_urls' ),
				$instance['debug_urls']
			);
			?>

		</div>
		<?php
	}
}
