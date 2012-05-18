<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>
<?php
if( isset($pages) ) {
	$pages = (array)$pages;
}
$id = isset($pages['id']) ? "/".$pages['id'] : '';

$categories_array = array();
foreach($categories as $c)
{
    $categories_array[$c->id] = $c->category_name;
}
?>
<script type="text/javascript">
//<![CDATA[
var editor = CKEDITOR.instances['page_text'];
if (editor) { editor.destroy(true); }
CKEDITOR.replace(page_text, {
    width:1000,
    height:400
});

function CKupdate(){
    for ( instance in CKEDITOR.instances )
        CKEDITOR.instances[instance].updateElement();
}
//]]>
</script>
<?php echo form_open($this->uri->uri_string(), 'class="constrained ajax-form"'); ?>
<?php if(isset($pages['id'])): ?><input id="id" type="hidden" name="id" value="<?php echo $pages['id'];?>"  /><?php endif;?>
<div>
        <?php echo form_label(lang('pages_title'), 'pages_title'); ?>
        <input id="pages_title" type="text" name="pages_title" maxlength="255" value="<?php echo set_value('pages_title', isset($pages['pages_title']) ? $pages['pages_title'] : ''); ?>"  />
</div>

<div>
        <?php echo form_label(lang('pages_slug'), 'pages_slug'); ?>
        <input id="pages_slug" type="text" name="pages_slug" maxlength="255" value="<?php echo set_value('pages_slug', isset($pages['pages_slug']) ? $pages['pages_slug'] : ''); ?>"  />
		<p class="small indent"><?php echo lang('pages_slug_description'); ?></p>
</div>

<div>
    <?php echo form_label(lang('category'), 'category'); ?>
    <?php echo form_dropdown('category', $categories_array, isset($pages['category_id']) ? $pages['category_id'] : ''); ?>
</div>

<div style="margin-left: 50px;">
        <?php echo form_textarea( array( 'name' => 'page_text', 'id' => 'page_text', 'value' => set_value('page_text', isset($pages['pages_text']) ? $pages['pages_text'] : '') ) )?>

</div>
<div class="submits">
	<input onClick="CKupdate();" type="submit" name="submit" value="<?php echo lang('pages_save'); ?>" /> or <?php echo anchor(SITE_AREA .'/content/pages', lang('pages_cancel')); ?>
</div>
<div class="text-right">
    <a class="button" id="delete-me" href="<?php echo site_url(SITE_AREA .'/content/pages/delete'. $id); ?>" onclick="return confirm('<?php echo lang('pages_delete_confirm'); ?>')"><?php echo lang('pages_delete_record'); ?></a>
</div>
<?php echo form_close(); ?>

