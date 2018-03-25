<?php

namespace Xim_Woo_Outfit\Traits;

trait Metabox {

	/**
	 * Load the required assets in wp-admin for this plugin.
	 *
	 * @since    1.0.0
	 */
	function admin_enqueue_scripts() {
		global $post_type;

		if ($post_type == 'outfit') {
			// css
			wp_enqueue_style('select2', plugin_dir_url(__FILE__) . '../assets/css/select2.min.css');
			wp_enqueue_style('metabox', plugin_dir_url(__FILE__) . '../assets/css/metabox.css');

			// js
			wp_enqueue_script('select2', plugin_dir_url(__FILE__) . '../assets/js/select2.min.js', array(), false, true);
			wp_enqueue_script('metabox', plugin_dir_url(__FILE__) . '../assets/js/metabox.js', array(), false, true);
			wp_localize_script('metabox', 'object', ['ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('wc_outfit_nonce')]);
		}
	}

	/**
	 * Register outfit meta boxes.
	 *
	 * @since    1.0.0
	 */
	function register_meta_boxes() {
		add_meta_box('wc-outfit-hooked-products', __('Used Products', 'xim'), array($this, 'mb_hooked_products_callback'), 'outfit');
		add_meta_box('wc-outfit-featured', __('Featured Post', 'xim'), array($this, 'mb_featured_products_callback'), 'outfit', 'side');
	}

	/**
	 * Hooked products metabox callback.
	 *
	 * @since    1.0.0
	 */
	function mb_hooked_products_callback($post) {
		$products = get_post_meta($post->ID, 'products', true);

		$content = '';
		$content .= wp_nonce_field('wc_outfit_meta_box_nonce', 'wc_outfit_meta_box_nonce');
		$content .= '<div class="wc-outfit-mb">
			<div class="selected-products">
				<div class="row">';
				if (!empty($products)) {
					foreach (json_decode($products) as $product) {
						$content .= '<div class="col-4">
							<img src="' . $this->get_outfit_thumbnail($product->id, 'product-thumb') . '">
							<a class="close" data-id="' . $product->id . '"></a>
							<span class="switch ' . ($product->labels == 1 ? 'active' : 'inactive') . '" data-id="' . $product->id . '"></span>
						</div>';
					}
				}
				$content .= '</div>
			</div>

			<div class="row">
				<div class="col-1">
					<select class="select-cat">
						<option></option>';
						foreach ($this->get_product_cats() as $cat) {
							$content .= '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
						}
					$content .= '</select>
				</div>
			</div>

			<div class="row">
				<div class="product-list">

				</div>
			</div>

			<input type="hidden" name="ids" id="ids" value=' . $products . '>
		</div>';

		echo $content;
	}

	/**
	 * Featured product metabox callback.
	 *
	 * @since    1.0.0
	 */
	function mb_featured_products_callback($post) {
		wp_nonce_field('wc_outfit_meta_box_nonce', 'wc_outfit_meta_box_nonce');

		$value = get_post_meta($post->ID, 'featured', true);

		echo '<div class="wc-outfit-mb">
			<label for="featured">
				<input type="checkbox" name="featured" id="featured" ' . (!empty($value) ? "checked" : "") . '/>
				' . __('Make this post featured ?', 'xim') . '
			</label>
		</div>';
	}

	/**
	 * Update post meta on save.
	 *
	 * @since    1.0.0
	 */
	function update_meta_on_submit($post_id, $post) {
		global $post_type;

		if (!isset($_POST['wc_outfit_meta_box_nonce']) || !wp_verify_nonce($_POST['wc_outfit_meta_box_nonce'], 'wc_outfit_meta_box_nonce')) {
			return;
		}

		if (wp_is_post_revision($post_id)) {
			return;
		}

		if ($post->post_type == 'outfit') {
			if (isset($_POST['featured'])) {
				update_post_meta($post->ID, 'featured', 'yes');
			} else {
				update_post_meta($post->ID, 'featured', '');
			}

			if (isset($_POST['ids'])) {
				update_post_meta($post->ID, 'products', $_POST['ids']);
			}
		}
	}

	/**
	 * Remove default metabox for thumb and init a new one.
	 *
	 * @since    1.0.0
	 */
	function re_init_thumb_box() {
		remove_meta_box('postimagediv', 'outfit', 'side');
		add_meta_box('postimagediv', __('Outfit Photo'), 'post_thumbnail_meta_box', 'outfit', 'normal', 'low');
	}

}