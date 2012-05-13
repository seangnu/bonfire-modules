<?php echo form_open(SITE_AREA.'/developer/migrate/check_connection', 'class="constrained ajax-form"'); ?>
<div>
    <label for="hostname">Hostname <span class="required">*</span></label>
    <input id="hostname" type="text" name="hostname" maxlength="255" value="localhost"  /><br>
</div>
<div>
    <label for="username">Username <span class="required">*</span></label>
    <input id="username" type="text" name="username" /><br>
</div>
<div>
    <label for="password">Passwort <span class="required">*</span></label>
    <input id="password" type="text" name="password"  /><br>
</div>
<div>
    <label for="database">Datenbank <span class="required">*</span></label>
    <input id="database" type="text" name="database" value="db_"  /><br>
</div>
<div>
    <label for="prefix">Prefix</label>
    <input id="prefix" type="text" name="prefix" value="fs2_" /><br>
</div>
<div class="text-right">
	<input type="submit" name="submit" value="Weiter" />
</div>
<?php echo form_close(); ?>
