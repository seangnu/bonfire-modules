<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>

<?php echo form_open($this->uri->uri_string(), 'class="constrained ajax-form"'); ?>
<?php if(isset($category->id)): ?><input id="id" type="hidden" name="id" value="<?php echo $category->id;?>"  /><?php endif;?>

<div>
        <?php echo form_label(lang('category_name'), 'category_name'); ?>
        <input id="news_title" type="text" name="category_name" maxlength="255" value="<?php echo set_value('category_name', isset($category->category_name) ? $category->category_name : ''); ?>"  />
</div>

<div>
    <?php echo form_label(lang('category_description'), 'category_name'); ?>
    <?php echo form_textarea( array( 'name' => 'category_description', 'id' => 'category_description', 'value' => set_value('category_description', isset($category->category_description) ? $category->category_description : '') ) )?>
</div>
<div class="submits">
    <input type="submit" name="submit" value="<?php echo lang('category_save'); ?>" /> <?php echo anchor(SITE_AREA .'/settings/news', lang('category_cancel')); ?>
</div>
<?php echo form_close(); ?>
