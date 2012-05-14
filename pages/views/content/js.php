$.subscribe('list-view/list-item/click', function(id) {
	$('#content').load('<?php echo site_url(SITE_AREA .'/content/pages/edit') ?>/'+ id);
});

/*
	Category Filter
*/
$('#category-filter').change(function(){
	
	var category = $(this).val();
	
	$('#pages-list .list-item').css('display', 'block');
	
	if (category != '0')
	{
		$('#pages-list .list-item[data-category!="'+ category +'"]').css('display', 'none');
	}
});
