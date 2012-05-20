<?php
if($categories)
{
    $categories_array = array();
    foreach($categories as $c)
    {
        $categories_array[$c->id] = $c->category_name;
    }
}
?>

<?php echo form_open($this->uri->uri_string() .'/save', 'class="constrained ajax-form"'); ?>
    <?php echo form_label(lang('files_per_page'), 'category'); ?>
    <?php echo form_dropdown('files_per_page', $files_per_page_options, isset($config['files_per_page']) ? $config['files_per_page'] : ''); ?>
    <input type="submit" name="submit" value="<?php echo lang('files_settings_save'); ?>" />
<?php echo form_close(); ?>

<fieldset>
    <legend><?php echo lang('category_categories'); ?></legend>
    <?php echo form_open($this->uri->uri_string() .'/category_edit', 'class="constrained ajax-form"'); ?>
        <?php echo form_label(lang('category_list'), 'category'); ?>
        <?php if(isset($categories_array)) : ?>
            <?php echo form_dropdown('category', $categories_array); ?>
            <input type="submit" name="submit" value="<?php echo lang('category_edit'); ?>" />
        <?php endif; ?>
        <a class="button good" href="<?php echo site_url(SITE_AREA .'/settings/files/category_create'); ?>"><?php echo lang('category_create_new'); ?></a>
    <?php echo form_close(); ?>
</fieldset>