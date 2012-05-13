<?php if($database_success)
{
    if( ! $all_copied) { /*echo '
    <div class="box create rounded">

        <a class="button good" href="'.site_url(SITE_AREA.'/developer/migrate/copy_all').'">Übertrage alles</a>
        <h3>Datenbankverbindung erfolgreich</h3>
	    <p>Bei großen Datenbanken ist es ratsam, alle Schritte einzeln durchzuführen.</p>

    </div>'; */ }
    //echo '<div class="box">Die Anzeige der noch offenen Schritte ist sessionbasiert und überprüft nicht ob die Daten bereits in der Vergangenheit übertragen wurden!</div>';
    if( ! $news_copied) { echo '
    <div class="box create rounded">

        <a class="button good" href="'.site_url(SITE_AREA.'/developer/migrate/copy_news').'">Übertrage News</a>
        <h3>'.$news_count.' News können übertragen werden</h3>
        <br>

    </div>'; }
    if( ! $articles_copied) { echo '
    <div class="box create rounded">

        <a class="button good" href="'.site_url(SITE_AREA.'/developer/migrate/copy_articles').'">Übertrage Artikel</a>
        <h3>'.$articles_count.' Artikel können übertragen werden</h3>
        <br>

    </div>'; }
}
else
{
    echo '<div class="box error">Datenbankverbindung gescheitert. Zugangsdaten korrekt?</div>';
}
?>
