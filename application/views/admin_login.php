<div class='grid_6 push_3'>
<div class='padding_20'>
<?
if (isset($message))
	echo "<h2>$message</h2>";

echo form_open('admin/login');
echo form_fieldset('Login');
echo form_label('Username: ', 'username');
echo form_input('username');
echo br(2);
echo form_label('Password: ', 'password');
echo form_password('password');
echo br(2);
echo form_submit('submit', 'Login', "class='submit'");
echo form_fieldset_close();
echo form_close();
?>
</div>
</div>