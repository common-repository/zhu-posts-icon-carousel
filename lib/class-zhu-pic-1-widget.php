<?php
/**
 * Post Icon Carousel WordPress Widget Class
 *  
 * @package   ZHU-PIC\Class
 * 
 * @wordpress-plugin
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Location: /' );
	exit;
}

// Default values.
define( 'ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_SIZE', 'custom' );
define( 'ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_HEIGHT', 64 );
define( 'ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_WIDTH', 64 );

define( 'ZHU_PIC_1_DEFAULT_SHOW_PREVIEW', 'on' );
define( 'ZHU_PIC_1_DEFAULT_SHOW_PREVIEW_IMAGE', 'on' );
define( 'ZHU_PIC_1_DEFAULT_SHOW_PREVIEW_TITLE', 'on' );
define( 'ZHU_PIC_1_DEFAULT_SHOW_PREVIEW_DATE', '' );
define( 'ZHU_PIC_1_DEFAULT_DEMO_MODE', '' );
define( 'ZHU_PIC_1_DEFAULT_PREVIEW_IMG_SIZE', 'custom' );
define( 'ZHU_PIC_1_DEFAULT_PREVIEW_IMG_HEIGHT', 64 );
define( 'ZHU_PIC_1_DEFAULT_PREVIEW_IMG_WIDTH', 64 );

define( 'ZHU_PIC_1_DEFAULT_NUM_POSTS', 5 );
define( 'ZHU_PIC_1_DEFAULT_HEIGHT', 300 );
define( 'ZHU_PIC_1_DEFAULT_SCROLL_DURATION', 1000 );
define( 'ZHU_PIC_1_DEFAULT_PAUSE_DURATION', 4000 );
define( 'ZHU_PIC_1_DEFAULT_EXCERPT_LENGTH', 30 );

// Limits.
define( 'ZHU_PIC_1_MIN_CAROUSEL_IMG_HEIGHT', 16 );
define( 'ZHU_PIC_1_MIN_CAROUSEL_IMG_WIDTH', 16 );

// Misc.
define( 'ZHU_PIC_1_NUM_DEMO_IMAGES', 15 );

/**
 * Zhu Post Icon Carousel Widget
 */
class Zhu_Pic_1_Widget extends WP_Widget {

	/**
	 * Used to preserve instance data for use in excerpt_length filter
	 * 
	 * @var string
	 */
	private $current_instance_data = null;

	/**
	 * Create instance of this class.  Passes widget options to base class's constructor.
	 */
	public function __construct() {
		$widget_options = array(
			'classname'						 => 'zhu-pic-1-widget',
			'description'					 => esc_html__( 'Rotate carousel of icons of posts with optional preview below', 'zhu_pic_domain' ),
			'customize_selective_refresh'	 => false, /* poss future improvement */
		);

		parent::__construct( 'zhu_pic_1', esc_html__( 'Zhu Post Icon Carousel', 'zhu_pic_domain' ), $widget_options );
	}

	/**
	 * Echoes the widget content.
	 *
	 * Overrides the base class's method
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$instance = $this->sanitize_with_defaults( $instance );

		$title	 = apply_filters( 'widget_title', $instance['title'] );
		$title	 = $args['before_title'] . esc_html( $title ) . $args['after_title'];

		// query posts to be displayed.
		// posts with passwords are not displayed.
		$max_posts	 = absint( $instance['num-posts'] );
		$query_args	 = array(
			'posts_per_page' => $max_posts,
			'has_password'	 => false,
		);
		$posts		 = new WP_Query( $query_args );

		$demo_mode = $instance['demo-mode'];

		// If no posts then no rendering.
		if ( $posts->post_count ) {

			try {

				// Add [temporary] filter so excerpt length is specific to this instance of this widget.
				add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
				$this->current_instance_data = $instance;

				if ( $demo_mode ) {
					$max_post_count = $max_posts;
				} else {
					// set $maxPostCount to lower of $posts->post_count or $maxPosts.
					$max_post_count = ( $posts->post_count > $max_posts ) ? $max_posts : $posts->post_count;
				}

				// Get images sizes.  When in demo mode always use custome size.
				if ( 'custom' === $instance['carousel-image-size'] || $demo_mode ) {
					$carousel_img_size			 = array( $instance['carousel-image-width'], $instance['carousel-image-height'] );
					$carousel_custom_style_size	 = 'width:' . esc_attr( $instance['carousel-image-width'] ) . 'px; height:' . esc_attr( $instance['carousel-image-height'] ) . 'px';
				} else {
					$carousel_img_size			 = $instance['carousel-image-size'];
					$carousel_custom_style_size	 = null;
				}

				if ( 'custom' === $instance['preview-image-size'] || $demo_mode ) {
					$preview_img_size			 = array( $instance['preview-image-width'], $instance['preview-image-height'] );
					$preview_custom_style_size	 = 'width:' . esc_attr( $instance['preview-image-width'] ) . 'px; height:' . esc_attr( $instance['preview-image-height'] ) . 'px';
				} else {
					$preview_img_size			 = $instance['preview-image-size'];
					$preview_custom_style_size	 = null;
				}

				$show_preview		 = $instance['show-preview'];
				$show_preview_image	 = $instance['show-preview-image'];
				$show_preview_title	 = $instance['show-preview-title'];
				$show_preview_date	 = $instance['show-preview-date'];
				$preview_posts_html	 = '';
				$carousel_html		 = '';
				$posts_used_count	 = 0;

				$opacity		 = 1;
				$opacity_step	 = 1.0 / $max_post_count;

				// Ensure that we only display $maxPosts, even if WP_Query returns more due to sticky posts also being included.
				// If in demo mode then output $maxPosts even if there are no posts.
				while ( ( $posts->have_posts() || $demo_mode ) && $posts_used_count < $max_posts ) {

					if ( $demo_mode ) {
						if ( 1 === $posts_used_count ) {
							$posts->the_post();
						}

						if ( isset( $GLOBALS['post'] ) ) {
							$post				 = $GLOBALS['post'];
							$post->post_title	 = $this->loremipsum( 5, 7 );
							$post->post_excerpt	 = $this->loremipsum( $instance['excerpt-length'], 0 );
						}

						$demo_image_number = wp_rand( 1, ZHU_PIC_1_NUM_DEMO_IMAGES );
					} else {
						$posts->the_post();
					}

					// posts must have a featured image.
					if ( has_post_thumbnail() || $demo_mode ) {
						$posts_used_count ++;

						$thumbnail_id = get_post_thumbnail_id();

						$post_url = esc_url( apply_filters( 'the_permalink', get_permalink() ) );

						// ensure that only the div containing the first post is displayed, and hide the others.
						$display = ( 1 === $posts_used_count ) ? 'inherit' : 'none';

						get_post_thumbnail_id( get_the_ID() );
						$alt_text		 = esc_attr( get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) );
						$post_title		 = get_the_title();
						$post_title_attr = esc_attr( $post_title );

						// add post to carousel.
						if ( ! $demo_mode ) {
							$carousel_srcset				 = wp_get_attachment_image_srcset( $thumbnail_id, $carousel_img_size );
							$carousel_sizes					 = wp_get_attachment_image_sizes( $thumbnail_id, $carousel_img_size );
							$carousel_thumbnail_attachment	 = wp_get_attachment_image_src( $thumbnail_id, $carousel_img_size );

							$carousel_attr_src		 = esc_attr( $carousel_thumbnail_attachment[0] );
							$carousel_attr_scrset	 = esc_attr( $carousel_srcset );
							$carousel_attr_sizes	 = esc_attr( $carousel_sizes );
						} else {
							// in demo mode use images from plugin's demo sub-directory.
							$carousel_attr_scrset	 = '';
							$carousel_attr_sizes	 = '';
							$carousel_attr_src		 = plugins_url( "zhu-posts-icon-carousel/demo/demo_{$demo_image_number}.jpg" );
						}

						$carousel_html .= "<a href='{$post_url}' style='opacity:{$opacity};padding-right:20px'><img style='{$carousel_custom_style_size};display:inline' src='{$carousel_attr_src}' alt='{$alt_text}' title='{$post_title_attr}' srcset='{$carousel_attr_scrset}' sizes='{$carousel_attr_sizes}'></a>";

						if ( $show_preview ) {
							// add post post preview.

							if ( $show_preview_image ) {

								if ( ! $demo_mode ) {
									$preview_srcset					 = wp_get_attachment_image_srcset( $thumbnail_id, $preview_img_size );
									$preview_sizes					 = wp_get_attachment_image_sizes( $thumbnail_id, $preview_img_size );
									$preview_thumbnail_attachment	 = wp_get_attachment_image_src( $thumbnail_id, $preview_img_size );

									$preview_attr_src	 = esc_attr( $preview_thumbnail_attachment[0] );
									$preview_attr_scrset = esc_attr( $preview_srcset );
									$preview_attr_sizes	 = esc_attr( $preview_sizes );
								} else {
									// in demo mode use images from plugin's demo sub-directory.
									$preview_attr_scrset = '';
									$preview_attr_sizes	 = '';
									$preview_attr_src	 = plugins_url( "zhu-posts-icon-carousel/demo/demo_{$demo_image_number}.jpg" );
								}

								$preview_image_html = <<<html
                                    <div class='post-thumb' style='float:left'><a href='{$post_url}'><img style='{$preview_custom_style_size}' src='{$preview_attr_src}' alt='{$alt_text}' title='{$post_title_attr}' srcset='{$preview_attr_scrset}' sizes='{$preview_attr_sizes}'></a></div>
html;
							} else {
								$preview_image_html = null;
							}

							if ( $show_preview_title ) {
								$preview_title_html = "<a class='zhu-pic-1-preview-title' href='{$post_url}'>{$post_title}</a>";
							} else {
								$preview_title_html = null;
							}

							if ( $show_preview_date ) {
								$preview_date_html = "<span class='zhu-pic-1-preview-date'>" . get_the_date() . '</span>';
							} else {
								$preview_date_html = null;
							}

							$excerpt = get_the_excerpt();
							$excerpt = "<div class='zhu-pic-1-preview-excerpt'><a href='{$post_url}'>" . $excerpt . '</a></div>';

							$preview_posts_html .= <<<html
                                <article class='zhu-pic-1-preview-artice' style='display:{$display};position:absolute;top:0'>
                                    
                                        {$preview_image_html}
                                        {$preview_title_html}
                                        {$preview_date_html}
                                    
                                    {$excerpt}
                                </article>
html;
						}

						$opacity -= $opacity_step;
					} // end has_post_thumbnail().
				} // end posts loop.

				wp_reset_postdata();

				$widget_id = esc_attr( $args['widget_id'] );

				// get the css and modify so it only applies to this widget.
				$css = $this->localize_css_for_element( "inner{$widget_id}", $instance['css'] );

				if ( $show_preview ) {
					$preview_html = <<<html
                    <hr class='zhu-pic-1-hr'>
                    <div class='zhu-pic-1-preview' style='position:relative;'>
                    {$preview_posts_html}
                    </div>
html;
				} else {
					$preview_html = null;
				}

				$scroll_duration = absint( $instance['scroll-duration'] );
				$pause_duration	 = absint( $instance['pause-duration'] );
				$widget_height	 = absint( $instance['widget-height'] );

				echo $args['before_widget'];
				echo <<<html
                    <style type='text/css'>
                        {$css}
                    </style><div id='inner{$widget_id}' class='zhu-pic-1-widget-inner' style='overflow:hidden; height:{$widget_height}px'>{$title}
                        <span class='zhu-pic-1-strip' scroll_duration='{$scroll_duration}' pause_duration='{$pause_duration}' style='position:relative; margin:0; padding:0; white-space:nowrap; '>
                            {$carousel_html}
                        </span>
                        {$preview_html}
                    </div>
html;
				echo $args['after_widget'];
			} finally {
				// Remove temporary filter.
				remove_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
				$this->current_instance_data = null;
			}
		} //end post count.
	}

	/**
	 * Get the number of words to include in the excerpt
	 *
	 * Within widget() filter excerpt_length is set to this method to
	 * return the value of the instance of the widget currently being rendered
	 *
	 * @since 1.0.0
	 *
	 * @param various $length       Length value to filter.  Ignore by this method.
	 * @return integer
	 */
	public function excerpt_length( $length ) {
		return absint( $this->current_instance_data['excerpt-length'] );
	}

	/**
	 * Process the CSS so it will only apply to this instance of our widget
	 *
	 * Modifies the supplied $css string string by prefixing the ID of
	 * a parent element to beginning of each CSS selector block.
	 *
	 * This method expects the CSS to be well formed
	 *
	 * @since 1.0.0
	 *
	 * @param string $outmost_element_id     The ID of the top-level element.
	 * @param string $css   initial CSS.
	 * @return string       modified CSS.
	 */
	private function localize_css_for_element( string $outmost_element_id, string $css ): string {
		// remove comments from css to make processing below simpler.
		$pattern = '!/\*[^*]*\*+([^/][^*]*\*+)*/!';
		$css	 = preg_replace( $pattern, '', $css );

		$outmost_element_id = '#' . $outmost_element_id;

		// all blocks end with a }.
		$blocks		 = explode( '}', trim( $css ) );
		$new_css	 = null;
		$num_block	 = count( $blocks );
		if ( $num_block > 0 ) {
			$count = 0;
			foreach ( $blocks as $blocks ) {
				$count ++;
				if ( $count < $num_block ) {

					$blocks		 = ltrim( $blocks );
					$blocks_len	 = strlen( $blocks );

					if ( null === $blocks ) {
						$new_css .= PHP_EOL;
					} elseif ( '@' === substr( $blocks, 0, 1 ) ) {
						/*
						 * Accomodate into account CSS @ rules, as these should
						 * not be prefixed by our parent element id
						 * find next ; or { character not within quotes
						 * ; is for termination of rule. e.g. @import ... ;
						 * { is for inner block or rule. e.g. @media ... {
						 */
						$within_string = false;
						for ( $idx = 0; $idx < $blocks_len; $idx ++ ) {
							$char = $blocks[$idx];

							if ( '"' === $char || "'" === $char ) {
								$within_string = ! $within_string;
							} elseif ( ! $within_string ) {
								if ( '{' === $char || ';' === $char ) {
									$new_css .= substr( $blocks, 0, $idx + 1 ) . PHP_EOL . localize_css_for_element( substr( $blocks, $idx + 1 ) . '}', $outmost_element_id );
									break;
								}
							}
						}
					} else {
						$new_css .= $outmost_element_id . ' ' . $blocks . '}' . PHP_EOL;  // ltrim to remove c/r.
					}
				}
			}
		}

		return str_replace( $outmost_element_id . ' ' . $outmost_element_id, $outmost_element_id, $new_css );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * Overrides the base class's method
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$defaults	 = $this->sanitize_with_defaults();   // no param = just get the defaults.
		$instance	 = wp_parse_args( $instance, $defaults );   // merge instance settings with defaults.

		if ( $instance['show-preview'] ) {
			$show_preview_checked			 = 'CHECKED';
			$show_hide_preview_settings_div	 = 'inherit';
		} else {
			$show_preview_checked			 = null;
			$show_hide_preview_settings_div	 = 'none';
		}

		if ( $instance['show-preview-image'] ) {
			$show_preview_image_checked				 = 'CHECKED';
			$show_hide_preview_image_settings_div	 = 'inherit';
		} else {
			$show_preview_image_checked				 = null;
			$show_hide_preview_image_settings_div	 = 'none';
		}

		if ( $instance['show-preview-title'] ) {
			$show_preview_title_checked = 'CHECKED';
		} else {
			$show_preview_title_checked = null;
		}

		if ( $instance['show-preview-date'] ) {
			$show_preview_date_checked = 'CHECKED';
		} else {
			$show_preview_date_checked = null;
		}

		if ( $instance['demo-mode'] ) {
			$demo_mode_checked = 'CHECKED';
		} else {
			$demo_mode_checked = null;
		}
		?>
		<div class='zhu-pic-carousel-form'>
			<p>
				<label for='<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>'>
					<strong><?php esc_html_e( 'Title', 'zhu_pic_domain' ); ?></strong> <?php esc_html__( '(leave blank for no display)', 'zhu_pic_domain' ); ?>
				</label>
				<input type='text' class='widefat' id='<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>' 
					   name='<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>' value='<?php echo esc_attr( $instance['title'] ); ?>'>
			</p>

			<p>
				<label for='<?php echo esc_attr( $this->get_field_id( 'num-posts' ) ); ?>'>
					<strong><?php esc_html_e( 'Number of Posts to Display', 'zhu_pic_domain' ); ?></strong>
				</label>
				<input type='number' class='widefat' min='1' id='<?php echo esc_attr( $this->get_field_id( 'num-posts' ) ); ?>' 
					   name='<?php echo esc_attr( $this->get_field_name( 'num-posts' ) ); ?>' value='<?php echo esc_attr( esc_attr( $instance['num-posts'] ) ); ?>'>
				<span class='zhu-pic-numeric-input-validator' for='<?php echo esc_attr( $this->get_field_id( 'num-posts' ) ); ?>'></span>
			</p>

			<p>
				<label for='<?php echo esc_attr( $this->get_field_id( 'widget-height' ) ); ?>' >
					<strong><?php esc_html_e( 'Widget Height', 'zhu_pic_domain' ); ?></strong>
				</label>
				<input type='number' class='widefat' min='100' max='9999' id='<?php echo esc_attr( $this->get_field_id( 'widget-height' ) ); ?>' 
					   name='<?php echo esc_attr( $this->get_field_name( 'widget-height' ) ); ?>' 
					   value='<?php echo esc_attr( esc_attr( $instance['widget-height'] ) ); ?>'>
				<span class='zhu-pic-numeric-input-validator' for='<?php echo esc_attr( $this->get_field_id( 'widget-height' ) ); ?>'></span>
			</p>

			<p>
				<label for='<?php echo esc_attr( $this->get_field_id( 'scroll-duration' ) ); ?>' >
					<strong><?php esc_html_e( 'Scroll Duration', 'zhu_pic_domain' ); ?></strong>
				</label>
				<input type='number' class='widefat' min='100' max='10000' id='<?php echo esc_attr( $this->get_field_id( 'scroll-duration' ) ); ?>' 
					   name='<?php echo esc_attr( $this->get_field_name( 'scroll-duration' ) ); ?>' 
					   value='<?php echo esc_attr( esc_attr( $instance['scroll-duration'] ) ); ?>'>
				<span class='zhu-pic-numeric-input-validator' for='<?php echo esc_attr( $this->get_field_id( 'scroll-duration' ) ); ?>'></span>
			</p>

			<p>
				<label for='<?php echo esc_attr( $this->get_field_id( 'pause-duration' ) ); ?>' >
					<strong><?php esc_html_e( 'Pause Duration', 'zhu_pic_domain' ); ?></strong>
				</label>
				<input type='number' class='widefat' min='1000' max='30000' id='<?php echo esc_attr( $this->get_field_id( 'pause-duration' ) ); ?>' 
					   name='<?php echo esc_attr( $this->get_field_name( 'pause-duration' ) ); ?>' value='<?php echo esc_attr( $instance['pause-duration'] ); ?>'>
				<span class='zhu-pic-numeric-input-validator' for='<?php echo esc_attr( $this->get_field_id( 'pause-duration' ) ); ?>'></span>
			</p>

			<p>
				<label for='<?php echo esc_attr( $this->get_field_id( 'carousel-image-size' ) ); ?>' >
					<strong><?php esc_html_e( 'Carousel Image Size', 'zhu_pic_domain' ); ?></strong>
				</label>
				<?php
				//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				//escaping is taking place within render_images_sizes_drop_down()
				//method returns dynamic HTML markup.
				echo $this->render_images_sizes_drop_down(
					$this->get_field_id( 'carousel-image-size' ),
					$this->get_field_name( 'carousel-image-size' ),
					$this->get_field_name( 'carousel-image-width' ),
					$this->get_field_name( 'carousel-image-height' ),
					$instance['carousel-image-size'],
					$instance['carousel-image-width'],
					$instance['carousel-image-height']
				);
				//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</p>
			<p>
				<input type='checkbox' id='<?php echo esc_attr( $this->get_field_id( 'show-preview' ) ); ?>' 
					   name='<?php echo esc_attr( $this->get_field_name( 'show-preview' ) ); ?>' <?php echo esc_attr( $show_preview_checked ); ?>
					   onchange='zhu_pic_set_visabity_for_checkbox_div("<?php echo esc_attr( $this->get_field_id( 'show-preview' ) ); ?>", "zhu-pic-1-preview-opts<?php echo esc_attr( $this->number ); ?>");'
					   >
				<label for='<?php echo esc_attr( $this->get_field_id( 'show-preview' ) ); ?>'>
					<strong><?php esc_html_e( 'Show Preview', 'zhu_pic_domain' ); ?></strong>
				</label>
			</p>

			<div id='zhu-pic-1-preview-opts<?php echo esc_attr( $this->number ); ?>' style='display:<?php echo esc_attr( $show_hide_preview_settings_div ); ?>'>
				<p>
					<input type='checkbox' id='<?php echo esc_attr( $this->get_field_id( 'show-preview-title' ) ); ?>' 
						   name='<?php echo esc_attr( $this->get_field_name( 'show-preview-title' ) ); ?>' <?php echo esc_attr( $show_preview_title_checked ); ?>
						   >
					<label for='<?php echo esc_attr( $this->get_field_id( 'show-preview-title' ) ); ?>'>
						<strong><?php esc_html_e( 'Show Preview Title', 'zhu_pic_domain' ); ?></strong>
					</label>
				</p>
				<p>
					<input type='checkbox' id='<?php echo esc_attr( $this->get_field_id( 'show-preview-date' ) ); ?>' 
						   name='<?php echo esc_attr( $this->get_field_name( 'show-preview-date' ) ); ?>' <?php echo esc_attr( $show_preview_date_checked ); ?>
						   >
					<label for='<?php echo esc_attr( $this->get_field_id( 'show-preview-date' ) ); ?>'>
						<strong><?php esc_html_e( 'Show Preview Date', 'zhu_pic_domain' ); ?></strong>
					</label>
				</p>
				<p>
					<label for='<?php echo esc_attr( $this->get_field_id( 'excerpt-length' ) ); ?>'>
						<strong><?php esc_html_e( 'Preview Excerpt Length', 'zhu_pic_domain' ); ?></strong>
					</label>
					<input type='number' class='widefat' min='5' id='<?php echo esc_attr( $this->get_field_id( 'excerpt-length' ) ); ?>' 
						   name='<?php echo esc_attr( $this->get_field_name( 'excerpt-length' ) ); ?>' 
						   value='<?php echo esc_attr( $instance['excerpt-length'] ); ?>'>
					<span class='zhu-pic-numeric-input-validator' for='<?php echo esc_attr( $this->get_field_id( 'excerpt-length' ) ); ?>'></span>
				</p>
				<p>
					<input type='checkbox' id='<?php echo esc_attr( $this->get_field_id( 'show-preview-image' ) ); ?>' 
						   name='<?php echo esc_attr( $this->get_field_name( 'show-preview-image' ) ); ?>' <?php echo esc_attr( $show_preview_image_checked ); ?>
						   onchange='zhu_pic_set_visabity_for_checkbox_div("<?php echo esc_attr( $this->get_field_id( 'show-preview-image' ) ); ?>", "zhu-pic-1-preview-image-opts<?php echo esc_attr( $this->number ); ?>");'
						   >
					<label for='<?php echo esc_attr( $this->get_field_id( 'show-preview-image' ) ); ?>'>
						<strong><?php esc_html_e( 'Show Preview Image', 'zhu_pic_domain' ); ?></strong>
					</label>
				</p>
				<div id='zhu-pic-1-preview-image-opts<?php echo esc_attr( $this->number ); ?>' style='display:<?php echo esc_attr( $show_hide_preview_image_settings_div ); ?>'>
					<p>
						<label for='<?php echo esc_attr( $this->get_field_id( 'preview-image-size' ) ); ?>' >
							<strong><?php esc_html_e( 'Preview Image Size', 'zhu_pic_domain' ); ?></strong>
						</label>
						<?php
						//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
						//escaping is taking place within render_images_sizes_drop_down()
						//method returns dynamic HTML markup.
						echo $this->render_images_sizes_drop_down(
							$this->get_field_id( 'preview-image-size' ),
							$this->get_field_name( 'preview-image-size' ),
							$this->get_field_name( 'preview-image-width' ),
							$this->get_field_name( 'preview-image-height' ),
							$instance['preview-image-size'],
							$instance['preview-image-width'],
							$instance['preview-image-height']
						);
						//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</p>
				</div>
			</div>

			<p>
				<label for='<?php echo esc_attr( $this->get_field_id( 'css' ) ); ?>'>
					<strong><?php esc_html_e( 'CSS', 'zhu_pic_domain' ); ?></strong>
				</label>
				<textarea class='widefat code content sync-input zhu-pic-1-css-editor' rows='16' cols='20' 
						  id='<?php echo esc_attr( $this->get_field_id( 'css' ) ); ?>' 
						  name='<?php echo esc_attr( $this->get_field_name( 'css' ) ); ?>'><?php echo esc_textarea( $instance['css'] ); ?></textarea>
			</p>

			<p>
				<input type='checkbox' id='<?php echo esc_attr( $this->get_field_id( 'demo-mode' ) ); ?>' 
					   name='<?php echo esc_attr( $this->get_field_name( 'demo-mode' ) ); ?>' <?php echo esc_attr( $demo_mode_checked ); ?>
					   >
				<label for='<?php echo esc_attr( $this->get_field_id( 'demo-mode' ) ); ?>'>
					<strong><?php esc_html_e( 'Demo Mode', 'zhu_pic_domain' ); ?></strong>
				</label>
			</p>

		</div>
		<script type='text/javascript'>
			zhu_pic_init_widget_css_editor('<?php echo esc_attr( $this->get_field_id( 'css' ) ); ?>');
		</script>
		<?php
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * Overrides the base class's method
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.  Not used here.
	 * @return array Settings to save.  Will not return false to cancel saving.
	 *
	 * @see sanitize_with_defaults()
	 */
	public function update( $new_instance, $old_instance ) {
		return $this->sanitize_with_defaults( $new_instance, true );
	}

	/**
	 * Validate instance settings
	 *
	 * Takes the widget instance settings.  Validates and sanitizes values.
	 * Will add settings with their default value it not present.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance   Settings for this instance.
	 * @param bool  $is_updating  Set to true when called from update() to assist
	 *                           with determining the absence of a setting or
	 *                           a checkbox that is unchecked.
	 * @return array Settings to save or use.
	 */
	private function sanitize_with_defaults( array $instance = null, bool $is_updating = false ): array {

		if ( null === $instance ) {
			$instance = array();
		}

		$ret_instance = array();

		// sanitzation or setting defaults.
		$ret_instance['title']			 = ( array_key_exists( 'title', $instance ) ) ? sanitize_text_field( $instance['title'] ) : '';
		$ret_instance['num-posts']		 = ( array_key_exists( 'num-posts', $instance ) ) ? absint( $instance['num-posts'] ) : ZHU_PIC_1_DEFAULT_NUM_POSTS;
		$ret_instance['widget-height']	 = ( array_key_exists( 'widget-height', $instance ) ) ? absint( $instance['widget-height'] ) : ZHU_PIC_1_DEFAULT_HEIGHT;
		$ret_instance['scroll-duration'] = ( array_key_exists( 'scroll-duration', $instance ) ) ? absint( $instance['scroll-duration'] ) : ZHU_PIC_1_DEFAULT_SCROLL_DURATION;
		$ret_instance['pause-duration']	 = ( array_key_exists( 'pause-duration', $instance ) ) ? absint( $instance['pause-duration'] ) : ZHU_PIC_1_DEFAULT_PAUSE_DURATION;

		$ret_instance['excerpt-length'] = ( array_key_exists( 'excerpt-length', $instance ) ) ? absint( $instance['excerpt-length'] ) : ZHU_PIC_1_DEFAULT_EXCERPT_LENGTH;

		$ret_instance['carousel-image-size']	 = ( array_key_exists( 'carousel-image-size', $instance ) ) ? sanitize_text_field( $instance['carousel-image-size'] ) : ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_SIZE;
		$ret_instance['carousel-image-width']	 = ( array_key_exists( 'carousel-image-width', $instance ) ) ? absint( $instance['carousel-image-width'] ) : ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_WIDTH;
		$ret_instance['carousel-image-height']	 = ( array_key_exists( 'carousel-image-height', $instance ) ) ? absint( $instance['carousel-image-height'] ) : ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_HEIGHT;

		$ret_instance['preview-image-size']		 = ( array_key_exists( 'preview-image-size', $instance ) ) ? sanitize_text_field( $instance['preview-image-size'] ) : ZHU_PIC_1_DEFAULT_PREVIEW_IMG_SIZE;
		$ret_instance['preview-image-width']	 = ( array_key_exists( 'preview-image-width', $instance ) ) ? absint( $instance['preview-image-width'] ) : ZHU_PIC_1_DEFAULT_PREVIEW_IMG_WIDTH;
		$ret_instance['preview-image-height']	 = ( array_key_exists( 'preview-image-height', $instance ) ) ? absint( $instance['preview-image-height'] ) : ZHU_PIC_1_DEFAULT_PREVIEW_IMG_HEIGHT;

		$ret_instance['css'] = ( array_key_exists( 'css', $instance ) ) ? sanitize_textarea_field( $instance['css'] ) : $this->get_default_css();

		if ( ! $is_updating && ! array_key_exists( 'show-preview', $instance ) ) {
			// If not updating, and setting not present, then set default.
			$ret_instance['show-preview'] = ZHU_PIC_1_DEFAULT_SHOW_PREVIEW;
		} else {
			$ret_instance['show-preview'] = ( isset( $instance['show-preview'] ) && strcasecmp( 'on', $instance['show-preview'] ) === 0 ) ? 'on' : '';
		}

		if ( ! $is_updating && ! array_key_exists( 'show-preview-image', $instance ) ) {
			// If not updating, and setting not present, then set default.
			$ret_instance['show-preview-image'] = ZHU_PIC_1_DEFAULT_SHOW_PREVIEW_IMAGE;
		} else {
			$ret_instance['show-preview-image'] = ( isset( $instance['show-preview-image'] ) && strcasecmp( 'on', $instance['show-preview-image'] ) === 0 ) ? 'on' : '';
		}

		if ( ! $is_updating && ! array_key_exists( 'show-preview-title', $instance ) ) {
			// If not updating, and setting not present, then set default.
			$ret_instance['show-preview-title'] = ZHU_PIC_1_DEFAULT_SHOW_PREVIEW_TITLE;
		} else {
			$ret_instance['show-preview-title'] = ( isset( $instance['show-preview-title'] ) && strcasecmp( 'on', $instance['show-preview-title'] ) === 0 ) ? 'on' : '';
		}

		if ( ! $is_updating && ! array_key_exists( 'show-preview-date', $instance ) ) {
			// If not updating, and setting not present, then set default.
			$ret_instance['show-preview-date'] = ZHU_PIC_1_DEFAULT_SHOW_PREVIEW_DATE;
		} else {
			$ret_instance['show-preview-date'] = ( isset( $instance['show-preview-date'] ) && strcasecmp( 'on', $instance['show-preview-date'] ) === 0 ) ? 'on' : '';
		}

		if ( ! $is_updating && ! array_key_exists( 'demo-mode', $instance ) ) {
			// If not updating, and setting not present, then set default.
			$ret_instance['demo-mode'] = ZHU_PIC_1_DEFAULT_DEMO_MODE;
		} else {
			$ret_instance['demo-mode'] = ( isset( $instance['demo-mode'] ) && strcasecmp( 'on', $instance['demo-mode'] ) === 0 ) ? 'on' : '';
		}

		// Range validation.
		if ( $ret_instance['num-posts'] < 1 ) {
			$ret_instance['num-posts'] = 5;
		}

		if ( $ret_instance['widget-height'] < 100 ) {
			$ret_instance['widget-height'] = ZHU_PIC_1_DEFAULT_HEIGHT;
		}

		if ( $ret_instance['scroll-duration'] < 100 || $ret_instance['scroll-duration'] > 10000 ) {
			$ret_instance['scroll-duration'] = ZHU_PIC_1_DEFAULT_SCROLL_DURATION;
		}

		if ( $ret_instance['pause-duration'] < 1000 || $ret_instance['pause-duration'] > 30000 ) {
			$ret_instance['pause-duration'] = ZHU_PIC_1_DEFAULT_PAUSE_DURATION;
		}

		if ( $ret_instance['excerpt-length'] < 5 ) {
			$ret_instance['excerpt-length'] = ZHU_PIC_1_DEFAULT_EXCERPT_LENGTH;
		}

		if ( $ret_instance['carousel-image-height'] < ZHU_PIC_1_MIN_CAROUSEL_IMG_HEIGHT ) {
			$ret_instance['carousel-image-height'] = ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_HEIGHT;
		}

		if ( $ret_instance['carousel-image-width'] < ZHU_PIC_1_MIN_CAROUSEL_IMG_WIDTH ) {
			$ret_instance['carousel-image-width'] = ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_WIDTH;
		}

		// option list items valid.
		if ( ! isset( $ret_instance['carousel-image-size'] ) ) {
			$ret_instance['carousel-image-size'] = ZHU_PIC_1_DEFAULT_CAROUSEL_IMG_SIZE;
		}

		return $ret_instance;
	}

	/**
	 * Return default CSS
	 *
	 * This default CSS is used when a new instance of this widget is added
	 *
	 * @since 1.0.0
	 *
	 * @return string   CSS.
	 */
	private function get_default_css(): string {
		// Keep this code left aligned as this is exacly how it will look in the textarea/CodeMirror editor.
		return <<<css
/* widget title */
.widget-title {

}

/* rotating carousel span */
.zhu-pic-1-strip {

}

.zhu-pic-1-strip img {
  border-radius:10px;
}

.zhu-pic-1-strip a
{
  box-shadow:none;
}

/* hr break */
.zhu-pic-1-hr {
  margin-left:30px;
  margin-right:30px;
  margin-top:15px;
  margin-bottom:15px;
  background-color:#EEEEEE;
}

/* outer preview div */
.zhu-pic-1-preview {

}

.zhu-pic-1-preview img {
  margin-right:15px;
  margin-bottom:5px;
}

/* title a element */
.zhu-pic-1-preview-title {
  box-shadow:none;
}

/* post date span */
.zhu-pic-1-preview-date {

}

/* preview excerpt div */
.zhu-pic-1-preview-excerpt {
  display:inline-block;
}

.zhu-pic-1-preview-excerpt a {
  text-decoration:none;
  color:inherit;
}
css;
	}

	/**
	 * Renders a select element to allow user to select a registered image size.
	 *
	 * Support function for when rending the widget.  Also renders width and
	 * height numeric boxes for use when the image size is set to 'custom'
	 *
	 * @since 1.0..0
	 *
	 * @requires_wordpress 5.3.0            (for wp_get_registered_image_subsizes)
	 *
	 * @param string $field_id              ID to give the select element.
	 * @param string $field_name            Name to give the select element.
	 * @param string $field_name_width      Name to give the width input element.
	 * @param string $field_name_height     Name to give the height input element.
	 * @param string $current_name          Name of the currently select size to
	 *                                      to default to currently selected item.
	 * @param string $current_width         Current width value to edit.
	 * @param string $current_height        Current height value to edit.
	 *
	 * @return string       Generate HTML.
	 */
	private function render_images_sizes_drop_down( string $field_id, string $field_name, string $field_name_width, string $field_name_height, string $current_name, string $current_width, string $current_height ): string {

		$sizes			 = wp_get_registered_image_subsizes();
		$sizes['custom'] = null;

		if ( '' === $current_name || ! array_key_exists( $current_name, $sizes ) ) {
			$current_name = 'custom';
		}

		$field_id = esc_attr( $field_id );

		$field_name			 = esc_attr( $field_name );
		$field_name_width	 = esc_attr( $field_name_width );
		$field_name_height	 = esc_attr( $field_name_height );
		$current_name		 = esc_attr( $current_name );
		$current_width		 = esc_attr( $current_width );
		$current_height		 = esc_attr( $current_height );

		$html = <<<html
                <select class='widefat' id='{$field_id}' name='{$field_name}' 
                    onchange='zhu_pic_set_visabity_for_custom("{$field_id}","{$field_id}_custom")'>
html;

		$is_custom_selected = false;
		foreach ( $sizes as $size_key => $size_data_unused ) {
			$html .= "<option value='" . esc_attr( $size_key ) . "' ";

			if ( $size_key === $current_name ) {
				$html .= ' SELECTED ';

				if ( 'custom' === $size_key ) {
					$is_custom_selected = true;
				}
			}

			$html .= '>' . esc_html( $size_key ) . '</option>';
		}

		$show_custom = ( $is_custom_selected ) ? 'inherit' : 'none';
		$min_height	 = esc_attr( ZHU_PIC_1_MIN_CAROUSEL_IMG_HEIGHT );
		$min_width	 = esc_attr( ZHU_PIC_1_MIN_CAROUSEL_IMG_WIDTH );

		$width_word	 = esc_html__( 'Width', 'zhu_pic_domain' );
		$height_word = esc_html__( 'Height', 'zhu_pic_domain' );

		$html .= <<<html
            </select>
            <div class='zhu-pci-widget-settings-sub' id='{$field_id}_custom' style='display:{$show_custom};'>
                <span class='zhu-pic-numeric-input-validator' for='{$field_id}_width'></span>
                <label for='{$field_id}_width'>
                    <strong>{$width_word}</strong>
                </label>
                <input type='number' class='zhu-pci-widget-settings-sub-input' min='{$min_width}' id='{$field_id}_width' style='width:100px' name='{$field_name_width}' value='{$current_width}'>
                
                <br>
                <span class='zhu-pic-numeric-input-validator' for='{$field_id}_height'></span>
                <label for='{$field_id}_height'>
                    <strong>{$height_word}</strong>
                </label>
                <input type='number' class='zhu-pci-widget-settings-sub-input' min='{$min_height}' id='{$field_id}_height' style='width:100px' name='{$field_name_height}' value='{$current_height}'>
            </div>
html;

		return $html;
	}

	/**
	 * Return a random portion of Lorem ipsum text for use as a placeholder
	 *
	 * Returns a random number of words between the two values supplied
	 *
	 * @since 1.0.0
	 *
	 * @staticvar type $as_array     Array of words
	 *
	 * @param int $min_words   Minimum number of words.
	 * @param int $max_words   Maximum number of words.
	 * @return string   Portion of Lorem ipsum text.
	 */
	private function loremipsum( $min_words, $max_words ) {
		static $as_array = null;

		if ( null === $as_array ) {
			$as_array = explode( ' ', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?' );
		}

		if ( $max_words > $min_words ) {
			$get_x_words = wp_rand( $min_words, $max_words );
		} else {
			$get_x_words = $min_words;
		}

		$num_words_available = count( $as_array );

		if ( $get_x_words > $num_words_available ) {
			// not enough words - return everything we've got.
			return implode( ' ', $as_array );
		}

		$start_idx = wp_rand( 1, $num_words_available - $get_x_words );
		return implode( ' ', array_slice( $as_array, $start_idx - 1, $get_x_words ) );
	}

}
