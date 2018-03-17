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
	    		<a href="?page=wc_outfit&section=outfit" class="nav-tab <?php echo $section == 'outfit' ? 'nav-tab-active' : ''; ?>"><?php _e('Outfit', 'xim'); ?></a>
	    		<a href="?page=wc_outfit&section=style-gallery" class="nav-tab <?php echo $section == 'style-gallery' ? 'nav-tab-active' : ''; ?>"><?php _e('Style Gallery', 'xim'); ?></a>
			</h2>

			<?php if ($section == 'outfit') { ?>
				<form method="post" action="options.php">
					<?php settings_fields('wc-outfit');?>
					<?php do_settings_sections('wc_outfit');?>

					<h2>Outfit Submission</h2>

					<table class="form-table">
				        <tr valign="top">
							<th scope="row"><?php _e('Verify Submission', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-verify-submission" id="wc-outfit-verify-submission" <?php echo (get_option('wc-outfit-verify-submission') ? 'checked' : ''); ?>>
								<label for="wc-outfit-verify-submission"><strong>Verify new submitted outfit</strong></label>
								<p class="description">If enabled, Admin will have to verify outfits manually and publish them.</p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Bought Product Only', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-bought-only" id="wc-outfit-bought-only" <?php echo (get_option('wc-outfit-bought-only') ? 'checked' : ''); ?>>
								<label for="wc-outfit-bought-only"><strong>Allow customer to attach bought product only</strong></label>
								<p class="description">If enabled, customer will be able to attach the products they bought.</p>
							</td>
						</tr>
					</table>

					<h2>Tagging</h2>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Enable Tagging', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-tagging" id="wc-outfit-tagging" <?php echo (get_option('wc-outfit-tagging') ? 'checked' : ''); ?>>
								<label for="wc-outfit-tagging"><strong>Enable Tag features</strong></label>
								<p class="description">Enable product tagging option.</p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e('Permission', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-customer-tagging" id="wc-outfit-customer-tagging" <?php echo (get_option('wc-outfit-customer-tagging') ? 'checked' : ''); ?>>
								<label for="wc-outfit-customer-tagging"><strong>Allow customer to add tag</strong></label>
								<p class="description">If enabled, customer will be able to add tag during submission of outfit.</p>
							</td>
						</tr>
				    </table>

				    <h2>Image Gallery</h2>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Cleanup Gallery', 'xim'); ?></th>
							<td>
								<input type="checkbox" name="wc-outfit-cleanup-gallery" id="wc-outfit-cleanup-gallery" <?php echo (get_option('wc-outfit-cleanup-gallery') ? 'checked' : ''); ?>>
								<label for="wc-outfit-cleanup-gallery"><strong>Remove Image from gallery if submission rejected</strong></label>
								<p class="description">If enabled, attached image will be removed from gallery if a submission is rejected.</p>
							</td>
						</tr>
				    </table>

					<?php submit_button();?>
				</form>
			<?php } else if ($section == 'style-gallery') { ?>
				<form method="post" action="options.php">
					<?php settings_fields('wc-outfit'); ?>
					<?php do_settings_sections('wc_outfit'); ?>

					<h2><?php _e('Style Gallery', 'xim'); ?></h2>
					
					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row">Style Gallery Page</th>
				        	<td>
								<?php wp_dropdown_pages(array('name' => 'wc-outfit-page-id', 'selected' => get_option('wc-outfit-page-id')))?>
								<p class="description"><?php _e('The base page that will be used in outfit permalinks.', 'xim')?></p>
							</td>
				        </tr>

				        <tr valign="top">
				        	<th scope="row">Thumbnail Size</th>
				        	<td>
								<input type="number" name="wc-outfit-gallery-thumb-w" id="wc-outfit-gallery-thumb-w" class="small-text" value="<?php echo get_option('wc-outfit-gallery-thumb-w', 480); ?>">
								<label for="mailserver_port"><?php _e('x', 'xim'); ?></label>
								<input type="number" name="wc-outfit-gallery-thumb-h" id="wc-outfit-gallery-thumb-h" class="small-text" value="<?php echo get_option('wc-outfit-gallery-thumb-h', 720); ?>">
								<p class="description"><?php _e('Updating the settings will take effect for newly uploaded images.', 'xim')?></p>
							</td>
				        </tr>
				    </table>
					
					<?php submit_button();?>
				</form>
			<?php } else { ?>				
				<form method="post" action="options.php">
					<?php settings_fields('wc-outfit'); ?>
					<?php do_settings_sections('wc_outfit'); ?>

					<h2><?php _e('Display', 'xim'); ?></h2>

					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row">Position</th>
				        	<td>
								<select name="wc-outfit-single-position" id="wc-outfit-single-position">
									<option value="woocommerce_before_single_product" <?php selected(get_option('wc-outfit-single-position')); ?>>Before Single Product</option>
									<option value="woocommerce_single_product_summary" <?php selected(get_option('wc-outfit-single-position')); ?>>Single Product Summary</option>
									<option value="woocommerce_before_add_to_cart_form" <?php selected(get_option('wc-outfit-single-position')); ?>>Before Add To Cart Form</option>
									<option value="woocommerce_before_add_to_cart_button" <?php selected(get_option('wc-outfit-single-position')); ?>>Before Add To Cart Button</option>
									<option value="woocommerce_after_add_to_cart_button" <?php selected(get_option('wc-outfit-single-position')); ?>>After Add To Cart Button</option>
									<option value="woocommerce_after_add_to_cart_form" <?php selected(get_option('wc-outfit-single-position')); ?>>After Add To Cart Form</option>
									<option value="woocommerce_product_meta_end" <?php selected(get_option('wc-outfit-single-position')); ?>>After Product Meta</option>
									<option value="woocommerce_after_single_product_summary" <?php selected(get_option('wc-outfit-single-position')); ?>>After Single Product Summary</option>
									<option value="woocommerce_after_single_product" <?php selected(get_option('wc-outfit-single-position')); ?>>After Single Product</option>
								</select>
								<p class="description"><?php _e('Outfit listing position on single product page.', 'xim')?></p>
							</td>
				        </tr>
				    </table>

					<h2><?php _e('API Keys', 'xim'); ?></h2>
					<p><?php _e('This will be used for sharing photos on social media.', 'xim'); ?></p>

					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row">Facebook App ID</th>
				        	<td>
								<input type="text" name="wc-outfit-fb-app-id" id="wc-outfit-fb-app-id" value="<?php echo get_option('wc-outfit-fb-app-id'); ?>">
								<p class="description"><?php _e('Get your Facebook app id from <a href="https://developers.facebook.com/" target="_blank">here</a>', 'xim')?></p>
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
		register_setting('wc-outfit', 'wc-outfit-single-position');
		register_setting('wc-outfit', 'wc-outfit-fb-app-id');
		register_setting('wc-outfit', 'wc-outfit-verify-submission');
		register_setting('wc-outfit', 'wc-outfit-bought-only');
		register_setting('wc-outfit', 'wc-outfit-tagging');
		register_setting('wc-outfit', 'wc-outfit-customer-tagging');
		register_setting('wc-outfit', 'wc-outfit-cleanup-gallery');
		register_setting('wc-outfit', 'wc-outfit-page-id');
		register_setting('wc-outfit', 'wc-outfit-gallery-thumb-w');
		register_setting('wc-outfit', 'wc-outfit-gallery-thumb-h');
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