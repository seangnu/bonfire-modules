<?php
$categories_array = array();
foreach($categories as $c)
{
    $categories_array[$c->id] = $c->category_name;
}
?>

<?php echo form_open($this->uri->uri_string() .'/save', 'class="constrained ajax-form"'); ?>
    <?php echo form_label(lang('news_per_page'), 'category'); ?>
    <?php echo form_dropdown('news_per_page', $news_per_page_options, isset($config['news_per_page']) ? $config['news_per_page'] : ''); ?>
    <input type="submit" name="submit" value="<?php echo lang('news_settings_save'); ?>" />
<?php echo form_close(); ?>

<fieldset>
    <legend><?php echo lang('category_categories'); ?></legend>
    <?php echo form_open($this->uri->uri_string() .'/category_edit', 'class="constrained ajax-form"'); ?>
        <?php echo form_label(lang('category_list'), 'category'); ?>
        <?php echo form_dropdown('category', $categories_array); ?>
        <input type="submit" name="submit" value="<?php echo lang('category_edit'); ?>" />
        <a class="button good" href="<?php echo site_url(SITE_AREA .'/settings/news/category_create'); ?>"><?php echo lang('category_create_new'); ?></a>
    <?php echo form_close(); ?>
</fieldset>