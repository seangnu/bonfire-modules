var editor = CKEDITOR.instances['page_text'];
if (editor) { editor.destroy(true); }
CKEDITOR.replace('page_text', {width: 1000, height: 400});