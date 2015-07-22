<?php
include 'inc.page.php';
@main($exp,$cmd);

function main($exp,$cmd){
	global $SIDU;
	if ($_GET['sql']){
		$cook=$SIDU['cook'][$SIDU[0]];
		tm_use_db($cook[1],$cook[2]);
		$mode='SQL';
		$_GET['sql']=stripslashes($_GET['sql']);
	}else $mode="DB = $SIDU[1]".($SIDU[2] ? ".$SIDU[2]" : '');
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	valid_data($SIDU,$exp,$cmd,$is_my,$is_pg,$is_cb,$is_sl);
	if ($cmd) main_cout($SIDU,$exp,$mode,$is_my,$is_pg,$is_cb,$is_sl);
	else main_form($SIDU,$exp,$mode,$is_my,$is_sl);
}
function main_cout_str($str,$fp){
	if ($fp) fwrite($fp,$str);
	else echo $str;
}
function main_cout($SIDU,$exp,$mode,$is_my,$is_pg,$is_cb,$is_sl){
	if ($mode=='SQL') $file='sidu-sql';
	else{
		$file=str_replace('/','_',$SIDU[1]).($SIDU[2] ? "_$SIDU[2]" : '');
		if (!$exp['sql'][1]) $file .='_'.$exp['tabs'][0];
	}
	$file .='_'.date('YmdHis.').$exp['ext'];
	if ($exp['zip']) $fp=fopen("/tmp/$file",'w');
	if (!$exp['zip'] || $exp['ext']=='html') main_cout_str("<html>\n<head>\n<title>SIDU Export: $file</title>\n<style>*{font-family:monospace}",$fp);
	if ($exp['ext']=='html') main_cout_str("\n.n{color:#888;font-style:italic}\n.th td{background:#ddd}\ntd{vertical-align:top;border:solid 1px #ccc}",$fp);
	if (!$exp['zip'] || $exp['ext']=='html') main_cout_str("\n</style>\n</head>\n<body><pre>\n",$fp);
	main_cout_str('/*SIDU Export Start-------------------'.date('Y-m-d H:i:s')."*/\n",$fp);
	if ($mode<>'SQL'){
		if ($exp['db']){
			if ($is_my) main_cout_str("\nUSE ".sql_kw($SIDU[1]).";\n",$fp);
			elseif ($is_pg) main_cout_str("\nSET search_path to ".sql_kw($SIDU[2]).";\n",$fp);
		}
		if ($exp['drop']){
			foreach ($exp['tabs'] as $v){
				if ((!$is_sl && !$is_cb) || $v<>'sqlite_master') main_cout_str("\nDROP ".($SIDU[3]=='r' ? 'TABLE ' : 'VIEW ').sql_kw($v).';',$fp);
			}
			main_cout_str("\n",$fp);
		}
		if ($exp['desc']){
			$typ=($SIDU[3]=='r' ? 'TABLE' : 'VIEW');
			foreach ($exp['tabs'] as $v) main_cout_desc($SIDU,$typ,$v,$fp,$exp['sql_slash'],$is_my,$is_pg,$is_cb,$is_sl);
			main_cout_str("\n",$fp);
		}
	}
	if (!$exp['data']) return main_cout_str("\n/*SIDU Export End-------------------*/</pre>\n</body></html>",$fp);
	if ($exp['ext']=='html') main_cout_str('</pre>',$fp);
	foreach ($exp['sql'] as $i=>$v){
		if ($exp['ext']<>'sql') main_cout_str("\n\n".($exp['ext']=='html' ? '<br>' : '/* ').nl2br(($exp['zip'] && $exp['ext']<>'html' ? $v : html8($v))).($exp['ext']=='html' ? '' : ' */')."\n",$fp);
		$res=tm_his($v);
		$err=sidu_err(1);
		if ($err) main_cout_str("\n".($exp['ext']=='html' ? '' : '/* ')."<font color='red'>$err</font>".($exp['ext']=='html' ? '' : ' */')."\n",$fp);
		else main_cout_data($SIDU,$exp,$res,$exp['tabs'][$i],$fp,$is_sl);
	}
	main_cout_str("\n".($exp['ext']=='html' ? '<p>' : '').'/*SIDU Export End-------------------*/'.($exp['ext']=='html' ? '</p>' : ''),$fp);
	if ($exp['ext']<>'html' && !$exp['zip']) main_cout_str("\n</pre>",$fp);
	if ($exp['ext']=='html' || !$exp['zip']) main_cout_str("\n\n</body></html>",$fp);
	if (!$fp) return;
	fclose($fp);
	$zip=new ZipArchive();
	$zipFile=$file.'.zip';
	if($zip->open("/tmp/$zipFile",ZIPARCHIVE::CREATE)!==true) return;
	$zip->addFile("/tmp/$file",$file);
	$zip->close();
	header('Expires: 0');
	header('Content-Description: File Transfer');
	header('Content-Type: application/zip');
	header("Content-Disposition: attachment; filename=\"$zipFile\"");
	$fp=fopen("/tmp/$zipFile",'rb');
	if ($fp){
		while(!feof($fp)){
			print(fread($fp,1024*8)); flush();
			if (connection_status()!=0){
				fclose($fp); die();
			}
		}
		fclose($fp);
	}
}
function main_cout_desc($SIDU,$typ,$tab,$fp,$sql_slash,$is_my,$is_pg,$is_cb,$is_sl){
	if ($typ=='VIEW'){
		if ($is_sl){
			$sql=get_var("SELECT sql FROM sqlite_master WHERE type='view' AND name='$tab'");
			main_cout_str("\n$sql;",$fp);
		}elseif ($is_my){
			$sql=get_var("SELECT VIEW_DEFINITION FROM information_schema.VIEWS\nWHERE TABLE_SCHEMA='$SIDU[1]' AND TABLE_NAME='$tab'");
			$sql=trim(str_replace('/* ALGORITHM=UNDEFINED */','',$sql));
			main_cout_str("\nCREATE VIEW ".sql_kw($tab)." AS $sql;",$fp);
		}elseif ($is_pg){
			$oid=get_var("SELECT a.oid FROM pg_class a,pg_namespace b\nWHERE a.relkind='v' AND a.relnamespace=b.oid\nAND a.relname='$tab' AND b.nspname='$SIDU[2]'");
			$sql=get_var("SELECT pg_get_viewdef($oid)");
			main_cout_str("\nCREATE VIEW ".sql_kw($tab)." AS $sql",$fp);
		}//cb not available yet
		return;
	}
	if ($is_pg){
		$info=get_row("SELECT a.oid,a.relnamespace,a.relhasoids,obj_description(a.oid,'pg_class')\nFROM pg_class a,pg_namespace b WHERE a.relkind='r' AND a.relnamespace=b.oid\nAND a.relname='$tab' AND b.nspname='$SIDU[2]'",0,'NUM');
		$defa=get_row("SELECT adnum,adsrc FROM pg_attrdef WHERE adrelid=$info[0]",2);
		$typs=get_row("SELECT oid,typname FROM pg_type",2);
		$comm=get_row("SELECT objsubid,description FROM pg_description\nWHERE objoid=$info[0] AND objsubid>0",2);
		$tran=array("'"=>"''");
		if ($sql_slash) $tran["\\"]="\\\\";
		main_cout_str("\nCREATE TABLE ".sql_kw($tab).'(',$fp);
		$rows=get_rows("SELECT attname,atttypid,attnotnull,atthasdef,\nCASE attlen WHEN -1 THEN atttypmod ELSE attlen END,\nattnum,format_type(atttypid,atttypmod) FROM pg_attribute\nWHERE attrelid=$info[0] AND attnum>0 AND attisdropped=FALSE ORDER BY attnum",'NUM');
		$i=0;
		foreach ($rows as $row){
			$row[3]=($row[3]=='t' ? $defa[$row[5]] : '');
			$row[1]=$typs[$row[1]];
			if ($row[1]=='numeric') $row[1]=$row[6];
			elseif ($row[1]=='int2') $row[1]='smallint';
			elseif ($row[1]=='int4') $row[1]='int';
			elseif ($row[1]=='int8') $row[1]='bigint';
			elseif ($row[1]=='bpchar') $row[1]='char';
			if ($row[4]>4 && ($row[1]=='varchar' || $row[1]=='char')) $row[1] .= '('.($row[4]-4).')';
			if (substr($row[3],0,9)=="nextval('") $row[1]=($row[1]=='int' ? 'serial' : 'bigserial');
			if ($i++) main_cout_str(',',$fp);
			main_cout_str("\n\t".sql_kw($row[0])." $row[1]".($row[2]=='t' ? ' NOT NULL' : '').($row[3]<>'' && substr($row[3],0,9)<>"nextval('" ? " DEFAULT $row[3]" : ''),$fp);
			if ($comm[$i]) $commStr .="\nCOMMENT ON COLUMN ".sql_kw($tab).'.'.sql_kw($row[0])." IS '".strtr($comm[$i],$tran)."';";
		}
		$fkmatch=array('f'=>'FULL','p'=>'PARTIAL','u'=>'SIMPLE');
		$fkact=array('a'=>'NO ACTION','r'=>'RESTRICT','c'=>'CASCADE','n'=>'SET NULL','d'=>'SET DEFAULT');
		$rows=get_rows("SELECT *,pg_get_constraintdef(oid,TRUE) AS kstr FROM pg_constraint\nWHERE conrelid=$info[0] AND connamespace=$info[1]",'ASSOC');
		foreach ($rows as $row){
			main_cout_str(",\nCONSTRAINT ".sql_kw($row['conname'])." $row[kstr]",$fp);
			if ($row['contype']=='f') main_cout_str(" MATCH {$fkmatch[$row[confmatchtype]]}\n\tON UPDATE {$fkact[$row[confupdtype]]} ON DELETE {$fkact[$row[confdeltype]]}",$fp);
		}
		main_cout_str("\n) WITH (OIDS=".($info[2]=='t' ? 'TRUE' : 'FALSE').');',$fp);
		if ($info[3]) main_cout_str("\nCOMMENT ON TABLE ".sql_kw($tab)." IS '".strtr($info[3],$tran)."';",$fp);
		main_cout_str($commStr,$fp);
		$arr=get_row("SELECT pg_get_indexdef(indexrelid) FROM pg_index\nWHERE indrelid=$info[0] AND indisprimary='f'",1);
		foreach ($arr as $idx) main_cout_str("\n$idx;",$fp);
		return;
	}
	if ($is_my) $desc=get_row("SHOW CREATE TABLE `$SIDU[1]`.`$tab`");
	elseif ($is_sl) $desc=get_row("SELECT name,sql FROM sqlite_master WHERE name=tbl_name AND name='$tab' LIMIT 1");
	main_cout_str("\n$desc[1];",$fp);
	if ($is_sl){
		$arr=get_row("SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name='$tab' AND sql IS NOT NULL",1);
		foreach ($arr as $s) main_cout_str("\n$s;",$fp);
	}//cubrid not ready yet
}
function main_cout_data($SIDU,$exp,$res,$tab,$fp,$is_sl){
	$col=get_sql_col($res,$SIDU['eng']);
	$arr=get_row_data($res,4,'NUM');
	if ($exp['ext']=='html'){
		init_pg_col_align($arr,$col);
		main_cout_str("<table style='border:solid 1px #888'>\n<tr class='th'>",$fp);
		foreach ($col as $v) main_cout_str('<td'.($v[8]=='i' ? " align='right'" : '').">$v[0]</td>",$fp);
		main_cout_str('</tr>',$fp);
	}else{
		if ($exp['ext']=='sql'){
			$tran["'"]="''";
			if ($exp['sql_slash']) $tran["\\"]="\\\\";
		}elseif ($exp['csvNL']) $tran=array("\r"=>'\r',"\n"=>'\n');
		$num=count($arr[0])-1;
		if ($exp['ext']=='sql'){
			foreach ($col as $k=>$v) $COL[]=sql_kw($v[0]);
			$head="\nINSERT INTO ".sql_kw($tab).'('.implode(',',$COL).') VALUES ';
			$ttl=count($arr)-1;
			$size=($is_sl ? 1 : 200);//commit at each 200 lines for select
		}else{
			foreach ($col as $k=>$v) $COL[]=$v[0];
			main_cout_str("\n/*".implode(',',$COL).'*/',$fp);
		}
	}
	if ($exp['ext']=='html'){
		foreach ($arr as $i=>$row){
			main_cout_str("\n<tr>",$fp);
			foreach ($row as $j=>$val) main_cout_str('<td'.($col[$j][8]=='i' ? " align='right'" : '').(is_null($val) ? " class='n'" : '').'>'.(is_null($val) ? 'NULL' : ($val==='' ? '&nbsp;' : nl2br(html8($val)))).'</td>',$fp);
			main_cout_str('</tr>',$fp);
		}
		main_cout_str("\n</table>",$fp);
	}else{
		$tran2=array('\n'=>"\n",'\r'=>"\r",'\t'=>"\t");
		if ($exp['ext']=='sql' || $exp['sepC']=='') $exp['sepC']=',';
		else $exp['sepC']=strtr($exp['sepC'],$tran2);
		if ($exp['ext']=='csv'){
			if ($exp['sepR']=='') $exp['sepR']='\n';
			$exp['sepR']=strtr($exp['sepR'],$tran2);
			$exp['csvEnc']=trim($exp['csvEnc']);
		}else $exp['csvEnc']="'";
		foreach ($arr as $i=>$row){
			if ($exp['ext']=='sql' && ($i%$size)==0) main_cout_str($head,$fp);
			main_cout_str(($exp['ext']=='sql' ? '(' : $exp['sepR']),$fp);
			foreach ($row as $j=>$val){
				if (is_null($val)) main_cout_str('NULL',$fp);
				elseif (is_numeric($val)) main_cout_str($val,$fp);
				else{
					if (isset($tran)) $val=strtr($val,$tran);
					main_cout_str($exp['csvEnc'].($exp['zip'] ? $val : html8($val)).$exp['csvEnc'],$fp);
				}
				if ($j<$num) main_cout_str($exp['sepC'],$fp);
			}
			if ($exp['ext']=='sql')	main_cout_str(')'.($i==$ttl || ($i%$size)==($size-1) ? ';' : ',')."\n",$fp);
		}
	}
}
function main_form($SIDU,$exp,$mode,$is_my,$is_sl){
	uppe();
	$obj=($SIDU[3]=='r' ? lang(1502) : lang(1503));
	echo "<form action='exp.php' method='get'>",html_form('hidden','id',"$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4]"),"
		<div class='web'><p class='dot'><b>SIDU ",lang(1501),":</b> <i class='b red'>$mode</i></p>";
	if ($mode=='SQL') echo "<p class='green'>",nl2br(html8($_GET['sql'])),'</p>',html_form('hidden','sql',$_GET['sql']);
	elseif ($_GET['tab']) echo "<p>$obj = <span class='green'>",str_replace(',',', ',$_GET['tab']),'</span></p>',html_form('hidden','tab',$_GET['tab']);
	elseif (!$SIDU[4]) return print("<p class='err'>".lang(1504,$obj).'</p></div></form>');
	$arr_ext=array('html'=>'HTML','csv'=>'CSV','sql'=>'SQL');
	if ($mode<>'SQL'){
		echo "<p class='dot b'>",lang(1505),'</p><p>';
		if (!$is_sl) echo html_form('checkbox','exp[db]',$exp['db'],'',array(1=>'Use ')),($is_my ? 'DB' : 'Sch'),' &nbsp; ';
		echo html_form('checkbox','exp[drop]',$exp['drop'],'',array(1=>lang(1506,$obj).' &nbsp; ')),
		html_form('checkbox','exp[desc]',$exp['desc'],'',array(1=>lang(1507,$obj).' &nbsp; ')),
		html_form('checkbox','exp[data]',$exp['data'],'',array(1=>lang(1508,$obj))),'</p>';
	}
	echo "<p class='dot b'>",lang(1509),'</p><p>',
	html_form('radio','exp[ext]',$exp['ext'],' &nbsp; ',$arr_ext),' &nbsp; ',
	html_form('checkbox','exp[zip]',$exp['zip'],'',array(1=>lang(1510))),"</p><p class='dot b'>",lang(1514),"</p>
<table>
<tr><td>&nbsp; &nbsp;</td><td>",lang(1515),'</td><td>',html_form('text','exp[sepC]',$exp['sepC'],50)," <i class='green'>eg , » \\t</i></td>
	<td style='padding:0 20px'></td><td>",lang(1517),html_form('text','exp[csvEnc]',$exp['csvEnc'],50)," <i class='green'>eg ' \"</i></td></tr>
<tr><td></td><td>",lang(1516),'</td><td>',html_form('text','exp[sepR]',$exp['sepR'],50)," <i class='green'>eg \\n</i></td>
	<td></td><td>",html_form('checkbox','exp[csvNL]',$exp['csvNL'],'',array(1=>lang(1518))),"</td></tr>
</table><p class='dot b'>",
	lang(1512),'</p><p>',html_form('checkbox','exp[sql_slash]',$exp['sql_slash'],'',array(1=>lang(1513))),'</p>';
	if ($mode<>'SQL' && !$exp['sql'][1]){
		echo "<p class='b dot'>",lang(1511,$obj),": <i class='red'>{$exp[tabs][0]}</i></p><p>";
		foreach ($exp['tab_col'] as $v) echo "<input type='checkbox' name='exp[col][]' value='$v'",(!isset($exp['col']) || in_array($v,$exp['col']) ? " checked='checked'" : ''),"> $v &nbsp; ";
		echo '</p><p>where ',html_form('text','exp[where]',$exp['where'],300),'</p>';
	}
	echo "<p class='dot'></p><p>",html_form('submit','cmd',lang(1501)),' Check your server setting for max size of export</p></div></form>';
	down();
}
function valid_data($SIDU,&$exp,$cmd,$is_my,$is_pg,$is_cb,$is_sl){
	if (!$exp['db'] && !$exp['drop'] && !$exp['desc'] && !$exp['data']) $exp['data']=1;
	if ($exp['drop']) $exp['desc']=1;
	if ($exp['ext']<>'html' && $exp['ext']<>'sql') $exp['ext']='csv';
//	if (!$cmd) $exp['zip']=1;//default save as zip
	if (!$cmd) $exp['csvNL']=1;
	$exp['where']=trim(stripslashes($exp['where']));
	$exp['sepC']=trim($exp['sepC']);
	if ($exp['sepC']=='') $exp['sepC']=',';
	$exp['sepR']=trim($exp['sepR']);
	if ($exp['sepR']=='') $exp['sepR']='\n';
	if (!$_GET['sql']){
		if ($SIDU[4]) $exp['tabs'][0]=$SIDU[4];
		else $exp['tabs']=explode(',',$_GET['tab']);
		if ($is_my){
			$good1=sql_kw($SIDU[1]);
			foreach ($exp['tabs'] as $tab) $exp['sql'][]="SELECT * FROM $good1.".sql_kw($tab);;
		}elseif ($is_pg){
			$good2=sql_kw($SIDU[2]);
			foreach ($exp['tabs'] as $tab) $exp['sql'][]="SELECT * FROM $good2.".sql_kw($tab);
		}elseif ($is_sl || $is_cb){
			foreach ($exp['tabs'] as $tab) $exp['sql'][]="SELECT * FROM ".sql_kw($tab);
		}
		if (!$exp['sql'][1]){
			$res=tm_his($exp['sql'][0].' LIMIT 1');
			$col=get_sql_col($res,$SIDU['eng']);
			foreach ($col as $v) $exp['tab_col'][]=$v[0];
			if ($exp['tab_col']<>$exp['col']){
				foreach ($exp['col'] as $k=>$v) $exp['col'][$k]=sql_kw($v);
				$exp['sql'][0]='SELECT '.implode(',',$exp['col']).substr($exp['sql'][0],8);
			}
			if ($exp['where']) $exp['sql'][0] .=" WHERE $exp[where]";
		}
	}else $exp['sql'][0]=$_GET['sql'];
}
?>
