<?php
if (is_user_logged_in()) {

	$errors = array();

	if (($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {

		if (trim($_POST['ids']) === '') {
			$errors[] = '<strong>Error:</strong> Please enter outfit products.';
		}

		if (!file_exists($_FILES['thumb']['tmp_name'])) {
			$errors[] = '<strong>Error:</strong> Please enter outfit image.';
		}

		if (count($errors) == 0) {

			$post_args = array(
				'post_title' => '',
				'post_type' => 'outfit',
				'post_status' => 'pending',
			);

			$post_id = wp_insert_post($post_args);

			if ($post_id) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';

				$attachment_id = media_handle_upload('thumb', $post_id);

				if (is_wp_error($attachment_id)) {
					$errors[] = '<strong>Error:</strong> Image upload error.';
				} else {
					set_post_thumbnail($post_id, $attachment_id);
					add_post_meta($post_id, 'products', $_POST['ids']);
					wp_update_post(['ID' => $post_id, 'post_title' => 'Outfit ' . $post_id]);

					wc_add_notice('Outfit photo has been submitted for review.', $notice_type = 'notice');
					wp_safe_redirect(esc_url(home_url('my-account/outfits/')));
				}
			}
		}
	}

/**
 * Template Name: New Outfit
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package 318couture
 */

	get_header();?>

	<div class="container">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

				<?php while (have_posts()): the_post();?>
						<div class="row">
							<div class="col-md-3 account-sidebar">
								<?php dynamic_sidebar('account');?>
							</div>

							<div class="col-md-9">
								<article id="post-<?php the_ID();?>" <?php post_class();?>>
									<header class="entry-header col-sm-offset-3">
										<?php the_title('<h1 class="entry-title">', '</h1>');?>
									</header><!-- .entry-header -->

									<div class="entry-content">
										<div class="woocommerce">
											<?php the_content();?>
											<?php if (count($errors) > 0) {
			echo '<ul class="alert alert-danger">';
			foreach ($errors as $error) {
				echo '<li>' . $error . '</li>';
			}
			echo '</ul>';
		}?>
										<form id="newOutfitForm" method="post" enctype="multipart/form-data">
											<div class="form-group">
												<!-- <label for="thumb"><?php // _e('Outfit Image:', 'couture')?></label> -->

												<div class="wc-row">
													<div class="wc-col-md-6">
														<input data-label="Select Image" class="filepicker" type="file" name="thumb" multiple="false" accept="image/*" value="<?php if (isset($_POST['thumb'])) {
			echo $_POST['thumb'];
		}
		?>">
													</div>
													<div class="wc-col-md-6">
														<h4>Photo Guidelines</h4>
														<?php // echo cs_get_option('outfit_guidelines'); ?>
													</div>
												</div>
											</div>

											<div class="form-group">
												<label><?php _e('Used Products:', 'couture')?></label>

												<div class="chosen">
													<div class="row"></div>
												</div>
												<input type="hidden" name="ids" id="ids" value="">
											</div>

											<div class="form-group">
												<select class="selectId">
													<option selected disabled>Choose a category</option>
													<?php foreach (wc_outfit_product_cats() as $cat): ?>
														<option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
													<?php endforeach?>
											</select>

											<div class="row">
												<div id="products" class="products"></div>
											</div>
										</div>

										<?php wp_nonce_field('post_nonce', 'post_nonce_field');?>
									    <input type="submit" value="<?php _e('Add Outfit', 'couture')?>">
									</form>
									</div>
								</div><!-- .entry-content -->
							</article><!-- #post-## -->
						</div>
					</div>
				<?php endwhile; // End of the loop. ?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .container -->

<?php
get_footer();

} else {
	wp_safe_redirect(esc_url(wc_get_page_permalink('myaccount')));
}