<h2><?php echo __('Webling Berechtigungen überprüfen', 'webling'); ?></h2>
<p>
	<?php echo __('Eine Verbindung zu Webling ist zwar möglich, aber der angegebene API-Key hat keinen Schreib-Zugriff auf die Mitgliederdaten.', 'webling'); ?>
	<br>
	<?php echo __('Bitte in der Webling Administration die Berechtigungen anpassen oder in den Einstellungen einen anderen API-Key eintragen:', 'webling'); ?>
	<a href="<?php echo admin_url( 'admin.php?page=webling_page_settings' ); ?>"><?php echo __('Webling Einstellungen', 'webling'); ?></a>
</p>
