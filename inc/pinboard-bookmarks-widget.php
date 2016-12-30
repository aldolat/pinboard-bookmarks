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

	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;

		pinboard_bookmarks_fetch_feed( array(
			'nickname'         => $instance['nickname'],
            'tags_list'        => $instance['tags_list'],
			'quantity'         => $instance['quantity'],
			'random'           => $instance['random'],
			'display_desc'     => $instance['display_desc'],
			'truncate'         => $instance['truncate'],
			'display_date'     => $instance['display_date'],
			'date_text'        => $instance['date_text'],
			'display_tags'     => $instance['display_tags'],
			'tags_text'        => $instance['tags_text'],
			'display_hashtag'  => $instance['display_hashtag'],
			'display_arrow'    => $instance['display_arrow'],
			'display_archive'  => $instance['display_archive'],
			'archive_text'     => $instance['archive_text'],
			'display_arch_arr' => $instance['display_arch_arr'],
			'new_tab'          => $instance['new_tab'],
			'nofollow'         => $instance['nofollow'],
		) );

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']             = strip_tags( $new_instance['title'] );
        $instance['tags_list']         = strip_tags( $new_instance['tags_list'] );
            $instance['tags_list'] = str_replace( array( ', ', ' ,' ), ' ', $instance['tags_list'] );
            $instance['tags_list'] = preg_replace( '!\s+!', ' ', $instance['tags_list'] );
            $instance['tags_list'] = trim( $instance['tags_list'] );
		$instance['nickname']          = strip_tags( $new_instance['nickname'] );
		$instance['quantity']          = absint( strip_tags( $new_instance['quantity'] ) );
			if ( '' == $instance['quantity'] || ! is_numeric( $instance['quantity'] ) ) $instance['quantity'] = 5;
			if ( 400 < $instance['quantity'] ) $instance['quantity'] = 400;
		$instance['random']            = $new_instance['random'];
		$instance['display_date']      = $new_instance['display_date'];
		$instance['date_text']         = strip_tags( $new_instance['date_text'] );
		$instance['display_desc']      = $new_instance['display_desc'];
		$instance['truncate']          = absint( strip_tags( $new_instance['truncate'] ) );
			if ( '' == $instance['truncate'] || ! is_numeric( $instance['truncate'] ) ) $instance['truncate'] = 0;
		$instance['display_tags']      = $new_instance['display_tags'];
		$instance['tags_text']         = strip_tags( $new_instance['tags_text'] );
		$instance['display_hashtag']   = $new_instance['display_hashtag'];
		$instance['display_arrow']     = $new_instance['display_arrow'];
		$instance['time']              = absint( strip_tags( $new_instance['time'] ) );
			if ( '' == $instance['time'] || ! is_numeric( $instance['time'] ) ) $instance['time'] = 3600;
			if ( 3600 < $instance['time'] ) $instance['time'] = 3600;
		$instance['display_archive']   = $new_instance['display_archive'];
		$instance['archive_text']      = strip_tags( $new_instance['archive_text'] );
		$instance['display_arch_arr']  = $new_instance['display_arch_arr'];
		$instance['new_tab']           = $new_instance['new_tab'];
		$instance['nofollow']          = $new_instance['nofollow'];

		return $instance;
	}

	public function form($instance) {
		$defaults = array(
			'title'            => esc_html__( 'My Bookmarks', 'pinboard-bookmarks' ),
			'nickname'         => '',
            'tags_list'        => '',
			'quantity'         => 5,
			'random'           => false,
			'display_desc'     => false,
			'truncate'         => 0,
			'display_date'     => false,
			'date_text'        => esc_html__( 'Stored on:', 'pinboard-bookmarks' ),
			'display_tags'     => false,
			'tags_text'        => esc_html__( 'Tags:', 'pinboard-bookmarks' ),
			'display_hashtag'  => true,
			'display_arrow'    => false,
			'time'             => 3600,
			'display_archive'  => true,
			'archive_text'     => esc_html__( 'More bookmarks', 'pinboard-bookmarks' ),
			'display_arch_arr' => true,
			'new_tab'          => false,
			'nofollow'         => true
		);
		$instance         = wp_parse_args( (array) $instance, $defaults );
		$random           = (bool) $instance['random'];
		$display_desc     = (bool) $instance['display_desc'];
		$display_date     = (bool) $instance['display_date'];
		$display_tags     = (bool) $instance['display_tags'];
		$display_hashtag  = (bool) $instance['display_hashtag'];
		$display_arrow    = (bool) $instance['display_arrow'];
		$display_archive  = (bool) $instance['display_archive'];
		$display_arch_arr = (bool) $instance['display_arch_arr'];
		$new_tab          = (bool) $instance['new_tab'];
		$nofollow         = (bool) $instance['nofollow'];
		?>
		<p>
			<?php _e( 'This widget allows you to publish a list of Pinboard bookmarks in your website. This widget can retrieve those bookmarks and publish them in your sidebar.', 'pinboard-bookmarks' ); ?>
		</p>

        <?php // Title
        pinboard_bookmarks_form_input_text(
            esc_html__( 'Title:', 'pinboard-bookmarks' ),
            $this->get_field_id( 'title' ),
            $this->get_field_name( 'title' ),
            esc_attr( $instance['title'] ),
            esc_html__( 'My bookmarks on Pinboard', 'pinboard-bookmarks' )
        );

        pinboard_bookmarks_form_input_text(
            esc_html__( 'Enter your nickname on Pinboard:', 'pinboard-bookmarks' ),
            $this->get_field_id( 'nickname' ),
            $this->get_field_name( 'nickname' ),
            esc_attr( $instance['nickname'] ),
            esc_html__( 'nickname', 'pinboard-bookmarks' )
        );

        pinboard_bookmarks_form_input_text(
            esc_html__( 'Enter tags (optional):', 'pinboard-bookmarks' ),
            $this->get_field_id( 'tags_list' ),
            $this->get_field_name( 'tags_list' ),
            esc_attr( $instance['tags_list'] ),
            esc_html__( 'books reading comics', 'pinboard-bookmarks' ),
            esc_html__( 'This is optional. Enter a space or comma separated list of tags. The plugin will fetch bookmarks from this list of tags.', 'pinboard-bookmarks' )
        );

        ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'quantity' ); ?>">
				<?php _e( 'Maximum number of items (maximum 400 items):', 'pinboard-bookmarks' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'quantity' ); ?>" name="<?php echo $this->get_field_name( 'quantity' ); ?>" type="text" value="<?php echo esc_attr( $instance['quantity'] ); ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $random ); ?> value="1" id="<?php echo $this->get_field_id( 'random' ); ?>" name="<?php echo $this->get_field_name( 'random' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'random' ); ?>">
				<?php printf( esc_html__( 'Display items in random order', 'pinboard-bookmarks' ), '<code>random</code>' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'time' ); ?>">
				<?php _e( 'Minimum time between two fetchings (in seconds, minimum 3600):', 'pinboard-bookmarks' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'time' ); ?>" name="<?php echo $this->get_field_name( 'time' ); ?>" type="text" value="<?php echo esc_attr( $instance['time'] ); ?>" />
		</p>

		<hr />

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $nofollow ); ?> value="1" id="<?php echo $this->get_field_id( 'nofollow' ); ?>" name="<?php echo $this->get_field_name( 'nofollow' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'nofollow' ); ?>">
				<?php printf( esc_html__( 'Add %s to links', 'pinboard-bookmarks' ), '<code>nofollow</code>' ); ?>
			</label>
			<br />
			<em><?php _e( 'It will be added only to the link in titles, not in tag links too.', 'pinboard-bookmarks' ); ?></em>
		</p>

		<hr />

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $display_desc ); ?> value="1" id="<?php echo $this->get_field_id( 'display_desc' ); ?>" name="<?php echo $this->get_field_name( 'display_desc' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'display_desc' ); ?>">
				<?php _e( 'Display the bookmark description', 'pinboard-bookmarks' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'truncate' ); ?>">
				<?php _e( 'Lenght of the description (in words):', 'pinboard-bookmarks' ); ?>
				<br />
				<?php printf( esc_html__( '(%s means full text)', 'pinboard-bookmarks' ), '<code>0</code>' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'truncate' ); ?>" name="<?php echo $this->get_field_name( 'truncate' ); ?>" type="text" value="<?php echo esc_attr( $instance['truncate'] ); ?>" />
		</p>

		<hr />

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $display_date ); ?> value="1" id="<?php echo $this->get_field_id( 'display_date' ); ?>" name="<?php echo $this->get_field_name( 'display_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'display_date' ); ?>">
				<?php _e( 'Display the date of the bookmark', 'pinboard-bookmarks' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'date_text' ); ?>">
				<?php _e( 'Text before the date:', 'pinboard-bookmarks' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'date_text' ); ?>" name="<?php echo $this->get_field_name( 'date_text' ); ?>" type="text" value="<?php echo esc_attr( $instance['date_text'] ); ?>" />
			<br />
			<em><?php _e( 'A space will be added after the text.', 'pinboard-bookmarks' ); ?></em>
		</p>

		<hr />

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $display_tags ); ?> value="1" id="<?php echo $this->get_field_id( 'display_tags' ); ?>" name="<?php echo $this->get_field_name( 'display_tags' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'display_tags' ); ?>">
				<?php _e( 'Display tags', 'pinboard-bookmarks' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'tags_text' ); ?>">
				<?php _e( 'Text before tags list:', 'pinboard-bookmarks' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'tags_text' ); ?>" name="<?php echo $this->get_field_name( 'tags_text' ); ?>" type="text" value="<?php echo esc_attr( $instance['tags_text'] ); ?>" />
			<br />
			<em><?php _e( 'A space will be added after the text.', 'pinboard-bookmarks' ); ?></em>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $display_hashtag ); ?> value="1" id="<?php echo $this->get_field_id( 'display_hashtag' ); ?>" name="<?php echo $this->get_field_name( 'display_hashtag' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'display_hashtag' ); ?>">
				<?php printf( esc_html__( 'Display an hashtag %s before each tag', 'pinboard-bookmarks' ), '(<code>#</code>)' ); ?>
			</label>
		</p>

		<hr />

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $display_arrow ); ?> value="1" id="<?php echo $this->get_field_id( 'display_arrow' ); ?>" name="<?php echo $this->get_field_name( 'display_arrow' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'display_arrow' ); ?>">
				<?php _e( 'Display an arrow after each title', 'pinboard-bookmarks' ); ?>
			</label>
		</p>

		<hr />

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $display_archive ); ?> value="1" id="<?php echo $this->get_field_id( 'display_archive' ); ?>" name="<?php echo $this->get_field_name( 'display_archive' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'display_archive' ); ?>">
				<?php _e( 'Display the link to my bookmarks archive on Pinboard', 'pinboard-bookmarks' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'archive_text' ); ?>">
				<?php _e( 'Use this text for the archive link:', 'pinboard-bookmarks' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'archive_text' ); ?>" name="<?php echo $this->get_field_name( 'archive_text' ); ?>" type="text" value="<?php echo esc_attr( $instance['archive_text'] ); ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $display_arch_arr ); ?> value="1" id="<?php echo $this->get_field_id( 'display_arch_arr' ); ?>" name="<?php echo $this->get_field_name( 'display_arch_arr' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'display_arch_arr' ); ?>">
				<?php _e( 'Display an arrow after the link to the archive', 'pinboard-bookmarks' ); ?>
			</label>
		</p>

		<hr />

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $new_tab ); ?> value="1" id="<?php echo $this->get_field_id( 'new_tab' ); ?>" name="<?php echo $this->get_field_name( 'new_tab' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'new_tab' ); ?>">
				<?php _e( 'Open links in a new browser tab', 'pinboard-bookmarks' ); ?>
			</label>
		</p>
		<?php
	}
}
