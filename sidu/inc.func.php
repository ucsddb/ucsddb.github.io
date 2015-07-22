<?php
//general func
function html8($str=''){return htmlspecialchars($str,ENT_QUOTES);}//,'UTF-8': with this para invalid str becomes empty str;fm sidu 3.5 this para been turned off, any bug found please fix this then!!!
function html_js($str=''){return "<script type='text/javascript'>$str</script>";}
function html_img($src='',$t='',$info=''){
	if (!strpos($src,'.')) $src .='.png';
	return "<img src='img/$src'".($t ? " title='$t'" : '').($info ? " $info" : '').'>';
}
function html_hkey($key='',$title=''){
	if ($key==',') $str='&lt;';
	elseif ($key=='.') $str='&gt;';
	elseif ($key=='=') $str='+';
	else $str=$key;
	return "accesskey='$key' title='$title - Fn+$str'";
}
/*textar name	val	x			 y		style
	select name	val	size	 arr	style defa
	radio	 name	val	sepa	 arr	-			defa
	cbox	 name	val	sepa	 arr	-			defa
	text	 name	val	size	 max	style
	pass	 name	val	size	 max	style
	submit name	val	size	 -		style
	hidden name	val	-			 -		-
	form	 name url method id		style */
function html_form($type='form',$name=null,$val=null,$size=null,$arr=null,$style=null,$defa=null){
	if ($type=='form') return "<form action='".($val ? $val : array_pop(explode('/',$_SERVER['SCRIPT_NAME'])))
		."' method='".($size ? $size : 'post')."'".($name ? " name='$name'" : '')
		.($arr ? " id='$arr'" : '').($style ? " $style" : '').'>';
	if ($val && !is_array($val)) $val=html8($val);
	$style=($style ? " $style" : '');
	$style_arr=explode("style='",$style,2);
	if ($type=='textarea'){
		if ($size || $arr){
			if ($size) $str="width:$size".'px;';
			if ($arr) $str .="height:$arr".'px;';
			if ($style_arr[1]) $style=$style_arr[0]."style='$str".$style_arr[1];
			else $style=" style='$str'".$style;
		}
		return "<textarea name='$name'$style>$val</textarea>";
	}
	if ($size && $type<>'radio' && $type<>'checkbox'){
		if ($style_arr[1]) $style=$style_arr[0]."style='width:$size"."px;$style_arr[1]";
		else $style=" style='width:$size"."px'$style";
	}
	$style="name='$name'$style";
	if ($type=='select'){
		$str="<select size='1' $style>";
		foreach ($defa as $k=>$v)
			$str .="<option value='$k'".(in_array($k,$val) || $k==$val ? " selected='selected'" : '').">$v</option>";
		foreach ($arr as $k=>$v)
			$str .="<option value='$k'".(in_array($k,$val) || $k==$val ? " selected='selected'" : '').">$v</option>";
		$str .="</select>";
		return $str;
	}
	if ($type=='radio' || $type=='checkbox'){
		if (!$size) $size=" ";//sepa
		foreach ($defa as $k=>$v)
			$str .="<input type='$type' $style value='$k'".($k==$val || in_array($k,$val) ? " checked='checked'" : '')."> $v$size";
		foreach ($arr as $k=>$v)
			$str .="<input type='$type' $style value='$k'".($k==$val || in_array($k,$val) ? " checked='checked'" : '')."> $v$size";
		return substr($str,0,0-strlen($size));
	}
	return "<input type='$type' $style value='$val'".(is_numeric($arr) ? " maxlength='$arr'" : '').'>';
}
function strip($val,$trim=0,$tag=0,$slash=0){//tag==1,0 or <p><b>
	if (is_array($val)){
		foreach ($val as $k=>$v) $val[$k]=strip($v,$trim,$tag,$slash);
	}else{
		if ($tag)	$val=strip_tags($val,($tag==1 ? '' : $tag));
		if ($slash) $val=stripslashes($val);
		if ($trim) $val=trim($val);
	}
	return $val;
}
function swap(&$a,&$b){$c=$a;$a=$b;$b=$c;}
//sidu only func
function initSIDU(){
	global $SIDU;
	//0lang.1gridMode.2pgSize.3tree.4sortObj.5sortData.6menuTextSQL.7menuText.8his.9hisErr.10hisSQL.11hisData.12dataEasy(pg).13oid(pg).14slconn
	$cook=explode('.',$_COOKIE['SIDUMODE']);
	$MODE['lang']=($cook[0] ? $cook[0] : 'en');//defa lang=en
	if ($_POST['opt']['lang']) $MODE['lang']=$_POST['opt']['lang'];
	elseif ($_POST['conn']['lang']) $MODE['lang']=$_POST['conn']['lang'];
	$MODE['gridMode']=$cook[1];
	$MODE['pgSize']=$cook[2];
	$cook[3]=substr($cook[3],0,1);
	$MODE['tree']=($cook[3]=='_' || ($cook[3]>-1 && $cook[3]<10) ? $cook[3] : '_');//defa tree=_
	$MODE['sortObj']=($cook[4]==2 ? 2 : 1);//defa sortObj=1
	$MODE['sortData']=($cook[5]==1 ? 1 : 2);//defa sortData=2
	$MODE['menuTextSQL']=($cook[6]=='' || $cook[6] ? 1 : 0);//0off 1on in sql window--defa=1
	$MODE['menuText']=$cook[7];//0off 1on in data window--defa=0
	$MODE['his']=($cook[8]=='' || $cook[8] ? 1 : 0);//0off 1log his--defa=1
	$MODE['hisErr']=($cook[9]=='' || !$cook[9] ? 0 : 1);//0off 1log his err--defa=0
	$MODE['hisSQL']=($cook[10]=='' || !$cook[10] ? 0 : 1);//0off 1log his in SQL window--defa=0
	$MODE['hisData']=($cook[11]=='' || $cook[11] ? 1 : 0);//0off 1log original 5 rows of data fefore upd|del--defa=1
	$MODE['dataEasy']=($cook[12]=='' || $cook[12] ? 1 : 0);//0off 1on pg int char varchar handy--defa=1
	$MODE['oid']=($cook[13]=='' || $cook[13] ? 1 : 0);//0off 1on pg show oid--defa=1
	$MODE['btn']=($cook[14]=='' || !$cook[14] ? 0 : 1);//0off 1on pg show oid--defa=1
	$SIDU['page']=$MODE;
	$SIDU['eng']=$SIDU['conn'][$SIDU[0]][1];
	$SIDU['sep']=html_img('dot','',"width='2' height='".($MODE['btn'] ? 65 : 16)."' class='vm'");
}
function siduMD5($str=''){
	$ip=(isset($_SERVER['HTTP_X_REMOTE_ADDR']) ? $_SERVER['HTTP_X_REMOTE_ADDR'] : $_SERVER['REMOTE_ADDR']);
	return $str.substr(md5($str.SIDU_PK().$ip),0,8);
}
function check_global_ip_access(){
	$ip=explode(';',SIDU_IP());
	if (!$ip[0]) return;//ok--no firewall
	foreach ($ip as $IP){
		if(ereg($IP,$_SERVER['REMOTE_ADDR'])) return;//ok
	}
	exit("Access from un-authorized IP (SIDU 防火墙阻止了 IP): $_SERVER[REMOTE_ADDR]<br><br>Please check SIDU firewall setting at <u>last line</u> of file <b>inc.page.php</b><br>OR visit <b>topnew.net/sidu</b> for solution");
}
function close_sidu_conn($id){
	global $SIDU;
	unset($SIDU['conn'][$id]);
	if (empty($SIDU['conn'])){
		setcookie(siduMD5('SIDUCONN'),'',-1);
		return;
	}
	unset($SIDU['cook'][$id]);
	foreach ($SIDU['cook'] as $v) $cook[]=implode('.',$v);
	setcookie('SIDUSQL',enc65(implode('@',$cook),1));
	foreach ($SIDU['conn'] as $k=>$v) $conn[]=implode('#',$v);
	setcookie(siduMD5('SIDUCONN'),enc65(implode('@',$conn),1));
	return $k;
}
function get_sidu_conn(){
	$conn=explode('@',dec65($_COOKIE[siduMD5('SIDUCONN')],1));
	foreach ($conn as $v){
		$arr=explode('#',$v);//0id 1eng[my|pg] 2host 3user 4pass 5port 6dbs 7save 8charset
		if ($arr[0]<>'') $res[$arr[0]]=$arr;
	}
	return $res;
}
function get_sidu_cook(){
	global $SIDU;
	$cook=explode('@',dec65($_COOKIE['SIDUSQL'],1));
	foreach ($cook as $v){
		$arr=explode('.',$v);//0id 1db 2sch 3typ 4tab
		if ($arr[0]<>'') $res[$arr[0]]=$arr;
	}
	return $res;
}
function is_eng($eng='',&$is_my,&$is_pg,&$is_cb,&$is_sl){
	$is_my=$eng=='PDO_mysql' || $eng=='my';
	$is_pg=$eng=='PDO_pgsql' || $eng=='pg';
	$is_cb=$eng=='PDO_cubrid' || $eng=='cb';
	$is_sl=$eng=='PDO_sqlite' || $eng=='sl3';
}
function get_engC($eng=''){
	if ($eng=='PDO_mysql') return 'my';
	if ($eng=='PDO_pgsql') return 'pg';
	if ($eng=='PDO_cubrid') return 'cb';
	if ($eng=='PDO_sqlite') return 'sl3';
	return $eng;
}
function update_sidu_cook($arr){
	global $SIDU;
	$SIDU['cook'][$SIDU[0]]=$arr;
	foreach ($SIDU['cook'] as $v) $res[]=implode('.',$v);
	setcookie('SIDUSQL',enc65(implode('@',$res),1));
}
function check_sidu_conn($SIDU){
	if ($SIDU[0] && isset($SIDU['conn'][$SIDU[0]])) return;//ok
	if (substr($_SERVER['SCRIPT_NAME'],-8)<>'conn.php'){
		echo html_js("top.location='./conn.php'");
		exit;//no connection
	}
}
function lang($id=0,$arr=null){
	global $LANG;
	if (!isset($arr)) return $LANG[$id];
	if (!is_array($arr)) return str_replace('%0%',$arr,$LANG[$id]);
	foreach ($arr as $k=>$v) $tr[]='%'.$k.'%';
	return str_replace($tr,$arr,$LANG[$id]);
}
function sidu_slash($eng=''){return ($eng=='my' || $eng=='PDO_mysql' || (($eng=='pg' || $eng=='PDO_pgsql') && get_var('SHOW standard_conforming_strings')<>'on'));}
function tm($cmd='',$tab='',$col=null,$val=null,$where=''){
	global $SIDU;
	if ($cmd=='SQL'){
		tm_use_db($col);
		$sql=trim($tab);//sql
		return tm_his($sql);
	}elseif ($cmd=='SQLS'){
		tm_use_db($col);
		foreach ($tab as $sql) $res=tm_his($sql);
		return $res;
	}
	foreach ($val as $k=>$v) $val[$k]=str_replace("'","''",$v);
	if (sidu_slash($SIDU['eng'])){
		foreach ($val as $k=>$v) $val[$k]=str_replace('\\','\\\\',$v);
	}
	if ($cmd=='insert' || $cmd=='replace'){
		foreach ($col as $i=>$v) $CV .=','.(strtoupper($val[$i])==='NULL' ? 'NULL' : ($val[$i]=='now()' ? $val[$i] : "'{$val[$i]}'"));
		$sql=strtoupper($cmd)." INTO $tab(".implode(',',$col).")\nVALUES(".substr($CV,1).')';
	}elseif ($cmd=='delete') $sql="DELETE FROM $tab\n$where";
	elseif ($cmd=='update'){
		foreach ($col as $i=>$v) $CV .=",{$col[$i]}=".(strtoupper($val[$i])==='NULL' ? 'NULL' : ($val[$i]=='now()' ? $val[$i] : "'{$val[$i]}'"));
		$sql="UPDATE $tab\nSET ".substr($CV,1)."\n$where";
	}
	if (($cmd=='update' || $cmd=='delete') && $where && $SIDU['page']['hisData']){//please check get_row() make sure no dead-loop here!!!
		$rows=get_rows("SELECT * FROM $tab $where LIMIT 5",'NUM');
		foreach ($rows as $row) tm_his_log('D',$row,0,$tab);
	}
	return tm_his($sql);
}
function tm_his($sql=''){
	global $SIDU;
	$time_start=microtime(true);
	$res=tm_run($sql);
	if (!$SIDU['page']['his']) return $res;
	$time_end=microtime(true);
	$time=round(($time_end - $time_start) * 1000);
	if ($SIDU['page']['hisErr']) $err=sidu_err(1);
	tm_his_log('B',$sql,$time,$err);
	return $res;
}
function tm_his_log($typ,$log,$time,$err){//[id]ts Back|Sql|Err|Data logs
	global $SIDU;
	$id=$SIDU[0];
	$ts=date('Y-m-d H:i:s');
	$cid=$SIDU['conn'][$id][9];
	$his= &$_SESSION['siduhis'][$cid];
	if ($typ=='D' && $SIDU['page']['hisData']) $hisT="$ts D 0 [$err]".implode('»',$log);
	else{
		if (($typ=='B' && $SIDU['page']['his']) || ($typ=='S' && $SIDU['page']['hisSQL'])) $hisT="$ts $typ $time $log";
		if ($err && $SIDU['page']['hisErr']) $hisT="$ts E 0 $err";
	}
	$his[]=$hisT;
/* optional save log to local file
	$fp=fopen('sidu.log','a');
	fwrite($fp,"\n\n$cid ".$hisT);
	fclose($fp);
*/
}
function db_conn($conn,$db){
	$pass=dec65($conn[4],1);
	$eng=$conn[1];$host=$conn[2];$user=$conn[3];$port=$conn[5];
	if (!$db){
		$dbs=explode(';',$conn[6],2);
		$db=$dbs[0];
	}
	if (substr($eng,0,4)=='PDO_'){//PDO added since SIDU 5.0
		$eng=substr($eng,4);
		$pdo="$eng:host=$host";
		if ($db) $pdo.=";dbname=$db";
		if ($eng=='cubrid' && !$port) $port=30000;
		if (($eng=='mysql' && $port && $port<>3306) || ($eng=='pgsql' && $port && $port<>5432) || $eng=='cubrid') $pdo.=";port=$port";
		if ($eng=='sqlite') $pdo="$eng:$db";
		if ($conn[8] && $conn[8]<>'latin1') $options=array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES '.$conn[8]);//mysql only
		return new PDO($pdo,$user,$pass,$options);
	}elseif ($eng=='my'){
		$res=mysql_connect($host.(($port && $port<>3306) ? ":$port" : ''),$user,$pass);
		if ($conn[8] && $conn[8]<>'latin1') mysql_set_charset($conn[8]);//bug---charset still not work :(
		if ($db) $res=mysql_select_db($db);
		return $res;
	}elseif ($eng=='pg'){
		$pg="host=$host user=$user";
		if ($pass) $pg .=' password='.str_replace('"','\"',$pass);
		if ($port && $port<>5432) $pg .=" port=$port";
		if ($db) $pg .=" dbname=$db";
		return pg_connect($pg);
	}elseif ($eng=='sl3') return new SQLite3($db);
	elseif ($eng=='cb') return cubrid_connect($host,($port ? $port : 30000),$db,$user,$pass);
}
function tm_use_db($db,$sch){
	global $SIDU;
	$db=trim($db);
	if (!$db) return;
	$conn=$SIDU['conn'][$SIDU[0]];
	$eng=$conn[1];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	if ($is_my) tm_his('USE '.sql_kw($db));
	elseif ($is_pg){
		$SIDU['dbL']=db_conn($conn,$db);
		if ($sch) pg_query('SET search_path to '.sql_kw($sch));
	}elseif ($is_cb || $eng=='PDO_sqlite') $SIDU['dbL']=db_conn($conn,$db);
	elseif ($eng=='sl3') $SIDU['dbL']=new SQLite3($db);
	//other PDO need to fix later...
}
function tm_run($sql){
	global $SIDU;
	$eng=$SIDU['eng'];$dbL=$SIDU['dbL'];
	if (substr($eng,0,4)=='PDO_') return $dbL->query($sql);
	if ($eng=='my') return mysql_query($sql);
	if ($eng=='pg') return pg_query($sql);
	if ($eng=='sl3') return $dbL->query($sql);
	if ($eng=='cb') return cubrid_execute($dbL,$sql);
}
function sidu_err($errStr){
	global $SIDU;
	$eng=$SIDU['eng'];$dbL=$SIDU['dbL'];
	$is_PDO=substr($eng,0,4)=='PDO_';
	if ($eng=='pg') return pg_last_error();
	if ($is_PDO) $err=$dbL->errorCode();
	elseif ($eng=='my') $err=mysql_errno();
	elseif ($eng=='sl3') $err=$dbL->lastErrorCode();
	elseif ($eng=='cb') $err=cubrid_error_code();
	if ($is_PDO){
		if (!$err || $err=='00000') return;
		$err=$dbL->errorInfo();
		if (!$errStr) return $err[1];
		return "err $err[1]: $err[2]";
	}
	if (!$errStr || !$err) return $err;
	if ($eng=='my')	return "$err ".mysql_error();
	if ($eng=='sl3')	return 'err: '.$dbL->lastErrorMsg();
	if ($eng=='cb')	return "$err: ".cubrid_error_msg();
}
//pk=0 return one row
//pk=1 return arr[]=row[0]
//pk=2 return arr[row[0]]=row[1]
//pk=3 return one var
//pk=4 return all rows
//pk=k return arr[k]=row
function get_row($sql,$pk,$mode='BOTH'){//ASSOC,NUM,BOTH
	$res=tm_his($sql);//or tm('SQL',$sql) only, definitely not insert|delete otherwise cause dead-loop in tm()
	return get_row_data($res,$pk,$mode);
}
function get_row_data($res,$pk,$mode='BOTH'){
	if (!$res) return;
	global $SIDU;
	$eng=$SIDU['eng'];
	if (!$pk || $pk==4) $mode=strtoupper($mode);
	elseif ($pk>0 && $pk<4) $mode='NUM';
	else $mode='ASSOC';
	if (substr($eng,0,4)=='PDO_'){
		$modeR=constant('PDO::FETCH_'.$mode);
		if (!$pk) return $res->fetch($modeR);
		if ($pk==3){
			$row=$res->fetch(PDO::FETCH_NUM); return $row[0];
		}
		if ($pk==1){
			$rows=$res->fetchAll(PDO::FETCH_NUM);
			foreach ($rows as $row) $arr[]=$row[0];
		}elseif ($pk==2){
			$rows=$res->fetchAll(PDO::FETCH_NUM);
			foreach ($rows as $row) $arr[$row[0]]=$row[1];
		}elseif ($pk==4){
			$arr=$res->fetchAll($modeR);
		}else{
			$rows=$res->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) $arr[$row[$pk]]=$row;
		}
		return $arr;
	}
	$db_type=array('my'=>'MYSQL','pg'=>'PGSQL','cb'=>'CUBRID','sl3'=>'SQLITE3');
	$modeR=constant($db_type[$eng].'_'.$mode);
	if ($eng=='pg'){
		$fn='pg_fetch_';
		if ($mode=='NUM') $fn.='row';
		elseif ($mode=='BOTH') $fn.='array';
		else $fn.='assoc';
		if (!$pk) return $fn($res);
		if ($pk==3){
			$row=$fn($res); return $row[0];
		}
		if ($pk==1){
			while ($row=$fn($res)) $arr[]=$row[0];
		}elseif ($pk==2){
			while ($row=$fn($res)) $arr[$row[0]]=$row[1];
		}elseif ($pk==4){
			while ($row=$fn($res)) $arr[]=$row;
		}else{
			while ($row=$fn($res)) $arr[$row[$pk]]=$row;
		}
		return $arr;
	}
	if ($eng=='sl3'){
		if (!$res) return;//this err took me lots of time to figure out!!!
		if (!$pk) return $res->fetchArray($modeR);
		if ($pk==3){
			$row=$res->fetchArray(SQLITE3_NUM); return $row[0];
		}
		if ($pk==1){
			while ($row=$res->fetchArray(SQLITE3_NUM)) $arr[]=$row[0];
		}elseif ($pk==2){
			while ($row=$res->fetchArray(SQLITE3_NUM)) $arr[$row[0]]=$row[1];
		}elseif ($pk==4){
			while ($row=$res->fetchArray($modeR)) $arr[]=$row;
		}else{
			while ($row=$res->fetchArray(SQLITE3_ASSOC)) $arr[$row[$pk]]=$row;
		}
		return $arr;
	}
	if ($eng=='my') $fn='mysql_fetch_array';
	elseif ($eng=='cb') $fn='cubrid_fetch';
	if (!$pk) return $fn($res,$modeR);
	if ($pk==3){
		$row=$fn($res,$modeR); return $row[0];
	}
	if ($pk==1){
		while ($row=$fn($res,$modeR)) $arr[]=$row[0];
	}elseif ($pk==2){
		while ($row=$fn($res,$modeR)) $arr[$row[0]]=$row[1];
	}elseif ($pk==4){
		while ($row=$fn($res,$modeR)) $arr[]=$row;
	}else{
		while ($row=$fn($res,$modeR)) $arr[$row[$pk]]=$row;
	}
	return $arr;
}
function get_rows($sql,$mode='BOTH'){return get_row($sql,4,$mode);}
function get_var($sql){return get_row($sql,3);}
function sidu_sort(&$s1,&$s2,&$sort,$mode){
	if (!$sort) return;
	if ($mode==1){//1 sort | 2 sort
		$s1=$sort.($s1=="$sort desc" ? '' : ' desc');
		$s2=$sort='';
		return;
	}
	if (!$s1) $s1="$sort desc";
	elseif ($s1=="$sort desc") $s1=$sort;
	elseif ($s1==$sort){$s1=$s2;$s2='';}
	elseif (!$s2) $s2="$sort desc";
	elseif ($s2=="$sort desc") $s2=$sort;
	elseif ($s2==$sort) $s2='';
	else{$s1=$s2;$s2="$sort desc";}
	$sort='';
return;//the following sort is by defaut:asc
	if (!$sort) return;
	if ($mode==1){//1 sort | 2 sort
		$s1=$sort.($s1==$sort ? ' desc' : '');
		$s2=$sort='';
		return;
	}
	if (!$s1) $s1=$sort;
	elseif ($s1==$sort) $s1 .=' desc';
	elseif ($s1=="$sort desc"){$s1=$s2;$s2='';}
	elseif (!$s2) $s2=$sort;
	elseif ($s2==$sort) $s2 .=' desc';
	elseif ($s2=="$sort desc") $s2='';
	else{$s1=$s2;$s2=$sort;}
	$sort='';
}
function get_sort_css($name,$sort1,$sort2){
	if ($name==$sort1) return " class='sort1'";
	if ("$name desc"==$sort1) return " class='sort1d'";
	if (!$sort2) return '';
	if ($name==$sort2) return " class='sort2'";
	if ("$name desc"==$sort2) return " class='sort2d'";
}
function sort_arr($arr,$s1=null,$s2=null){
	if (!$s1) return $arr;
	if (substr($s1,-5)==' desc'){
		$s1=substr($s1,0,-5); $desc1=1;
	}
	if ($s2 && substr($s2,-5)==' desc'){
		$s2=substr($s2,0,-5); $desc2=1;
	}
	foreach ($arr as $k=>$v){
		$a1[$k]=$v[$s1];
		if ($s2) $a2[$k]=$v[$s2];
	}
	if ($s2) array_multisort($a1,($desc1 ? SORT_DESC : SORT_ASC),$a2,($desc2 ? SORT_DESC : SORT_ASC),$arr);
	else array_multisort($a1,($desc1 ? SORT_DESC : SORT_ASC),$arr);
	return $arr;
}
function table2tabs($str,$tree){
	if ($tree=='_'){
		$arr=explode('_',$str,3);
		if ((substr($str,0,1)=='_' || substr($str,0,3)=='pg_' || substr($str,0,4)=='log_') && $arr[2]<>'') $tabs="$arr[0]_$arr[1]";
		else $tabs=$arr[0];
	}elseif ($tree) $tabs=substr($str,0,$tree);
	if ($tabs==$str) $tabs='';
	return $tabs;
}
function sql_kw($str){//not need always add ` and \"?
	global $SIDU;
	$s1=substr($str,0,1);
	if (!is_numeric($s1) && !in_array(strtoupper($str),$SIDU['sql_kw'])) return $str;
	if (in_array($SIDU['eng'],array('my','PDO_mysql'))) return "`$str`";
	return "\"$str\"";
}
function init_col_width(&$SIDU){
	foreach ($SIDU['col'] as $i=>$v){//init grid size
		$px=ceil($_POST['g'][$i]);
		if (!$px || $px<-1 || $_POST['showCol']=="$i"){
			init_tab_grid($SIDU['data'],$i,$px);
			if ($px<60) $px=60;
			if ($px==60){
				$len=strlen($v[0])*8;
				if ($len>$px) $px=$len;
				if ($px>110) $px=110;
			}
		}
		$SIDU['g'][$i]=$px;
	}
}
function init_tab_grid($data,$j,&$px){
	foreach ($data as $row){
		$grid=strlen($row[$j])*8;
		if ($grid>$px) $px=$grid;
		if ($px>300){
			$px=300; return;
		}
	}
}
function init_pg_col_align($data,&$col){//this method sucks and not good
	foreach ($data as $row){
		foreach ($col as $j=>$v){
			if (!is_null($row[$j])) $col[$j][8]=(!is_numeric($row[$j]) || $col[$j][8]=='c' ? 'c' : 'i');
		}
	}
}
function is_blob($col,$data){//data is not in use...this method is not 100% correct--mostly affected is update|delete with no pk
	global $SIDU;
	if (!in_array($SIDU['eng'],array('my','pg','PDO_mysql','PDO_pgsql')) && $SIDU['g'][$col[9]-1]<300) return 0;
	if (in_array($col[1],array('text','mediumtext','longtext','blob','mediumblob','longblob'))) return 1;
}
function get_sql_col($res,$eng){
	if (substr($eng,0,4)=='PDO_'){
		$num=$res->columnCount();
		for ($i=0;$i<$num;$i++){
			$meta=$res->getColumnMeta($i);
			$col[$i]=array($meta['name'],$meta['native_type']);//watch... may need update
		}
	}elseif ($eng=='my'){
		$num=mysql_num_fields($res);
		for ($i=0;$i<$num;$i++) $col[$i]=array(mysql_field_name($res,$i),mysql_field_type($res,$i));
	}elseif ($eng=='pg'){
		$num=pg_num_fields($res);
		for ($i=0;$i<$num;$i++) $col[$i]=array(pg_field_name($res,$i),pg_field_type($res,$i));
	}elseif ($eng=='sl3'){
		$num=$res->numColumns();
		for ($i=0;$i<$num;$i++) $col[$i]=array($res->columnName($i),$res->columnType($i));
	}elseif ($eng=='cb'){
		$names=cubrid_column_names($res);
		$types=cubrid_column_types($res);
		$num=count($names);
		for ($i=0;$i<$num;$i++) $col[$i]=array($names[$i],$types[$i]);
	}
	return $col;
}
function sidu_affected_rows($res,$eng){
	global $SIDU;
	if (substr($eng,0,4)=='PDO_') return $res->rowCount();//update|delete|insert not correct--need upgrade later...
	if ($eng=='my') return mysql_affected_rows();
	if ($eng=='pg') return pg_affected_rows($res);
	if ($eng=='sl3') return $SIDU['dbL']->changes();
	if ($eng=='cb') return cubrid_affected_rows();
}
function sidu_num_rows($res,$eng){
	if (substr($eng,0,4)=='PDO_') return $res->rowCount();//sl not working
	if ($eng=='my') return mysql_num_rows($res);
	if ($eng=='pg') return pg_num_rows($res);
	if ($eng=='sl3') return $res->numColumns();//err ??
	if ($eng=='cb') return cubrid_num_rows($res);
}
function get_fk($colType,$fk,$is_null){
	if ($is_null=='YES' || $is_null=='f') $arr['NULL']='NULL';
	if (substr($colType,0,5)=='enum('){
		$arr2=explode("','",substr($colType,6,-2));
		foreach ($arr2 as $k) $arr[$k]=$k;
		return $arr;
	}
	if (!$fk) return;
	$fk=explode('#',$fk,3);//ref_tab#ref_col;name#whereSort max200
	if (!$fk[0]) return;
	$col=explode(';',$fk[1],2);//exp default colSep=, so make it ; here
	$col0=sql_kw($col[0]);
	$col1=($col[1] ? sql_kw($col[1]) : $col0);
	$sql="SELECT DISTINCT $col0,$col1 FROM ".sql_kw($fk[0])." $fk[2] LIMIT 201";
	$arr2=get_row($sql,2);
	if (count($arr2)>200) return;
	foreach ($arr2 as $k=>$v) $arr[$k]=$v;
	return $arr;
}
function cout_data($SIDU,$link,$conn,$sql){
	if ($_POST['cmd']=='data_save' || $_POST['cmd']=='data_del') save_data($SIDU,$conn[1],$_POST['cmd']);
	$url=(!$sql ? 'tab' : 'sql');
	echo "\n<form id='dataTab' name='dataTab' action='$url.php?id=$link[0],$link[1],$link[2],$link[3],$link[4],$SIDU[5],$SIDU[6]",get_oidStr(),"' method='post'>\n";
	if (!$sql){
		$arrH=array('cmd','sidu7','sidu8','sidu9','showCol','hideCol');
		foreach ($arrH as $v) echo html_form('hidden',$v);
		echo "\n<input type='hidden' id='gridMode' name='gridMode' value='$SIDU[gridMode]'>\n<iframe name='hiddenfr' src='#' class='hide'></iframe>\n<p style='padding:3px'>where ",html_form('text','f[sql]',$SIDU['f']['sql'],300),' ',html_img('sidu','',"onclick=\"submitForm('cmd','p1')\" class='vm hand'")," eg col='abc'</p>";
		foreach ($SIDU['g'] as $j=>$gSize){
			$colHshow .='<td'.($gSize==-1 ? '' : " class='hide bg grid'").">{$SIDU[col][$j][0]}</td>";
			$colHhide .='<td'.($gSize==-1 ? " class='hide'" : '').'></td>';
		}
	}
	if (isset($SIDU['pk'])) $pk=$SIDU['pk'];
	foreach ($SIDU['col'] as $j=>$v){
		$disp[$j]=($SIDU['g'][$j]==-1 ? ' hide' : '');
		$title="$v[0] ".str_replace("'",'',$v[1]);
		$color='';
		if (in_array($j,$SIDU['pk'])){
			$title="PK $title"; $color='blue';
		}
		if ($v[5]=='auto_increment' || $v[1]=='serial' || $v[1]=='bigserial') $color='red';
		if (!$sql) $color2=' '.substr(get_sort_css($v[0],$SIDU[5],$SIDU[6]),8,-1);
		$colH .='<td'.($SIDU['g'][$j]==-1 ? " class='hide'" : '')." title='$title'><div id='gH$j' class='gridH'".(!$SIDU['gridMode'] ? " style='width:{$SIDU[g][$j]}px'" : '')
			."><a href='#' class='colSort$color2'>".($color ? "<span class='$color'>$v[0]</span>" : $v[0]).'</a></div></td>';
		$jsStr .="xHRD.init('gH$j',10);";
		$filter .="<td".($disp[$j] ? " class='{$disp[$j]}'" : '')."><input type='text' size='1' id='f$j' name='f[$j]' value='".html8($SIDU['f'][$j])."'></td>";
		$grid .="<td><input type='text' size='1' name='g[$j]' id='g$j' value='".$SIDU['g'][$j]."'></td>";
		if ($v[3]=='CURRENT_TIMESTAMP' || $v[3]=='now()') $v[3]="'+his.getFullYear()+'-'+(parseInt(his.getMonth())+1)+'-'+his.getDate()+' '+his.getHours()+':'+his.getMinutes()+':'+his.getSeconds()+'";
		elseif (substr($v[3],0,9)=="nextval('") $v[3]='';
		else $v[3]=html8($v[3] ? $v[3] : ($v[2]=='YES' || $v[2]=='f' ? 'NULL' : ''));
		$align=($SIDU['col'][$j][8]=='i' ? ' ar' : '');
		$id='data_new\'+x+\'_'.$j;
		$is_blob=is_blob($SIDU['col'][$j]);
		$jsColNew .='<td class="blue"><input type="text" size="1"'.($v[3]=='' ? '' : ' value="'.$v[3].'"');
		if ($is_blob) $jsColNew .=' readonly="readonly" class="Hpop bg1"><div class="pop"><b class="xpop" title="Close"></b><textarea name="'.$id.'">'.$v[3].'</textarea></div></td>';
		else $jsColNew .=' name="'.$id.'"'.($align ? ' class="'.trim($align).'"' : '').'></td>';
		if (!isset($SIDU['pk'])) $pk[]=$j;//no pk table with blob col will be slow here
		if (!$sql) $FK[$j]=get_fk($v[1],$v[12],$v[2]);
	}
	if (!$sql) echo "\n<table class='colShow hand'><tr class='th' title='",lang(104),"'><td class='hide'></td>$colHshow</table>";
	echo "\n<table class='grid' id='dataTable'>";
	if (!$sql) echo "\n<tr class='colTool hand hide' title='",lang(105),"'><td title='",lang(124),"' class='cbox'></td>$colHhide</tr>";
	echo "\n<tr class='th hand'><td class='cbox'><input type='checkbox' id='checkAll'></td>$colH</tr>";
	if (!$sql) echo "\n<tr class='hide' title='",lang(106),"'><td class='cbox'></td>$grid</tr>\n<tr id='addRowAfter' class='gridf' title='",lang(107)," eg: =12'><td class='cbox'><a href='tab.php?id=$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4]' title='",lang(108),"' id='tfilter' class='toolS'></a></td>$filter</tr>";
	foreach ($SIDU['data'] as $i=>$row){
		echo "\n<tr id='tr_$i'><td class='cbox'><input type='checkbox' name='cbox_data_$i'></td>";
		foreach ($row as $j=>$v){
			$align=($SIDU['col'][$j][8]=='i' ? ' ar' : '');
			if (is_null($v)){
				$v='NULL'; $classNull=' null';
			}else $classNull='';
			if ($SIDU['eng']=='cb' && is_array($v)) $v='{'.implode(',',$v).'}';
			$v8=html8($v);
			$id="data_$i"."_$j";
			$is_blob=is_blob($SIDU['col'][$j]);
			$classA=trim($classNull.$align);
			$classB=trim($classA.$disp[$j]);
			echo '<td',($classB<>'' ? " class='$classB'" : ''),'>';
			if ($SIDU['gridMode']){
				if ($is_blob || $sql) echo nl2br($v8);
				elseif ($v8<>''){
					$v8str=($v8==='NULL' ? 'IS NULL' : "=\\'".strtr($v8,array("&#039;"=>"\&#039;\&#039;","\\"=>"\\\\\\\\"))."\\'");
					echo "<a href='#' onclick=\"val('f$j','$v8str');submitForm('cmd','p1')\">".nl2br($v8)."</a>";
				}
			}else{//mysql set() too complicated. i m lazy to do it. maybe in the future.
				if ($is_blob || ($SIDU['col'][$j][4]>259 && $SIDU['col'][$j][8]<>'i')) echo "<input type='text' size='1' readonly='readonly' class='Hpop bg1$classNull' value='".html8(substr($v,0,200))."'><div class='pop'><textarea name='$id'",(!$sql ? " class='updateP'" : ''),">$v8</textarea></div>";
				elseif (isset($FK[$j])) echo html_form('select',$id,$v,'',$FK[$j],"title='$j'");//css width seems buggy
				else echo "<input type='text' size='1'",($classA<>'' ? " class='$classA'" : '')," name='$id'",($v8<>'' ? " value='$v8'" : ''),'>';
			}
			if (!$sql && in_array($j,$pk)) echo "<input type='hidden' name='pkV[$i][$j]' value='".($is_blob && strlen($v)>50 ? '::md5BLOG::'.md5($v) : $v8)."'>";
			echo '</td>';
			$ttl[$j]+=$v;
		}
		echo '</tr>';
	}
	if (isset($ttl)){
		echo "\n<tr title='",lang(125),"'><td class='hideP hand' title='",lang(126),"'></td>";
		foreach ($ttl as $j=>$v) echo "<td class='ar grey small{$disp[$j]}'>",($v ? $v : ''),'</td>';
		echo "</tr>";
	}
	echo "\n</table></form>";
	if (!$sql || $sql<2) echo "\n<script type='text/javascript'>window.onload=function(){".$jsStr."}";
	if (!$sql) echo "\nfunction addRow(){\nvar his=new Date();\nvar x=his.getHours()+his.getMinutes()+his.getSeconds();\nreturn '<td class=\"cbox\"><input type=\"checkbox\" name=\"cbox_data_new'+x+'\"></td>$jsColNew';\n}";
	if (!$sql || $sql<2) echo '</script>';
}
function get_oidStr(){
	if ($_POST['oid']) return "&#38;oid=$_POST[oid]";
	if ($_GET['oid']) return "&#38;oid=$_GET[oid]";
}
function tab_tool(){
	global $SIDU;
	$obj=array('r'=>lang(111),'v'=>lang(112),'S'=>lang(113),'f'=>lang(114));
	if (!$obj[$SIDU[3]]) return;
	$objcmd=($_POST['objcmd'] ? $_POST['objcmd'] : $_GET['objcmd']);
	$url=substr($_SERVER['SCRIPT_NAME'],-8);
	if ($url=='/tab.php'){
		echo "<form action='tab.php?id=$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4],$SIDU[5],$SIDU[6]",get_oidStr(),"' method='post'>",html_form('hidden','objs[0]',$SIDU[4]);
		$objs[0]=$SIDU[4];
		$is_tab=1;
	}else $objs=$_POST['objs'];
	$txt=($url=='/tab.php' ? "{$obj[$SIDU[3]]}: $SIDU[4]" : $obj[$SIDU[3]]);
	echo "<div id='objToolSH' style='margin:5px",($objcmd ? ';display:block' : ''),"' class='box hide'><p class='dot'>",html_form('submit','objcmd','Drop',76,'',"onclick=\"return confirm('".lang(117,$txt)."?')\"");
	$eng=$SIDU['eng'];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	if ($SIDU[3]=='r'){
		echo html_form('submit','objcmd','Empty',77,'',"onclick=\"return confirm('".lang(119,$txt)."?')\"");
		if ($is_my || $is_pg) echo html_form('submit','objcmd','Analyze',76);
		if ($is_my) echo html_form('submit','objcmd','Check',73),html_form('submit','objcmd','Optimize',84),html_form('submit','objcmd','Repair',73),html_form('submit','objcmd','Change Engine to MyISAM',230,'',"onclick=\"return confirm('".lang(116,array('MyISAM',$txt))."?')\""),html_form('submit','objcmd','Change Engine to InnoDB',230,'',"onclick=\"return confirm('".lang(116,array('InnoDB',$txt))."?')\"");
		elseif (!$is_cb) echo html_form('submit','objcmd','Vacuum');
		if ($is_pg) echo html_form('submit','objcmd','Re-index'),html_form('submit','objcmd','Truncate Cascade',154,'',"onclick=\"return confirm('".lang(118,$txt)."?')\"");
	}
	if ($is_pg) echo html_form('submit','objcmd','Drop Cascade',154,'',"onclick=\"return confirm('".lang(120,$txt)."?')\"");
	echo " <a href='db-cmp.php?id=$SIDU[0]&fm[host]=$SIDU[0]&fm[db]=$SIDU[1]&fm[tab]=",($is_tab ? '' : ':'),"$SIDU[4]' class='xwin'>DB Compare</a></p>";
	if ($objcmd){
		if (!$objs[0]) echo "<br><p class='err'>",lang(121,$obj[$SIDU[3]]),'</p>';
		else tab_tool_save($SIDU,$objcmd,$objs);
	}
	echo '</div>';
	if ($url=='/tab.php') echo '</form>';
}
function tab_tool_save($SIDU,$cmd,$objs){//note: do not translate cmd to other lang
	$obj=array('r'=>'TABLE','v'=>'VIEW','S'=>'SEQUENCE','f'=>'FUNCTION');
	$obj=$obj[$SIDU[3]];
	$eng=$SIDU['eng'];
	if (in_array($cmd,array('Drop','Analyze','Check','Optimize','Repair'))) $CMD=strtoupper($cmd)." $obj";
	elseif ($cmd=='Empty') $CMD=($eng=='PDO_sqlite' || $eng=='sl3' ? 'DELETE FROM' : 'TRUNCATE TABLE');
	elseif ($cmd=='Vacuum') $CMD='VACUUM';
	elseif ($cmd=='Re-index') $CMD='REINDEX TABLE';
	elseif ($cmd=='Drop Cascade'){
		$CMD="DROP $obj"; $CMD2=' CASCADE';
	}elseif ($cmd=='Truncate Cascade'){
		$CMD="TRUNCATE $obj"; $CMD2=' CASCADE';
	}
	if ($cmd=='Analyze' && ($eng=='pg' || $eng=='PDO_pgsql')) $CMD='ANALYZE';
	if (substr($cmd,0,17)=='Change Engine to '){
		$engTo=substr($cmd,17);
		$rows=get_rows("SHOW TABLE STATUS FROM `$SIDU[1]`");
		foreach ($rows as $row){
			if ($row['Engine']<>$engTo) $tabs[]=$row['Name'];
		}
		foreach ($objs as $v){
			if (in_array($v,$tabs)) tab_tool_run_sql("ALTER TABLE `$SIDU[1]`.`$v` ENGINE = $engTo;");
		}
	}elseif ($CMD=='DROP FUNCTION'){
		foreach ($objs as $v) tab_tool_run_sql("$CMD $v$CMD2;");
	}else{
		foreach ($objs as $v) tab_tool_run_sql("$CMD ".sql_kw($v)."$CMD2;");
	}
}
function tab_tool_run_sql($sql){
	tm_his($sql);
	echo "<br>$sql";
	$err=sidu_err(1);
	if ($err) echo "<br><span class='red'>$err</span>";
	else echo "<br><span class='green'>OK</span>";
}
function sidu_sl_pk($tab){
	$rows=get_rows("pragma table_info($tab)");
	foreach ($rows as $row){
		if ($row['pk']) $pk[]=$row['name'];
	}
	return implode(',',$pk);
}
//sidu page maker
function uppe(){
	global $SIDU;
	$url=array_pop(explode('/',$_SERVER['SCRIPT_NAME']));
	if ($url=='db.php') $title=($SIDU[4]<>'' ? "$SIDU[4] « ": '').($SIDU[2] ? "$SIDU[2] « " : '').$SIDU[1];
	elseif ($url=='tab.php') $title=$SIDU[4].($SIDU[2] ? " « $SIDU[2]" : '')." « $SIDU[1]";
	if (!$title) $title="SIDU $SIDU[sidu_ver] Database Web GUI: MySQL + Postgres + SQLite + CUBRID";
	$bigBtn=$SIDU['page']['btn'];
	echo "<!DOCTYPE html>\n<html",($bigBtn ? " class='LargeBtn'" : ''),">\n<head>\n<meta http-equiv='Content-Type' content='text/html;charset=utf-8'>
<link rel='shortcut icon' href='img/sidu.png'>\n<title>$title</title>
<link rel='stylesheet' media='all' type='text/css' href='img/my.css'>
<meta name='viewport' content='width=device-width,height=device-height,initial-scale=1.0'>
<script src='img/jquery-1.8.2.min.js' type='text/javascript'></script>
<script src='img/my.js' type='text/javascript'></script>";
	if ($SIDU['page']['xJS']) echo "\n<script type='text/javascript' src='img/x.js'></script>\n<script type='text/javascript' src='img/xenabledrag.js'></script>";
	if ($bigBtn) echo "
<style>
#menu,#sqls,#data{top:56px}
#tool{height:48px;line-height:65px}
#tool a,#tool b,.toolV #tool a{background:url(img/tool48.png);padding:24px}
#tool i{display:none}
.toolV #tool{width:50px}
.toolV #tool a{height:4px}
.toolV #menu{left:56px}
</style>";
	echo "\n</head>\n<body>";
	if ($SIDU['page']['nav']){
		echo "<div id='tool'>";
		if ($SIDU['page']['nav']=='defa') echo " <b></b><b class='none'>SIDU $SIDU[sidu_ver]</b> Database Web GUI <b id='tweb'></b><b class='none'>topnew.net/sidu</b>";
		else navi();
		echo "</div><p id='sqlwait'></p><div id='data'>";
	}
}
function down(){
	global $SIDU;
	if ($SIDU['page']['nav']) echo '</div>';
	echo '</body></html>';
}
function navi_obj($arr,$is_db){//db.php not use this func yet
	global $SIDU;
	if (!$arr[1]) return;
	echo $SIDU['sep'].'&nbsp;';
	$oidStr=get_oidStr();
	if ($arr[4]<>'' && !$is_db) echo "<a id='tx$arr[3]' href='tab.php?id=$arr[0],$arr[1],$arr[2],$arr[3],$arr[4]&#38;desc=1$oidStr' title='",lang(122),":$arr[4]' onclick=\"xwin(this.href,720);return false\"></a> ";
	elseif ($arr[4]<>'') echo "<b id='tx$arr[3]'></b>";
	else echo "<span class='ft db'></span>";
	echo "<a class='none' href='db.php?id=$arr[0],$arr[1]'>$arr[1]</a>";
	if ($arr[2]<>''){
		if ($arr[2]) echo " » <a class='none' href='db.php?id=$arr[0],$arr[1],$arr[2]",($arr[3] ? ",$arr[3]" : ""),"$oidStr'>$arr[2]</a>";
		if ($arr[4]<>''){
			$tabs=table2tabs($arr[4],$SIDU['page']['tree']);
			if ($tabs<>'') echo " » <a class='none' href='db.php?id=$arr[0],$arr[1],$arr[2],$arr[3],$tabs$oidStr'>$tabs</a>";
			echo " » <a class='none' href='tab.php?id=$arr[0],$arr[1],$arr[2],$arr[3],$arr[4]$oidStr' title='",lang(123),"'>$arr[4]</a>";
		}
	}
}
?>
