<?php

namespace Woocommerce_Outfit\Traits;

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
			wp_enqueue_style('woo-outfit-metabox', plugin_dir_url(__FILE__) . '../assets/css/metabox.css');

			// js
			wp_enqueue_script('select2', plugin_dir_url(__FILE__) . '../assets/js/select2.min.js', array(), false, true);
			wp_enqueue_script('woo-outfit-metabox', plugin_dir_url(__FILE__) . '../assets/js/metabox.js', array(), false, true);
			wp_localize_script('woo-outfit-metabox', 'woo_outfit_tr_obj', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('woo_outfit_nonce')));
		}
	}

	/**
	 * Register outfit meta boxes.
	 *
	 * @since    1.0.0
	 */
	function register_meta_boxes() {
		add_meta_box('woo-outfit-hooked-products', __('Used Products', 'woo-outfit'), array($this, 'mb_hooked_products_callback'), 'outfit');
		add_meta_box('woo-outfit-featured', __('Featured Post', 'woo-outfit'), array($this, 'mb_featured_products_callback'), 'outfit', 'side');
	}

	/**
	 * Hooked products metabox callback.
	 *
	 * @since    1.0.0
	 */
	function mb_hooked_products_callback($post) {
		$products = get_post_meta($post->ID, 'products', true);

		echo wp_nonce_field('woo_outfit_meta_box_nonce', 'woo_outfit_meta_box_nonce');
		echo '<div class="woo-outfit-mb">
			<div class="selected-products">
				<div class="row has-col">';
				if (!empty($products)) {
					foreach (json_decode($products) as $product) {
						echo '<div class="col">
							<div class="item">
								<img src="' . esc_url($this->get_outfit_thumbnail(intval($product->id), 'product-thumb')) . '">
								<a href="#" class="close" data-id="' . intval($product->id) . '"></a>
								<a href="#" class="switch ' . ($product->labels == 1 ? 'active' : 'inactive') . '" data-id="' . intval($product->id) . '"></a>
							</div>
						</div>';
					}
				}
				echo '</div>
				<input type="hidden" name="ids" class="ids" value=' . esc_textarea($products) . '>
			</div>

			<div class="select-cat-wrap">
				<select class="select-cat">
					<option></option>';
					foreach ($this->get_product_cats() as $cat) {
						echo '<option value="' . intval($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
					}
				echo '</select>
			</div>

			<div class="product-list"></div>

			<div class="product-nav hidden">
				<a href="#" class="prev" data-page="0">&lt; Prev</a>
				<a href="#" class="next" data-page="0">Next &gt;</a>
			</div>
		</div>';
	}

	/**
	 * Featured product metabox callback.
	 *
	 * @since    1.0.0
	 */
	function mb_featured_products_callback($post) {
		wp_nonce_field('woo_outfit_meta_box_nonce', 'woo_outfit_meta_box_nonce');

		$value = get_post_meta($post->ID, 'featured', true);

		echo '<div class="woo-outfit-mb">
			<label for="featured">
				<input type="checkbox" name="featured" id="featured" ' . (!empty($value) ? "checked" : "") . '/>
				' . __('Make this post featured?', 'woo-outfit') . '
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

		if (!isset($_POST['woo_outfit_meta_box_nonce']) || !wp_verify_nonce($_POST['woo_outfit_meta_box_nonce'], 'woo_outfit_meta_box_nonce')) {
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
				update_post_meta($post->ID, 'products', strval($_POST['ids']));
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