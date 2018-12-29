<?php
require_once(dirname(__FILE__).'/boot.php');
require_once(SG_PUBLIC_INCLUDE_PATH.'header.php');
?>
<?php require_once(SG_PUBLIC_INCLUDE_PATH.'sidebar.php'); ?>
<div id="sg-content-wrapper">
	<div class="container-fluid">
		<legend><?php _backupGuardT('Special services')?><?php echo backupGuardLoggedMessage(); ?></legend>
		<div class="sg-service-container plugin-card">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="<?php echo SG_MIGRATION_SERVICE_URL?>" class="thickbox" target="_blank"><?php _backupGuardT('WordPress website migration')?><img src="<?php echo SG_PUBLIC_URL."img/128.png"?>" class="plugin-icon" alt=""></a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li>
							<p id="sg-migration-service-price"><b>$49.95</b></p>
						</li>
						<li>
							<a class="oreder-now button btn-primary" target="_blank" data-slug="" href="<?php echo SG_MIGRATION_SERVICE_URL?>" aria-label="" data-name=""><?php _backupGuardT('Order now')?></a>
						</li>
					</ul>
				</div>
				<div class="desc column-description">
					<p>
						<?php _backupGuardT('Our professionals will migrate all of your files and database and ensure everything is working properly on your new server. With our migration service, you can expect:')?>
						<div class="sg-margin-left-20">
							<p>
								<i class="glyphicon glyphicon-ok"></i>
								<?php _backupGuardT('Migration of your files')?>
							</p>
							<p>
								<i class="glyphicon glyphicon-ok"></i>
								<?php _backupGuardT('Migration of your database')?>
							</p>
							<p>
								<i class="glyphicon glyphicon-ok"></i>
								<?php _backupGuardT('Refactoring of all urls')?>
							</p>
							<p>
								<i class="glyphicon glyphicon-ok"></i>
								<?php _backupGuardT('Refactoring of all file names and image paths')?>
							</p>
							<p>
								<i class="glyphicon glyphicon-ok"></i>
								<?php _backupGuardT('Serialized data refactoring')?>
							</p>
						</div>
					</p>
				</div>
			</div>
		</div>
	</div>
	<?php require_once(SG_PUBLIC_INCLUDE_PATH . '/footer.php'); ?>
</div>
