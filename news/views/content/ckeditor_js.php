var editor = CKEDITOR.instances['news_text'];
if (editor) { editor.destroy(true); }
CKEDITOR.replace('news_text', {width: 1000, height: 400});