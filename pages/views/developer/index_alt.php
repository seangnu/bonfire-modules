
<div class="view split-view">
	
	<!-- pages List -->
	<div class="view">
	
	<?php if (isset($records) && is_array($records) && count($records)) : ?>
		<div class="scrollable">
			<div class="list-view" id="role-list">
				<?php foreach ($records as $record) : ?>
					<?php $record = (array)$record;?>
					<div class="list-item" data-id="<?php echo $record['id']; ?>">
						<p>
							<b><?php echo (empty($record['pages_name']) ? $record['id'] : $record['pages_name']); ?></b><br/>
							<span class="small"><?php echo (empty($record['pages_description']) ? lang('pages_edit_text') : $record['pages_description']);  ?></span>
						</p>
					</div>
				<?php endforeach; ?>
			</div>	<!-- /list-view -->
		</div>
	
	<?php else: ?>
	
	<div class="notification attention">
		<p><?php echo lang('pages_no_records'); ?> <?php echo anchor(SITE_AREA .'/developer/pages/create', lang('pages_create_new'), array("class" => "ajaxify")) ?></p>
	</div>
	
	<?php endif; ?>
	</div>
	<!-- pages Editor -->
	<div id="content" class="view">
		<div class="scrollable" id="ajax-content">
				
			<div class="box create rounded">
				<a class="button good ajaxify" href="<?php echo site_url(SITE_AREA .'/developer/pages/create')?>"><?php echo lang('pages_create_new_button');?></a>

				<h3><?php echo lang('pages_create_new');?></h3>

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
		<th>Text</th>
		<th><?php echo lang('pages_actions'); ?></th>
		</tr>
		</thead>
		<tbody>
<?php
foreach ($records as $record) : ?>
			<tr>
				<td><?php echo $record->pages_title?></td>
				<td><?php echo $record->pages_slug?></td>
				<td><?php echo $record->pages_text?></td>
				<td><?php echo anchor(SITE_AREA .'/developer/pages/edit/'. $record->id, lang('pages_edit'), 'class="ajaxify"'); ?></td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table>
				<?php endif; ?>
				
		</div>	<!-- /ajax-content -->
	</div>	<!-- /content -->
</div>
