<div>
	<form action="<?PHP echo $data['path']; ?>" method="post">
		<p><label for="username">Username:</label> <input type="text" name="username" value="<?PHP //echo $username;?>" id="username" /></p>
		<p><label for="password">Password:</label> <input type="password" name="password" value="" id="password" /></p>
		<p><input type="submit" name="btnlogin" value="Login" id="btnlogin" /></p>
		<input type="hidden" name="r" value="<?PHP echo htmlspecialchars(@$_REQUEST['r']); ?>" id="r">
	</form>
</div>