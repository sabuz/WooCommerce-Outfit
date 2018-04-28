<?php

namespace Xim_Woo_Outfit\Traits;

use WP_Query;

trait Template {
	/**
	 * Outfits shortcode.
	 *
	 * @since    1.0.0
	 */
	function template_outfits() {
		$query = new WP_Query(array(
			'post_type' => 'outfit',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'author' => get_current_user_id(),
		));

		$html = '';

		if (isset($_COOKIE['wc_outfit_success']) && $_COOKIE['wc_outfit_success'] == 'true') {
			$html .= '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">' . __('Outfit submitted successfully.', 'xim') . '</div>

			<script>
				document.cookie="wc_outfit_success=;expires=expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/"
			</script>';
		}

		if ($query->have_posts()) {
			$html .= '<p>' . sprintf(__('To add more outfit photos, go to <a href="%1$s">new outfit</a>', 'xim'), esc_url(wc_get_endpoint_url('outfits/new-outfit'))) . '</p>

			<table class="table">
				<thead>
					<tr>
						<th>' . __('ID', 'xim') . '</th>
						<th>' . __('Title', 'xim') . '</th>
						<th>' . __('Date', 'xim') . '</th>
						<th>' . __('Status', 'xim') . '</th>
					</tr>
				</thead>

				<tbody>';
					while ($query->have_posts()) {
						$query->the_post();
						$html .= '<tr>
							<td>' . get_the_ID() . '</td>
							<td>' . the_title('', '', false) . '</td>
							<td>' . get_the_date() . '</td>
							<td>' . ucfirst(get_post_status()) . '</td>
						</tr>';
					}
					
					wp_reset_postdata();

				$html .= '</tbody>
			</table>';
		} else {
			$html .= '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="' . esc_url(wc_get_endpoint_url('outfits/new-outfit')) . '">' . __('Add new', 'xim') . '</a>
				' . __('No outfits available yet.', 'xim') . '
			</div>';
		}

		return $html;
	}

	/**
	 * New outfit shortcode.
	 *
	 * @since    1.0.0
	 */
	function template_new_outfit($atts, $content = null) {
		// enqueue styles
		wp_enqueue_style('wc-outfit-icon');
		wp_enqueue_style('bootstrap');
		wp_enqueue_style('select2');
		wp_enqueue_style('new-outfit');

		// enqueue scripts
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('jquery-validate');
		wp_enqueue_script('select2');
		wp_enqueue_script('new-outfit');

		$html = '<form id="new-outfit-form" method="post">
			<div class="form-group">
				<label>' . __('Outfit Image', 'xim') . '</label>

				<div class="row">
					<div class="col-sm-12">
 						<input type="file" name="thumb" id="thumb">
 						<span class="wc-outfit-icon wc-outfit-icon-question-circle" data-toggle="popover" data-placement="left" data-content="' . get_option('wc-outfit-submission-guideline') . '"></span>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label>' . __('Used Products', 'xim') . '</label>

				<div class="selected-products empty">
					<div class="row">

					</div>
				</div>
				
				<input type="hidden" name="ids" id="ids" value="">
			</div>

			<div class="form-group">
				<div class="select-cat-wrap">
					<select class="select-cat">
						<option></option>';
						foreach ($this->get_product_cats() as $cat) {
							$html .= '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
						}
					$html .= '</select>
				</div>

				<div class="product-list">

				</div>

				<div class="product-nav hidden">
					<a href="#" class="prev" data-page="0">&lt; Prev</a>
					<a href="#" class="next" data-page="0">Next &gt;</a>
				</div>
			</div>';

			$html .= '<input type="submit" id="submit" value="' . __('Add Outfit', 'xim') . '">
		</form>';

		return $html;
	}

	/**
	 * Single product carousel.
	 *
	 * @since    1.0.0
	 */
	function template_single_product_listing() {
		global $post;
		// enqueue styles
		wp_enqueue_style('wc-outfit-icon');
		wp_enqueue_style('bootstrap');
		wp_enqueue_style('owl-carousel');
		wp_enqueue_style('outfit-modal');
		wp_enqueue_style('single-product');

		// enqueue scripts
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('owl-carousel');
		wp_enqueue_script('single-product');

		$query = new WP_Query(array(
			'post_type' => 'outfit',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'products',
					'value' => $post->ID,
					'compare' => 'LIKE',
				),
			),
		));

		if ($query->have_posts()) {
			echo '<div class="wc-outfit-single-carousel">
				<h3>' . __('Outfit Photos', 'xim') . '</h3>

				<div class="owl-carousel">';
				while ($query->have_posts()) {
					$query->the_post();
					
					echo '<div class="wc-outfit-gallery-item" data-id="' . $query->post->ID . '">
						<div class="wc-outfit-gallery-item-inner-wrap">
							<img src="' . $this->get_outfit_thumbnail($query->post->ID, 'wc-outfit-single-listing') . '" class="wc-outfit-gallery-item-thumb" />

							<div class="wc-outfit-gallery-item-footer clearfix">
								<div class="pull-left">
									<a class="wc-outfit-meta-author" href="#">
										' . ucwords(get_the_author_meta('display_name')) . '</a>
									<p class="wc-outfit-meta-time">' . $this->outfit_posted_ago() . '</p>
								</div>
								<div class="pull-right">
									' . $this->like_button_html($query->post->ID) . '
								</div>
							</div>
						</div>
					</div>';
				}

				wp_reset_postdata();
				
				echo '</div>
			</div>';
		}
	}
}