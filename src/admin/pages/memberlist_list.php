<?php

class webling_page_memberlist_list {

	public static function html() {
		$memberlist = new Memberlist_List();
		$memberlist->prepare_items();

		?>
			<div class="wrap">
				<h2>
					Mitgliederlisten
					<a href="<?php echo admin_url( 'admin.php?page=webling_page_memberlist_edit' ); ?>" class="page-title-action"><?php echo esc_html_x( 'Neue Liste', 'webling' ); ?></a>
				</h2>

				<div id="poststuff">
					<div id="post-body">
						<div id="post-body-content">
							<div class="meta-box-sortables ui-sortable">
								<form method="post">
									<?php
									$memberlist->display(); ?>
								</form>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
		<?php
	}
}

