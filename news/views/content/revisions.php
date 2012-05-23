<style type="text/css">
ins {
	color: green;
	background: #dfd;
	text-decoration: none;
	}
del {
	color: red;
	background: #fdd;
	text-decoration: none;
	}

</style>

<h2><?php echo $news->news_title; ?></h2>
<?php foreach(array_reverse($revisions) as $r) : ?>
<div class="row">
    <div class="span1"><?php echo $r->id; ?></div>
    <div class="span8"><?php echo $r->htmldiff; ?></div>
    <div class="span1"><a href="<?php echo site_url(SITE_AREA.'/content/news/restore_revision/'.$r->id) ?>" class="btn btn-primary"><?php echo lang('news_action_restore') ?></a></div>
</div><br />
<?php endforeach; ?>
