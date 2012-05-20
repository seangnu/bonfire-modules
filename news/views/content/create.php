<?php
$new = TRUE;
if(isset($news) && ! empty($news))
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
    <h3><?php echo $new ? lang('news_create') : lang('news_edit') ?></h3>
    <?php echo form_open($this->uri->uri_string(), 'class="form-horizontal"'); ?>
    <fieldset>
        <legend><?php echo $new ? lang('news_content') : $news->news_title ?></legend>
        <?php echo form_input('news_title', $new ? '' : $news->news_title, lang('news_title'), 'maxlength="255"' ); ?>
        <?php echo form_dropdown('category', $categories_array, $new ? '' : $news->category_id, lang('category')); ?>
            <?php echo form_textarea( array( 'name' => 'news_text',  'id' => 'news_text', 'value' => set_value('news_text', $new ? '' : $news->news_text) ) )?>
        <div class="form-actions">
            <input class="btn btn-primary" onClick="CKupdate();" type="submit" name="save" value="<?php echo lang('news_save'); ?>" /> <input class="btn btn-success" onClick="CKupdate();" type="submit" name="publish" value="<?php echo lang('news_publish'); ?>" />
            <?php echo anchor(SITE_AREA .'/content/news', '<i class="icon-refresh icon-white">&nbsp;</i> '.lang('news_cancel'), 'class="btn btn-warning"'); ?>
            <?php if(! $new): ?>
                 <a class="btn btn-danger" href="<?php echo site_url(SITE_AREA.'/content/news/delete/'.$news->id); ?>" onclick="return confirm('<?php echo lang('news_delete_confirm'); ?>')"><?php echo lang('news_action_delete') ?></a>
            <?php endif;?>
        </div>
        <fieldset>
            <legend><?php echo lang('news_additional_settings'); ?></legend>
            <div class="control-group">
                <?php echo form_label(lang('news_published'), 'news_published', array('class' => 'control-label')); ?>
                 <div class="controls">
                     <input type="checkbox" name="news_published" id="news_active" value="news_published" <?php if(isset($news->news_published)) echo $news->news_published ? 'checked="checked"' : ''; ?> />
                </div>
            </div>
            <div class="control-group">
                <?php echo form_label(lang('news_slug'), 'news_slug', array('class' => 'control-label')); ?>
                <div class="controls">
                    <input id="news_slug" type="text" name="news_slug" maxlength="255" value="<?php echo $new ? '' : $news->news_slug; ?>"  />
                    <p class="help-inline"><?php echo lang('news_slug_description'); ?></p>
                </div>
            </div>
        </fieldset>
    </fieldset>
    <?php echo form_close(); ?>
</div>