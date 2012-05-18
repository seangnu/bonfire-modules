<div class="view split-view">
	<div class="view">
		<div class="panel-header list-search">
            <select id="category-filter" style="display: inline-block; max-width: 40%;">
                <option value="0"><?php echo lang('news_all_categories'); ?></option>
                <?php foreach ($categories as $c) : ?>
                        <option><?php echo $c->category_name ?></option>
                <?php endforeach; ?>
            </select>
            <?php render_search_box(); ?>
		</div>
        <div class="scrollable">
            <div class="list-view" id="news-list">
                <?php if (isset($records) && is_array($records) && count($records)) : ?>
                <?php foreach ($records as $record) : ?>
                <div class="list-item with-icon" data-id="<?php echo $record->id ?>" data-category="<?php  if(isset($categories[$record->category_id -1]->category_name)) echo $categories[$record->category_id -1]->category_name ?>">
                    <img src="<?php echo Template::theme_url('images/page.png') ?>" />
                    <p>
                        <b><?php echo $record->news_title ?></b><br />
                        <span><span><?php if(isset($record->category_id)) echo $categories[$record->category_id -1]->category_name; ?></span></span>
                    </p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <br /><br />
        </div>
    </div>
    <div id="content" class="view">
        <div class="scrollable" id="ajax-content">
            <div class="padded">
                <div class="row" style="margin-bottom: 2.5em">
                    <div class="box create rounded">
                            <a class="button good ajaxify" href="<?php echo site_url(SITE_AREA.'/content/news/create'); ?>">
                                    <?php echo lang('news_create_new_button'); ?>
                            </a>
                            <h3><?php echo lang('news_create_new'); ?></h3>&nbsp;
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>