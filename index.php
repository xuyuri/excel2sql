<!--
<html>
<body>
<form action="excel2sql.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>
-->
<?php

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Music Download</title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="all" href="style.css" />
	<script src="http://code.jquery.com/jquery-latest.js"></script>
    <!--<script src="dropzone.js"></script>
    <script src="dropzone.css"></script>-->
    <script src="dropzone.js"></script>
    <link rel="stylesheet" href="dropzone.css">
</head>
<body>	
	<div class="logo">Excel => SQL</div>
		<div id="main">
			<div class="form" >
<!--                <form action="excel2sql.php" class="dropzone"></form>-->
                <form action="excel2sql.php" class="dropzone"></form>
			</div>
            <div >
                <textarea class="result" rows="16" cols="50" id="sql"></textarea>
            </div>
		</div>	
</body>
</html>
<?php

?>