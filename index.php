<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Excel => SQL</title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="all" href="resource/style.css" />
	<script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="resource/dropzone.js"></script>
    <link rel="stylesheet" href="resource/dropzone.css">
</head>
<body>
	<div class="logo">Excel => SQL</div>
	<div id="main">
		<div class="excel" >
            <form action="excel2sql.php" class="dropzone" id="upload"></form>
        </div>
        <div class="sql">
            <textarea class="sqltext"  id="sql"></textarea>
        </div>
	</div>
    <div id="footer" align="center">
        <div align="center" style="margin-bottom: 15px">
            <a href="resource/demo.xls">Demo Download</a>
        </div>
        Powered BY Yurixu
    </div>
</body>

</html>
<script type="text/javascript">
    function show() {
        $.ajax({
            async: false,
            type: "GET",
            url: "<?php echo 'result.txt'; ?>",
            success: function (data) {
                $("#sql").text(data);
            }
        });
    }
    //上传完成事件重写
    $("#upload").dropzone({
        success: function() {
            show();
        }
    });
</script>