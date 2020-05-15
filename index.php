<?php
	if($_POST['start'])
	{
		$url = $_POST['link'];
		$n= $_POST['phone_numbers'];
		header("Location:find_href.php?url=$url&n=$n");
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title> login</title>
<link rel="stylesheet" type="text/css" href="bootstrap.css">
<style type="text/css" media="screen, projection, print">
.text-center {
    margin-right: auto;
    margin-left: auto;
}
</style>
</head> 
<body>
<div style="margin-top:20px;" class="container">

<form method="post" class="justify-content-center ">
	<input class="form-control" type="text" name="link" placeholder="Введите ссылку">
	<label for="phone_numbers">Количество телефонов</label>
	<select class="form-control" id="phone_numbers" name="phone_numbers">
	<option>100</option>
	<option>200</option>
	<option>500</option>
	<option>1000</option>
	<option>1500</option>
	<option>3000</option>
	</select>
	<input style="margin-top:20px;" type="submit" name="start" class="btn btn-primary" value="Начать загрузку">
</form>
</div>
</body>
</html>
