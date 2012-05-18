<?php if (isset($news->news_title) && isset($news->news_text)) : ?>
    <h2><?php echo $news->news_title; ?></h2>
    <?php echo nl2br($news->news_text); ?>
<?php endif; ?>

