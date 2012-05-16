<?php if (isset($records) && is_array($records) && count($records)) : ?>
		<?php foreach ($records as $record) : ?>
            <?php $record = (array)$record;?>
			<h2><a href="<?php echo site_url('/news/view').'/'.$record['news_slug']; ?>" alt="<?php echo $record['news_title']; ?>"><?php echo $record['news_title']; ?></a></h2>
            <?php echo $record['news_text']; ?><br><br>
		<?php endforeach; ?>
<?php endif; ?>
<br><br>
<div class="pagination"><?php echo $pagination_links ?></div>
