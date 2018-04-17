<?php

namespace Xim_Woo_Outfit\Admin;

trait Admin {
	/**
	 * Create options page.
	 *
	 * @since    1.0.0
	 */
	function menu_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}?>

		<div class="wrap">
			<!-- <h2><?php // _e('Woocommerce Outfit Options', 'xim')?></h2> -->

			<?php if (isset($_GET['section'])) {
				$section = $_GET['section'];
			} else {
				$section = 'general';
			} ?>

			<h2 class="nav-tab-wrapper">
	    		<a href="?page=wc_outfit&section=general" class="nav-tab <?php echo $section == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'xim'); ?></a>
	    		<a href="?page=wc_outfit&section=style-gallery" class="nav-tab <?php echo $section == 'style-gallery' ? 'nav-tab-active' : ''; ?>"><?php _e('Style Gallery', 'xim'); ?></a>
	    		<a href="?page=wc_outfit&section=api" class="nav-tab <?php echo $section == 'api' ? 'nav-tab-active' : ''; ?>"><?php _e('API Keys', 'xim'); ?></a>
			</h2>

			
			<?php if ($section == 'style-gallery') { ?>
				<form method="post" action="options.php">
					<?php settings_fields('wc-outfit'); ?>
					<?php do_settings_sections('wc_outfit'); ?>

					<h2><?php _e('Style Gallery', 'xim'); ?></h2>
					
					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row"><?php _e('Style Gallery Page', 'xim'); ?></th>
				        	<td>
								<?php wp_dropdown_pages(array('name' => 'wc-outfit-page-id', 'selected' => get_option('wc-outfit-page-id')))?>
								<p class="description"><?php _e('The base page that will be used in outfit permalinks.', 'xim')?></p>
							</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><?php _e('Page Title', 'xim'); ?></th>
				        	<td>
								<input type="text" name="wc-outfit-page-title" id="wc-outfit-page-title" value="<?php echo get_option('wc-outfit-page-title'); ?>" placeholder="<?php _e('Style Gallery', 'xim'); ?>">
							</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><?php _e('Page Subtitle/Slogan', 'xim'); ?></th>
				        	<td>
								<input type="text" name="wc-outfit-page-slogan" id="wc-outfit-page-slogan" value="<?php echo get_option('wc-outfit-page-slogan'); ?>" placeholder="<?php _e('Inspire and Admire', 'xim'); ?>">
							</td>
				        </tr>
				    </table>
					
					<?php submit_button();?>
				</form>
			<?php } else if ($section == 'api') { ?>
				<form method="post" action="options.php">
					<?php settings_fields('wc-outfit');?>
					<?php do_settings_sections('wc_outfit');?>

					<h2><?php _e('API Keys', 'xim'); ?></h2>
					<p><?php _e('This will be used for sharing outfit on social media.', 'xim'); ?></p>

					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row"><?php _e('Facebook App ID', 'xim'); ?></th>
				        	<td>
								<input type="text" name="wc-outfit-fb-app-id" id="wc-outfit-fb-app-id" value="<?php echo get_option('wc-outfit-fb-app-id'); ?>">
								<p class="description"><?php _e('Get your Facebook app id from <a href="https://developers.facebook.com/" target="_blank">here</a>', 'xim')?></p>
							</td>
				        </tr>
				    </table>					

					<?php submit_button();?>
				</form>
			<?php } else { ?>				
				<form method="post" action="options.php">
					<?php settings_fields('wc-outfit'); ?>
					<?php do_settings_sections('wc_outfit'); ?>

				    <h2><?php _e('Outfit Submission', 'xim'); ?></h2>

					<table class="form-table">
				        <tr valign="top">
							<th scope="row"><?php _e('Verify Submission', 'xim'); ?></th>

							<td>
								<input type="checkbox" name="wc-outfit-verify-submission" id="wc-outfit-verify-submission" <?php echo (get_option('wc-outfit-verify-submission', 'on') ? 'checked' : ''); ?>>
								<label for="wc-outfit-verify-submission"><strong><?php _e('Verify new submitted outfit', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, Admin will have to verify outfits manually and publish them.', 'xim'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Bought Product Only', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-bought-only" id="wc-outfit-bought-only" <?php echo (get_option('wc-outfit-bought-only', 'on') ? 'checked' : ''); ?>>
								<label for="wc-outfit-bought-only"><strong><?php _e('Allow customer to attach bought product only', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, customer will be able to attach the products they bought.', 'xim'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Submission Guideline', 'xim'); ?></th>

							<td>
								<?php wp_editor(get_option('wc-outfit-submission-guideline'), 'wc-outfit-submission-guideline', array('wpautop' => false, 'media_buttons' => false, 'teeny' => true, 'textarea_rows' => 8)); ?>
								<p class="description"><?php _e('Add guideline for uploading outfit photos.', 'xim'); ?></p>
							</td>
						</tr>
					</table>

					<h2><?php _e('Tagging', 'xim'); ?></h2>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Enable Tagging', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-tagging" id="wc-outfit-tagging" <?php echo (get_option('wc-outfit-tagging', 'on') ? 'checked' : ''); ?>>
								<label for="wc-outfit-tagging"><strong><?php _e('Enable Tag features', 'xim'); ?></strong></label>
								<p class="description"><?php _e('Enable product tagging option.', 'xim'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Permission', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-customer-tagging" id="wc-outfit-customer-tagging" <?php echo (get_option('wc-outfit-customer-tagging', 'on') ? 'checked' : ''); ?>>
								<label for="wc-outfit-customer-tagging"><strong><?php _e('Allow customer to add tag', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, customer will be able to add tag during submission of outfit. Requires Tag features enabled.', 'xim'); ?></p>
							</td>
						</tr>
				    </table>

				    <h2><?php _e('Image Gallery', 'xim'); ?></h2>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Cleanup Gallery', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-cleanup-gallery" id="wc-outfit-cleanup-gallery" <?php echo (get_option('wc-outfit-cleanup-gallery', 'on') ? 'checked' : ''); ?>>
								<label for="wc-outfit-cleanup-gallery"><strong><?php _e('Remove Image from gallery if submission rejected', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, attached image will be removed from gallery if a submission is rejected.', 'xim'); ?></p>
							</td>
						</tr>
				    </table>

				    <h2><?php _e('Display', 'xim'); ?></h2>

					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row"><?php _e('Position', 'xim'); ?></th>
				        	<td>
								<select name="wc-outfit-single-position" id="wc-outfit-single-position">
									<option value="woocommerce_before_single_product" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_before_single_product', true); ?>>Before Single Product</option>
									<option value="woocommerce_single_product_summary" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_single_product_summary', true); ?>>Single Product Summary</option>
									<option value="woocommerce_before_add_to_cart_form" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_before_add_to_cart_form', true); ?>>Before Add To Cart Form</option>
									<option value="woocommerce_before_add_to_cart_button" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_before_add_to_cart_button', true); ?>>Before Add To Cart Button</option>
									<option value="woocommerce_after_add_to_cart_button" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_after_add_to_cart_button', true); ?>>After Add To Cart Button</option>
									<option value="woocommerce_after_add_to_cart_form" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_after_add_to_cart_form', true); ?>>After Add To Cart Form</option>
									<option value="woocommerce_product_meta_end" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_product_meta_end', true); ?>>After Product Meta</option>
									<option value="woocommerce_after_single_product_summary" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_after_single_product_summary', true); ?>>After Single Product Summary</option>
									<option value="woocommerce_after_single_product" <?php selected(get_option('wc-outfit-single-position'), 'woocommerce_after_single_product', true); ?>>After Single Product</option>
								</select>
								<p class="description"><?php _e('Outfit listing position on single product page.', 'xim')?></p>
							</td>
				        </tr>
				    </table>

					<?php submit_button();?>
				</form>
			<?php }?>
		</div>
	<?php }

	/**
	 * Register options field.
	 *
	 * @since    1.0.0
	 */
	function register_settings() {
		register_setting('wc-outfit', 'wc-outfit-verify-submission');
		register_setting('wc-outfit', 'wc-outfit-bought-only');
		register_setting('wc-outfit', 'wc-outfit-submission-guideline');
		register_setting('wc-outfit', 'wc-outfit-tagging');
		register_setting('wc-outfit', 'wc-outfit-customer-tagging');
		register_setting('wc-outfit', 'wc-outfit-cleanup-gallery');
		register_setting('wc-outfit', 'wc-outfit-single-position');
		register_setting('wc-outfit', 'wc-outfit-page-id');
		register_setting('wc-outfit', 'wc-outfit-page-title');
		register_setting('wc-outfit', 'wc-outfit-page-slogan');
		register_setting('wc-outfit', 'wc-outfit-fb-app-id');
	}

	/**
	 * Register option page.
	 *
	 * @since    1.0.0
	 */
	function admin_menu() {
		add_options_page('Woocommerce Outfit', 'Woocommerce Outfit', 'manage_options', 'wc_outfit', array($this, 'menu_page'));
		add_action('admin_init', array($this, 'register_settings'));
	}
}