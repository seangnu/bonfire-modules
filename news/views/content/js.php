var editor = CKEDITOR.instances['news_text'];
if (editor) { editor.destroy(true); }
CKEDITOR.replace(news_text, {
    extraPlugins : 'autogrow',
    autoGrow_maxHeight : 300,
    removePlugins : 'resize',
});

function CKupdate(){
    for ( instance in CKEDITOR.instances )
        CKEDITOR.instances[instance].updateElement();
}