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
<div class="admin-box">
    <h3><?php echo $new ? lang('news_heading_create_category') : lang('news_heading_edit_category') ?></h3>
    <?php echo form_open($this->uri->uri_string(), 'class="form-horizontal"'); ?>
    <?php if( ! $new): ?><input id="id" type="hidden" name="id" value="<?php echo $category->id;?>"  /><?php endif;?>
    <fieldset>
        <legend><?php echo $new ? lang('news_heading_new_category') : $category->category_name ?></legend>
        <div class="control-group<?php echo form_error('category_name') ? ' error' : '' ?>">
            <?php echo form_label(lang('news_input_category_name'), 'category_name', array('class' => 'control-label')); ?>
            <div class="controls">
                <input type="input" name="category_name" value="<?php echo $new ? '' : $category->category_name ?>" maxlength="255" />
            </div>
        </div>
    </fieldset>
    <div class="control-group">
        <?php echo form_label(lang('news_input_category_description'), 'category_name', array('class' => 'control-label')); ?>
        <div class="controls">
            <?php echo form_textarea( array('name' => 'category_description', 'id' => 'category_description', 'value' => set_value('category_description', $new ? '' : $category->category_description) ) )?>
        </div>
    </div>
    <div class="form-actions">
        <input class="btn btn-primary" type="submit" name="submit" value="<?php echo lang('news_action_save_category'); ?>" /> <?php echo anchor(SITE_AREA .'/settings/news', '<i class="icon-refresh icon-white">&nbsp;</i> '.lang('news_action_cancel_category'), array('class' => 'btn btn-warning')); ?>
    </div>
    <?php echo form_close(); ?>
</div>
