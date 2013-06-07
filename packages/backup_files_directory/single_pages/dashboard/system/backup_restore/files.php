<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
?>


<?php echo $dh->getDashboardPaneHeaderWrapper(t('Backup Files Directory')); ?>
	
	<?php if ($has_permission): ?>
		<?php if (count($files)): ?>
		<table class="table table-bordered table-striped" style="width: auto;">
			<tr>
				<th><?php echo t('Created On'); ?></th>
				<th><?php echo t('File'); ?></th>
				<th><?php echo t('Size'); ?></th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>
			<?php foreach ($files as $file): ?>
			<tr>
				<td><?php echo $file['created']; ?></td>
				<td><?php echo $file['name']; ?></td>
				<td><?php echo $file['size']; ?></td>
				<td>
					<form action="<?php echo $this->action('download_backup'); ?>" method="post" style="margin: 0;">
						<input type="hidden" name="file" value="<?php echo $file['name']; ?>" />
						<input type="submit" class="btn" name="submit" value="<?php echo t('Download'); ?>">
					</form>
				</td>
				<td>
					<form action="<?php echo $this->action('delete_backup'); ?>" method="post" style="margin: 0;">
						<input type="hidden" name="file" value="<?php echo $file['name']; ?>" />
						<input type="submit" class="btn btn-danger" name="submit" value="<?php echo t('Delete'); ?>">
					</form>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
		
		<div class="alert alert-block">
			<h4><?php echo t('Warning!'); ?></h4>
			<?php echo t('Leaving backups on a publicly-accessible server poses a serious security risk!'); ?>
			<br>
			<?php echo t('If this is a publicly-accessible site, you should download your backup now and then delete it.'); ?>
			<br>
			<?php echo t('You should also delete any <a href="%s">database backups</a> you may have.', View::url('/dashboard/system/backup_restore/backup/')); ?>
		</div>
		
		<hr>
		<?php else: ?>
		<br>
		<?php endif; ?>
		
		<form action="<?php echo $this->action('create_backup'); ?>" method="post" class="form-inline">
			<input type="submit" class="btn btn-primary" name="submit" value="<?php echo t('Create New Backup'); ?>">
			&nbsp;
			<label class="checkbox">
				<?php
				echo Loader::helper('form')->checkbox('db_too', 1, true);
				echo t('and a database backup too');
				?>
			</label>
		</form>
	<?php else: ?>
		<p><?php echo t('You do not have permission to backup the files directory.'); ?></p>
	<?php endif; ?>

<?php echo $dh->getDashboardPaneFooterWrapper(); ?>