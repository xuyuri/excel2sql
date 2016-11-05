<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Music Download</title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="all" href="resource/style.css" />
	<script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="resource/dropzone.js"></script>
    <link rel="stylesheet" href="resource/dropzone.css">
</head>
<body>	
	<div class="logo">Excel => SQL</div>
		<div id="main">
			<div class="form" >
                <form action="excel2sql.php" class="dropzone" id="upload"></form>
            </div>
            <div >
                <textarea class="result" rows="16" cols="50" id="sql"></textarea>
            </div>
		</div>	
</body>
<div id="footer" align="center">
    powered by yurixu
</div>
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
    //----footer底部居中-----
    var w=window.innerWidth
    || document.documentElement.clientWidth
    || document.body.clientWidth;

    var h=window.innerHeight
        || document.documentElement.clientHeight
        || document.body.clientHeight;
    document.getElementById("footer").style.width=w + "px";
    //----footer底部居中-----
</script>