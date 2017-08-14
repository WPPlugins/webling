<?php

class webling_page_form_list {

	public static function html() {
		$forms = new Form_List();
		$forms->prepare_items();

		?>
			<div class="wrap">
				<h2>
					Anmeldeformulare
					<a href="<?php echo admin_url( 'admin.php?page=webling_page_form_edit' ); ?>" class="page-title-action"><?php echo esc_html_x( 'Neues Formular', 'webling' ); ?></a>
				</h2>

				<div id="poststuff">
					<div id="post-body">
						<div id="post-body-content">
							<div class="meta-box-sortables ui-sortable">
								<form method="post">
									<?php
									$forms->display(); ?>
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
