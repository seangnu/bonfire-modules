<?php
$new = TRUE;
if(isset($file) && ! empty($file))
{
    $new = FALSE;
}
$categories_array = array();
if($categories)
{
    foreach($categories as $c)
    {
        $categories_array[$c->id] = $c->category_name;
    }
}
?>
<div class="admin-box">
    <h3><?php echo $new ? lang('files_add_new') : lang('files_edit') ?></h3>
    <fieldset>
        <legend><?php echo $new ? lang('files_new') : $file->file_title ?></legend>
        <?php echo form_open_multipart($this->uri->uri_string(), 'class="form-horizontal"'); ?>
        <?php if( ! $new) : ?><input id="id" type="hidden" name="id" value="<?php echo $file->id;?>"  /><?php endif;?>
        <?php if( ! $new) : ?>
        <div class="control-group">
            <?php echo form_label(lang('files_name'), '', array('class' => 'control-label')); ?>
            <div class="controls">
                <a href="<?php echo site_url('files/'.$file->file_name) ?>"><?php echo site_url('files/'.$file->file_name) ?></a>
            </div>
        </div>
        <?php else : ?>
        <div class="control-group">
            <?php echo form_label(lang('files_select_file'), 'userfile', array('class' => 'control-label')); ?>
            <div class="controls">
                <input type="file" name="userfile" size="20" />
            </div>
        </div>
        <?php endif; ?>
        <div class="control-group">
                <?php echo form_label(lang('files_file_title'), 'file_title', array('class' => 'control-label')); ?>
                <div class="controls">
                    <input id="file_title" type="text" name="file_title" maxlength="255" value="<?php echo $new ? '' : $file->file_title; ?>"  />
                </div>
        </div>
        <div class="control-group">
                <?php echo form_label(lang('files_file_description'), 'file_description', array('class' => 'control-label')); ?>
                <div class="controls">
                    <textarea id="file_description" name="file_description" /><?php echo $new ? '' : $file->file_description; ?></textarea>
                </div>
        </div>
        <?php echo form_dropdown('file_category_id', $categories_array, $new ? '' : $file->category_id, lang('files_file_category'))?>


        <div class="form-actions">
                <input class="btn btn-primary" type="submit" name="submit" value="<?php echo $new ? lang('files_add_new_button') : lang('files_save_button') ?>" /> <?php echo anchor(SITE_AREA .'/content/files', lang('files_cancel'), 'class="btn btn-warning"'); ?>
        </div>
        <?php echo form_close(); ?>
    </fieldset>
</div>