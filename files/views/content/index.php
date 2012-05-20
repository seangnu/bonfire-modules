<div class="admin-box">
    <h3><?php echo lang('files_manage') ?></h3>
    <ul class="nav nav-tabs" >
        <li <?php echo $filter=='' && ! isset($search_term) ? 'class="active"' : ''; ?>><a href="<?php echo $current_url; ?>"><?php echo lang('files_filter_all') ?></a></li>
        <li <?php echo $filter=='published' ? 'class="active"' : ''; ?>><a href="<?php echo site_url(SITE_AREA.'/content/files?filter=published'); ?>"><?php echo lang('files_filter_published') ?></a></li>
        <li <?php echo $filter=='unpublished' ? 'class="active"' : ''; ?>><a href="<?php echo site_url(SITE_AREA.'/content/files?filter=unpublished'); ?>"><?php echo lang('files_filter_unpublished') ?></a></li>
        <li class="<?php echo $filter=='category' ? 'active ' : ''; ?>dropdown">
            <a href="#" class="drodown-toggle" data-toggle="dropdown">
                <?php echo lang('files_filter_category') ?> <?php echo isset($filter_category) ? ": $filter_category" : ''; ?>
                <b class="caret light-caret"></b>
            </a>
            <?php if($categories) : ?>
            <ul class="dropdown-menu">
                <?php foreach ($categories as $c) : ?>
                    <li>
                        <a href="<?php echo site_url(SITE_AREA .'/content/files?filter=category&category_id='. $c->id) ?>">
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
                <input type="submit" class="btn btn-primary" value="<?php echo lang('files_do_search'); ?>" />
            <?php echo form_close(); ?>
        </li>
    </ul>
    <?php echo form_open($this->uri->uri_string()); ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="column-check"><input class="check-all" type="checkbox" /></th>
                <th style="width: 3em"><?php echo lang('files_id'); ?></th>
                <th><?php echo lang('files_title'); ?></th>
                <th style="width: 11em"><?php echo lang('files_name'); ?></th>
                <th style="width: 11em"><?php echo lang('category'); ?></th>
                <th style="width: 11em"><?php echo lang('files_created_on'); ?></th>
                <th style="width: 10em"><?php echo lang('files_status'); ?></th>
            </tr>
        </thead>
        <?php if (isset($files) && is_array($files) && count($files)) : ?>
        <tfoot>
            <tr>
                <td colspan="7">
                    <input type="submit" name="publish_selected" class="btn" value="<?php echo lang('files_action_publish') ?>">
                    <input type="submit" name="unpublish_selected" class="btn" value="<?php echo lang('files_action_unpublish') ?>">
                    <!--<input type="submit" name="delete_selected" class="btn btn-danger" id="delete-me" value="<?php echo lang('files_action_delete') ?>" onclick="return confirm('<?php echo lang('files_delete_confirm'); ?>')">-->
                    <?php endif;?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php if (isset($files) && is_array($files) && count($files)) : ?>
            <?php foreach ($files as $f) : ?>
            <tr>
                <td>
                    <input type="checkbox" name="checked[]" value="<?php echo $f->id ?>" />
                </td>
                <td><?php echo $f->id ?></td>
                    <td>
                        <a href="<?php echo site_url(SITE_AREA .'/content/files/edit/'. $f->id); ?>"><?php echo $f->file_title; ?></a>
                    </td>
                    <td>
                        <a href="<?php echo site_url('/files/'. $f->file_name); ?>"><?php echo $f->file_name; ?></a>
                    </td>
                    <td>
                        <?php if($f->category_id) : ?>
                        <?php echo $categories[$f->category_id -1]->category_name; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo date('M j, y g:i A', strtotime($f->created_on)); ?>
                    </td>
                    <td>
                        <?php
                        $class = '';
                        switch ($f->file_published)
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
                            if ($f->file_published == 1)
                            {
                                    echo(lang('files_published'));
                            }
                            else
                            {
                                    echo(lang('files_unpublished'));
                            }
                        ?>
                        </span>
                    </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><?php echo(lang('files_nothing_found')); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php echo form_close(); ?>
    <?php echo $this->pagination->create_links(); ?>
</div>