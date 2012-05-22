<?php if (has_permission('News.Content.Manage')):?>
<ul class="nav nav-pills">
	<li <?php echo $this->uri->segment(4) == '' ? 'class="active"' : '' ?>>
		<a href="<?php echo site_url(SITE_AREA .'/content/news') ?>"><?php echo lang('news_subnav_main'); ?></a>
	</li>
	<li <?php echo $this->uri->segment(4) == 'create' ? 'class="active"' : '' ?>>
		<a href="<?php echo site_url(SITE_AREA .'/content/news/create') ?>" id="create_new"><?php echo lang('news_subnav_new_news'); ?></a>
	</li>
</ul>
<?php endif;?>