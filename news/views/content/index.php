<div class="admin-box">
    <h3><?php echo lang('news_manage') ?></h3>
    <ul class="nav nav-tabs" >
        <li <?php echo $filter=='' && ! isset($search_term) ? 'class="active"' : ''; ?>><a href="<?php echo $current_url; ?>"><?php echo lang('news_filter_all') ?></a></li>
        <li <?php echo $filter=='published' ? 'class="active"' : ''; ?>><a href="<?php echo site_url(SITE_AREA.'/content/news?filter=published'); ?>"><?php echo lang('news_filter_published') ?></a></li>
        <li <?php echo $filter=='unpublished' ? 'class="active"' : ''; ?>><a href="<?php echo site_url(SITE_AREA.'/content/news?filter=unpublished'); ?>"><?php echo lang('news_filter_unpublished') ?></a></li>
        <li <?php echo $filter=='deleted' ? 'class="active"' : ''; ?>><a href="<?php echo site_url(SITE_AREA.'/content/news/?filter=deleted'); ?>"><?php echo lang('news_filter_deleted') ?></a></li>
        <li class="<?php echo $filter=='category' ? 'active ' : ''; ?>dropdown">
            <a href="#" class="drodown-toggle" data-toggle="dropdown">
                <?php echo lang('news_filter_category') ?> <?php echo isset($filter_category) ? ": $filter_category" : ''; ?>
                <b class="caret light-caret"></b>
            </a>
            <?php if($categories) : ?>
            <ul class="dropdown-menu">
                <?php foreach ($categories as $c) : ?>
                    <li>
                        <a href="<?php echo site_url(SITE_AREA .'/content/news?filter=category&category_id='. $c->id) ?>">
                            <?php echo $c->category_name; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <li>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'span4')); ?>
                <input type="text" id="search_term" name="search_term" class="input-medium search-query" value="<?php if(isset($search_term)) echo $search_term; ?>">
                <input type="submit" class="btn btn-primary" value="<?php echo lang('news_do_search'); ?>" />
            <?php echo form_close(); ?>
        </li>
    </ul>
    <?php echo form_open($this->uri->uri_string()); ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="column-check"><input class="check-all" type="checkbox" /></th>
                <th style="width: 3em"><?php echo lang('news_id'); ?></th>
                <th><?php echo lang('news_title'); ?></th>
                <th style="width: 11em"><?php echo lang('news_slug'); ?></th>
                <th style="width: 11em"><?php echo lang('category'); ?></th>
                <th style="width: 11em"><?php echo lang('news_created_on'); ?></th>
                <th style="width: 10em"><?php echo lang('news_status'); ?></th>
            </tr>
        </thead>
        <?php if (isset($news) && is_array($news) && count($news)) : ?>
        <tfoot>
            <tr>
                <td colspan="7">
                    <?php echo lang('news_with_selected') ?>
                    <?php if($filter == 'deleted'):?>
                        <input type="submit" name="restore_selected" class="btn btn-success" value="<?php echo lang('news_action_restore') ?>">
                        <input type="submit" name="purge_selected" class="btn btn-danger" value="<?php echo lang('news_action_purge') ?>" onclick="return confirm('<?php echo lang('news_purge_confirm'); ?>')">
                    <?php else: ?>
                    <input type="submit" name="publish_selected" class="btn" value="<?php echo lang('news_action_publish') ?>">
                    <input type="submit" name="unpublish_selected" class="btn" value="<?php echo lang('news_action_unpublish') ?>">
                    <input type="submit" name="delete_selected" class="btn btn-danger" id="delete-me" value="<?php echo lang('news_action_delete') ?>" onclick="return confirm('<?php echo lang('news_delete_confirm'); ?>')">
                    <?php endif;?>
                </td>
            </tr>
        </tfoot>
        <?php endif; ?>
        <tbody>
            <?php if (isset($news) && is_array($news) && count($news)) : ?>
            <?php foreach ($news as $n) : ?>
            <tr>
                <td>
                    <input type="checkbox" name="checked[]" value="<?php echo $n->id ?>" />
                </td>
                <td><?php echo $n->id ?></td>
                    <td>
                        <a href="<?php echo site_url(SITE_AREA .'/content/news/edit/'. $n->id); ?>"><?php echo $n->news_title; ?></a>
                    </td>
                    <td>
                        <a href="<?php echo site_url('/news/view/'. $n->news_slug); ?>"><?php echo $n->news_slug; ?></a>
                    </td>
                    <td>
                        <?php if($n->category_id) : ?>
                        <?php echo $categories[$n->category_id -1]->category_name; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo date('M j, y g:i A', strtotime($n->created_on)); ?>
                    </td>
                    <td>
                        <?php
                        $class = '';
                        switch ($n->news_published)
                        {
                            case 1:
                                $class = " label-success";
                                break;
                            case 0:
                                $class = " label";
                                break;
                        }
                        ?>
                        <span class="label<?php echo($class); ?>">
                        <?php
                            if ($n->news_published == 1)
                            {
                                    echo(lang('news_published'));
                            }
                            else
                            {
                                    echo(lang('news_unpublished'));
                            }
                        ?>
                        </span>
                    </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><?php echo(lang('news_nothing_found')); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php echo form_close(); ?>
    <?php echo $this->pagination->create_links(); ?>
</div>