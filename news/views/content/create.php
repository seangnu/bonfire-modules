<div id="ajax-content">
<?php if (validation_errors()) : ?>
<div class="notification error">
    <?php echo validation_errors(); ?>
</div>
<?php endif; ?>
<?php
if(isset($news))
{
    $news = (array)$news;
}

$categories_array = array();
foreach($categories as $c)
{
    $categories_array[$c->id] = $c->category_name;
}
?>
<script type="text/javascript">
//<![CDATA[
var editor = CKEDITOR.instances['news_text'];
if (editor) { editor.destroy(true); }
CKEDITOR.replace(news_text, {
    width:1000,
    height:400
});

function CKupdate(){
    for ( instance in CKEDITOR.instances )
        CKEDITOR.instances[instance].updateElement();
}
//]]>
</script>
                                    

<?php echo form_open($this->uri->uri_string(), 'class="ajax-form"'); ?>
<div>
    <?php echo form_label(lang('news_title'), 'news_title'); ?>
    <input id="news_title" type="text" name="news_title" maxlength="255" value="<?php echo set_value('news_title', isset($news['news_title']) ? $news['news_title'] : ''); ?>"  />
</div>

<div>
    <?php echo form_label(lang('category'), 'category'); ?>
    <?php echo form_dropdown('category', $categories_array, isset($news['category_id']) ? $news['category_id'] : ''); ?>
</div>
<br />
<div style="margin-left: 50px;">
    <?php echo form_textarea( array( 'name' => 'news_text',  'id' => 'news_text', 'value' => set_value('news_text', isset($news['news_text']) ? $news['news_text'] : '') ) )?>
</div>

<div class="submits">
    <input onClick="CKupdate();" type="submit" name="save" value="<?php echo lang('news_save'); ?>" /><input onClick="CKupdate();" type="submit" name="publish" value="<?php echo lang('news_publish'); ?>" /> <?php echo anchor(SITE_AREA .'/content/news', lang('news_cancel')); ?>
</div>
<?php if(isset($news['id'])): ?>
    <div class="text-right">
        <a class="button" id="delete-me" href="<?php echo site_url(SITE_AREA .'/content/news/delete/'.$news['id']); ?>" onclick="return confirm('<?php echo lang('news_delete_confirm'); ?>')"><?php echo lang('news_delete_record'); ?></a>
    </div>
<?php endif;?>
<fieldset>
    <legend><?php echo lang('news_additional_settings'); ?></legend>
    <div>
        <?php echo form_label(lang('news_published'), 'news_published'); ?>
        <input type="checkbox" name="news_published" id="news_active" value="news_published" <?php if(isset($news['news_published'])) echo $news['news_published'] ? 'checked="checked"' : ''; ?>)">
    </div>
    <div>
        <?php echo form_label(lang('news_slug'), 'news_slug'); ?>
        <input id="news_slug" type="text" name="news_slug" maxlength="255" value="<?php echo set_value('news_slug', isset($news['news_slug']) ? $news['news_slug'] : ''); ?>"  />
        <p class="small indent"><?php echo lang('news_slug_description'); ?></p>
    </div>
</fieldset>
<?php echo form_close(); ?>
</div>