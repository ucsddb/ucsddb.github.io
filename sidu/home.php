<?php
if (isset($_GET['phpinfo'])){
	phpinfo(); exit;
}
include 'inc.page.php';
@main_close();
$SIDU['page']['nav']='defa';
@uppe();
@main();
@down();

function main_close(){
	$id=ceil($_GET['close']);
	if (!$id) return;
	close_sidu_conn($id);
}
function main(){
	global $SIDU;
	$conn=$SIDU['conn'][$SIDU[0]];
	$eng=$conn[1];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	echo "<div class='web'>
<div class='box right hand' style='margin-left:10px' onclick=\"top.location='conn.php?cmd=quit'\">",lang(2101),"</div>
<div class='box right hand' style='margin-left:10px'><a href='conn.php' target='_blank' style='text-decoration:none'>",lang(2102),"</a></div>
<a href='?phpinfo' class='box right'>phpinfo</a>
<div class='box left'><b>",lang(2103),":</b></div>
<p class='clear' style='margin-left:40px'>";
	foreach ($SIDU['conn'] as $conn){
		is_eng($conn[1],$is_my2,$is_pg2,$is_cb2,$is_sl2);
		echo "<br><a title='",lang(2104),"' href=",($SIDU[0]==$conn[0] ? "'conn.php?cmd=close&#38;id=$conn[0]' class='goto err'" : "'home.php?id=$SIDU[0]&#38;close=$conn[0]' class='err'"),
		"></a> <b class='ft eng_",get_engC($conn[1]),"'></b> <a href='./?id=$conn[0]'",($SIDU[0]==$conn[0] ? " class='green b'" : '')," target='_blank' title='",lang(2105),"'>",($is_sl2 ? 'SQLite' : "$conn[3] @ $conn[2]"),"</a>";
		if ($is_pg2 && !$conn[5]) $conn[5]="<i class='grey'>(5432)</i>";
		elseif ($is_my2 && !$conn[5]) $conn[5]="<i class='grey'>(3306)</i>";
		elseif ($is_cb2 && !$conn[5]) $conn[5]="<i class='grey'>(30000)</i>";
		elseif (!$is_sl2) $conn[5]="($conn[5])";
		echo " $conn[5]";
		if ($conn[6]) echo " {DB=<i class='green'>$conn[6]</i>}";
		if ($conn[8]) echo " {",lang(2106),"=<i class='blue'>$conn[8]</i>}";
		if (substr($conn[1],0,4)=='PDO_') echo " <b>PDO</b>";
	}
	$ip=SIDU_IP();
	echo "<br><br><b class='inf' style='margin-left:3px'></b>",($ip ? "<b class='green'>".lang(2107).": $ip</b>" : "<b class='red'>".lang(2108)."</b>"),"
<br><b style='margin-left:28px'></b>",lang(2109,array($_SERVER['REMOTE_ADDR'],'inc.page.php')),"</p>
<p class='box'>",html_img('sidu','',"class='vm'"),' <b>',lang(2111),"</b>
» <a href='temp.php?id=$SIDU[0]'>",lang(3427),"</a> » <a href='option.php?id=$SIDU[0]'>",lang(3429),'</a>';
	if ($is_my || $is_pg){
		$sql=($is_my ? 'SHOW+PROCESSLIST' : 'SELECT+*+from+pg_stat_activity');
		echo " » <a href='user.php?id=$SIDU[0]'>",lang(3431),"</a> » <a href='sql.php?id=$SIDU[0]&#38;sql=$sql'>PROCESSLIST</a> (check Temp howto kill)";
	}
	echo "</p><p class='ml30'>Additional menus not listed on tool bars<br>";
	if ($is_my){
		echo 'SHOW:';
		$show=array('STATUS','GRANTS','VARIABLES');
		foreach ($show as $v) echo " <a href='sql.php?id=$SIDU[0]&sql=SHOW $v'>$v</a>;";
		echo '<br>FLUSH:';
		$mysql=array('ALL','LOGS','HOSTS','PRIVILEGES','TABLES','STATUS','DES_KEY_FILE','QUERY CACHE','USER_RESOURCES','TABLES WITH READ LOCK');
		foreach ($mysql as $v) echo " <a href='sql.php?id=$SIDU[0]&sql=FLUSH $v'>$v</a>;";
	}else echo 'Table relationship map--in next release';
	echo "</p>
<p class='box hand show' id='HK' title='",lang(2110),"'><b class='ft'></b> <b>",lang(2112),' (Fn):</b> FF|Chrome (Alt+Shift+',lang(2113),') IE (Alt+',lang(2114),") Opera (Shift+Esc)</p>
<pre id='HKSH' class='ml30 hide'>» http://en.wikipedia.org/wiki/Access_key\n\n",lang(2115),"\n\n</pre>
<p class='box hand show' id='thankyou' title='",lang(2110),"'><b class='ft'></b> <b>",lang(2116),"</b></p>
<p class='ml30 hide' id='thankyouSH'><i class='green'>www.cross-browser.com/x/examples/drag3.php</i> for grid drag resize</p>
<p class='box'><b>SQL SIDU : May You be Happy and at Ease</b><br>土星善度：国土遍七宝，欢喜日日生；善护身口意，平等度一心。</p>
",lang(2117)," <i class='green'>http://topnew.net/sidu</i><br>",lang(2118),": <i class='green'>topnew@hotmail.com</i> ? subject=<i class='green'>sidu</i>
</div>";
}
?>
