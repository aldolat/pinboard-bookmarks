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
			'width'   => 600,
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

		echo $args['before_widget'] . "\n";

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			echo $args['after_title'] . "\n";
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

		/*
		 * Check for entered value.
		 *
		 * @since 1.0
		 * @since 1.10.0 Added control if quantity is 0.
		 */
		if ( 0 === $instance['quantity'] || '' === $instance['quantity'] || ! is_numeric( $instance['quantity'] ) ) {
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
		$instance['date_text']       = sanitize_text_field( $new_instance['date_text'] );
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

		$instance['display_site_url'] = isset( $new_instance['display_site_url'] ) ? true : false;
		$instance['leave_domain']     = isset( $new_instance['leave_domain'] ) ? true : false;
		$instance['site_url_text']    = sanitize_text_field( $new_instance['site_url_text'] );
		$instance['display_archive']  = isset( $new_instance['display_archive'] ) ? true : false;
		$instance['archive_text']     = sanitize_text_field( $new_instance['archive_text'] );
		$instance['list_type']        = sanitize_text_field( $new_instance['list_type'] );
		$instance['display_arch_arr'] = isset( $new_instance['display_arch_arr'] ) ? true : false;
		$instance['new_tab']          = isset( $new_instance['new_tab'] ) ? true : false;
		$instance['nofollow']         = isset( $new_instance['nofollow'] ) ? true : false;
		$instance['noreferrer']       = isset( $new_instance['noreferrer'] ) ? true : false;
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
		?>

		<div class="pinboard-bookmarks-widget-content">

			<!-- Basic setup -->
			<div class="pinboard-bookmarks-section">

				<p><em>
					<?php
					esc_html_e(
						'This widget allows you to publish a list of Pinboard bookmarks in your sidebar. Simply enter a username on Pinboard. Then click on Save button.',
						'pinboard-bookmarks'
					);
					?>
				</em></p>

				<p><em>
					<?php
					esc_html_e(
						'Note that a username is required, at least.',
						'pinboard-bookmarks'
					);
					?>
				</em></p>

				<h4>
					<?php esc_html_e( 'Basic setup', 'pinboard-bookmarks' ); ?>
				</h4>

				<div class="pinboard-bookmarks-column-container pinboard-bookmarks-2col">

					<div class="pinboard-bookmarks-column">
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
						?>
					</div>

					<div class="pinboard-bookmarks-column">
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
					</div>

				</div>

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
					'resize: vertical; height: 80px; min-height: 80px;'
				);
				?>

			</div>

			<!-- Getting bookmarks -->
			<div class="pinboard-bookmarks-section">

				<h4 data-panel="pinboard-bookmarks-getting" class="pinboard-bookmarks-widget-title">
					<?php esc_html_e( 'Getting bookmarks', 'pinboard-bookmarks' ); ?>
				</h4>

				<div class="pinboard-bookmarks-container">

					<p><em>
						<?php
						esc_html_e(
							'Define here some aspects regarding the bookmarks retrieval.',
							'pinboard-bookmarks'
						);
						?>
					</em></p>

					<div class="pinboard-bookmarks-section">

						<div class="pinboard-bookmarks-column-container pinboard-bookmarks-2col">

							<div class="pinboard-bookmarks-column">

								<h5><?php esc_html_e( 'Tags', 'pinboard-bookmarks' ); ?></h5>

								<?php
								// Tags.
								pinboard_bookmarks_form_input_text(
									esc_html__( 'Tags:', 'pinboard-bookmarks' ),
									$this->get_field_id( 'tags' ),
									$this->get_field_name( 'tags' ),
									esc_attr( $instance['tags'] ),
									esc_html__( 'books reading comics', 'pinboard-bookmarks' ),
									esc_html__( 'Enter a space separated list of tags, up to 4 tags. The plugin will fetch bookmarks from this list of tags.', 'pinboard-bookmarks' )
								);
								?>

								<h5><?php esc_html_e( 'Source', 'pinboard-bookmarks' ); ?></h5>

								<?php
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
									'pinboard'   => array(
										'value' => 'pinboard',
										'desc'  => esc_html__( 'Pinboard', 'pinboard-bookmarks' ),
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
									// translators: The name of a bookmarking service like Instapaper.
									sprintf( esc_html__( 'Select the source of the bookmarks, like %s. Since Pinboard accepts tags or a source, the tags from the field above will be ignored if you activate this option.', 'pinboard-bookmarks' ), '<code>from:pocket</code>' )
								);
								?>
							</div>

							<div class="pinboard-bookmarks-column">

								<h5><?php esc_html_e( 'Quantity of bookmarks', 'pinboard-bookmarks' ); ?></h5>

								<?php
								// Number of items.
								pinboard_bookmarks_form_input_text(
									esc_html__( 'Get this number of bookmarks:', 'pinboard-bookmarks' ),
									$this->get_field_id( 'quantity' ),
									$this->get_field_name( 'quantity' ),
									esc_attr( $instance['quantity'] ),
									'5',
									esc_html__( 'Maximum 400 items.', 'pinboard-bookmarks' )
								);
								?>

								<h5><?php esc_html_e( 'Random order', 'pinboard-bookmarks' ); ?></h5>

								<?php
								// Random order.
								pinboard_bookmarks_form_checkbox(
									esc_html__( 'Display items in random order', 'pinboard-bookmarks' ),
									$this->get_field_id( 'random' ),
									$this->get_field_name( 'random' ),
									$instance['random']
								);
								?>

								<h5><?php esc_html_e( 'Cache', 'pinboard-bookmarks' ); ?></h5>

								<?php
								// Time between two requests.
								pinboard_bookmarks_form_input_text(
									esc_html__( 'Cache duration:', 'pinboard-bookmarks' ),
									$this->get_field_id( 'time' ),
									$this->get_field_name( 'time' ),
									esc_attr( $instance['time'] ),
									'1800',
									esc_html__( 'This is the minimum time, in seconds, between two requests to Pinboard server. Minimum 1800 seconds (30 minutes).', 'pinboard-bookmarks' )
								);
								?>
							</div>

						</div>

					</div>

				</div>

			</div>

			<!-- Displaying bookmarks -->
			<div class="pinboard-bookmarks-section">

				<h4 data-panel="pinboard-bookmarks-displaying" class="pinboard-bookmarks-widget-title">
					<?php esc_html_e( 'Displaying bookmarks', 'pinboard-bookmarks' ); ?>
				</h4>

				<div class="pinboard-bookmarks-container">

					<p><em>
						<?php
						esc_html_e(
							'Define here which elements you want to display in the widget.',
							'pinboard-bookmarks'
						);
						?>
					</em></p>

					<div class="pinboard-bookmarks-section">

						<div class="pinboard-bookmarks-column-container pinboard-bookmarks-2col">

							<div class="pinboard-bookmarks-column">
								<h5><?php esc_html_e( 'Bookmarks description', 'pinboard-bookmarks' ); ?></h5>

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
									// translators: The number of words for the bookmark description.
									sprintf( esc_html__( '(In words. %s means full text)', 'pinboard-bookmarks' ), '<code>0</code>' )
								);
								?>

								<h5><?php esc_html_e( 'Date of the bookmark', 'pinboard-bookmarks' ); ?></h5>

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

								<h5><?php esc_html_e( 'Tags and source of the bookmark', 'pinboard-bookmarks' ); ?></h5>

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
									// translators: Placeholder is an hashtag.
									sprintf( esc_html__( 'Display an hashtag %s before each tag', 'pinboard-bookmarks' ), '(<code>#</code>)' ),
									$this->get_field_id( 'display_hashtag' ),
									$this->get_field_name( 'display_hashtag' ),
									$instance['display_hashtag']
								);

								// Comma.
								pinboard_bookmarks_form_checkbox(
									// translators: Placeholder is a comma.
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
							</div>

							<div class="pinboard-bookmarks-column">
								<h5><?php esc_html_e( 'Display site URL', 'pinboard-bookmarks' ); ?></h5>

								<?php
								// Name of the original site.
								pinboard_bookmarks_form_checkbox(
									esc_html__( 'Display the base URL of the original site', 'pinboard-bookmarks' ),
									$this->get_field_id( 'display_site_url' ),
									$this->get_field_name( 'display_site_url' ),
									$instance['display_site_url'],
									sprintf(
										// translators: Placeholders are two URL examples.
										esc_html__(
											'Remove the path from the URL. For example, if the URL of the article is %1$s, the base URL %2$s will be displayed.',
											'pinboard-bookmarks'
										),
										'<code>https://www.example.com/path/to/news</code>',
										'<code>https://www.example.com</code>'
									)
								);

								// Leave only the domain.
								pinboard_bookmarks_form_checkbox(
									esc_html__( 'Leave the domain only', 'pinboard-bookmarks' ),
									$this->get_field_id( 'leave_domain' ),
									$this->get_field_name( 'leave_domain' ),
									$instance['leave_domain'],
									sprintf(
										// translators: Placeholder is http(s)://www.
										esc_html__(
											'Remove the %s part.',
											'pinboard-bookmarks'
										),
										'<code>http(s)://www.</code>'
									)
								);

								// Text for original site.
								pinboard_bookmarks_form_input_text(
									esc_html__( 'Text before the base URL of the original site:', 'pinboard-bookmarks' ),
									$this->get_field_id( 'site_url_text' ),
									$this->get_field_name( 'site_url_text' ),
									esc_attr( $instance['site_url_text'] ),
									esc_html__( 'From:', 'pinboard-bookmarks' ),
									esc_html__(
										'Put this text before the base URL of the original site. A space will be added after the text.',
										'pinboard-bookmarks'
									)
								);
								?>

								<h5><?php esc_html_e( 'Link to the archive', 'pinboard-bookmarks' ); ?></h5>

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
							</div>

						</div>

						<div class="pinboard-bookmarks-column-container pinboard-bookmarks-2col">

							<h5><?php esc_html_e( 'Links relationship', 'pinboard-bookmarks' ); ?></h5>

							<div class="pinboard-bookmarks-column">
								<?php
								// Open links in new tab.
								pinboard_bookmarks_form_checkbox(
									esc_html__( 'Open links in a new browser tab', 'pinboard-bookmarks' ),
									$this->get_field_id( 'new_tab' ),
									$this->get_field_name( 'new_tab' ),
									$instance['new_tab'],
									sprintf(
										// translators: The noopener rel attribute.
										esc_html__( 'If activated, the rel attribute %s will be added.', 'pinboard-bookmarks' ),
										'<code>noopener</code>'
									)
								);

								// No follow.
								pinboard_bookmarks_form_checkbox(
									// translators: The rel attribute for links.
									sprintf( esc_html__( 'Add %s to links', 'pinboard-bookmarks' ), '<code>nofollow</code>' ),
									$this->get_field_id( 'nofollow' ),
									$this->get_field_name( 'nofollow' ),
									$instance['nofollow'],
									esc_html__( 'It will be added to all external links.', 'pinboard-bookmarks' )
								);
								?>
							</div>

							<div class="pinboard-bookmarks-column">
								<?php
								// No referrer.
								pinboard_bookmarks_form_checkbox(
									// translators: The rel attribute for links.
									sprintf( esc_html__( 'Add %s to links', 'pinboard-bookmarks' ), '<code>noreferrer</code>' ),
									$this->get_field_id( 'noreferrer' ),
									$this->get_field_name( 'noreferrer' ),
									$instance['noreferrer'],
									esc_html__( 'It will be added to all external links.', 'pinboard-bookmarks' )
								);

								printf(
									// translators: The placeholder is a link.
									esc_html__( 'For more information about links attributes, please visit %s.', 'pinboard-bookmarks' ),
									'<a rel="external noopener noreferrer nofollow" href="https://www.w3schools.com/tags/att_a_rel.asp" target="_blank">w3schools.com</a>'
								);
								?>
							</div>

						</div>

					</div>

				</div>

			</div>

			<!-- Styling -->
			<div class="pinboard-bookmarks-section">

				<h4 data-panel="pinboard-bookmarks-styling" class="pinboard-bookmarks-widget-title">
					<?php esc_html_e( 'Styling', 'pinboard-bookmarks' ); ?>
				</h4>

				<div class="pinboard-bookmarks-container">

					<p><em>
						<?php
						esc_html_e(
							'Define here some aspects of the style of the widget.',
							'pinboard-bookmarks'
						);
						?>
					</em></p>

					<div class="pinboard-bookmarks-section">

						<div class="pinboard-bookmarks-column-container pinboard-bookmarks-2col">

							<div class="pinboard-bookmarks-column">
								<h5><?php esc_html_e( 'Type of list', 'pinboard-bookmarks' ); ?></h4>

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
								?>
							</div>

							<div class="pinboard-bookmarks-column">
								<h5><?php esc_html_e( 'Displaying order', 'pinboard-bookmarks' ); ?></h5>

								<p><?php esc_html_e( 'Define the order in which the elements of each item will be displayed. The available elements are:', 'pinboard-bookmarks' ); ?></p>

								<p>
									<code>title</code>
									<code>site</code>
									<code>description</code>
									<code>date</code>
									<code>tags</code>
								</p>

								<?php
								// Displaying order.
								pinboard_bookmarks_form_input_text(
									esc_html__( 'Order of the elements of each item:', 'pinboard-bookmarks' ),
									$this->get_field_id( 'items_order' ),
									$this->get_field_name( 'items_order' ),
									esc_attr( $instance['items_order'] ),
									'title site description date tags', // String NOT to be translated.
									esc_html__( 'Enter a space separated list of elements.', 'pinboard-bookmarks' )
								);
								?>
							</div>

						</div>

					</div>

				</div>

			</div>

			<!-- Debugging -->
			<div class="pinboard-bookmarks-section">

				<h4 data-panel="pinboard-bookmarks-debug" class="pinboard-bookmarks-widget-title">
					<?php esc_html_e( 'Debugging', 'pinboard-bookmarks' ); ?>
				</h4>

				<div class="pinboard-bookmarks-container">

					<p>
						<?php
						printf(
							// translators: The version of the plugin.
							esc_html__( 'You are using Pinboard Bookmarks version %s', 'pinboard-bookmarks' ),
							'<strong>' . esc_attr( PINBOARD_BOOKMARKS_PLUGIN_VERSION ) . '</strong>'
						);
						?>
					</p>

					<p>
						<?php
						printf(
							// translators: The ID of the widget.
							esc_html__( 'The ID of this widget is: %s' ),
							'<strong>' . $instance['widget_id'] . '</strong>'
						);
						?>
					</p>

					<p class="pinboard-bookmarks-boxed pinboard-bookmarks-boxed-orange">
						<strong>
							<?php esc_html_e( 'Use these options for debugging purposes only.', 'pinboard-bookmarks' ); ?>
						</strong>
					</p>

					<div class="pinboard-bookmarks-boxed pinboard-bookmarks-boxed-red">

						<strong>
							<?php
							esc_html_e(
								'Deactivate the following option only if you want to display debugging information publicly on your site.',
								'pinboard-bookmarks'
							);
							?>
						</strong>
						<?php
						// Admins only.
						pinboard_bookmarks_form_checkbox(
							esc_html__( 'Display debugging information to admins only', 'pinboard-bookmarks' ),
							$this->get_field_id( 'admin_only' ),
							$this->get_field_name( 'admin_only' ),
							$instance['admin_only']
						);
						?>

					</div>

					<?php
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

			</div>

		</div>
		<?php
	}
}
