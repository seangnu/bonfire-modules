
<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>
<?php // Change the css classes to suit your needs    
if( isset($pages) ) {
	$pages = (array)$pages;
}
$id = isset($pages['id']) ? "/".$pages['id'] : '';
?>
<?php echo form_open($this->uri->uri_string(), 'class="constrained ajax-form"'); ?>
<?php if(isset($pages['id'])): ?><input id="id" type="hidden" name="id" value="<?php echo $pages['id'];?>"  /><?php endif;?>
<div>
        <?php echo form_label('Title', 'pages_title'); ?>
        <input id="pages_title" type="text" name="pages_title" maxlength="255" value="<?php echo set_value('pages_title', isset($pages['pages_title']) ? $pages['pages_title'] : ''); ?>"  />
</div>

<div>
        <?php echo form_label('Slug', 'pages_slug'); ?>
        <input id="pages_slug" type="text" name="pages_slug" maxlength="255" value="<?php echo set_value('pages_slug', isset($pages['pages_slug']) ? $pages['pages_slug'] : ''); ?>"  />
</div>

<div>
        <?php echo form_label('Text', 'pages_text'); ?>
	<?php echo form_textarea( array( 'name' => 'pages_text', 'id' => 'pages_text', 'rows' => '5', 'cols' => '80', 'value' => set_value('pages_text', isset($pages['pages_text']) ? $pages['pages_text'] : '') ) )?>
</div>


	<div class="text-right">
		<br/>
		<input type="submit" name="submit" value="Create pages" /> or <?php echo anchor(SITE_AREA .'/developer/pages', lang('pages_cancel')); ?>
	</div>
	<?php echo form_close(); ?>
