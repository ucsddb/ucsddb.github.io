<?php
@main();

function main(){
	if (substr($_FILES['fsql']['type'],0,4)!='text' || !$_FILES['fsql']['size'] || $_FILES['fsql']['error']) return;
	echo "<!doctype html><html><head>
<script type='text/javascript' src='img/jquery-1.8.2.min.js'></script>
<script>
$(document).ready(function(){
	var sql=$('#sql').text();
	if (sql.length!=0) $('#sqltxt', window.parent.document).val(sql);
});
</script>
</head><body><div id='sql'>";
	echo file_get_contents($_FILES['fsql']['tmp_name']);
	echo "</div></body></html>";
}
?>
