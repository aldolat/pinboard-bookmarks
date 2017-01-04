<?php
/**
 * The widget
 *
 * @package PinboardBookmarks
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
		$widget_ops = array(
			'classname'   => 'pinboard_bookmarks_widget',
			'description' => esc_html__( 'Publish a bookmarks list using your Pinboard bookmarks', 'pinboard-bookmarks' )
		);
		$control_ops = array(
			'width'   => 350,
			'id_base' => 'pinboard_bookmarks_widget'
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
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

        echo "\n" . '<!-- Start Pinboard Bookmarks - Widget ID: ' . $widget_id . ' -->' . "\n";

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

		pinboard_bookmarks_fetch_feed( array(
			'username'         => $instance['username'],
            'tags'             => $instance['tags'],
			'quantity'         => $instance['quantity'],
			'random'           => $instance['random'],
			'display_desc'     => $instance['display_desc'],
			'truncate'         => $instance['truncate'],
			'display_date'     => $instance['display_date'],
			'date_text'        => $instance['date_text'],
			'display_tags'     => $instance['display_tags'],
			'tags_text'        => $instance['tags_text'],
			'display_hashtag'  => $instance['display_hashtag'],
            'use_comma'        => $instance['use_comma'],
			'display_arrow'    => $instance['display_arrow'],
			'display_archive'  => $instance['display_archive'],
			'archive_text'     => $instance['archive_text'],
			'display_arch_arr' => $instance['display_arch_arr'],
			'new_tab'          => $instance['new_tab'],
			'nofollow'         => $instance['nofollow'],
            'debug_options'    => $instance['debug_options'],
            'debug_urls'       => $instance['debug_urls']
		) );

		echo $after_widget;

        echo "\n" . '<!-- End Pinboard Bookmarks - Widget ID: ' . $widget_id . ' -->' . "\n\n";
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
		$instance['title'] = strip_tags( $new_instance['title'] );
        $instance['username'] = strip_tags( $new_instance['username'] );
        $instance['tags'] = strip_tags( $new_instance['tags'] );
            $instance['tags'] = trim( preg_replace( '([\s,]+)', ' ', $instance['tags'] ) );
            $tags = explode( ' ', $instance['tags'] );
            if ( 3 < count( $tags ) ) {
                $tags = array_slice( $tags, 0, 3 );
                $instance['tags'] = implode( ' ', $tags );
            }
		$instance['quantity'] = absint( strip_tags( $new_instance['quantity'] ) );
			if ( '' == $instance['quantity'] || ! is_numeric( $instance['quantity'] ) ) $instance['quantity'] = 5;
			if ( 400 < $instance['quantity'] ) $instance['quantity'] = 400;
        $instance['random'] = isset( $new_instance['random'] ) ? $new_instance['random'] : false;
        $instance['display_date'] = isset( $new_instance['display_date'] ) ? $new_instance['display_date'] : false;
		$instance['date_text'] = trim( strip_tags( $new_instance['date_text'] ) );
        $instance['display_desc'] = isset( $new_instance['display_desc'] ) ? $new_instance['display_desc'] : false;
		$instance['truncate'] = absint( strip_tags( $new_instance['truncate'] ) );
			if ( '' == $instance['truncate'] || ! is_numeric( $instance['truncate'] ) ) $instance['truncate'] = 0;
        $instance['display_tags'] = isset( $new_instance['display_tags'] ) ? $new_instance['display_tags'] : false;
		$instance['tags_text'] = strip_tags( $new_instance['tags_text'] );
		$instance['display_hashtag'] = $new_instance['display_hashtag'];
        $instance['use_comma'] = isset( $new_instance['use_comma'] ) ? $new_instance['use_comma'] : false;
        $instance['display_arrow'] = isset( $new_instance['display_arrow'] ) ? $new_instance['display_arrow'] : false;
		$instance['time'] = absint( strip_tags( $new_instance['time'] ) );
			if ( '' == $instance['time'] || ! is_numeric( $instance['time'] ) ) $instance['time'] = 1800;
			if ( 1800 > $instance['time'] ) $instance['time'] = 1800;
		$instance['display_archive'] = $new_instance['display_archive'];
		$instance['archive_text'] = strip_tags( $new_instance['archive_text'] );
		$instance['display_arch_arr'] = $new_instance['display_arch_arr'];
        $instance['new_tab'] = isset( $new_instance['new_tab'] ) ? $new_instance['new_tab'] : false;
		$instance['nofollow'] = $new_instance['nofollow'];
        $instance['debug_options'] = isset( $new_instance['debug_options'] ) ? $new_instance['debug_options'] : false;
        $instance['debug_urls'] = isset( $new_instance['debug_urls'] ) ? $new_instance['debug_urls'] : false;

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
		$defaults = array(
			'title'            => esc_html__( 'My bookmarks on Pinboard', 'pinboard-bookmarks' ),
			'username'         => '',
            'tags'             => '',
			'quantity'         => 5,
			'random'           => false,
			'display_desc'     => false,
			'truncate'         => 0,
			'display_date'     => false,
			'date_text'        => esc_html__( 'Stored on:', 'pinboard-bookmarks' ),
			'display_tags'     => false,
			'tags_text'        => esc_html__( 'Tags:', 'pinboard-bookmarks' ),
			'display_hashtag'  => true,
            'use_comma'        => false,
			'display_arrow'    => false,
			'time'             => 1800,
			'display_archive'  => true,
			'archive_text'     => esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' ),
			'display_arch_arr' => true,
			'new_tab'          => false,
			'nofollow'         => true,
            'debug_options'    => false,
            'debug_urls'       => false,
		);
		$instance         = wp_parse_args( (array) $instance, $defaults );
		$random           = (bool) $instance['random'];
		$display_desc     = (bool) $instance['display_desc'];
		$display_date     = (bool) $instance['display_date'];
		$display_tags     = (bool) $instance['display_tags'];
		$display_hashtag  = (bool) $instance['display_hashtag'];
        $use_comma        = (bool) $instance['use_comma'];
		$display_arrow    = (bool) $instance['display_arrow'];
		$display_archive  = (bool) $instance['display_archive'];
		$display_arch_arr = (bool) $instance['display_arch_arr'];
		$new_tab          = (bool) $instance['new_tab'];
		$nofollow         = (bool) $instance['nofollow'];
        $debug_options    = (bool) $instance['debug_options'];
        $debug_urls       = (bool) $instance['debug_urls'];
		?>

        <div class="pinboard-bookmarks-widget-content">

            <h4 class="no-border"><?php esc_html_e( 'Informations', 'pinboard-bookmarks' ); ?></h4>

    		<p>
    			<?php esc_html_e( 'This widget allows you to publish a list of Pinboard bookmarks in your sidebar. Simply enter a username on Pinboard and/or one or more tags. Then click on Save button.', 'pinboard-bookmarks' ); ?>
    		</p>

            <p>
                <?php esc_html_e( 'Please note that a username or one tag is required, at least.', 'pinboard-bookmarks' ); ?>
            </p>

            <h4><?php esc_html_e( 'Title of the widget', 'pinboard-bookmarks' ); ?></h4>

            <?php
            // Title
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Title:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'title' ),
                $this->get_field_name( 'title' ),
                esc_attr( $instance['title'] ),
                esc_html__( 'My bookmarks on Pinboard', 'pinboard-bookmarks' )
            ); ?>

            <h4><?php esc_html_e( 'Basic Setup', 'pinboard-bookmarks' ); ?></h4>

            <?php // Username
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Username on Pinboard:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'username' ),
                $this->get_field_name( 'username' ),
                esc_attr( $instance['username'] ),
                esc_html__( 'username', 'pinboard-bookmarks' )
            );

            // Tags
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Tags:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'tags' ),
                $this->get_field_name( 'tags' ),
                esc_attr( $instance['tags'] ),
                esc_html__( 'books reading comics', 'pinboard-bookmarks' ),
                esc_html__( 'Enter a space separated list of tags, up to 3 tags. The plugin will fetch bookmarks from this list of tags.', 'pinboard-bookmarks' )
            );

            // Number of items
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Maximum number of items:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'quantity' ),
                $this->get_field_name( 'quantity' ),
                esc_attr( $instance['quantity'] ),
                '5',
                esc_html__( 'Maximum 400 items.', 'pinboard-bookmarks' )
            );

            // Random order
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Display items in random order', 'pinboard-bookmarks' ),
                $this->get_field_id( 'random' ),
                $this->get_field_name( 'random' ),
                checked( $random, true, false )
            );

            // Time
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Minimum time between two fetchings:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'time' ),
                $this->get_field_name( 'time' ),
                esc_attr( $instance['time'] ),
                '1800',
                esc_html__( 'In seconds. Minimum 1800 seconds (30 minutes).', 'pinboard-bookmarks' )
            ); ?>

            <h4><?php esc_html_e( 'Bookmark description', 'pinboard-bookmarks' ); ?></h4>

            <?php
            // Description
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Display the bookmark description', 'pinboard-bookmarks' ),
                $this->get_field_id( 'display_desc' ),
                $this->get_field_name( 'display_desc' ),
                checked( $display_desc, true, false )
            );

            // Description length
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Length of the description:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'truncate' ),
                $this->get_field_name( 'truncate' ),
                esc_attr( $instance['truncate'] ),
                '50',
                sprintf( esc_html__( '(In words. %s means full text)', 'pinboard-bookmarks' ), '<code>0</code>' )
            ); ?>

            <h4><?php esc_html_e( 'Date of the bookmark', 'pinboard-bookmarks' ); ?></h4>

            <?php
            // Date
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Display the date of the bookmark', 'pinboard-bookmarks' ),
                $this->get_field_id( 'display_date' ),
                $this->get_field_name( 'display_date' ),
                checked( $display_date, true, false )
            );

            // Text for the date
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Text before the date:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'date_text' ),
                $this->get_field_name( 'date_text' ),
                esc_attr( $instance['date_text'] ),
                esc_html__( 'Stored on', 'pinboard-bookmarks' ),
                esc_html__( 'A space will be added after the text.', 'pinboard-bookmarks' )
            ); ?>

            <h4><?php esc_html_e( 'Tags of the bookmark', 'pinboard-bookmarks' ); ?></h4>

            <?php
            // Tags
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Display tags', 'pinboard-bookmarks' ),
                $this->get_field_id( 'display_tags' ),
                $this->get_field_name( 'display_tags' ),
                checked( $display_tags, true, false )
            );

            // Text for tags
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Text before tags list:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'tags_text' ),
                $this->get_field_name( 'tags_text' ),
                esc_attr( $instance['tags_text'] ),
                esc_html__( 'Tags:', 'pinboard-bookmarks' ),
                esc_html__( 'A space will be added after the text.', 'pinboard-bookmarks' )
            );

            // Hashtag
            pinboard_bookmarks_form_checkbox(
                sprintf( esc_html__( 'Display an hashtag %s before each tag', 'pinboard-bookmarks' ), '(<code>#</code>)' ),
                $this->get_field_id( 'display_hashtag' ),
                $this->get_field_name( 'display_hashtag' ),
                checked( $display_hashtag, true, false )
            );

            // Comma
            pinboard_bookmarks_form_checkbox(
                sprintf( esc_html__( 'Use a comma %s after each tag', 'pinboard-bookmarks' ), '(<code>,</code>)' ),
                $this->get_field_id( 'use_comma' ),
                $this->get_field_name( 'use_comma' ),
                checked( $use_comma, true, false )
            ); ?>

            <h4><?php esc_html_e( 'Link to the archive', 'pinboard-bookmarks' ); ?></h4>

            <?php
            // Archive
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Display the link to my bookmarks archive on Pinboard', 'pinboard-bookmarks' ),
                $this->get_field_id( 'display_archive' ),
                $this->get_field_name( 'display_archive' ),
                checked( $display_archive, true, false )
            );

            // Text for archive
            pinboard_bookmarks_form_input_text(
                esc_html__( 'Text for the archive link:', 'pinboard-bookmarks' ),
                $this->get_field_id( 'archive_text' ),
                $this->get_field_name( 'archive_text' ),
                esc_attr( $instance['archive_text'] ),
                esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' )
            );

            // Archive arrow
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Display an arrow after the link to the archive', 'pinboard-bookmarks' ),
                $this->get_field_id( 'display_arch_arr' ),
                $this->get_field_name( 'display_arch_arr' ),
                checked( $display_arch_arr, true, false )
            ); ?>

            <h4><?php esc_html_e( 'Other options', 'pinboard-bookmarks' ); ?></h4>

            <?php
            // Arrow
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Display an arrow after each title', 'pinboard-bookmarks' ),
                $this->get_field_id( 'display_arrow' ),
                $this->get_field_name( 'display_arrow' ),
                checked( $display_arrow, true, false )
            );

            // Open links in new tab
            pinboard_bookmarks_form_checkbox(
                esc_html__( 'Open links in a new browser tab', 'pinboard-bookmarks' ),
                $this->get_field_id( 'new_tab' ),
                $this->get_field_name( 'new_tab' ),
                checked( $new_tab, true, false )
            );

            // No follow
            pinboard_bookmarks_form_checkbox(
                sprintf( esc_html__( 'Add %s to links', 'pinboard-bookmarks' ), '<code>nofollow</code>' ),
                $this->get_field_id( 'nofollow' ),
                $this->get_field_name( 'nofollow' ),
                checked( $nofollow, true, false ),
                __( 'It will be added only to the link in titles, not in tag links too.', 'pinboard-bookmarks' )
            ); ?>

            <h4><?php esc_html_e( 'Debug options', 'pinboard-bookmarks' ); ?></h4>

            <p><?php printf( __( 'You are using Pinboard Bookmarks version %s.', 'pinboard-bookmarks' ), '<strong>' . PINBOARD_BOOKMARKS_PLUGIN_VERSION . '</strong>' ); ?></p>

            <p class="pinboard-bookmarks-alert"><strong><?php _e( 'Use this options for debugging purposes only. Only the Administrator can view the debugging informations.', 'pinboard-bookmarks' ); ?></strong></p>

            <?php // Debugging options
            pinboard_bookmarks_form_checkbox(
                sprintf( esc_html__( 'Display parameters', 'pinboard-bookmarks' ), '<code>nofollow</code>' ),
                $this->get_field_id( 'debug_options' ),
                $this->get_field_name( 'debug_options' ),
                checked( $debug_options, true, false )
            );

            // Debugging URLs
            pinboard_bookmarks_form_checkbox(
                sprintf( esc_html__( 'Display URLs', 'pinboard-bookmarks' ), '<code>nofollow</code>' ),
                $this->get_field_id( 'debug_urls' ),
                $this->get_field_name( 'debug_urls' ),
                checked( $debug_urls, true, false )
            ); ?>

        </div>
	<?php }
}
