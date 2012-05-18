<h2><?php echo lang('category').': '.$category->category_name; ?></h2>
<?php if (isset($news) && is_array($news) && count($news)) : ?>
		<?php foreach ($news as $n) : ?>
            <?php $n = (array)$n;?>
			<h3><a href="<?php echo site_url('/news/view').'/'.$n['news_slug']; ?>" alt="<?php echo $n['news_title']; ?>"><?php echo $n['news_title']; ?></a></h3>
            <?php echo nl2br($n['news_text']); ?><br><br>
		<?php endforeach; ?>
<?php endif; ?>
<br><br>
<div class="pagination"><?php echo $pagination_links ?></div>