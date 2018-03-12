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
			<h2><?php _e('Woocommerce Outfit Options', 'xim')?></h2>

			<?php if (isset($_GET['section'])) {
				$section = $_GET['section'];
			} ?>

			<h2 class="nav-tab-wrapper">
	    		<a href="?page=wc_outfit&section=general" class="nav-tab <?php echo $section == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
	    		<a href="?page=wc_outfit&section=im" class="nav-tab <?php echo $section == 'im' ? 'nav-tab-active' : ''; ?>">Import Users</a>
			</h2>

			<?php if ($section == 'im') {?>
				<form method="post" action="#">
					<table class="form-table">
				        <tr valign="top">
							<th scope="row">Backup Data</th>
							<td><textarea name="xd-exim-user-im-data" id="xd-exim-user-im-data" rows="5" cols="100"></textarea>
							<p class="description">Paste the exported data strings here to import users</p></td>
						</tr>
				    </table>

					<?php submit_button('Import');?>
				</form>
			<?php } else {?>
				<form method="post" action="#">
					<table class="form-table">
				        <tr valign="top">
				        	<th scope="row">Role</th>
				        	<td><select name="roles" id="roles" multiple>
								<?php wp_dropdown_roles()?>
							</select>
							<p class="description"><?php _e('Leave blank to export users of all roles', 'xd')?></p></td>
				        </tr>

				        <tr valign="top">
							<th scope="row">Backup Data</th>
							<td><textarea name="xd-exim-user-ex-data" id="xd-exim-user-ex-data" rows="5" cols="100" readonly></textarea>
							<p class="description">Save the backup strings</p></td>
						</tr>
				    </table>

					<?php submit_button('Export');?>
				</form>
			<?php }?>
		</div>
	<?php }

	/**
	 * Register option page.
	 *
	 * @since    1.0.0
	 */
	function admin_menu() {
		add_options_page('Woocommerce Outfit', 'Woocommerce Outfit', 'manage_options', 'wc_outfit', array($this, 'menu_page'));
	}
}