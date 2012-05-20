<?php
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
    <h3><?php echo lang('news_settings'); ?></h3>
    <?php echo form_open($this->uri->uri_string() .'/save', 'class="form-horizontal"'); ?>
    <fieldset>
        <legend><?php echo lang('news_general_settings'); ?></legend>
        <?php echo form_dropdown('news_per_page', $news_per_page_options, isset($config['news_per_page']) ? $config['news_per_page'] : '', lang('news_per_page')); ?>

        <div class="control-group">
            <?php echo form_label(lang('news_enable_caching'), 'caching', array('class' => 'control-label')); ?>
            <div class="controls">
                <input type="checkbox" name="caching" id="caching" value="caching" <?php if(isset($config['caching'])) echo $config['caching'] ? 'checked="checked"' : ''; ?>)">
                <p class="help-inline"><?php echo lang('news_caching_description'); ?></p>
            </div>
        </div>
        <div class="form-actions">
            <input class="btn btn-primary" type="submit" name="submit" value="<?php echo lang('news_settings_save'); ?>" />
        </div>
        <?php echo form_close(); ?>
    

        <?php echo form_open($this->uri->uri_string() .'/category_edit', 'class="constrained ajax-form"'); ?>
            <fieldset>
                <legend><?php echo lang('categories'); ?></legend>
                <?php echo form_dropdown('category', $categories_array, '', lang('category_list')); ?>
                <div class="form-actions">
                    <input class="btn btn-primary" type="submit" name="submit" value="<?php echo lang('category_edit'); ?>" />
                    <a class="btn btn-primary" href="<?php echo site_url(SITE_AREA .'/settings/news/category_create'); ?>"><?php echo lang('category_create'); ?></a>
                </div>
            </fieldset>
        <?php echo form_close(); ?>
    </fieldset>
</div>