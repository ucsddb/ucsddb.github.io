<?php
//0id 1xdb 2xsch 3modeTab 4user@host 5db 6table
include 'inc.page.php';
$SIDU['page']['nav']='defa';
@uppe();
echo "<div class='web'>";
@main($SIDU,$user,$acs,$acs2,$grant,$cmd);
echo '</div>';
@down();

function main($SIDU,$user,$acs,$acs2,$grant,$cmd){
	$eng=$SIDU['eng'];
	if ($eng<>'my' && $eng<>'PDO_mysql') return main2();
	$tabs=array(lang(4701),lang(4702),lang(4703));
	foreach ($tabs as $k=>$v) echo ($k==$SIDU[3] ? "<div class='box left b red mr'>$v</div>" : "<div class='box left bg1 mr'><a class='anone' href='user.php?id=$SIDU[0],,,$k,$SIDU[4]'>$v</a></div>");
	echo "<p class='clear'></p><form class='clear' action='user.php?id=$SIDU[0],,,$SIDU[3],$SIDU[4],$SIDU[5],$SIDU[6]' method='post' id='dataTab' name='dataTab'>";
	if (!get_var('SELECT 1 FROM mysql.user LIMIT 1')) echo "<p class='err'>".lang(4704).'</p>';
	elseif ($SIDU[3]==1) main_all($SIDU,$acs,$acs2,$grant,$cmd);
	elseif ($SIDU[3]==2) main_db($SIDU,$acs,$acs2,$cmd);
	else main_user($SIDU,$user,$cmd);
	echo '</form>';
}
function main_all($SIDU,$acs,$acs2,$grant,$cmd){
	$acs_data=array('Select','Insert','Delete','Update','File');
	$acs_adm=array('Super','Reload','Shutdown','Process','References','Show_db','Lock_tables','Repl_slave','Repl_client','Create_user');
	$str=main_all_init($SIDU,$acs_data,$acs_adm,$row);
	if ($cmd==lang(4713)){
		main_all_save($SIDU,$acs,$acs2,$grant,$str['ttl']);
		$str=main_all_init($SIDU,$acs_data,$acs_adm,$row);
	}
	echo "<p class='b dot'>",lang(4705),' ',html_form('select','dummy',$SIDU[4],280,main_get_user(),"onchange='window.location=\"user.php?id=$SIDU[0],,,1,\"+this.options[this.selectedIndex].value'"),"</p>
<div style='width:200px' class='left mr'>
<p class='dot b'>".lang(4706)."</p><p>$str[data]</p>
<p class='dot'><b>".lang(4707).'</b> ('.lang(4708).')</p><p>';
	$arr=array('max_questions','max_updates','max_connections','max_user_connections');
	foreach ($arr as $v) echo html_form('text',"acs2[$v]",$row[$v],30),' ',substr($v,4),'<br>';
	echo "</p>
</div><div class='left mr'><p class='dot b'>".lang(4710)."</p><p>$str[stru]</p></div>
<div class='left'><p class='dot b'>".lang(4709)."</p><p>$str[adm]</p></div>
<p class='clear'><br><span><input type='checkbox' id='checkAll'></span> ".lang(4714)."
&nbsp; <input type='checkbox' name='grant' value='Y'",($row['Grant_priv']=='Y' ? " checked='checked'" : ''),"> ",lang(4711),"
&nbsp; ",html_form('submit','cmd',lang(4713)),'</p><p><br>',lang(4712),'</p>';
}
function main_all_save($SIDU,$acs,$acs2,$grant,$ttl){
	$user=str_replace('@',"'@'",$SIDU[4]);
	tm_run("REVOKE ALL PRIVILEGES ON *.* FROM '$user'");
	if (!isset($grant)) tm_run("REVOKE GRANT OPTION ON *.* FROM '$user'");
	else $strGrant=' GRANT OPTION';
	$prev='GRANT';
	if (!isset($acs)) $prev='GRANT USAGE';
	elseif (count($acs)==$ttl-1) $prev='GRANT ALL PRIVILEGES';
	else{
		$tran=array('Show_db'=>'SHOW DATABASES','Repl_'=>'REPLICATION ','_tmp_table'=>' TEMPORARY TABLES','_'=>' ');
		$prev='GRANT '.strtoupper(strtr(implode(', ',$acs),$tran));
	}
	foreach ($acs2 as $k=>$v) $acs2[$k]=abs(ceil($v));
	$prev .=" ON *.* TO '$user' WITH$strGrant MAX_QUERIES_PER_HOUR $acs2[max_questions] MAX_CONNECTIONS_PER_HOUR $acs2[max_connections] MAX_UPDATES_PER_HOUR $acs2[max_updates] MAX_USER_CONNECTIONS $acs2[max_user_connections]";
	tm_run($prev);
}
function main_all_init($SIDU,$acs_data,$acs_adm,&$row){
	if (!$SIDU[4]) return;
	$user=explode('@',$SIDU[4]);
	$kv=get_row("SELECT * FROM mysql.user WHERE user='$user[0]' and host='$user[1]'",0,'ASSOC');
	$str['ttl']=0;
	foreach ($kv as $k=>$v){if (substr($k,-5)=='_priv'){
		$cur=substr($k,0,-5);
		$str_cur="<input type='checkbox' name='acs[]' value='$cur'".($v=='Y' ? " checked='checked'" : '')."> $cur<br>";
		if (in_array($cur,$acs_data)) $str['data'] .=$str_cur;
		elseif (in_array($cur,$acs_adm)) $str['adm'] .=$str_cur;
		elseif($cur<>'Grant') $str['stru'] .=$str_cur;
		$str['ttl']++;
	}}
	return $str;
}
function main_db($SIDU,$acs,$acs2,$cmd){
	$rows=get_rows('show fields from mysql.db','ASSOC');
	foreach ($rows as $row){
		if (substr($row['Field'],-5)=='_priv') $col[]=substr($row['Field'],0,-5);
	}
	$user=explode('@',$SIDU[4],2);
	$arr=get_row("select * from mysql.db where host='$user[1]' and user='$user[0]'",'Db');
	if ($cmd==lang(4715)) $arr=main_db_save("'$user[0]'@'$user[1]'",$acs,$arr,count($col)-1);
	echo "<p class='b dot'>",lang(4716),' ',html_form('select','dummy',$SIDU[4],272,main_get_user(),"onchange='window.location=\"user.php?id=$SIDU[0],,,2,\"+this.options[this.selectedIndex].value'"),"</p>
<p class='b dot'>",lang(4717,array("<i class='red'>$SIDU[4]</i>")),"</p>
<p class='inf'>",lang(4718),"</p>
<div style='overflow:auto'>
<table class='grid'><tr class='th'><td>",lang(4719),"</td><td>",implode('</td><td>',$col),'</td></tr>';
	$dbs=get_row('show databases',1);
	foreach ($dbs as $db){if ($db<>'information_schema'){
		echo "<tr><td><a href='user.php?id=$SIDU[0],,,2,$SIDU[4],$db'>$db</a></td>";
		foreach ($col as $v) echo "<td class='ac'><input type='checkbox' name='acs[$db][]' value='$v'",($arr[$db][$v.'_priv']=='Y' ? " checked='checked'" : ''),'></td>';
		echo '</tr>';
	}}
	echo "</table></div><p class='ac'><br>",html_form('submit','cmd',lang(4715)),'</p>';
	if ($SIDU[5]) main_db_tab($SIDU,$acs2,$user,$cmd);
}
function main_db_save($user,$acs,$arr,$num_all){
	$tran=array('_tmp_table'=>' TEMPORARY TABLES','_'=>' ');
	foreach ($acs as $db=>$v){//acs:new
		if (isset($arr[$db])){//arr:old
			tm_run("REVOKE ALL PRIVILEGES ON $db.* FROM $user");
			if (!in_array('Grant',$v) && $arr[$db]['Grant_priv']=='Y') tm_run("REVOKE GRANT OPTION ON $db.* FROM $user");
		}
		unset($arr2);
		foreach ($v as $v2){
			$RES[$db][$v2.'_priv']='Y';
			if ($v2<>'Grant') $arr2[]=strtr($v2,$tran);
		}
		$num=count($arr2);
		if ($num==$num_all) $prev='ALL PRIVILEGES';
		elseif (!$num) $prev='USAGE';
		else $prev=strtoupper(implode(', ',$arr2));
		tm_run("GRANT $prev ON $db.* TO $user".(in_array('Grant',$v) ? ' WITH GRANT OPTION' : ''));
	}
	foreach ($arr as $db=>$v){if (!isset($acs[$db])){
		tm_run("REVOKE ALL PRIVILEGES ON $db.* FROM $user");
		if ($arr[$db]['Grant_priv']=='Y') tm_run("REVOKE GRANT OPTION ON $db.* FROM $user");
	}}
	return $RES;
}
function main_db_tab($SIDU,$acs2,$user,$cmd){
	$col=array('Select','Insert','Update','Delete','Create','Drop','Grant','References','Index','Alter','Create View','Show view');
	$arr=get_row("select table_name,table_priv from mysql.tables_priv where host='$user[1]' and user='$user[0]' and db='$SIDU[5]'",2);
	foreach ($arr as $k=>$v) $arr2[$k]=explode(',',$v);
	if ($cmd==lang(4720)) $arr2=main_db_tab_save("'$user[0]'@'$user[1]'",$SIDU[5],$acs2,$arr2);
	echo "<p class='b dot'>",lang(4721,array("<i class='red'>$SIDU[5]</i>")),"</p>
<p class='inf'>",lang(4722),"</p>
<div style='overflow:auto;max-height:200px'>
<table class='grid'><tr class='th'><td>",lang(4723),"</td><td>".implode("</td><td>",$col),"</td></tr>";
	$tabs=get_row("show tables from $SIDU[5]",1);
	foreach ($tabs as $tab){
		echo "<tr><td>$tab</td>";
		foreach ($col as $v) echo "<td align='center'><input type='checkbox' name='acs2[$tab][]' value='$v'",(in_array($v,$arr2[$tab]) ? " checked='checked'" : ''),"></td>";
		echo "</tr>";
	}
	echo "</table></div><p align='center'><br>",html_form('submit','cmd',lang(4720)),"</p>
<p class='inf'>",lang(4724),"</p>
<p><i>GRANT SELECT, INSERT, UPDATE(id,name), REFERENCES(id) ON $SIDU[5].table_name TO '$user[0]'@'$user[1]'</i></p>
<p>",lang(4725,array('<b>mysql.columns_priv</b>')),'</p>';
}
function main_db_tab_save($user,$db,$acs,$arr){
	foreach ($acs as $tab=>$v){//acs:new
		if (isset($arr[$tab])){//arr:old
			tm_run("REVOKE ALL PRIVILEGES ON $db.$tab FROM $user");
			if (!in_array('Grant',$v) && in_array('Grant',$arr[$tab]))
				tm_run("REVOKE GRANT OPTION ON $db.$tab FROM $user");
		}
		unset($arr2); $strGrant='';
		foreach ($v as $v2){
			if ($v2<>'Grant') $arr2[]=strtoupper($v2);
			else $strGrant=' WITH GRANT OPTION';
		}
		tm_run('GRANT '.(count($arr2)<1 ? 'USAGE' : implode(', ',$arr2))." ON $db.$tab TO $user$strGrant");
	}
	foreach ($arr as $tab=>$v){
		if (!isset($acs[$tab])){
			tm_run("REVOKE ALL PRIVILEGES ON $db.$tab FROM $user");
			if (in_array('Grant',$v)) tm_run("REVOKE GRANT OPTION ON $db.$tab FROM $user");
		}
	}
	return $acs;
}
function main_user($SIDU,$user,$cmd){
	if ($cmd) $err=main_user_save($user,$cmd);
	else{
		$arr=explode('@',$SIDU[4]);
		$user['name']=$arr[0]; $user['host']=$arr[1];
	}
	echo "<p class='b dot'>",lang(4729),"</p>
<p class='inf'>",lang(4730),"</p>
<p class='err'>$err</p><table>
<tr><td>",lang(4731),":</td><td>",html_form('select','dummy',$SIDU[4],286,main_get_user(1),"onchange='window.location=\"user.php?id=$SIDU[0],,,0,\"+this.options[this.selectedIndex].value'"),"</td></tr>
<tr><td><br>",lang(4733),":</td><td><br>",html_form('text','user[name]',$user['name'],280,16),"</td></tr>
<tr><td>",lang(4734),":</td><td>",html_form('text','user[host]',$user['host'],280,60),"</td></tr>
<tr><td>",lang(4735),":</td><td>",html_form('password','user[pass]',$user['pass'],280),"</td></tr>
<tr><td>",lang(4736),":</td><td>",html_form('password','user[pass2]',$user['pass2'],280),"</td></tr>
<tr><td></td><td><br>",html_form('submit','cmd',lang(4737),100),' ',html_form('submit','cmd',lang(4738),100),"</td></tr>
</table>
<p class='dot'>&nbsp;</p><p class='inf'>",lang(4739),"</p>
<pre>
<b>MySQL SQL</b>

REVOKE ALL PRIVILEGES ON *.* FROM user;
REVOKE GRANT OPTION ON *.* FROM user;

GRANT USAGE ON *.* TO user;
GRANT ALL PRIVILEGES ON *.* TO user WITH GRANT OPTION;

GRANT SELECT,INSERT,DELETE,UPDATE,FILE,
SUPER,RELOAD,SHUTDOWN,PROCESS,REFERENCES,SHOW DATABASES,LOCK TABLES,
REPLICATION SLAVE,REPLICATION CLIENT,CREATE USER 
ON *.* TO user WITH GRANT OPTION
MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 
MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;

REVOKE ALL PRIVILEGES ON db.* FROM user;
REVOKE GRANT OPTION ON db.* FROM user;

GRANT USAGE USAGE ON db.* TO user;
GRANT ALL PRIVILEGES ON db.* TO user WITH GRANT OPTION;

REVOKE ALL PRIVILEGES ON db.tab FROM user;
REVOKE GRANT OPTION ON db.tab FROM user;

GRANT USAGE USAGE ON db.tab TO user;
GRANT ALL PRIVILEGES ON db.tab TO user WITH GRANT OPTION;
</pre>";
}
function main_user_save($user,$cmd){
	$user=strip($user,1,1,1);
	$userH="<b>$user[name]@$user[host]</b>";
	if ($cmd==lang(4737)){
		tm_run("DROP USER '$user[name]'@'$user[host]'");
		$err=sidu_err(1);
		if ($err) return $err;
		echo "<p class='ok'>".lang(4726,$userH).'</p>';
		return;
	}//else edit
	$tr=array(' ',"'");
	$user['name']=str_replace($tr,'',$user['name']);
	$user['host']=str_replace($tr,'',$user['host']);
	$user['pass']=str_replace($tr,'',$user['pass']);
	if ($user['pass']<>$user['pass2']) return lang(4727);
	$pass=get_var("select password('$user[pass]')");
	$where="WHERE user='$user[name]' AND host='$user[host]' LIMIT 1";
	if (get_var("select 1 from mysql.user $where")) tm_run("UPDATE mysql.user SET Password='$pass' $where");
	else tm_run("INSERT INTO mysql.user(Host,User,Password) values('$user[host]','$user[name]','$pass')");
	$err=sidu_err(1);
	if ($err) return $err;
	echo "<p class='ok'>".lang(4728,$userH).'</p>';
}
function main_get_user($new=0){
	$rows=get_rows("SELECT Host,User,Password FROM mysql.user ORDER BY 2,1");
	foreach ($rows as $row){
		$uh="$row[1]@$row[0]";
		$arr[$uh]="$uh -- ".($row[2] ? 'Has Pwd' : 'No Pwd');
	}
	if ($new) $arr['']=lang(4732);
	return $arr;
}
function main2(){//non mysql--use manager not ready yet
	echo "<p><b>Database User</b> -- not available yet</p>
<pre>
<b>Postgres</b>

CREATE ROLE ben LOGIN ENCRYPTED PASSWORD 'md5021fae7a1b5955'
	SUPERUSER NOINHERIT CREATEDB CREATEROLE
	VALID UNTIL 'infinity';
COMMENT ON ROLE benb IS 'comm';

CREATE USER name SUPERUSER CREATEDB CREATEROLE CREATEUSER INHERIT LOGIN
ALTER Role postgres ENCRYPTED PASSWORD 'md5965fb1f623b2c';

DB:Find Variables process Lock Admin Privileges Export Create Drop Alter
Sch:Find Priv Create Drop Alter
Tab:analyze vaccum empty drop create alter

</pre>

<table class='grid'>
<tr class='th'><td>user</td><td>super</td><td>+db</td><td>+role</td><td>inherit</td><td>conn limit</td><td>expire</td></tr>
<tr><td>ben</td><td>Y</td><td>Y</td><td>Y</td><td>Y</td><td>no limit</td><td>never</td></tr>
</table>";
}
?>
