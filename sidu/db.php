<?php
//db=0id 1db 2sch 3typ 4tab 5sort1 6sort2 7sort
include 'inc.page.php';
$SIDU['page']['nav']=1;
@set_cook_db();
@uppe();
@main();
@down();

function navi(){
	global $SIDU;
	$conn=$SIDU['conn'][$SIDU[0]];
	$link=explode(',',$_GET['id']);
	$eng=$conn[1];
	sidu_sort($SIDU[5],$SIDU[6],$SIDU[7],$SIDU['page']['sortObj']);
	$obj=array('r'=>lang(1416),'v'=>lang(1417),'S'=>lang(1418),'f'=>lang(1419));
	if ($link[3]<>''){
		if ($link[3]=='r') echo "<a id='tadd' href='tab-new.php?id=$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4],$SIDU[5],$SIDU[6]' class='xwin' ",html_hkey('=',lang(1401)),"></a>&nbsp;$SIDU[sep] ";
		if ($link[3]=='r' || $link[3]=='v') echo "<a id='texp' href='#' ",html_hkey('E',lang(1402))," onclick='dbexp(\"$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3]\",\"objs[]\")'></a> ";
		if ($link[3]=='r') echo "<a id='timp' href='imp.php?id=$SIDU[0],$SIDU[1],$SIDU[2]' ",html_hkey('I',lang(1404))," class='xwin'></a>
<a id='objTool' href='#' class='show' title='",lang(1406),"'></a>
<a id='tflush' href='#' onclick=\"val('objcmd','Empty');return (confirm('",lang(1409),"') ? dataTab.submit() : false)\" ",html_hkey('-',lang(1410)),"></a> ";
		echo "<a id='tdel' href='#' onclick=\"val('objcmd','Drop'); return (confirm('",lang(1412,$obj[$SIDU[3]]),"') ? dataTab.submit() : false)\" ",html_hkey('X',lang(1413)),"></a> ",
		navi_seek($eng,$link)," <b id='tx$link[3]'></b><a class='none' href='db.php?id=$link[0],$link[1],,,,$SIDU[5],$SIDU[6]'>$link[1]</a>",
		($link[2] ? " » <a class='none' href='db.php?id=$link[0],$link[1],$link[2],$link[3],,$SIDU[5],$SIDU[6]".get_oidStr()."'>$link[2]</a>" : ''),
		($link[4]<>'' ? " » $link[4]" : '');
	}else{
		if (substr($eng,0,4)=='PDO_') $serv=$SIDU['dbL']->getAttribute(PDO::ATTR_SERVER_VERSION);
		elseif ($eng=='my') $serv=mysql_get_server_info();
		elseif ($eng=='pg'){$ver=pg_version();$serv=$ver['server'];}
		elseif ($eng=='sl3'){
			$ver=$SIDU['dbL']->version();
			$serv=$ver['versionString'];
		}elseif ($eng=='cb') $serv=cubrid_version();
		$engs=array('my'=>'MySQL','pg'=>'Postgres','sl3'=>'SQLite 3','cb'=>'CUBRID','PDO_mysql'=>'MySQL','PDO_pgsql'=>'Postgres','PDO_sqlite'=>'SQLite','PDO_cubrid'=>'CUBRID');
		$engN=$engs[$eng];
		if (!$engN) $engN=$eng;
		echo "<b class='eng_",get_engC($eng),"'></b><b class='none'>SIDU $SIDU[sidu_ver]</b> for <b class='none'>$engN</b>
$SIDU[sep] <a class='none' href='db.php?id=$link[0],,,,,$SIDU[5],$SIDU[6]'><b class='none'>",($eng=='sl3' || $eng=='PDO_sqlite' ? 'SQLite' : "$conn[3]@$conn[2]"),"</b></a> v $serv
$SIDU[sep] ",date('Y-m-d H:i:s');
	}
}
function navi_seek($eng,$link){
	global $SIDU;
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	$str="<a id='tfind' ".html_hkey('F',lang(1414))." href='tab.php?id=$link[0],";
	if ($is_pg && $_GET['oid']) $oidStr="&#38;oid=$_GET[oid]&#38;f[2]==$_GET[oid]";
	if ($is_sl) $str .="$link[1],0,r,sqlite_master&#38;f[0]==%27".($link[3]=='r' ? 'table' : 'view').'%27'.($link[4] ? "&#38;f[2]=like %27$link[4]%%27" : '');
	elseif ($is_my) $str .='information_schema,0,r,'.($link[3]=='r' ? 'TABLES&#38;f[3]=<>%27VIEW%27' : 'VIEWS')."&#38;f[1]==%27$link[1]%27".($link[4]<>'' ? "&#38;f[2]=like %27$link[4]%25%27" : '');
	elseif ($is_cb) $str .="$link[1],sys,v,db_class&#38;f[2]==%27".($link[3]=='r' ? 'CLASS' : 'VCLASS').'%27&#38;f[3]==%27'.($link[2]=='sys' ? 'YES' : 'NO').'%27'.($link[4]<>'' ? "&#38;f[0]=like %27$link[4]%25%27" : '');
	elseif ($link[3]=='f') $str .="$link[1],pg_catalog,r,pg_proc$oidStr".($link[4]<>'' ? "&#38f[1]=like %27$link[4]%%27" : '');
	else $str .="$link[1],pg_catalog,r,pg_class$oidStr".($link[3] ? "&#38;f[16]==%27$link[3]%27" : '').($link[4]<>'' ? "&#38;f[1]=like %27$link[4]%%27" : '');//f[] is not safe, in case pg changed table structure!!!
	return $str."'></a>&nbsp;$SIDU[sep]";
}
function main(){
	global $SIDU;
	$conn=$SIDU['conn'][$SIDU[0]];
	$link=explode(',',$_GET['id']);
	is_eng($conn[1],$is_my,$is_pg,$is_cb,$is_sl);
	echo "<form id='dataTab' name='dataTab' action='db.php?id=$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4],$SIDU[5],$SIDU[6]' method='post'><input type='hidden' id='objcmd' name='objcmd'>";
	tab_tool();
	//these 4 can be merged
	$oidStr=get_oidStr();
	if ($link[3]=='r') main_tab($SIDU,$link,$conn,$oidStr,$is_my,$is_pg,$is_cb,$is_sl);
	elseif ($link[3]=='v') main_view($SIDU,$link,$conn,$oidStr,$is_my,$is_pg,$is_cb,$is_sl);
	elseif ($link[3]=='S' && $is_pg) main_seq($SIDU,$link,$conn);
	elseif ($link[3]=='f' && $is_pg) main_func($SIDU,$link,$conn);
	else main_db($SIDU,$link,$conn,$is_my,$is_pg,$is_cb,$is_sl);
	echo '</form>';
/*
echo "======the following is testing======";
$SIDU[0]=1;
	$conn=$SIDU['conn'][$SIDU[0]];
$link=array(1,'mysql',0,'r','help');
	main_tab($SIDU,$link,$conn,$oidStr);
echo "<pre>conn=$SIDU[0];db=$SIDU[1];$SIDU[2];$SIDU[3];$SIDU[4];dbL=$SIDU[dbL]";
print_r($SIDU['conn']);
*/
}
function main_db(&$SIDU,$link,$conn,$is_my,$is_pg,$is_cb,$is_sl){
	if ($conn[6]) $dbs=explode(';',$conn[6]);
	if ($is_my){
		if (!$link[1]){
			foreach ($dbs as $db) $where .=" OR a.SCHEMA_NAME LIKE '$db%'";
			if ($where) $where=' WHERE '.substr($where,4);
		}else $where=" WHERE a.SCHEMA_NAME='$link[1]'";
		$rows=get_rows("SELECT a.SCHEMA_NAME,a.DEFAULT_CHARACTER_SET_NAME,\nif(b.TABLE_TYPE='VIEW','v','r'),count(b.TABLE_NAME),sum(DATA_LENGTH+INDEX_LENGTH)\nFROM information_schema.SCHEMATA a LEFT JOIN information_schema.TABLES b\non a.SCHEMA_NAME=b.TABLE_SCHEMA$where GROUP BY 1,2,3",'NUM');
		foreach ($rows as $row){
			$arr[$row[0]][0][0][0][$row[2]]=$row[3];
			$arr[$row[0]][1]=$row[1];
			$arr[$row[0]][5]+=$row[4];
			$arr[$row[0]][0][0][1]=0;
		}
	}elseif ($is_pg){//0sch[oid sch typ num] 1enc 2oid 3owner 4ts 5size
		$owner=get_row('SELECT oid,rolname FROM pg_authid',2);
		if (!$link[1]){
			foreach ($dbs as $db) $where .=" OR a.datname LIKE '$db%'";
			if ($where) $where="\nAND (".substr($where,4).')';
		}else $where=" AND a.datname='$link[1]'";
		$rows=get_rows("SELECT a.oid,a.datname,pg_encoding_to_char(a.encoding),a.datdba,c.spcname,pg_database_size(a.oid)\nFROM pg_database a,pg_tablespace c\nWHERE a.datistemplate='f' AND a.dattablespace=c.oid$where ORDER BY 2",'NUM');
		foreach ($rows as $row){
			db_conn($conn,$row[1]);
			$func=get_row("select pronamespace,count(*) from pg_proc group by 1",2);
			$rows2=get_rows("SELECT a.oid,a.nspname,a.nspowner,b.relkind,count(b.oid)\nFROM pg_namespace a LEFT JOIN pg_class b ON a.oid=b.relnamespace\nWHERE a.nspname".($link[2] ? "='$link[2]'" : " NOT LIKE 'pg_toast%' AND a.nspname NOT LIKE 'pg_temp%'")."\nGROUP BY 1,2,3,4 ORDER BY 2",'NUM');
			unset($arr2);
			foreach ($rows2 as $row2){
				$arr2[$row2[0]][0][$row2[3]]=$row2[4];
				$arr2[$row2[0]][1]=$row2[1];
				$arr2[$row2[0]][2]=$owner[$row2[2]];
				$arr2[$row2[0]][0]['f']=$func[$row2[0]];
			}
			$arr[$row[1]]=array($arr2,$row[2],$row[0],$owner[$row[3]],$row[4],$row[5]);
		}
	}elseif ($is_sl){
		if ($link[1]) $dbs=array($link[1]);//forgot why this line?
		foreach ($dbs as $db){
			$SIDU['dbL']=db_conn($conn,$db);
			$stat=stat($db);
			$arr[$db][5]=$stat['size'];
			if (function_exists('posix_getpwuid')){
				$arr[$db][6]=posix_getpwuid($stat['uid']);
				$arr[$db][7]=($stat['uid']==$stat['gid'] ? $arr[$db][6] : posix_getpwuid($stat['gid']));
			}else $arr[$db][6]['name']=$arr[$db][7]['name']=getenv('USERNAME');//windows
			$arr[$db][8]=date('Y-m-d H:i:s',$stat['mtime']);
			$rows=get_rows('SELECT type,count(*) FROM sqlite_master GROUP BY 1');
			foreach ($rows as $row){
				if ($row[0]=='table') $typ='r';
				elseif ($row[0]=='view') $typ='v';
				else $typ='other';
				$arr[$db][0][0][0][$typ]=$row[1];
				$arr[$db][0][0][1]=0;
			}
			$arr[$db][0][0][0]['r']+=1;
		}
	}elseif ($is_cb){
		foreach ($dbs as $db){
			$SIDU['dbL']=db_conn($conn,$db);
			$rows=get_rows("SELECT class_type,count(*) FROM db_class WHERE is_system_class='NO' GROUP BY 1");
			foreach ($rows as $row){
				if ($row[0]=='CLASS') $typ='r';
				elseif ($row[0]=='VCLASS') $typ='v';
				else $typ='other';
				$arr[$db][0][0][0][$typ]=$row[1];
				$arr[$db][0][0][1]=0;
			}
		}
	}
	echo "<table class='grid'><tr class='th'><td>",lang(1420),'</td><td>',lang(1421),'</td><td>',lang(1422),'</td>';
	if ($is_pg) echo '<td>',lang(1423),'</td><td>',lang(1424),'</td><td>',lang(1425),'</td><td>',lang(1423),'</td><td>',lang(1426),'</td>';
	elseif ($is_sl) echo '<td>',lang(1423),'</td><td>',lang(1427),'</td>';
	echo '<td>',lang(1428),'</td><td>',lang(1429),'</td><td>',lang(1430),'</td>',($is_sl ? '<td>'.lang(1431).'</td>' : ''),'</tr>';
	foreach ($arr as $k=>$v){
		$i=0;
		foreach ($v[0] as $k1=>$v1){
			echo '<tr>';
			if (!$i){
				echo "<td><b class='ft db'></b><a href='db.php?id=$link[0],$k,,,,$SIDU[5],$SIDU[6]'",($is_pg ? " title='oid=$v[2]'" : ''),">$k</a></td><td>$v[1]</td><td class='ar'>",size2str($v[5]),'</td>';
				if ($is_pg) echo "<td>$v[3]</td><td>$v[4]</td>";
			}else echo "<td colspan='5'></td>";
			if ($is_pg) echo "<td><b class='ft sch'></b><a href='db.php?id=$link[0],$k,$v1[1],,,$SIDU[5],$SIDU[6]' title='oid=$k1'>$v1[1]</a></td><td>$v1[2]</td>
				<td class='ar'><a href='db.php?id=$link[0],$k,$v1[1],S,,$SIDU[5],$SIDU[6]'>",($v1[0]['S']+0),'</a></td>';
			elseif ($is_sl) echo "<td>{$v[6][name]}</td><td>{$v[7][name]}</td>";
			echo "<td class='ar'><a href='db.php?id=$link[0],$k,$v1[1],r,,$SIDU[5],$SIDU[6]'>",($v1[0]['r']+0),"</a></td>
			<td class='ar'><a href='db.php?id=$link[0],$k,$v1[1],v,,$SIDU[5],$SIDU[6]'>",($v1[0]['v']+0),"</a></td>
			<td class='ar'><a href='db.php?id=$link[0],$k,$v1[1],f,,$SIDU[5],$SIDU[6]'>",($v1[0]['f']+0),'</a></td>';
			if ($is_sl) echo "<td>$v[8]</td>";
			echo '</tr>';
			$i++;
		}
	}
	echo "</table><pre>\n\n";
	if (!$link[1]){
		if ($is_pg) echo "<b>CREATE DATABASE name</b> WITH ENCODING='UTF8' OWNER=postgres TABLESPACE=pg_default;
COMMENT ON DATABASE name IS 'comm';
DROP DATABASE name;
ALTER DATABASE name RENAME TO newname;
ALTER DATABASE name OWNER TO new_owner;
ALTER DATABASE name SET TABLESPACE new_tablespace;

<b>CREATE SCHEMA name</b> AUTHORIZATION postgres;
COMMENT ON SCHEMA mysch IS 'comm';
DROP SCHEMA mysch;
ALTER SCHEMA name RENAME TO newname;
ALTER SCHEMA name OWNER TO newowner;";
		elseif ($is_my) echo '<b>CREATE DATABASE</b> name;<br><b>DROP DATABASE</b> name;';
	}elseif ($is_my){
		$row=get_row("SHOW CREATE DATABASE `$link[1]`");
		echo $row[1];
	}elseif ($is_pg){
		$db=$arr[$link[1]];
		$desc=get_var("SELECT description FROM pg_shdescription WHERE objoid=$db[2]");
		echo "CREATE DATABASE \"<b>$link[1]</b>\" WITH ENCODING='<b>$db[1]</b>' OWNER=<b>$db[3]</b> TABLESPACE=<b>$db[4]</b>;\nCOMMENT ON DATABASE \"$link[1]\" IS '<b>",addslashes($desc),"</b>';";
		if ($link[2]){
			foreach ($db[0] as $sch=>$v);
			$desc=get_var("SELECT obj_description('$sch','pg_namespace')");
			echo "\n\nCREATE SCHEMA \"<b>$link[2]</b>\" AUTHORIZATION <b>$v[2]</b>;\nCOMMENT ON SCHEMA \"$link[2]\" IS '<b>",addslashes($desc),"</b>';";
		}
	}
	echo '</pre>';
}
function main_tab($SIDU,$link,$conn,$oidStr,$is_my,$is_pg,$is_cb,$is_sl){
	if ($is_my){
		$col=array('Table'=>'TABLE_NAME','Engine'=>'ENGINE','RowFMT'=>'ROW_FORMAT','Auto'=>'AUTO_INCREMENT','Rows'=>'TABLE_ROWS','Avg'=>'AVG_ROW_LENGTH','Size'=>'DATA_LENGTH','Index'=>'INDEX_LENGTH','PK'=>'PK','Created'=>'CREATE_TIME','Updated'=>'UPDATE_TIME','Checked'=>'CHECK_TIME','TabColl'=>'TABLE_COLLATION','Comment'=>'TABLE_COMMENT');
		$rows=get_rows("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE\nWHERE TABLE_SCHEMA='$link[1]'".($link[4]<>'' ? " AND TABLE_NAME LIKE '$link[4]%'" : "")."\nAND CONSTRAINT_NAME='PRIMARY' ORDER BY TABLE_NAME,ORDINAL_POSITION",'NUM');
		foreach ($rows as $row) $pk[$row[0]][]=$row[1];
		foreach ($pk as $tab=>$v) $PK[$tab]=implode(',',$v);
		$rows=get_rows("SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA='$link[1]'\nAND TABLE_TYPE<>'VIEW'".($link[4]<>'' ? " AND TABLE_NAME LIKE '$link[4]%'" : ''));
		foreach ($rows as $row){
			if ($row['TABLE_TYPE']<>'BASE TABLE') $row['TABLE_ROWS']=get_var("SELECT COUNT(*) FROM `$link[1]`.`$row[TABLE_NAME]`");
			$row['PK']=$PK[$row['TABLE_NAME']];
			foreach ($col as $k=>$v) $data[$k]=$row[$v];
			$arr[]=$data;
		}
	}elseif ($is_pg){
		$col=array('OID'=>'oid','Table'=>'relname','Owner'=>'towner','TS'=>'reltablespace','Rows'=>'Rows','Avg'=>'Avg','Size'=>'size','Index'=>'ind','PK'=>'PK','Comment'=>'comm');
		db_conn($conn,$link[1]);
		$ts=get_row('SELECT oid,spcname FROM pg_tablespace',2);
		$rows=get_rows("SELECT b.relname,b.oid,pg_get_userbyid(b.relowner) AS towner,b.reltablespace,\npg_relation_size(b.oid) AS size,pg_total_relation_size(b.oid) AS ind,\nobj_description(b.oid,'pg_class') AS comm,b.relnamespace\nFROM pg_namespace a,pg_class b\nWHERE a.oid=b.relnamespace AND a.nspname='$link[2]' AND b.relkind='$link[3]'".($link[4]<>'' ? "\nAND b.relname LIKE '$link[4]%'" : '').' ORDER BY 1');
		foreach ($rows as $row){
			$row['Rows']=get_var("SELECT COUNT(*) FROM \"$link[2]\".\"$row[relname]\"");
			$row['PK']=get_tab_key_pg($row[1],$row['relnamespace']);
			$row['reltablespace']=$ts[$row['reltablespace']];
			$row['ind'] -=$row['size'];
			$row['Avg']=ceil($row['size']/$row['Rows']);
			foreach ($col as $k=>$v) $data[$k]=$row[$v];
			$arr[]=$data;
		}
	}elseif ($is_cb){
		$rows=get_rows('SELECT class_name,key_attr_name FROM db_index_key ORDER BY key_order');
		foreach ($rows as $row) $PK[$row[0]][]=$row[1];
		$col=array('Table'=>'Table','Rows'=>'Rows','owner'=>'owner','PK'=>'PK','partition'=>'partition','reuse_oid'=>'reuser_oid');
		$rows=get_rows("SELECT * FROM db_class WHERE is_system_class='".($link[2]=='sys' ? 'YES' : 'NO')."' AND class_type='CLASS'".($link[4] ? " AND class_name LIKE '$link[4]%'" : '').' ORDER BY class_name');
		foreach ($rows as $row){
			$num=get_var("SELECT count(*) FROM \"$row[0]\"");
			$arr[]=array('Table'=>$row['class_name'],'Rows'=>$num,'owner'=>$row['owner_name'],'PK'=>implode(',',$PK[$row['class_name']]),'partition'=>$row['partitioned'],'reuse_oid'=>$row['is_reuse_oid_class']);
		}
	}elseif ($is_sl){
		$col=array('Table'=>'Table','Rows'=>'Rows','Definition'=>'Definition','PK'=>'PK');
		$rows=get_rows("SELECT name,sql FROM sqlite_master WHERE type='table'".($link[4] ? " AND name LIKE '$link[4]%'" : '').' ORDER BY name');
		foreach ($rows as $row){
			$num=get_var("SELECT count(*) FROM \"$row[0]\"");
			$arr[]=array('Table'=>$row[0],'Rows'=>$num,'Definition'=>$row[1],'PK'=>sidu_sl_pk($row[0]));
		}
		$num=get_var('SELECT count(*) FROM sqlite_master');
		$arr[]=array('Table'=>'sqlite_master','Rows'=>$num,'Definition'=>'create table sqlite_master(type text,name text,tbl_name text,rootpage int,sql text)');
	}
	cout_obj($SIDU,$link,$arr,$col,$oidStr);
	$dataMap='smallint#smallint#±32,768#2B##smallint
int#int#±2,147,483,647#4B#int#int
numeric#numeric#(x,y)#4B#real#numeric
char#char#max255?###char
varchar#varchar#max255?###varchar
text#text#max65535?##text#varchar
date 3B#date#4713bc 5874897ad#4B##date
timestamp 4B#timestamp#4713bc 294276ad#8B CURRENT_TIMESTAMP##timestamp
time 3B#time#0:0:0 24:0:0#8B##time
blob#BYTEA##blob##
enum#####
auto_increment PK#serial PK##int PK##auto_increment PK';
	echo "<br><table class='grid'><tr class='th'><td>MySQL</td><td>Postgres</td><td>Range</td><td>Storage</td><td>SQLite</td><td>CUBRID</td></tr>";
	$arr=explode("\n",$dataMap);
	foreach ($arr as $line){
		$arr2=explode('#',$line);
		echo '<tr><td>',implode('</td><td>',$arr2),'</td></tr>';
	}
	echo "</table><pre>\n\nCREATE TABLE tab(";
	if ($is_pg) echo "
\tid <i class='green'>serial</i> <i class='blue'>NOT NULL</i> <b>PRIMARY KEY</b>,
\tid2 <i class='green'>smallint</i> NOT NULL DEFAULT 0,
\tid4 <i class='green'>int</i> NOT NULL DEFAULT 0,
\tccy <i class='green'>char(3)</i> NOT NULL DEFAULT 'USD',
\tnotes <i class='green'>varchar(255)</i>,
\tcreated <i class='green'>date</i>,
\tupdated <i class='green'>timestamp</i> NOT NULL DEFAULT <i class='blue'>now()</i>,
\tprice <i class='green'>numeric(7,2)</i> NOT NULL DEFAULT 0.00,
\ttxt <i class='green'>text</i>,
CONSTRAINT uk UNIQUE (id2,ccy),
CONSTRAINT fk FOREIGN KEY (id) REFERENCES tabB(idx)
)";
	elseif ($is_my) echo "
\tid <i class='green'>int</i> <u>unsigned</u> <i class='blue'>NOT NULL</i> <i class='red'>auto_increment</i> <b>PRIMARY KEY</b>,
\tid2 <i class='green'>smallint</i> NOT NULL DEFAULT 0,
\tccy <i class='green'>char(3)</i> NOT NULL DEFAULT 'USD',
\tnotes <i class='green'>varchar(255)</i> <i class='blue'>binary</i>,
\tcreated <i class='green'>date</i> NOT NULL DEFAULT '0000-00-00',
\tupdated <i class='green'>timestamp</i> NOT NULL DEFAULT <i class='blue'>now()</i>,
\tprice <i class='green'>numeric(7,2)</i> NOT NULL DEFAULT 0.00,
\ttxt <i class='green'>text</i>,
UNIQUE uk (id2,ccy)
)";
	elseif ($is_cb) echo "
\tid <i class='green'>int</i> <i class='blue'>NOT NULL</i> <i class='red'>auto_increment</i> <b>PRIMARY KEY</b>,
\tid2 <i class='green'>smallint</i> NOT NULL DEFAULT 0,
\tccy <i class='green'>char(3)</i> NOT NULL DEFAULT 'USD',
\tnotes <i class='green'>varchar(255)</i> NOT NULL DEFAULT '',
\tcreated <i class='green'>date</i>,
\tupdated <i class='green'>timestamp</i>,
\tprice <i class='green'>numeric(7,2)</i> NOT NULL DEFAULT 0.00,
CONSTRAINT tab_uk UNIQUE (id2),
FOREIGN KEY (id) REFERENCES tabB(id)
)";
	elseif ($is_sl) echo "
\tid <i class='green'>int</i> <b>PRIMARY KEY</b>,
\tccy <i class='green'>text</i>,
\tprice <i class='green'>real</i>
)";
	echo '</pre>';
}
function cout_obj($SIDU,$link,$arr,$col,$oidStr){
	$arr=sort_arr($arr,$SIDU[5],$SIDU[6]);
	$right=array('Rows','Avg','Size','Auto','Index','cur','min');
	$slink="db.php?id=$link[0],$link[1],$link[2],$link[3],$link[4],$SIDU[5],$SIDU[6]";
	echo "<table class='grid'><tr class='th'><td class='cbox'><input type='checkbox' id='checkAll' style='width:15px'></td>";
	if ($SIDU['page']['lang']<>'en') $colStr=lang(1432);
	foreach ($col as $k=>$v){
		$align[$k]=(in_array($k,$right) ? " align='right'" : '');
		echo '<td><a',get_sort_css($k,$SIDU[5],$SIDU[6])," href='$slink,$k'>",($colStr[$k] ? $colStr[$k] : $k),'</a></td>';
	}
	echo '</tr>';
	$obj=($SIDU[3]=='r' ? 'Table' : ($SIDU[3]=='v' ? 'View' : ($SIDU[3]=='S' ? 'Seq' : 'Func')));
	foreach ($arr as $i=>$row){
		echo "<tr><td class='cbox'>",html_form('checkbox','objs[]',$_POST['objs'],'',array($row[$obj]=>'')),'</td>';
		foreach ($col as $k=>$v){
			$ttl[$k]+=($align[$k] ? $row[$k] : 1);
			$url="tab.php?id=$link[0],$link[1],$link[2],$link[3],".$row[$k].$oidStr;
			if ($k=='Table' || $k=='View') $row[$k]="<a href='$url&#38;desc=1' title='".lang(1433)."' class='ft x$SIDU[3]'></a> <a href='$url'>{$row[$k]}</a>";
			elseif ($k=='Size' || $k=='Index' || $k=='Avg') $row[$k]=size2str($row[$k]);
			elseif ($k=='Definition') $row[$k]=html_form('text','n',substr($row[$k],0,100),200,0,"class='bg1 Hpop'")."<div class='pop'>".html_form('textarea','n',$row[$k])."</div>";
			echo "<td{$align[$k]}",($k=='Rows' || $k=='PK' ? " class='green'" : ($k=='Auto' && $row[$k]>2000000000 ? " class='red'" : '')),">{$row[$k]}</td>";
		}
		echo '</tr>';
	}
	if ($SIDU[3]=='r'){
		echo '<tr><td></td>';
		foreach ($ttl as $k=>$v){
			echo "<td{$align[$k]}>";
			if ($k=='Table') echo "Total $v Tables";
			elseif ($k=='Rows') echo $v;
			elseif ($k=='Size' || $k=='Index') echo size2str($v);
		 	echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
}
function get_tab_key_pg($tab,$nsp){
	$pk=get_var("SELECT pg_get_constraintdef(oid,TRUE) FROM pg_constraint\nWHERE contype='p' AND conrelid=$tab AND connamespace=$nsp");
	if (!$pk) return;
	return substr($pk,13,-1);
}
function size2str($i){
	if ($i<1024) $c='grey';
	elseif ($i<1048576) $c='green';//1m
	elseif ($i<10485760) $c='';//10m
	elseif ($i<104857600) $c='blue';//100m
	else $c='red';
	if ($i<10000) $i=$i.'B';
	elseif ($i<10238976) $i=round($i/1024).'K';
	elseif ($i<10484711424) $i=round($i/1048576).'M';
	else $i=round($i/1073741824,1).'G';
	if (!$c) return $i;
	return "<span class='$c'>$i</span>";
}
function main_view($SIDU,$link,$conn,$oidStr,$is_my,$is_pg,$is_cb,$is_sl){
	if ($is_my){
		$col=array('View'=>'View','Rows'=>'Rows','Owner'=>'Owner','Definition'=>'Definition');
		$arr=get_rows("SELECT TABLE_NAME View,VIEW_DEFINITION as def,DEFINER Owner\nFROM information_schema.VIEWS WHERE TABLE_SCHEMA='$link[1]'".($link[4]<>'' ? " AND TABLE_NAME LIKE '$link[4]%'" : '').' ORDER BY 1');
		foreach ($arr as $i=>$v){
			$arr[$i]['Rows']=get_var("SELECT COUNT(*) FROM `$link[1]`.`$v[View]`");
			$arr[$i]['Definition']=trim(str_replace('/* ALGORITHM=UNDEFINED */','',$v['def']));
		}
	}elseif ($is_pg){
		$col=array('OID'=>'oid','View'=>'relname','Owner'=>'towner','TS'=>'reltablespace','Rows'=>'Rows','Definition'=>'def','Comment'=>'comm');
		db_conn($conn,$link[1]);
		$ts=get_row("SELECT oid,spcname FROM pg_tablespace",2);
		$rows=get_rows("SELECT b.relname,b.oid,pg_get_userbyid(b.relowner) AS towner,b.reltablespace,\nobj_description(b.oid,'pg_class') AS comm,pg_get_viewdef(b.oid) AS def\nFROM pg_namespace a,pg_class b WHERE a.oid=b.relnamespace\nAND a.nspname='$link[2]' AND b.relkind='$link[3]'".($link[4]<>'' ? " AND b.relname LIKE '$link[4]%'" : '').' ORDER BY 1');
		foreach ($rows as $row){
			$row['Rows']=get_var("SELECT COUNT(*) FROM \"$link[2]\".\"$row[relname]\"");
			$row['reltablespace']=$ts[$row['reltablespace']];
			foreach ($col as $k=>$v) $data[$k]=$row[$v];
			$arr[]=$data;
		}
	}elseif ($is_cb){
		$col=array('View'=>'View','Rows'=>'Rows','owner'=>'owner','partition'=>'partition','reuse_oid'=>'reuser_oid');
		$rows=get_rows("SELECT * FROM db_class WHERE is_system_class='".($link[2]=='sys' ? 'YES' : 'NO')."' AND class_type='VCLASS'".($link[4] ? " AND class_name LIKE '$link[4]%'" : '').' ORDER BY class_name');
		foreach ($rows as $row){
			$num=get_var("SELECT count(*) FROM \"$row[0]\"");
			$arr[]=array('View'=>$row['class_name'],'Rows'=>$num,'owner'=>$row['owner_name'],'partition'=>$row['partitioned'],'reuse_oid'=>$row['is_reuse_oid_class']);
		}
	}elseif ($is_sl){
		$col=array('View'=>'View','Rows'=>'Rows','Definition'=>'Definition');
		$rows=get_rows("SELECT name,sql FROM sqlite_master WHERE type='view'".($link[4]<>'' ? " AND name LIKE '$link[4]%'" : ''));
		foreach ($rows as $row){
			$num=get_var("SELECT count(*) FROM $row[0]");
			$arr[]=array('View'=>$row[0],'Rows'=>$num,'Definition'=>$row[1]);
		}
	}
	cout_obj($SIDU,$link,$arr,$col,$oidStr);
	echo "<pre>\n\n<b>CREATE VIEW</b> vvv <b>AS</b>\nSELECT * FROM tab WHERE col&gt;5</pre>";
}
function main_seq($SIDU,$link,$conn){
	$col=array('OID'=>'oid','Seq'=>'relname','Owner'=>'towner','TS'=>'reltablespace','cur'=>'cur','min'=>'min','max'=>'max','inc'=>'inc','cache'=>'cache','cycle'=>'cycle','called'=>'called','min'=>'min','Comment'=>'comm');
	db_conn($conn,$link[1]);
	$ts=get_row('SELECT oid,spcname FROM pg_tablespace',2);
	$rows=get_rows("SELECT b.relname,b.oid,pg_get_userbyid(b.relowner) AS towner,b.reltablespace,\nobj_description(b.oid,'pg_class') AS comm\nFROM pg_namespace a,pg_class b\nWHERE a.oid=b.relnamespace AND a.nspname='$link[2]' AND b.relkind='$link[3]'".($link[4]<>'' ? " AND b.relname LIKE '$link[4]%'" : '').' ORDER BY 1');
	foreach ($rows as $row){
		$row['reltablespace']=$ts[$row['reltablespace']];
		$seq=get_row("SELECT * FROM $row[relname]");
		foreach ($col as $k=>$v) $data[$k]=$row[$v];
		$data['min']=$seq['min_value'];
		$data['max']=$seq['max_value'];
		$data['inc']=$seq['increment_by'];
		$data['cache']=$seq['cache_value'];
		$data['cycle']=$seq['is_cycled'];
		$data['called']=$seq['is_called'];
		$data['cur']=$seq['last_value'];
		if ($seq['is_called']=='f') $data['cur']-=$data['inc'];
		$arr[]=$data;
	}
	cout_obj($SIDU,$link,$arr,$col);
}
function main_func($SIDU,$link,$conn){
	$col=array('OID'=>'oid','Func'=>'proname','Owner'=>'towner','return'=>'prorettype','lang'=>'prolang','Definition'=>'prosrc','Comment'=>'comm');
	db_conn($conn,$link[1]);
	$lang=get_row('SELECT oid,lanname FROM pg_language',2);
	$typ=get_row('SELECT oid,typname FROM pg_type',2);
	$rows=get_rows("SELECT b.proname,b.oid,pg_get_userbyid(b.proowner) AS towner,b.pronamespace,\nobj_description(b.oid,'pg_proc') AS comm,b.proargtypes,b.prorettype,b.prolang,b.prosrc\nFROM pg_namespace a,pg_proc b\nWHERE a.oid=b.pronamespace AND a.nspname='$link[2]'".($link[4]<>'' ? " AND b.proname LIKE '$link[4]%'" : '').' ORDER BY 1');
	foreach ($rows as $row){
		$row['prorettype']=$typ[$row['prorettype']];
		$row['prolang']=$lang[$row['prolang']];
		$para=explode(' ',trim($row['proargtypes']));
		unset($parr);
		foreach ($para as $v) $parr[]=$typ[$v];
		$para=implode(',',$parr);
		if ($para) $row['proname'] .="($para)";
		foreach ($col as $k=>$v) $data[$k]=$row[$v];
		$arr[]=$data;
	}
	cout_obj($SIDU,$link,$arr,$col);
}
function set_cook_db(){
	global $SIDU;
	if (!$SIDU[1]) return;
	$cook=$SIDU['cook'][$SIDU[0]];
	if ($SIDU[1]<>$cook[1]) $arr=array($SIDU[0],$SIDU[1]);//db
	elseif ($SIDU[2]<>$cook[2]) $arr=array($SIDU[0],$SIDU[1],$SIDU[2]);//sch
	elseif ($SIDU[3]<>$cook[3])	$arr=array($SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3]);//typ
	if (isset($arr) && $cook<>$arr) update_sidu_cook($arr);
}
?>
