<?php
require_once('Main/myClass.php');
if (isset($_POST['submit1']) && !empty($_FILES['file']))
{
	MyClass::getFiles();
	MyClass::executeBgTasks();
	$clean = new MyClass();
	$dwnldFile = $clean->getCleanArr();
	MyClass::rm();
	MyClass::sendFile($dwnldFile);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
</head>
<body>
<form action = "" method="post" id="#myform" enctype="multipart/form-data">
	<input type="file" name="file[]" multiple="true" />
	<input type="submit" value="загрузить" name="submit1" />
	<input type="reset" value="очистить" name="submit2"  />
</form>

</body>
</html>
