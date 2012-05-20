<?php if (isset($news) && is_array($news) && count($news)) : ?>
    <?php foreach ($news as $n) : ?>
    <div class="item news">
        <div class="item-head news-head">
            <h2><a href="<?php echo site_url('/news/view').'/'.$n->news_slug; ?>" title="<?php echo $n->news_title; ?>"><?php echo $n->news_title; ?></a></h2>
        </div>
        <div class="item-body news-body">
            <?php echo nl2br($n->news_text); ?>
        </div>
        <div class="item-foot news-foot">
            <span class="news-date"><?php echo date('M j, y g:i A', strtotime($n->created_on)); ?></span>
            <span class="news-category"><?php if($n->category_id) : ?><?php echo lang('category').': ';?><a href="<?php echo site_url('news/category/'.$categories[$n->category_id -1]->category_slug) ; ?>"><?php echo $categories[$n->category_id -1]->category_name ; ?></a><?php endif; ?></span>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
<br><br>
<div class="item-pagination news-pagination"><?php echo $pagination_links ?></div>