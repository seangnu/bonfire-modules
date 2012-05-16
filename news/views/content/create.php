<?php if (validation_errors()) : ?>
<div class="notification error">
	<?php echo validation_errors(); ?>
</div>
<?php endif; ?>
<?php
if( isset($news) ) {
	$news = (array)$news;
}
$id = isset($news['id']) ? "/".$news['id'] : '';


$categories_array = array();
foreach($categories as $c)
{
    $categories_array[$c->id] = $c->category_name;
}
?>

                                    

<?php echo form_open($this->uri->uri_string(), 'class="constrained ajax-form"'); ?>
<?php if(isset($news['id'])): ?><input id="id" type="hidden" name="id" value="<?php echo $news['id'];?>"  /><?php endif;?>
<div>
        <?php echo form_label(lang('news_title'), 'news_title'); ?>
        <input id="news_title" type="text" name="news_title" maxlength="255" value="<?php echo set_value('news_title', isset($news['news_title']) ? $news['news_title'] : ''); ?>"  />
</div>

<div>
        <?php echo form_label(lang('news_slug'), 'news_slug'); ?>
        <input id="news_slug" type="text" name="news_slug" maxlength="255" value="<?php echo set_value('news_slug', isset($news['news_slug']) ? $news['news_slug'] : ''); ?>"  />
		<p class="small indent"><?php echo lang('news_slug_description'); ?></p>
</div>

<div>
    <?php echo form_label(lang('category'), 'category'); ?>
    <?php echo form_dropdown('category', $categories_array, isset($news['category_id']) ? $news['category_id'] : ''); ?>
</div>
<br />
<div style="margin-left: 100px;">
    <?php echo form_textarea( array( 'class' => 'text-right', 'style' => '', 'name' => 'news_text',  'id' => 'news_text', 'value' => set_value('news_text', isset($news['news_text']) ? $news['news_text'] : '') ) )?>
</div>

<div class="submits">
	<input type="submit" name="submit" value="<?php echo lang('news_save'); ?>" /> <?php echo anchor(SITE_AREA .'/content/news', lang('news_cancel')); ?>
</div>
<div class="text-right">
    <a class="button" id="delete-me" href="<?php echo site_url(SITE_AREA .'/content/news/delete'. $id); ?>" onclick="return confirm('<?php echo lang('news_delete_confirm'); ?>')"><?php echo lang('news_delete_record'); ?></a>
</div>
<?php echo form_close(); ?>
