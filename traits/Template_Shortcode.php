<?php

namespace Xim_Woo_Outfit\Traits;

use WP_Query;

trait Template_Shortcode {
	// use Helper;

	public function new_outfit_shortcode($atts, $content = null) {
		wp_enqueue_style('bootstrapValidator');
		wp_enqueue_script('bootstrapValidator');
		wp_enqueue_script('filepicker');
		wp_enqueue_script('new-outfit');

		$atts = shortcode_atts(array(), $atts);

		$html = '<form id="newOutfitForm" method="post" enctype="multipart/form-data">
			<div class="form-group">
				<label for="thumb">' . __('Outfit Image', 'couture') . '</label>

				<div class="row">
					<div class="col-sm-8">
						<input data-label="Select Image" class="filepicker" type="file" name="thumb" multiple="false" accept="image/*">
					</div>
					<div class="col-sm-4">
						<h4>Photo Guidelines</h4>
						<ul>
							<li>Lorem ipsum dolor set amet</li>
							<li>Lorem ipsum dolor set amet</li>
							<li>Lorem ipsum dolor set amet</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label>' . __('Used Products:', 'couture') . '</label>

				<div class="chosen">
					<div class="row"></div>
				</div>
				<input type="hidden" name="ids" id="ids" value="">
			</div>

			<div class="form-group">
				<select class="selectId">
					<option selected disabled>Choose a category</option>';
		foreach ($this->wc_outfit_product_cats() as $cat):
			$html .= '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
		endforeach;
		$html .= '</select>

				<div class="row">
					<div id="products" class="products"></div>
				</div>
			</div>';

		$html .= wp_nonce_field('post_nonce', 'post_nonce_field') . '
		    <input type="submit" value="' . __('Add Outfit', 'couture') . '">
		</form>';

		return $html;
	}

	public function all_outfit_shortcode() {
		$html = '<table class="table table-bordered">
		<thead>
			<tr>
				<th><span class="nobr">' . __('ID', 'couture') . '</span></th>
				<th><span class="nobr">' . __('Date', 'couture') . '</span></th>
				<th><span class="nobr">' . __('Status', 'couture') . '</span></th>
				<th><span class="nobr">' . __('Title', 'couture') . '</span></th>
			</tr>
		</thead>';
		$args = array(
			'post_type' => 'outfit',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'author' => get_current_user_id(),
		);

		$query = new WP_Query($args);

		$html .= '<tbody>';

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$html .= '<tr>
					<td>' . get_the_ID() . '</td>
					<td>' . get_the_date() . '</td>
					<td>' . get_post_status() . '</td>
					<td>' . the_title('', '', false) . '</td>
				</tr>';
			}
		} else {
			$html .= '<tr>
				<td colspan="4" class="text-center">
					' . __('There is no outfit yet.', 'couture') . '
				</td>
			</tr>';
		}

		wp_reset_postdata();

		$html .= '</tbody></table>';

		return $html;
	}

	public function style_gallery_shortcode() {
		global $post;

		wp_enqueue_script('arctext');
		wp_enqueue_script('infinite');
		wp_enqueue_script('imgLoaded');
		wp_enqueue_script('isotope');
		wp_enqueue_script('style-gallery');

		//page-header start
		if (isset($_GET['cat'])) {
			$catData = get_term_by('slug', $_GET['cat'], 'outfit_cats');

			echo '<h4 class="page-subtitle">' . ucwords($catData->name) . '</h4>';
			echo '<div class="page-bar">';
			echo '<strong>' . __('BE THE STYLIST: ', 'couture') . '</strong>' . __('Make it work at the office with posh professional styles!', 'couture');
			echo '</div>';
		} elseif (isset($_GET['user'])) {
			$author_data = $this->wc_outfit_get_author_data($_GET['user']);

			echo '<h4 class="page-subtitle">' . $author_data['nickname'][0] . '</h4>';

			echo '<div class="tab-btn">';
			if (isset($_GET['page'])) {
				$current = $_GET['page'];
			} else {
				$current = 'photos';
			}
			echo '<a href="' . $this->user_gallery_link_by_id($_GET['user']) . '" class="' . ($current == 'photos' ? 'active' : '') . '">Photos</a>';
			echo '<a href="' . $this->user_gallery_likes_by_id($_GET['user']) . '" class="' . ($current == 'likes' ? 'active' : '') . '">Liked Looks</a>';
			echo '<a href="#" class="follower" data-user="' . $_GET['user'] . '" data-toggle="modal" data-target="#fanModal">' . $this->wc_outfit_get_follower_count($_GET['user']) . ' Followers</a>';
			echo '<a href="#" class="following" data-user="' . $_GET['user'] . '" data-toggle="modal" data-target="#fanModal">' . $this->wc_outfit_get_following_count($_GET['user']) . ' Following</a>';
			echo '</div>';
		} else {
			echo '<h4 class="page-subtitle">' . __('Inspire and Admire', 'couture') . '</h4>';

			echo '<div class="page-bar">';
			echo '<strong>' . __('BE THE STYLIST: ', 'couture') . '</strong>' . __('Make it work at the office with posh professional styles!', 'couture');
			echo '</div>';

			echo '<div class="tab-btn">';
			if (isset($_GET['page'])) {
				$current = $_GET['page'];
			} else {
				$current = 'all';
			}

			echo '<div class="filter">';
			echo '<a href="' . home_url('style-gallery') . '" class="' . ($current == 'all' ? 'active' : '') . '">All</a>';
			if (is_user_logged_in()) {
				echo '<a href="' . home_url('style-gallery/?page=following') . '" class="' . ($current == 'following' ? 'active' : '') . '">Following</a>';
			} else {
				echo '<a href="#" data-toggle="modal" data-target="#loginModal">Following</a>';
			}
			echo '<a href="' . home_url('style-gallery/?page=feat') . '" class="' . ($current == 'feat' ? 'active' : '') . '">Featured</a>';
			echo '</div>';
			echo '</div>';
		}

		if (isset($_GET['user'])) {
			if ($_GET['user'] != get_current_user_id()) {
				echo '<a href="#" class="medal medal-big" data-id="' . $_GET['user'] . '">';
				echo '<span><strong>';
				if ($this->wc_outfit_is_following($_GET['user'])) {
					echo 'Unfollow';
				} else {
					echo 'Follow';
				}
				echo '</strong></span>';
				echo '</a>';
			} else {
				if (is_user_logged_in()) {
					echo '<a href="' . esc_url(wc_get_endpoint_url('new-outfit', '', wc_get_page_permalink('myaccount'))) . '" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
				} else {
					echo '<a href="#" data-toggle="modal" data-target="#loginModal" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
				}
			}
		} else {
			if (is_user_logged_in()) {
				echo '<a href="' . esc_url(wc_get_endpoint_url('new-outfit', '', wc_get_page_permalink('myaccount'))) . '" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
			} else {
				echo '<a href="#" data-toggle="modal" data-target="#loginModal" class="medal medal-big" data-id="#"><span><strong>Add Your Photo</strong></span></a>';
			}
		}
		//page-header end

		// Content begin
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

		if (isset($_GET['user'])) {
			if (@$_GET['page'] == 'likes') {
				$ids = $this->wc_outfit_get_likes_by_user($_GET['user']);
				if (!empty($ids)) {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => 9,
						'order' => 'desc',
						'post__in' => $ids,
						'paged' => $paged,
					);
				} else {
					$args = array();
				}

			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'author' => $_GET['user'],
					'paged' => $paged,
				);
			}
		} elseif (isset($_GET['cat'])) {
			$args = array(
				'post_type' => 'outfit',
				'post_status' => 'publish',
				'posts_per_page' => 9,
				'order' => 'desc',
				'tax_query' => array(
					array(
						'taxonomy' => 'outfit_cats',
						'field' => 'slug',
						'terms' => $_GET['cat'],
					),
				),
				'paged' => $paged,
			);
		} else {
			if (isset($_GET['page'])) {
				if ($_GET['page'] == 'feat') {
					$args = array(
						'post_type' => 'outfit',
						'post_status' => 'publish',
						'posts_per_page' => 9,
						'order' => 'desc',
						'meta_query' => array(
							array(
								'key' => 'featured',
								'value' => 'yes',
							),
						),
						'paged' => $paged,
					);
				} elseif ($_GET['page'] == 'following') {
					if (is_user_logged_in()) {
						$data = get_user_meta(get_current_user_id(), 'following', true);

						if (!empty($data)) {
							$args = array(
								'post_type' => 'outfit',
								'post_status' => 'publish',
								'posts_per_page' => 9,
								'order' => 'desc',
								'author__in' => $data,
								'paged' => $paged,
							);
						} else {
							$args = array();
						}

					} else {
						wp_redirect(home_url('my-account'));
					}
				}
			} else {
				$args = array(
					'post_type' => 'outfit',
					'post_status' => 'publish',
					'posts_per_page' => 9,
					'order' => 'desc',
					'paged' => $paged,
				);
			}
		}

		$query = new WP_Query($args);

		echo '<div class="row">';
		if ($query->have_posts()) {
			echo '<div class="grid">';
			while ($query->have_posts()): $query->the_post();
				$author_data = $this->wc_outfit_get_post_author_data($post->ID);
				echo '<div class="grid-item col-sm-4" data-id="' . $post->ID . '">
						<div class="gal-header">
							<div class="gal-product clearfix">
								<ul>
									' . $this->wc_outfit_hooked_product($post->ID, 4) . '
								</ul>
							</div>
							<a class="gal-thumb clearfix">
								<img src="' . $this->wc_outfit_post_thumb_by_id($post->ID) . '" class="gal-img" />
							</a>
						</div>
						<div class="gal-footer clearfix">
							<div class="pull-left">
								<a class="author" href="' . $this->user_gallery_link_by_id(get_the_author_meta('ID')) . '">
									' . $author_data['nickname'][0] . '</a>
								<span class="time">' . $this->wc_outfit_time_ago() . '</span>
							</div>
							<div class="pull-right">
								<div class="gal-bubble">
									<a href="#" class="bubble-btn"><i class="fa fa-share"></i></a>

									<div class="bubble-content">
										' . $this->wc_outfit_outfit_share_button($post->ID) . '
									</div>
								</div>

								' . $this->wc_outfit_post_like_button($post->ID) . '
							</div>
						</div>
					</div>';
			endwhile;
			echo '</div>';

			// echo '<div class="more">
			// 	<button class="button" data-current="1" data-max="'. $query->max_num_pages .'"
			// 		' (isset($_GET['order']) ? 'data-order=' . $_GET['order'] : '')
			// 		(isset($_GET['user']) ? 'data-user=' . $_GET['user']: '')
			// 		(isset($_GET['page'])? 'data-page=' . $_GET['page'] : '')
			// 		(isset($_GET['cat']) ? 'data-cat=' . $_GET['cat'] : '') .'>Load More</button>
			// </div>';
		} else {
			if (@$_GET['page'] == 'following') {
				echo '<div class="not-following">
				<div class="col-sm-12">
					<p>' . __('You aren\'t following anyone -- let\'s fix that!') . '</p>
					<p>' . __('Start Following Some Style Gallery Stars') . '</p>
				</div>

				<div class="col-sm-12">
					<div class="row">';
				get_template_part('inc/not-following');
				echo '</div>
				</div>
			</div>';
			}
		}

		wp_reset_postdata();

		echo '</div>';

		// User modal
		if (isset($_GET['user'])) {
			$author_data = $this->wc_outfit_get_author_data($_GET['user']);
			echo '<div class="modal fade" id="fanModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="modalLabel">
								' . $author_data['nickname'][0] . '
							</h4>
						</div>
						<div class="modal-body">
							<ul class="list-group">
							</ul>
						</div>
					</div>
				</div>
			</div>';
		}

		// Product modal
		if (isset($_GET['view'])) {
			$author = $this->wc_outfit_get_post_author($_GET['view']);
			$author_data = $this->wc_outfit_get_author_data($author);

			echo '<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-body clearfix">
						<div class="thumb">
							<img src="' . $this->wc_outfit_post_thumb_by_id($_GET['view']) . '" />
						</div>

						<div class="details">
							<div class="author clearfix">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>

								<a class="name" href="' . $this->user_gallery_link_by_id($author) . '">
									' . $author_data['nickname'][0] . '
								</a>';

			if ($author != get_current_user_id()) {
				echo '<a href="#" class="medal" data-id="' . $author . '">';
				if ($this->wc_outfit_is_following($author)) {
					echo 'Unfollow';
				} else {
					echo 'Follow';
				}
				echo '</a>';
			}

			echo '</div>


							<div class="products owl-carousel">
								' . $this->wc_outfit_modal_hooked_product($_GET['view']) . '
							</div>

							<div class="tags">';

			$tags = wp_get_post_categories($_GET['view']);

			foreach ($tags as $tag) {
				$tag = get_category($tag);
				echo '<a href="' . home_url('style-gallery/?cat=' . strtolower($tag->name)) . '">' . $tag->name . '</a>';
			}

			echo '</div>

							<div class="info">
								<div class="pull-left">
									<span class="time">' . __('Added ', 'couture') . $this->wc_outfit_time_ago($_GET['view']) . '</span>
								</div>

								<div class="pull-right">
									' . $this->wc_outfit_outfit_share_button($_GET['view']) . '
									' . $this->wc_outfit_post_like_button($_GET['view']) . '
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(\'#productModal\').modal({
					backdrop: \'static\',
					show: true
				});

				jQuery("#productModal .products").owlCarousel({
					items: 3,
					margin: 10,
					nav:true
				});
			})
		</script>';

		} else {
			echo '<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">

				</div>
			</div>
		</div>';
		}

	}
}