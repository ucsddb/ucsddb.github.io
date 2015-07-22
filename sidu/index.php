<?php
if ($_SERVER['QUERY_STRING']=='') echo "<script language='JavaScript' type='text/javascript'>top.location='conn.php'</script>";
include 'inc.page.php';
?>
<!doctype html>
<html>
<head>
<meta http-equiv='content-type' content='text/html;charset=UTF-8'>
<title>SIDU 5.1 Database Web GUI: MySQL + PostgreSQL + SQLite + CUBRID and more supported by PHP PDO - topnew.net/sidu</title>
<meta name='viewport' content='width=device-width,height=device-height,initial-scale=1.0'>
<meta name='ROBOTS' content='NOODP'>
<meta name='description' content='MySQL SIDU, PostgreSQL SIDU, SQLite SIDU, CUBRID SIDU: Free SQL client front-end GUI - Select Insert Delete Update'>
<meta name='keywords' content='MySQL,SIDU,SQL client,font-end GUI,PostgreSQL,SQLite,CUBRID'>
<meta name='author' content='Topnew Geo'>
<link rel='shortcut icon' href='img/sidu.png'>
<link rel='stylesheet' type='text/css' href='img/my.css' media='all'>
<script type='text/javascript'>if (top.location!=location) top.location.href=document.location.href;</script>
<script type='text/javascript' src='img/jquery-1.8.2.min.js'></script>
<script type='text/javascript' src='img/my.js'></script>
</head>
<body>
<div id='tool' style='padding-left:3px'>
<?php
@navi();
function navi(){
	global $SIDU;
	echo "<div class='hide'><a href='option.php?id=$SIDU[0]' ",html_hkey('O',lang(3401))," target='ifr'></a><a href='user.php?id=$SIDU[0]' ",html_hkey('U',lang(3402))," target='ifr'></a>";//prevent screen move
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	$is_mypg=$is_my || $is_pg;
	if ($is_mypg){
		$sql=($is_my ? 'SHOW+PROCESSLIST' : 'SELECT+*+from+pg_stat_activity');
		echo "<a href='sql.php?id=$SIDU[0]&#38;sql=$sql' ",html_hkey('P',lang(3403))," target='ifr'></a>";
	}
	echo "</div><a href='home.php?id=$SIDU[0]' target='ifr' ",html_hkey('B',lang(3405)),"></a>
<a id='toolV' href='#' title='",lang(3418),"'></a>
<a id='p-' href='#' ",html_hkey('/',lang(3420)),"></a>
<a id='resizeSQL' href='#' ",html_hkey('\\',lang(3421)),"></a>
<a id='tconn' href='conn.php' target='_blank' ",html_hkey('N',lang(3406)),"><i>",lang(3407),"</i></a>
<a id='run_a' href='#' ",html_hkey('A',lang(3410)),"><i>",lang(3411),"</i></a>
<a id='run_r' href='#' ",html_hkey('R',lang(3412)),"><i>",lang(3413),"</i></a>
<a id='run_m' href='#' ",html_hkey('M',lang(3414)),"><i>",lang(3415),"</i></a>
<a id='SQLoad' class='show' ",html_hkey('L',lang(3408)),"><i>",lang(3409),"</i></a>
<a id='tHis' href='his.php?id=$SIDU[0]' target='ifr' ",html_hkey('H',lang(3416)),"><i>",lang(3417),"</i></a>";
	if ($is_mypg) echo " <a id='tvar' href='sql.php?id=$SIDU[0]&#38;sql=show+vars' target='ifr' ",html_hkey('V',lang(3422)),"><i>",lang(3423),'</i></a>';
	if ($is_my) echo " <a id='tflush' href='sql.php?id=$SIDU[0]&#38;sql=FLUSH+ALL' target='ifr' title='",lang(3424),"'><i>",lang(3425),'</i></a>';
	echo " <a id='temp' class='xwin' href='temp.php?id=$SIDU[0]' ",html_hkey('T',lang(3426)),"><i>",lang(3427),"</i></a>
<a id='topt' href='option.php?id=$SIDU[0]' target='ifr' title='",lang(3428),"'><i>",lang(3429),'</i></a>';
	if ($is_mypg) echo " <a id='tuser' href='user.php?id=$SIDU[0]' target='ifr' title='",lang(3430),"'><i>",lang(3431),'</i></a>';
	echo "<a id='tquit' href='conn.php?cmd=quit' ",html_hkey('Q',lang(3404)),'></a>';
	if ($SIDU['page']['btn']) echo "<script>\$(document).ready(function(){\$('html').addClass('LargeBtn')});</script>
<style id='toolBtn'>
#menu,#sqls{top:56px}
#tool{height:48px;line-height:65px}
#tool a,#tool b,.toolV #tool a{background:url(img/tool48.png);padding:24px}
#tool i{display:none}
.toolV #tool{width:50px}
.toolV #tool a{height:4px}
.toolV #menu{left:56px}
</style>";
}
?>
</div><!--tool-->
<div id='menu'><a href='#' accesskey='W' title='Refresh - Fn+W' id='refW'><b id='fref'></b></a><span id='t-' class='load'><b class='load'></b></span></div>
<div id='sqls'><div><textarea spellcheck='false' id='sqltxt'>select now();</textarea></div></div>
<div id='main'><iframe id='ifr' name='ifr' src='home.php?<?php echo $_SERVER['QUERY_STRING'];?>'></iframe></div>

<?php
	echo "<div id='SQLoadSH' class='pop' style='top:80px;left:80px'>
<form action='sqls.php?id=$SIDU[0]' method='post' enctype='multipart/form-data' target='isql'>
<p><b>",lang(3432),":</b></p>
<input type='file' name='fsql'>",html_form('submit','cmd',lang(3434),'','',"id='fsqlSub'"),"
</form></div>
<div class='hide'>sys configure
<form name='sqlrun' action='sql.php?id=$_GET[id]' target='ifr' method='post'>",html_form('hidden','sqlcur','','','',"id='sqlcur'"),html_form('hidden','sqlmore','','','',"id=sqlmore"),"</form>";
?>
<iframe id='isql' name='isql' src='sqls.php'></iframe>
<p id='connCur'><?php echo $SIDU[0];?></p>
<p id='menuCur'>100</p><p id='menuSize'>0.100.200.300</p>
<p id='sqlsCur'>100</p><p id='sqlsSize'>0.100.200.300</p>
<p id='Theme1'>body{background:#531 url(img/bg.png)}
.toolV #tool>div,#tool>div{border-color:#420}
.toolV #tool,#tool,#menu{border-color:#864}</p>
<p id='Theme2'>body{background:#fff}
#tool{background:#eed}
#tool .btn{color:#111}
.toolV #tool>div,#tool>div{border-color:#cca}
.toolV #tool,#tool{border-color:#fff}
#menu{border-color:#cca}</p>
<p id='Theme3'>body{background:#555 url(img/bg.png)}
.toolV #tool>div,#tool>div{border-color:#444}
.toolV #tool,#tool,#menu{border-color:#888}</p>
<p id='Theme4'>body{background:#444 url(img/bg.png)}
.toolV #tool>div,#tool>div{border-color:#333}
.toolV #tool,#tool,#menu{border-color:#777}</p>
<p id='Theme5'>body{background:url(img/bg5.png)}
#tool .btn{color:#531}
.toolV #tool>div,#tool>div{border-color:#b96}
.toolV #tool,#tool,#menu{border-color:#db8}</p>
<p id='LargeBtn'>#menu,#sqls{top:56px}
#tool{height:48px;line-height:65px}
#tool a,#tool b,.toolV #tool a{background:url(img/tool48.png);padding:24px}
#tool i{display:none}
.toolV #tool{width:50px}
.toolV #tool a{height:4px}
.toolV #menu{left:56px}
</p>
<p id='SmallBtn'>#tool .btn{width:24px;height:24px}</p>
</div><!--sysConfigure-->
</body>
</html>
