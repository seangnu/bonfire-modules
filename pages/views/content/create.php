<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>
<?php
if( isset($pages) ) {
	$pages = (array)$pages;
}
$id = isset($pages['id']) ? "/".$pages['id'] : '';
?>
<?php echo form_open($this->uri->uri_string(), 'class="constrained ajax-form"'); ?>
<?php if(isset($pages['id'])): ?><input id="id" type="hidden" name="id" value="<?php echo $pages['id'];?>"  /><?php endif;?>
<div>
        <?php echo form_label(lang('pages_title'), 'pages_title'); ?>
        <input id="pages_title" type="text" name="pages_title" maxlength="255" value="<?php echo set_value('pages_title', isset($pages['pages_title']) ? $pages['pages_title'] : ''); ?>"  />
</div>

<div>
        <?php echo form_label(lang('pages_slug'), 'pages_slug'); ?>
        <input id="pages_slug" type="text" name="pages_slug" maxlength="255" value="<?php echo set_value('pages_slug', isset($pages['pages_slug']) ? $pages['pages_slug'] : ''); ?>"  />
		<p class="small indent"><?php echo lang('pages_slug_description'); ?></p>
</div>

<div>
        <?php echo form_label(lang('pages_text'), 'pages_text'); ?>
        <?php echo form_textarea( array( 'name' => 'pages_text', 'style' => 'width:40em;', 'id' => 'pages_text', 'value' => set_value('pages_text', isset($pages['pages_text']) ? $pages['pages_text'] : '') ) )?>


<div class="text-center">
	<input type="submit" name="submit" value="<?php echo lang('pages_save'); ?>" /> or <?php echo anchor(SITE_AREA .'/content/pages', lang('pages_cancel')); ?>
</div>
<fieldset>
    <a class="button" id="delete-me" href="<?php echo site_url(SITE_AREA .'/content/pages/delete'. $id); ?>" onclick="return confirm('<?php echo lang('pages_delete_confirm'); ?>')"><?php echo lang('pages_delete_record'); ?></a>
</fieldset>
<?php echo form_close(); ?>

