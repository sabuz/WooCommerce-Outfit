<?php
/**
 * Template Name: Outfits
 *
 * This is the template that displays outfit.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package 318couture
 */

get_header(); ?>

	<div class="container">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

				<?php while ( have_posts() ) : the_post(); ?>
					<div class="row">
	                    <?php if (is_user_logged_in()) {?>
							<div class="col-md-3 account-sidebar">
								<?php dynamic_sidebar('account'); ?>
							</div>

							<div class="col-md-9">
						<?php } else { ?>
							<div class="col-md-12">
						<?php } ?>
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
								<header class="entry-header">
									<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
								</header><!-- .entry-header -->

								<div class="entry-content">
									<div class="woocommerce">
										<?php wc_print_notices(); ?>
										
										<?php the_content(); ?>

										<table class="table table-bordered">
											<thead>
												<tr>
													<th><span class="nobr"><?php _e('ID', 'couture'); ?></span></th>
													<th><span class="nobr"><?php _e('Date', 'couture'); ?></span></th>
													<th><span class="nobr"><?php _e('Status', 'couture'); ?></span></th>
													<th><span class="nobr"><?php _e('Title', 'couture'); ?></span></th>
												</tr>
											</thead>
											<?php $args = array(
												'post_type' => 'outfit',
												'post_status' => 'any',
												'posts_per_page' => -1,
												'author' => get_current_user_id()
											);

											$query = new WP_Query($args); ?>
											<tbody>
											<?php if ($query->have_posts()) {
											    while ($query->have_posts()) {
											    	$query->the_post(); ?>
												<tr>
													<td><?php echo get_the_ID(); ?></td>
													<td><?php echo get_the_date(); ?></td>
													<td><?php echo get_post_status(); ?></td>
													<td><?php echo the_title(); ?></td>
												</tr>
												<?php }
											} else {
												echo '<tr>
													<td colspan="4" class="text-center">
														' . __('There is no outfit yet.', 'couture') . '
													</td>
												</tr>';
											}
											wp_reset_postdata(); ?>
											</tbody>
										</table>
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
