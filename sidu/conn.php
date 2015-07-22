<?php
include 'inc.page.php';
@main_php($conn['eng']);
@main($conn,$cmd);

function main_php($eng){
	if (!$eng) return;
	$engs=array('my'=>array('mysql','mysql','MySQL'),'pg'=>array('pg','pgsql','PostgreSQL'),'cb'=>array('cubrid','cubrid','CUBRID'));
	if (isset($engs[$eng])){
		if (!function_exists($engs[$eng][0].'_connect')) $err="You need to install php5-{$engs[$eng][1]} to run {$engs[$eng][2]} SIDU";
	}
	if ($eng=='sl3' && !class_exists('SQLite3')) $err="You need to install php5-sqlite to run SQLite SIDU";
	if ($err) echo "<p style='color:red;padding:20px;text-align:center'>$err</p>";
}
function main_salt($cmd='set'){//set,get,del
	if ($cmd=='get') return explode('|',$_SESSION['sidu_conn_salt']);
	if ($cmd=='del') unset($_SESSION['sidu_conn_salt']);
	if ($cmd<>'set') return;
	$fm=parse_url($_SERVER['HTTP_REFERER']);
	$arr=parse_url('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
	if ($fm['host']<>$arr['host'] || $fm['path']<>$arr['path']) return;//prevent heck
	$_SESSION['sidu_conn_salt']=time().'|'.hash('sha256',rand().time().$_SERVER['SERVER_SIGNATURE'].$_SERVER['HTTP_USER_AGENT'].session_id().$_SERVER['REMOTE_ADDR']);
}
function main($conn,$cmd){
	if ($cmd=='get_login_salt'){
		main_salt();
		$arr=main_salt('get');
		$salt=benkey65($arr[1]);
		return print(implode('|',$salt));
	}
	if ($cmd=='quit'){
		setcookie(siduMD5('SIDUCONN'),'',-1);
		setcookie('SIDUSQL','',-1);
		$conn['penc']=1;
	}elseif ($cmd=='close'){
		$goto=close_sidu_conn($_GET['id']);
		if ($goto) return header('Location:./?id='.$goto);
	}elseif ($cmd==lang(1104)){//Connect
		$err=test_conn($conn);
		if (!$err) return main_conn($conn);
	}
	uppe();
	echo "<script type='text/javascript'>if (top.location!=location) top.location.href=document.location.href;</script>";
	main_form($conn,$err,$cmd);
	down();
}
function main_form($conn,$err,$cmd){
	global $SIDU;
	$lang=$conn['lang'];
	if (!lang) $lang=$SIDU['page']['lang'];
	if (!$lang) $lang='en';
	$host=$conn['host'];
	if (!$host) $host='localhost';
	$eng=$conn['eng'];
	if (!in_array($eng,array('my','pg','sl3','cb','PDO_mysql','PDO_pgsql','PDO_sqlite','PDO_cubrid','PDO_oci','PDO_mssql','PDO_firebird'))) $eng='PDO_mysql';
	$user=$conn['user'];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	if (!$user){
		if ($is_my) $user='root';
		elseif ($is_pg) $user='postgres';
		elseif ($is_cb) $user='dba';
	}elseif (!$cmd){
		if (($user=='root' || $user=='dba') && $is_pg) $user='postgres';
		elseif (($user=='postgres' || $user=='dba') && $is_my) $user='root';
		elseif (($user=='postgres' || $user=='root') && $is_cb) $user='dba';
	}
	$dbs=$conn['dbs'];
	if (!$cmd){
		if ($is_cb && !$dbs) $dbs='demodb';
		elseif (!$is_cb && $dbs=='demodb') $dbs='';
		$conn['penc']=1;
	}
	$arr_lang=array('en'=>'English','cn'=>'中文');
	echo "<form name='connf' id='connf' action='conn.php' method='post' enctype='multipart/form-data'>
<div align='center' class='web'><table class='box'><tr><td align='left'>
<div class='ar'>",html_form('select','conn[lang]',$lang,0,$arr_lang,"onchange='connf.submit()'"),"</div>
<p class='clear dot b'><br>",html_img('sidu'),' ',lang(1105),':</p>';
	if ($err) echo "<p class='err' style='width:400px'>$err</p>";
	echo '<table>',html_form('hidden','cmd','','','',"id='cmd'");
	$arr_eng=array('PDO_mysql'=>'PDO MySQL','PDO_pgsql'=>'PDO PostgreSQL','PDO_sqlite'=>'PDO SQLite','PDO_cubrid'=>'PDO CUBRID','PDO_oci'=>'PDO Oracle','PDO_mssql'=>'PDO MSSQL','PDO_firebird'=>'PDO Firebird','my'=>'MySQL 3/4/5 &nbsp; ','pg'=>'PostgreSQL 7/8/9 &nbsp; ','sl3'=>'SQLite 3 &nbsp; ','cb'=>'CUBRID 8');
	echo '<tr><td>',lang(1108),'</td><td>',html_form('select','conn[eng]',$eng,304,'',"onchange='connf.submit()'",$arr_eng),'</td></tr>';
	$is_PDO=substr($eng,0,4)=='PDO_';
	if (in_array($eng,array('my','pg','cb')) || ($is_PDO && $eng<>'PDO_sqlite')) echo "<tr><td>",lang(1109)," <b class='red'>*</b></td><td>",html_form("text","conn[host]",$host,300),"</td></tr>
<tr><td>",lang(1110)," <b class='red'>*</b></td><td>",html_form('text',"conn[user]",$user,300),"</td></tr>
<tr><td>",lang(1111),"</td><td>",html_form('password','conn[pass]','',300,'',"id='pwd'"),"</td></tr>
<tr><td>",lang(1112),"</td><td>",html_form('text','conn[port]',$conn['port'],60)," &nbsp; <input type='checkbox' id='penc' name='conn[penc]' value='1'",($conn['penc'] ? " checked='checked'" : ''),"> ",lang(1113),"</td></tr>";
//	if ($eng=='pg') echo '<tr><td></td><td>',html_form('checkbox','conn[ssl]',$conn['ssl'],'',array(1=>'')),'require SSL mode</td></tr>';
	echo "<tr><td>",lang(1114),($is_my || $is_pg ? '' : " <b class='red'>*</b>"),'</td><td>',html_form('text','conn[dbs]',$dbs,300),"</td></tr><tr><td></td><td>";
	if (!$is_PDO && $eng<>'cb') echo "eg: db1<b class='red'>;</b> db2<br>",($eng<>'my' && $eng<>'pg' ? lang(1115) : lang(1116));
	echo '</td></tr>';
	if ($is_my) echo '<tr><td>',lang(1117),'</td><td>',html_form('select','conn[char]',$conn['char'],304,get_conn_char()),'</td></tr>';
	echo "<tr><td></td><td><br>",html_form('submit','cmd',lang(1104),'','',"id='encPwd'"),"</td></tr></table>
</td></tr></table><p id='sidu_login_salt' class='hide'></p><p><br><a href='http://topnew.net/sidu'>topnew.net/sidu</a></p></div></form>";
}
function test_conn(&$conn){
	$conn=strip($conn,1,0,1);
	$conn['port']=ceil($conn['port']);
	if ($conn['port']<1) $conn['port']='';
	if ($conn['penc']){
		$salt=main_salt('get');
		$now=time();
		if ($salt[0]<$now-480 || $salt[0]>$now) return lang(1106);//salt exp in 8 minutes
		$conn['pass']=dec65($conn['pass'],$salt[1]);
	}
	$conn['dbs']=strtr($conn['dbs'],array(' '=>'',','=>';','%%'=>'%','%%%'=>'%','%%%%'=>'%'));//if you keep play with % i do not care
	$dbs=explode(';',$conn['dbs']);
	foreach ($dbs as $v){
		$v=trim($v);
		if ($v && $v<>'%' && $v<>'%%' && $v<>'%%%' && $v<>'%%%%') $db[]=$v;
	}
	$conn['dbs']=implode(';',$db);
	$eng=$conn['eng'];$host=$conn['host'];$port=$conn['port'];$user=$conn['user'];$pass=$conn['pass'];
	if ($eng=='my'){
		$res=mysql_connect($host.($port && $port<>'3306' ? ":$port" : ''),$user,$pass);
		if (!$res) $err=mysql_error();
	}elseif ($eng=='pg'){
		$res=pg_connect("host=$host".($db[0] ? " dbname=$db[0]" : '')." user=$user".($pass<>'' ? " password=$pass" : '').($port && $port<>'5432' ? " port=$port" : ''));
		if (!$res) $err=lang(1126);
	}elseif (!$conn['dbs'] && $eng<>'PDO_mysql' && $eng<>'PDO_pgsql') $err=lang(1127);
	elseif ($eng=='sl3'){
		foreach ($db as $v){
			$res=fopen($v,'a+');
			if (!$res) $err .="can not open [$v] make sure permission=RW<br>";
			fclose($v);
		}
	}elseif ($eng=='cb'){
		$res=cubrid_connect($host,(!$port ? 30000 : $port),$db[0],$user,$pass);
		if (!$res) $err=cubrid_error_msg();
	}elseif (substr($eng,0,4)=='PDO_'){//PDO added since SIDU 5.0
		$eng=substr($eng,4);
		$pdo="$eng:host=$host";
		if ($db[0]) $pdo.=";dbname=$db[0]";
		if ($eng=='cubrid' && !$port) $port=30000;
		if (($eng=='mysql' && $port && $port<>3306) || ($eng=='pgsql' && $port && $port<>5432) || $eng=='cubrid') $pdo.=";port=$port";
		if ($eng=='sqlite') $pdo="$eng:$db[0]";
		try{
			$dbh=new PDO($pdo,$user,$pass);
		}catch (PDOException $e){
			$err='Connection failed: '.$e->getMessage();
		}
	}
	return $err;
}
function main_conn($conn){
	global $SIDU;
	$cook=explode('@',dec65($_COOKIE[siduMD5('SIDUCONN')],1));
	foreach ($cook as $v){
		$arr=explode('#',$v,2);
		if ($id<$arr[0]) $id=$arr[0];
	}
	$id++;
	//0id 1eng[my|pg] 2host 3user 4pass 5port 6dbs 7save 8charset 9connID
	$cid=time()-strtotime('2012-08-08');//more then 1 user login same time same cid? not good enough fix later
	$cook[$id]="$id#$conn[eng]#$conn[host]#$conn[user]#".enc65($conn['pass'],1)."#$conn[port]#$conn[dbs]#$conn[save]#$conn[char]#$cid";
	setcookie(siduMD5('SIDUCONN'),enc65(implode('@',$cook),1));
	$mood=explode('.',$_COOKIE['SIDUMODE']);
	if ($mood[0]<>$conn['lang']){
		$mood[0]=$conn['lang'];
		setcookie('SIDUMODE',implode('.',$mood),time()+311040000);
	}
	$SIDU[0]=$id;
	$SIDU['conn'][$id][9]=$cid;
	tm_his_log('B',"$conn[eng]CONN$cid: $conn[user]@$conn[host] - ".$_SERVER['REMOTE_ADDR'],0);
	header('Location:./?id='.$id);
}
function get_conn_char(){
	$res[0]=lang(1128);
	$char='utf8:UTF-8 Unicode|armscii8:ARMSCII-8 Armenian|ascii:US ASCII|big5:Big5 繁体中文|binary:Binary pseudo charset|cp1250:Windows Central European|cp1251:Windows Cyrillic|cp1256:Windows Arabic|cp1257:Windows Baltic|cp850:DOS West European|cp852:DOS Central European|cp866:DOS Russian|cp932:SJIS for Windows Japanese|dec8:DEC West European|eucjpms:UJIS for Windows Japanese|euckr:EUC-KR Korean|gb2312:GB2312 简体中文|gbk:GBK 简体中文|geostd8:GEOSTD8 Georgian|greek:ISO 8859-7 Greek|hebrew:ISO 8859-8 Hebrew|hp8:HP West European|keybcs2:DOS Kamenicky Czech-Slovak|koi8r:KOI8-R Relcom Russian|koi8u:KOI8-U Ukrainian|latin1:cp1252 West European|latin2:ISO 8859-2 Central European|latin5:ISO 8859-9 Turkish|latin7:ISO 8859-13 Baltic|macce:Mac Central European|macroman:Mac West European|sjis:Shift-JIS Japanese|swe7:7bit Swedish|tis620:TIS620 Thai|ucs2:UCS-2 Unicode|ujis:EUC-JP Japanese';
	$arr=explode('|',$char);
	foreach ($arr as $v){
		$arr2=explode(':',$v,2);
		$res[$arr2[0]]="$arr2[0]: $arr2[1]";
	}
	return $res;
}
?>
