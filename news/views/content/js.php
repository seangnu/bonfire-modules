var editor = CKEDITOR.instances['news_text'];
if (editor) { editor.destroy(true); }
CKEDITOR.replace(news_text, {
    extraPlugins : 'autogrow',
    autoGrow_maxHeight : 300,
    removePlugins : 'resize',
    skin : 'BootstrapCK-Skin',
    toolbar : [ [ 'Source' ], [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat', 'Format' ], [ 'Link','Unlink','Anchor', 'Image', 'Table' ], [ 'TextColor','BGColor' ],  [ 'NumberedList','BulletedList','-','Blockquote','CreateDiv',
	'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ], [ 'Find','-','SelectAll','-','SpellChecker', 'Scayt'] ]
});

function CKupdate(){
    for ( instance in CKEDITOR.instances )
        CKEDITOR.instances[instance].updateElement();
}