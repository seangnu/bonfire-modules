<div class="box create rounded">

	<a class="button good" href="<?php echo site_url(SITE_AREA .'/content/pages/create'); ?>">
		<?php echo lang('pages_create_new_button'); ?>
	</a>

	<h3><?php echo lang('pages_create_new'); ?></h3>

	<p><?php echo lang('pages_edit_text'); ?></p>

</div>

<br />

<?php if (isset($records) && is_array($records) && count($records)) : ?>
				
	<h2>pages</h2>
	<table>
		<thead>
			<tr>
			
		<th>Title</th>
		<th>Slug</th>
		
			<th><?php echo lang('pages_actions'); ?></th>
			</tr>
		</thead>
		<tbody>
		
		<?php foreach ($records as $record) : ?>
			<tr>
				
				<td><?php echo $record->pages_title?></td>
				<td><?php echo $record->pages_slug?></td>
				<td><?php echo anchor(SITE_AREA .'/content/pages/edit/'. $record->id, lang('pages_edit'), '') ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
