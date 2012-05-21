<div class="admin-box">
<?php echo form_open(SITE_AREA.'/developer/migrate/check_connection', 'class="form-horizontal"'); ?>
<div class="control-group">
    <label class="control-label" for="hostname">Hostname</label>
    <div class="controls">
        <input id="hostname" type="text" name="hostname" maxlength="255" value="localhost"  />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="username">Username</label>
    <div class="controls">
        <input id="username" type="text" name="username" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password">Passwort</label>
    <div class="controls">
        <input id="password" type="text" name="password"  />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="database">Datenbank</label>
    <div class="controls">
        <input id="database" type="text" name="database" value="db_"  />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="prefix">Prefix</label>
    <div class="controls">
        <input id="prefix" type="text" name="prefix" value="fs2_" />
    </div>
</div>
<div class="form-actions">
	<input class="btn btn-success" type="submit" name="submit" value="Weiter" />
</div>
<?php echo form_close(); ?>
</div>