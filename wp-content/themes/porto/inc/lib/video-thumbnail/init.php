<?php
/**
 * Product Video Thumbnail
 *
 * Display video instead of thumbnail images
 * 
 * @since 6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Porto_Video_Thumbnail' ) ) :

	class Porto_Video_Thumbnail {
		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
			add_filter( 'porto_single_product_after_thumbnails', array( $this, 'printVideoThumbnails' ) );
		}

		/**
		 * Load assets for video thumbnails
		 */
		public function enqueue_scripts() {
			wp_register_script( 'porto-video-thumbnail', PORTO_LIB_URI . '/video-thumbnail/video-thumbnail.min.js', array( 'porto-theme' ), PORTO_VERSION, true );
		}

		/**
		 * Print video for product thumbnails.
		 */
		public function printVideoThumbnails() {
			global $product;
			if ( empty( $product ) ) {
				return;
			}
			ob_start();
			
			$featured_id    = method_exists( $product, 'get_image_id' ) ? $product->get_image_id() : get_post_thumbnail_id();
			$featured_thumb = wp_get_attachment_image_src( $featured_id, has_image_size( 'shop_thumbnail' ) ? 'shop_thumbnail' : 'woocommerce_thumbnail' );
			if ( ! empty( $featured_thumb ) && ! empty( $featured_thumb[0] ) ) {
				$featured_thumb = $featured_thumb[0];
			} else {
				$featured_thumb = '';
			}
			$featured_large = wp_get_attachment_image_src( $featured_id, apply_filters( 'woocommerce_gallery_image_size', 'full' ) );
			if ( ! empty( $featured_large ) && ! empty( $featured_large[0] ) ) {
				$featured_large   = $featured_large[0];
			} else {
				$featured_large = '';
			}			
			$video_thumb   = get_post_meta( get_the_ID(), 'porto_video_thumbnail_img', true );
			$video_poster  = get_post_meta( get_the_ID(), 'porto_video_thumbnail_poster', true );
			$video_source  = get_post_meta( get_the_ID(), 'porto_video_source', true );
			$video_sh_type = get_post_meta( get_the_ID(), 'porto_video_sh_type', true );

			if ( 'popup' == $video_sh_type || empty( $video_sh_type ) ) {
				if ( '' == $video_source || 'shortcode' == $video_source ) {
					if ( '' == $video_source ) {
						$ids = get_post_meta( get_the_ID(), 'porto_product_video_thumbnails' );
						if ( ! empty( $ids ) ) {
							wp_enqueue_script( 'jquery-fitvids' );
							wp_enqueue_script( 'porto-theme-fit-vd' );
							wp_enqueue_script( 'porto-video-thumbnail' );
			
							foreach ( $ids as $id ) {
								$url = wp_get_attachment_url( $id );
								$poster = get_the_post_thumbnail_url( $id ) ? get_the_post_thumbnail_url( $id ) : $featured_large;
								?>
			
								<div class="img-thumbnail">
									<a href="#" class="porto-video-thumbnail-viewer"><img src="<?php echo esc_url( $poster ); ?>" alt="poster image"></a>
									<script type="text/template" class="porto-video-thumbnail-data">
										<figure class="post-media fit-video">
											<?php echo do_shortcode( '[video src="' . esc_url( $url ) . '" poster="' . esc_url( $poster ) . '"]' ); ?>
										</figure>
									</script>
								</div>
			
								<?php
							}
						}
					}
					// with video thumbnail shortcode
					$video_code = get_post_meta( get_the_ID(), 'porto_product_video_thumbnail_shortcode', true );
					$video_html = '';
					if ( false !== strpos( $video_code, '[video src="' ) ) {
						wp_enqueue_script( 'jquery-fitvids' );
						wp_enqueue_script( 'porto-video-thumbnail' );

						preg_match( '/poster="([^\"]*)"/', $video_code, $poster );
						$poster_lg    = empty( $poster ) ? $featured_large : $poster[1];
						$poster_thumb = empty( $poster ) ? $featured_thumb : $poster[1];
						$video_html   = do_shortcode( $video_code );
					} else {
						$youtube_id = preg_match( '/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/', $video_code, $matches );
						if ( ! empty( $matches ) && ! empty( $matches[1] ) ) {
							$youtube_id = $matches[1];
						} else {
							$youtube_id = '';
						}
						if ( ! $youtube_id ) {
							$vimeo_id = preg_match( '/^(?:https?:\/\/)?(?:www|player\.)?(?:vimeo\.com\/)?(?:video\/|external\/)?(\d+)([^.?&#"\'>]?)/', $video_code, $matches );
							if ( ! empty( $matches ) && ! empty( $matches[1] ) ) {
								$vimeo_id = $matches[1];
							} else {
								$vimeo_id = '';
							}
						}
						$poster_lg    = $featured_large;
						$poster_thumb = $featured_thumb;
					}
					if ( ! $video_thumb ) {
						$video_thumb = $poster_thumb;
					}
					if ( ! $video_poster ) {
						$video_poster = $poster_lg;
					}
					if ( $video_html ) {
						wp_enqueue_script( 'jquery-fitvids' );
						wp_enqueue_script( 'porto-theme-fit-vd' );
						wp_enqueue_script( 'porto-video-thumbnail' );
						?>
						<div class="img-thumbnail">
							<a href="#" class="porto-video-thumbnail-viewer popup-video"><img src="<?php echo esc_url( $video_thumb ); ?>" alt="poster image"></a>
							<script type="text/template" class="porto-video-thumbnail-data">
								<figure class="post-media fit-video">
								<?php echo porto_strip_script_tags( $video_html ); ?>
								</figure>
							</script>
						</div>
						<?php
					} else if ( ! empty( $youtube_id ) || ! empty( $vimeo_id ) ) {
						?>
						<div class="img-thumbnail">
							<a href="<?php echo esc_url( $video_code ); ?>" class="porto-video-thumbnail-viewer popup-<?php echo ! empty( $youtube_id ) ? 'youtube' : 'vimeo'; ?>"><img src="<?php echo esc_url( $video_thumb ); ?>" alt="poster image"></a>
						</div>
						<?php
					}
				} else if ( 'mp4' == $video_source ) {
					// from library
					$ids = get_post_meta( get_the_ID(), 'porto_product_video_thumbnails' );
					if ( isset( $ids[0] ) ) {
						wp_enqueue_script( 'jquery-fitvids' );
						wp_enqueue_script( 'porto-theme-fit-vd' );
						wp_enqueue_script( 'porto-video-thumbnail' );

						$url          = wp_get_attachment_url( $ids[0] );
						$poster_lg    = get_the_post_thumbnail_url( $ids[0] ) ? get_the_post_thumbnail_url( $ids[0] ) : $featured_large;
						$poster_thumb = get_the_post_thumbnail_url( $ids[0] ) ? get_the_post_thumbnail_url( $ids[0] ) : $featured_thumb;
						if ( ! $video_thumb ) {
							$video_thumb = $poster_thumb;
						}
						if ( ! $video_poster ) {
							$video_poster = $poster_lg;
						}
						?>

						<div class="img-thumbnail">
							<a href="#" class="porto-video-thumbnail-viewer"><img src="<?php echo esc_url( $video_thumb ); ?>" alt="poster image"></a>
							<script type="text/template" class="porto-video-thumbnail-data">
								<figure class="post-media fit-video">
									<?php echo do_shortcode( '[video src="' . esc_url( $url ) . '" poster="' . esc_url( $video_poster ) . '"]' ); ?>
								</figure>
							</script>
						</div>

						<?php
					}
				} else if ( 'youtube' == $video_source ) {
					// with video thumbnail shortcode
					$video_code = get_post_meta( get_the_ID(), 'porto_video_youtube', true );
					$youtube_id = preg_match( '/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/', $video_code, $matches );
					if ( ! empty( $matches ) && ! empty( $matches[1] ) ) {
						$youtube_id = $matches[1];
					} else {
						$youtube_id = '';
					}
					
					$poster = $featured_thumb;
				
					if ( ! $video_thumb ) {
						$video_thumb = $poster;
					}
					if ( ! empty( $youtube_id ) ) {
						?>
						<div class="img-thumbnail">
							<a href="<?php echo esc_url( $video_code ); ?>" class="porto-video-thumbnail-viewer popup-youtube"><img src="<?php echo esc_url( $video_thumb ); ?>" alt="poster image"></a>
						</div>
						<?php
					}
				}  else if ( 'vimeo' == $video_source ) {
					// with video thumbnail shortcode
					$video_code = get_post_meta( get_the_ID(), 'porto_video_vimeo', true );
					$vimeo_id = preg_match( '/^(?:https?:\/\/)?(?:www|player\.)?(?:vimeo\.com\/)?(?:video\/|external\/)?(\d+)([^.?&#"\'>]?)/', $video_code, $matches );
					if ( ! empty( $matches ) && ! empty( $matches[1] ) ) {
						$vimeo_id = $matches[1];
					} else {
						$vimeo_id = '';
					}
					
					$poster = $featured_thumb;
				
					if ( ! $video_thumb ) {
						$video_thumb = $poster;
					}
					if ( ! empty( $vimeo_id ) ) {
						?>
						<div class="img-thumbnail">
							<a href="<?php echo esc_url( $video_code ); ?>" class="porto-video-thumbnail-viewer popup-vimeo"><img src="<?php echo esc_url( $video_thumb ); ?>" alt="poster image"></a>
						</div>
						<?php
					}
				}
			} else if ( 'slide' == $video_sh_type ) {
				if ( ! $video_thumb ) {
					$video_thumb = $featured_thumb;
				}
				?>
					<div class="img-thumbnail">
						<img width="300" height="300" src="<?php echo esc_url( $video_thumb ); ?>" alt="thumbnail image">
					</div>
				<?php
			}
			return ob_get_clean();
		}
	}
endif;

new Porto_Video_Thumbnail;
