<?php
$categories_array = array();
foreach($categories as $c)
{
    $categories_array[$c->id] = $c->category_name;
}
?>

<fieldset>
    <legend><?php echo lang('categories') ?></legend>
    <?php echo form_open($this->uri->uri_string() .'/category_edit', 'class="constrained ajax-form"'); ?>
        <?php echo form_label(lang('category_list'), 'category'); ?>
        <?php echo form_dropdown('category', $categories_array); ?>
        <input type="submit" name="submit" value="<?php echo lang('category_edit'); ?>" />
        <a class="button good" href="<?php echo site_url(SITE_AREA .'/settings/pages/category_create'); ?>"><?php echo lang('category_create') ?></a>
    <?php echo form_close(); ?>
</fieldset>