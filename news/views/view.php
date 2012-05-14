<?php if (isset($records) && count($records)) : ?>
    <?php $record = (array)$records;?>
    <?php if (isset($record['news_title']) && isset($record['news_text'])) : ?>
			   <h2><?php echo $record['news_title']; ?></h2>
                <?php echo $record['news_text']; ?>
    <?php endif; ?>
<?php endif; ?>
