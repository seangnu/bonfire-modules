<?php
$new = TRUE;
if(isset($category))
{
    if($category)
    {
        $new = FALSE;
    }
}
?>
<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>
<div class="admin-box">
    <h3><?php echo $new ? lang('category_create') : lang('category_edit') ?></h3>
    <?php echo form_open($this->uri->uri_string(), 'class="form-horizontal"'); ?>
    <?php if( ! $new): ?><input id="id" type="hidden" name="id" value="<?php echo $category->id;?>"  /><?php endif;?>
    <fieldset>
        <legend><?php echo $new ? lang('category_new') : $category->category_name ?></legend>
        <?php echo form_input('category_name', $new ? '' : $category->category_name, lang('category_name'), 'maxlength="255"' ); ?>

    </fieldset>
    <div class="control-group">
        <?php echo form_label(lang('category_description'), 'category_name', array('class' => 'control-label')); ?>
        <div class="controls">
            <?php echo form_textarea( array('name' => 'category_description', 'id' => 'category_description', 'value' => set_value('category_description', $new ? '' : $category->category_description) ) )?>
        </div>
    </div>
    <div class="form-actions">
        <input class="btn btn-primary" type="submit" name="submit" value="<?php echo lang('category_save'); ?>" /> <?php echo anchor(SITE_AREA .'/settings/news', '<i class="icon-refresh icon-white">&nbsp;</i> '.lang('category_cancel'), array('class' => 'btn btn-warning')); ?>
    </div>
    <?php echo form_close(); ?>
</div>
