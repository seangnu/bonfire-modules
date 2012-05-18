<?php 
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<rss version="2.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:admin="http://webns.net/mvcb/"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">

    <channel>
    
    <title><?php echo $site_name; ?></title>

    <link><?php echo site_url(); ?></link>
    <description></description>
    <dc:language></dc:language>

    <admin:generatorAgent rdf:resource="http://www.codeigniter.com/" />

    <?php foreach($news as $n): ?>

        <item>
            <title><?php echo xml_convert($n->news_title); ?></title>
            <link><?php echo site_url() ?></link>
            <guid><?php echo site_url('news/'.$n->news_slug) ?></guid>

            <description><![CDATA<?php echo nl2br($n->news_text) ?>]></description>
            <pubDate><?php echo $n->created_on;?></pubDate>
        </item>

    <?php endforeach; ?>
    
    </channel>
</rss> 