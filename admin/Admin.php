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
			wp_die(__('You do not have sufficient permissions to access this page.', 'xim'));
		}?>

		<div class="wrap">
			<?php if (isset($_GET['section'])) {
				$section = $_GET['section'];
			} else {
				$section = 'general';
			} ?>

			<h2 class="nav-tab-wrapper">
	    		<a href="?page=woo-outfit&section=general" class="nav-tab <?php echo $section == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'xim'); ?></a>
	    		<a href="?page=woo-outfit&section=style-gallery" class="nav-tab <?php echo $section == 'style-gallery' ? 'nav-tab-active' : ''; ?>"><?php _e('Style Gallery', 'xim'); ?></a>
	    		<a href="?page=woo-outfit&section=api" class="nav-tab <?php echo $section == 'api' ? 'nav-tab-active' : ''; ?>"><?php _e('API Keys', 'xim'); ?></a>
			</h2>

			
			<?php if ($section == 'style-gallery') { ?>
				<form method="post" action="options.php">
					<?php settings_fields('woo-outfit-option-group-style-gallery'); ?>
					<?php do_settings_sections('woo-outfit-option-group-style-gallery'); ?>

					<h2><?php _e('Style Gallery', 'xim'); ?></h2>
					
					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row"><?php _e('Style Gallery Page', 'xim'); ?></th>
				        	<td>
								<?php wp_dropdown_pages(array('name' => 'woo-outfit-page-id', 'selected' => get_option('woo-outfit-page-id')))?>
								<p class="description"><?php _e('The base page that will be used in outfit permalinks.', 'xim')?></p>
							</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><?php _e('Page Title', 'xim'); ?></th>
				        	<td>
								<input type="text" name="woo-outfit-page-title" id="woo-outfit-page-title" value="<?php echo get_option('woo-outfit-page-title'); ?>" placeholder="<?php _e('Style Gallery', 'xim'); ?>">
							</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><?php _e('Page Subtitle/Slogan', 'xim'); ?></th>
				        	<td>
								<input type="text" name="woo-outfit-page-slogan" id="woo-outfit-page-slogan" value="<?php echo get_option('woo-outfit-page-slogan'); ?>" placeholder="<?php _e('Inspire and Admire', 'xim'); ?>">
							</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><?php _e('Posts Per Query', 'xim'); ?></th>
				        	<td>
								<input type="number" name="woo-outfit-ppq" id="woo-outfit-ppq" value="<?php echo get_option('woo-outfit-ppq', 9); ?>" placeholder="9">
								<p class="description"><?php _e('Number of outfit to load in each request.', 'xim')?></p>
							</td>
				        </tr>
				    </table>
					
					<?php submit_button();?>
				</form>
			<?php } else if ($section == 'api') { ?>
				<form method="post" action="options.php">
					<?php settings_fields('woo-outfit-option-group-api');?>
					<?php do_settings_sections('woo-outfit-option-group-api');?>

					<h2><?php _e('API Keys', 'xim'); ?></h2>
					<p><?php _e('This will be used for sharing outfit on social media.', 'xim'); ?></p>

					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row"><?php _e('Facebook App ID', 'xim'); ?></th>
				        	<td>
								<input type="text" name="woo-outfit-fb-app-id" id="woo-outfit-fb-app-id" value="<?php echo get_option('woo-outfit-fb-app-id'); ?>">
								<p class="description"><?php _e('Get your Facebook app id from <a href="https://developers.facebook.com/" target="_blank">here</a>', 'xim')?></p>
							</td>
				        </tr>
				    </table>					

					<?php submit_button();?>
				</form>
			<?php } else { ?>				
				<form method="post" action="options.php">
					<?php settings_fields('woo-outfit-option-group-general'); ?>
					<?php do_settings_sections('woo-outfit-option-group-general'); ?>

				    <h2><?php _e('Outfit Submission', 'xim'); ?></h2>

					<table class="form-table">
				        <tr valign="top">
							<th scope="row"><?php _e('Verify Submission', 'xim'); ?></th>

							<td>
								<input type="checkbox" name="woo-outfit-verify-submission" id="woo-outfit-verify-submission" <?php echo (get_option('woo-outfit-verify-submission', 'on') ? 'checked' : ''); ?>>
								<label for="woo-outfit-verify-submission"><strong><?php _e('Verify new submitted outfit', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, Admin will have to verify outfits manually and publish them.', 'xim'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Bought Product Only', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="woo-outfit-bought-only" id="woo-outfit-bought-only" <?php echo (get_option('woo-outfit-bought-only', 'on') ? 'checked' : ''); ?>>
								<label for="woo-outfit-bought-only"><strong><?php _e('Allow customer to attach bought product only', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, customer will be able to attach the products they bought.', 'xim'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Submission Guideline', 'xim'); ?></th>

							<td>
								<?php wp_editor(get_option('woo-outfit-submission-guideline'), 'woo-outfit-submission-guideline', array('wpautop' => false, 'media_buttons' => false, 'teeny' => true, 'textarea_rows' => 8)); ?>
								<p class="description"><?php _e('Add guideline for uploading outfit photos.', 'xim'); ?></p>
							</td>
						</tr>
					</table>

					<h2><?php _e('Tagging', 'xim'); ?></h2>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Enable Tagging', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="woo-outfit-tagging" id="woo-outfit-tagging" <?php echo (get_option('woo-outfit-tagging', 'on') ? 'checked' : ''); ?>>
								<label for="woo-outfit-tagging"><strong><?php _e('Enable Tag features', 'xim'); ?></strong></label>
								<p class="description"><?php _e('Enable product tagging option.', 'xim'); ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Permission', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="woo-outfit-customer-tagging" id="woo-outfit-customer-tagging" <?php echo (get_option('woo-outfit-customer-tagging', 'on') ? 'checked' : ''); ?>>
								<label for="woo-outfit-customer-tagging"><strong><?php _e('Allow customer to add tag', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, customer will be able to add tag during submission of outfit. Requires Tag features enabled.', 'xim'); ?></p>
							</td>
						</tr>
				    </table>

				    <h2><?php _e('Image Gallery', 'xim'); ?></h2>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Cleanup Gallery', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="woo-outfit-cleanup-gallery" id="woo-outfit-cleanup-gallery" <?php echo (get_option('woo-outfit-cleanup-gallery', 'on') ? 'checked' : ''); ?>>
								<label for="woo-outfit-cleanup-gallery"><strong><?php _e('Remove Image from gallery if submission rejected', 'xim'); ?></strong></label>
								<p class="description"><?php _e('If enabled, attached image will be removed from gallery if a submission is rejected.', 'xim'); ?></p>
							</td>
						</tr>
				    </table>

				    <h2><?php _e('Display', 'xim'); ?></h2>

					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row"><?php _e('Position', 'xim'); ?></th>
				        	<td>
				        		<?php $single_position = get_option('woo-outfit-single-position', 'woocommerce_after_single_product_summary'); ?>
								<select name="woo-outfit-single-position" id="woo-outfit-single-position">
									<option value="woocommerce_before_single_product" <?php selected($single_position, 'woocommerce_before_single_product', true); ?>>Before Single Product</option>
									<option value="woocommerce_single_product_summary" <?php selected($single_position, 'woocommerce_single_product_summary', true); ?>>Single Product Summary</option>
									<option value="woocommerce_before_add_to_cart_form" <?php selected($single_position, 'woocommerce_before_add_to_cart_form', true); ?>>Before Add To Cart Form</option>
									<option value="woocommerce_before_add_to_cart_button" <?php selected($single_position, 'woocommerce_before_add_to_cart_button', true); ?>>Before Add To Cart Button</option>
									<option value="woocommerce_after_add_to_cart_button" <?php selected($single_position, 'woocommerce_after_add_to_cart_button', true); ?>>After Add To Cart Button</option>
									<option value="woocommerce_after_add_to_cart_form" <?php selected($single_position, 'woocommerce_after_add_to_cart_form', true); ?>>After Add To Cart Form</option>
									<option value="woocommerce_product_meta_end" <?php selected($single_position, 'woocommerce_product_meta_end', true); ?>>After Product Meta</option>
									<option value="woocommerce_after_single_product_summary" <?php selected($single_position, 'woocommerce_after_single_product_summary', true); ?>>After Single Product Summary</option>
									<option value="woocommerce_after_single_product" <?php selected($single_position, 'woocommerce_after_single_product', true); ?>>After Single Product</option>
								</select>
								<p class="description"><?php _e('Outfit listing position on single product page.', 'xim')?></p>
							</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row"><?php _e('Item To Show', 'xim'); ?></th>
				        	<td>
								<input type="number" name="woo-outfit-single-num-item" id="woo-outfit-single-num-item" value="<?php echo get_option('woo-outfit-single-num-item', 4); ?>" placeholder="4">
								<p class="description"><?php _e('Number of item to show in a screen.', 'xim')?></p>
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
		register_setting('woo-outfit-option-group-general', 'woo-outfit-verify-submission');
		register_setting('woo-outfit-option-group-general', 'woo-outfit-bought-only');
		register_setting('woo-outfit-option-group-general', 'woo-outfit-submission-guideline');
		register_setting('woo-outfit-option-group-general', 'woo-outfit-tagging');
		register_setting('woo-outfit-option-group-general', 'woo-outfit-customer-tagging');
		register_setting('woo-outfit-option-group-general', 'woo-outfit-cleanup-gallery');
		register_setting('woo-outfit-option-group-general', 'woo-outfit-single-position');
		register_setting('woo-outfit-option-group-general', 'woo-outfit-single-num-item');
		register_setting('woo-outfit-option-group-style-gallery', 'woo-outfit-page-id');
		register_setting('woo-outfit-option-group-style-gallery', 'woo-outfit-page-title');
		register_setting('woo-outfit-option-group-style-gallery', 'woo-outfit-page-slogan');
		register_setting('woo-outfit-option-group-style-gallery', 'woo-outfit-ppq');
		register_setting('woo-outfit-option-group-api', 'woo-outfit-fb-app-id');
	}

	/**
	 * Register option page.
	 *
	 * @since    1.0.0
	 */
	function admin_menu() {
		add_options_page('Woocommerce Outfit', 'Woocommerce Outfit', 'manage_options', 'woo-outfit', array($this, 'menu_page'));
		add_action('admin_init', array($this, 'register_settings'));
	}
}