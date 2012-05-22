<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CONTENT 
 */
//Filters
$lang['news_filter_all']                        = 'All';
$lang['news_filter_published']                  = 'Published';
$lang['news_filter_unpublished']                = 'Unpublished';
$lang['news_filter_deleted']                    = 'Deleted';
$lang['news_filter_category']                   = 'Category';

// Table Heading
$lang['news_id']                                = 'ID';
$lang['news_slug']                              = 'Slug';
$lang['news_title']                             = 'Title';
$lang['news_category']                          = 'Category';
$lang['news_created_on']                        = 'Created on';
$lang['news_status']                            = 'Status';
$lang['news_manage']                            = 'Manage news';

// Table content
$lang['news_nothing_found']			= 'No news found.';
$lang['news_status_published']                  = 'Published';
$lang['news_status_unpublished']                = 'Unpublished';
$lang['news_status_deleted']                    = 'Deleted';

// Acton Buttons
$lang['news_action_edit']                       = 'Edit';
$lang['news_action_create']                     = 'Create';
$lang['news_action_cancel']                     = 'Cancel';
$lang['news_action_delete']                     = 'Delete';
$lang['news_action_delete_confirm']             = 'After Deleting, the news will not be visible anymore. Are you sure?';
$lang['news_action_save']                       = 'Save';
$lang['news_action_publish']                    = 'Publish';
$lang['news_action_purge']                      = 'Purge';
$lang['news_action_purge_confirm']              = 'After purging, the news can not be restored. Are you sure?';
$lang['news_action_restore']                    = 'Restore';
$lang['news_action_publish']                    = 'Publish';
$lang['news_action_unpublish']                  = 'Unpublish';
$lang['news_action_delete']                     = 'Delete';
$lang['news_action_search']                     = 'Search';

// Navigation
$lang['news_subnav_heading']                    = 'News';
$lang['news_subnav_main']                       = 'News';
$lang['news_subnav_new_news']                   = 'New News';

// News form
$lang['news_heading_edit']			= 'Edit news';
$lang['news_heading_create']			= 'Create news';
$lang['news_heading_content']                   = 'Content';
$lang['news_heading_additional']                = 'Additional settings';

$lang['news_input_title']                       = 'Title';
$lang['news_input_slug']                        = 'Slug';
$lang['news_input_slug_description']            = 'Will be filled automatically if empty.';

$lang['news_input_category']                    = 'Category';
$lang['news_input_category_invalid_id']         = 'Invalid category ID.';

// Flash messages
$lang['news_create_success']			= 'News has been created.';
$lang['news_edit_success']			= 'News has been saved.';
$lang['news_invalid_id']			= 'Invalid news ID.';
$lang['news_publish_success']			= 'News have been published.';
$lang['news_publish_failure']			= 'Problem occured while publishing.';
$lang['news_unpublish_success']			= 'News have been unpublished.';
$lang['news_unpublish_failure']			= 'Problem occured while unpublishing.';
$lang['news_delete_success']			= 'News has been deleted.';
$lang['news_delete_failure']			= 'Problem occured while deleting.';
$lang['news_purge_success']			= 'News has been purged.';
$lang['news_purge_failure']			= 'Problem occured while purging.';
$lang['news_restore_success']			= 'News has been restored.';
$lang['news_restore_failure']			= 'Problem occured while restoring.';

/*
 * SETTINGS
 */
// Navigation


// Settings form
$lang['news_categories']			= 'Categories';
$lang['news_category_invalid_id']               = 'Invalid category ID';
$lang['news_dropdown_per_page']                 = 'Displayed News per page';
$lang['news_checkbox_caching']                  = 'Enable Caching';
$lang['news_checkbox_caching_description']      = 'Will cache some (not all!) content using APC and fall back to files.';

$lang['news_action_save_settings']              = 'Save settings';
$lang['news_action_save_settings_success']      = 'Settings have been saved.';
$lang['news_action_save_settings_failed']       = 'Problem occured while saving.';

$lang['news_heading_general_settings']          = 'General settings';
$lang['news_heading_settings']                  = 'News settings';
$lang['news_heading_create_category']           = 'Create cateogry';
$lang['news_heading_new_category']              = 'New cateogry';
$lang['news_heading_edit_category']             = 'Edit category';

// Category form
$lang['news_create_category_success']           = 'Category has been created.';
$lang['news_save_category_success']             = 'Category has been saved.';
$lang['news_input_category_name']               = 'Category name';
$lang['news_input_category_description']        = 'Category description';

$lang['news_dropdown_categories']               = 'Categories';
$lang['news_action_cancel_category']            = 'Cancel';
$lang['news_action_edit_category']              = 'Edit category';
$lang['news_action_create_category']            = 'Create category';
$lang['news_action_cancel_category']            = 'Cancel';
$lang['news_action_save_category']              = 'Save category';