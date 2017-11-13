<?php
/**
 * Template Name: Style Gallery
 *
 * This is the template that displays style gallery.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package 318couture
 */

get_header();?>

	<div class="container">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<header class="page-header">
					<?php
echo '<h1 class="page-title">' . __('Style Gallery') . '</h1>';

if (isset($_GET['cat'])) {
	$catData = get_term_by('slug', $_GET['cat'], 'outfit_cats');

	echo '<h4 class="page-subtitle">' . ucwords($catData->name) . '</h4>';
	echo '<div class="page-bar">';
	echo '<strong>' . __('BE THE STYLIST: ', 'couture') . '</strong>' . __('Make it work at the office with posh professional styles!', 'couture');
	echo '</div>';
} elseif (isset($_GET['user'])) {
	$author_data = wc_outfit_get_author_data($_GET['user']);
	print_r($author_data);

	echo '<h4 class="page-subtitle">' . $author_data['first_name'] . '</h4>';

	echo '<div class="tab-btn">';
	if (isset($_GET['page'])) {
		$current = $_GET['page'];
	} else {
		$current = 'photos';
	}
	echo '<a href="' . user_gallery_link_by_id($_GET['user']) . '" class="' . ($current == 'photos' ? 'active' : '') . '">Photos</a>';
	echo '<a href="' . user_gallery_likes_by_id($_GET['user']) . '" class="' . ($current == 'likes' ? 'active' : '') . '">Liked Looks</a>';
	echo '<a href="#" class="follower" data-user="' . $_GET['user'] . '" data-toggle="modal" data-target="#fanModal">' . wc_outfit_get_follower_count($_GET['user']) . ' Followers</a>';
	echo '<a href="#" class="following" data-user="' . $_GET['user'] . '" data-toggle="modal" data-target="#fanModal">' . wc_outfit_get_following_count($_GET['user']) . ' Following</a>';
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
		if (is_following($_GET['user'])) {
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

?>
				</header><!-- .entry-header -->

				<?php
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

if (isset($_GET['user'])) {
	if (@$_GET['page'] == 'likes') {
		$ids = wc_outfit_get_likes_by_user($_GET['user']);
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

$query = new WP_Query($args);?>

				<div class="row">
					<?php if ($query->have_posts()) {?>
						<div class="grid">
							<?php while ($query->have_posts()): $query->the_post();?>
								<div class="grid-item col-sm-4" data-id="<?php echo $post->ID; ?>">
									<div class="gal-header">
										<div class="gal-product clearfix">
											<ul>
												<?php echo wc_outfit_hooked_product($post->ID, 4); ?>
											</ul>
										</div>
										<a class="gal-thumb clearfix">
											<img src="<?php echo wc_outfit_post_thumb_by_id($post->ID); ?>" class="gal-img" />
										</a>
									</div>
									<div class="gal-footer clearfix">
										<div class="pull-left">
											<a class="author" href="<?php echo user_gallery_link_by_id(get_the_author_meta('ID')); ?>">
												<?php
												$author_data = wc_outfit_get_post_author_data($post->ID);
												echo $author_data['nickname'][0]; ?></a>
											<span class="time"><?php echo wc_outfit_time_ago(); ?></span>
										</div>
										<div class="pull-right">
											<div class="gal-bubble">
												<a href="#" class="bubble-btn"><i class="fa fa-share"></i></a>

												<div class="bubble-content">
													<?php echo wc_outfit_outfit_share_button($post->ID); ?>
												</div>
											</div>

											<?php echo wc_outfit_post_like_button($post->ID); ?>
										</div>
									</div>
								</div>
							<?php endwhile; // End of the loop. ?>
						</div>

						<div class="more">
							<button class="button" data-current="1" data-max="<?php echo $query->max_num_pages; ?>" <?php if (isset($_GET['order'])) {echo 'data-order=' . $_GET['order'];}?> <?php if (isset($_GET['user'])) {echo 'data-user=' . $_GET['user'];}?> <?php if (isset($_GET['page'])) {echo 'data-page=' . $_GET['page'];}?> <?php if (isset($_GET['cat'])) {echo 'data-cat=' . $_GET['cat'];}?>>Load More</button>
						</div>

						<?php } else {
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
}?>
					<?php wp_reset_postdata();?>
				</div>

				<?php if (isset($_GET['user'])) {?>
					<div class="modal fade" id="fanModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
									<h4 class="modal-title" id="modalLabel">
										<?php
										$author_data = wc_outfit_get_author_data($_GET['user']);
										echo $author_data['nickname'][0];
										?>		
									</h4>
								</div>
								<div class="modal-body">
									<ul class="list-group">
									</ul>
								</div>
							</div>
						</div>
					</div>
				<?php }?>


				<?php if (isset($_GET['view'])) {
					$author = wc_outfit_get_post_author($_GET['view']);?>
					<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-body clearfix">
									<div class="thumb">
										<img src="<?php echo wc_outfit_post_thumb_by_id($_GET['view']); ?>" />
									</div>

									<div class="details">
										<div class="author clearfix">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>

											<a class="name" href="<?php echo user_gallery_link_by_id($author); ?>">
												<?php 
												$author_data = wc_outfit_get_author_data($author);
												echo $author_data['nickname'][0];
												?>
											</a>

											<?php
											if ($author != get_current_user_id()) {
													echo '<a href="#" class="medal" data-id="' . $author . '">';
													if (wc_outfit_is_following($author)) {
														echo 'Unfollow';
													} else {
														echo 'Follow';
													}
													echo '</a>';
												}
											?>
										</div>


										<div class="products">
											<?php echo wc_outfit_modal_hooked_product($_GET['view']); ?>
										</div>

										<div class="tags">
											<?php
$tags = wp_get_post_categories($_GET['view']);
	foreach ($tags as $tag) {
		$tag = get_category($tag);
		echo '<a href="' . home_url('style-gallery/?cat=' . strtolower($tag->name)) . '">' . $tag->name . '</a>';
	}?>
										</div>

										<div class="info">
											<div class="pull-left">
												<span class="time"><?php echo __('Added ', 'couture') . wc_outfit_time_ago($_GET['view']); ?></span>
											</div>

											<div class="pull-right">
												<?php echo wc_outfit_outfit_share_button($_GET['view']); ?>
												<?php echo wc_outfit_post_like_button($_GET['view']); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('#productModal').modal({
								backdrop: 'static',
								show: true
							});

							jQuery("#productModal .products").owlCarousel({
								items: 3,
								margin:20,
								nav:true
							});
						})
					</script>
				<?php } else {
					echo '<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
						<div class="modal-dialog" role="document">
							<div class="modal-content">

							</div>
						</div>
					</div>';
}?>

			</main><!-- #main -->
		</div><!-- #primary -->

	</div><!-- .container -->
<?php
get_footer();
