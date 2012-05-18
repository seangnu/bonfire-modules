<?php if (isset($news) && is_array($news) && count($news)) : ?>
    <?php foreach ($news as $n) : ?>
        <?php $n = (array)$n;?>
            <p><h3><a href="<?php echo site_url('/news/view').'/'.$n['news_slug']; ?>" alt="<?php echo $n['news_title']; ?>"><?php echo $n['news_title']; ?></a></h3></p>
        <p><?php echo nl2br($n['news_text']); ?></p>
        <p><?php if($n['category_id']) : ?>
            <?php echo lang('category').': ';?><a href="<?php echo site_url('news/category/'.$categories[$n['category_id'] -1]->category_slug) ; ?>"><?php echo $categories[$n['category_id'] -1]->category_name ; ?></a>
        <?php endif; ?>| <?php echo $n['created_on']; ?></p>
    <?php endforeach; ?>
<?php endif; ?>
<br><br>
<div class="pagination"><?php echo $pagination_links ?></div>