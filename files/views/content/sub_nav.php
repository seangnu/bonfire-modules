<?php if (TRUE):?>
<ul class="nav nav-pills">
	<li <?php echo $this->uri->segment(4) == '' ? 'class="active"' : '' ?>>
		<a href="<?php echo site_url(SITE_AREA .'/content/files') ?>"><?php echo lang('files'); ?></a>
	</li>
	<li <?php echo $this->uri->segment(4) == 'create' ? 'class="active"' : '' ?>>
		<a href="<?php echo site_url(SITE_AREA .'/content/files/create') ?>" id="create_new"><?php echo lang('files_create'); ?></a>
	</li>
</ul>
<?php endif;?>