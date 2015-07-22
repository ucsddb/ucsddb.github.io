<?php
include 'inc.page.php';
@uppe();
@main($imp,$cmd);
@down();

function main($imp,$cmd){
	global $SIDU;
	if (!$cmd) $imp['rmEnc']=$imp['NL']=$imp['stop']=1;
	$eng=$SIDU['eng'];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	if (!$SIDU[1]) $err=lang(2201);
	elseif ($is_pg && !$SIDU[2]) $err=lang(2202);
	echo "<form action='imp.php?id=$SIDU[0],$SIDU[1],$SIDU[2]' method='post' enctype='multipart/form-data'>
<div class='web'><p class='dot'><b>",lang(2203),": <i class='red'>DB = $SIDU[1]",($SIDU[2] ? ".$SIDU[2]" : ''),'</i></b></p>';
	if ($err) return print("<p class='err'>$err</p></div></form>");
	if ($cmd) $SIDU[4]=$imp['tab'];
	if ($SIDU[4]){
		$res=tm_his('SELECT * FROM '.sql_kw($SIDU[4]).' LIMIT 1');
		$col=get_sql_col($res,$eng);
		foreach ($col as $v) $imp['cols'][]=$v[0];
	}
	if (!$imp['col']) $imp['col']=implode("\n",$imp['cols']);
	if ($cmd){
		$err=valid_data($SIDU,$imp);
		if ($err) echo "<p class='err'>$err</p>";
		else return save_data($SIDU,$imp,$is_my,$is_pg,$is_cb,$is_sl);
	}
	if ($is_my) $sql='SHOW TABLES from '.sql_kw($SIDU[1]);
	elseif ($is_sl) $sql="SELECT name FROM sqlite_master WHERE type='table' ORDER BY 1";
	elseif ($is_pg) $sql="SELECT relname FROM pg_class a,pg_namespace b\nWHERE a.relnamespace=b.oid AND b.nspname='public' AND a.relkind='r' ORDER BY 1";
	elseif ($is_cb) $sql="SELECT class_name FROM db_class WHERE class_type='CLASS' AND is_system_class='NO' ORDER BY 1";
	$arr=get_row($sql,1);
	$tabs[0]=lang(2204);
	foreach ($arr as $v) $tabs[$v]=$v;
	echo "\n<table><tr><td>",lang(2205),':</td><td>',html_form('select','imp[tab]',$SIDU[4],'',$tabs,
		"onchange=\"location='imp.php?id=$SIDU[0],$SIDU[1],$SIDU[2],r,'+this.options[this.selectedIndex].value\""),'</td></tr>';
	if ($SIDU[4]) echo "\n<tr><td valign='top'>",lang(2206),':</td><td>',html_form('textarea','imp[col]',$imp['col'],350,90),'</td></tr>';
	echo "\n<tr><td valign='top'>",lang(2207),":</td><td><input type='file' name='f'></td></tr>\n</table>
<p class='dot'><br><b>",lang(2209),":</b></p>
<p class='dot'>",lang(2210),': ',html_form('text','imp[sepC]',($imp['sepC'] ? $imp['sepC'] : ','),50)," <i class='green'>eg \\t , ; « ||| »</i>
<br>",lang(2211),': ',html_form('text','imp[sepR]',($imp['sepR'] ? $imp['sepR'] : '\n'),50)," <i class='green'>eg \\n</i>
<br>",lang(2214),': ',html_form('text','imp[pk]',$imp['pk'],150)," <i class='green'>eg pk1;pk2</i>
<br>",html_form('checkbox','imp[rmEnc]',$imp['rmEnc'],'',array(1=>'')),lang(2212),"
<br>",html_form('checkbox','imp[NL]',$imp['NL'],'',array(1=>'')),lang(2213),"
<br>",html_form('checkbox','imp[del]',$imp['del'],'',array(1=>"<i class='red'>")),lang(2215),"</i>
<br>",html_form('checkbox','imp[merge]',$imp['merge'],'',array(1=>"<i class='green'>")),lang(2216),"</i>
<br>",html_form('checkbox','imp[stop]',$imp['stop'],'',array(1=>'')),lang(2217),"</p>
<p id='waitHide'>",html_form('submit','cmd',lang(2218),'','',"id='impCMD'"),"</p>
<p id='wait' class='ac hide'>",html_img('loading.gif'),"<br><br><span class='green'>",lang(2225),"</span>
<br><br><span class='red'>",lang(2226),"</span></p>
</div></form>";
}
function valid_data($SIDU,&$imp){
	$imp['sepC']=trim($imp['sepC']);
	if (!$imp['sepC']) $imp['sepC']=',';
	if (!$SIDU[4]) return lang(2219);
	$col=explode("\n",$imp['col']);
	foreach ($col as $v){
		$v=trim($v);
		if ($v){
			if (!in_array($v,$imp['cols'])) return lang(2220,$v);
			$arr[]=$v;
		}
	}
	$imp['col']=implode("\n",$arr);
	if (!$imp['col']){
		$arr=$imp['cols'];
		$imp['col']=implode("\n",$arr);
	}
	$imp['pk']=strip($imp['pk'],1,1,1);
	if ($imp['pk']){
		$arrPK=explode(';',$imp['pk']);
		foreach ($arrPK as $k=>$v){
			$v=trim($v);
			if ($v=='') unset($arrPK[$k]);
			elseif (!in_array($v,$arr)) return lang(2221,$v);
		}
		$imp['pk']=implode(';',$arrPK);
	}
	if (!$_FILES['f']['error'] && $_FILES['f']['tmp_name']){
		if (substr($_FILES['f']['type'],0,4)<>'text') return lang(2222);
		$tran2=array('\r'=>"\r",'\n'=>"\n");
		$imp['sepC2']=strtr($imp['sepC'],$tran2);
		$imp['file']=explode(strtr($imp['sepR'],$tran2),file_get_contents($_FILES['f']['tmp_name']));
	}else return lang(2224);
}
function clean_str($str,$rmEnc){
	$str=trim($str);
	if ($str=='') return;
	if (!$rmEnc) return $str;
	$s1=substr($str,0,1);
	if ($s1=="'" || $s1=='"') $str=substr($str,1);
	else{
		$s1=substr($str,-1);
		if ($s1=="'" || $s1=='"') $str=substr($str,0,-1);
		else return $str;
	}
	return clean_str($str,$rmEnc);
}
function save_data($SIDU,$imp,$is_my,$is_pg,$is_cb,$is_sl){
	if ($imp['del']) save_data_sql_run(0,'DELETE FROM '.sql_kw($SIDU[4]),$imp['stop']);
	$cols=explode("\n",$imp['col']);
	if ($imp['pk']) $pk=explode(';',$imp['pk']);
	foreach ($cols as $k=>$v){
		if (in_array($v,$pk)) $arrPK[]=$k;
		$cols[$k]=sql_kw($v);
	}
	$sql=(!$imp['pk'] ? 'INSERT INTO ' : 'UPDATE ');
	if ($imp['NL']) $tran=array('\n'=>"\n",'\r'=>"\r");
	$tran["'"]="''";
	if (sidu_slash($SIDU['eng'])) $tran['\\']='\\\\';
	if ($is_my) $sql .="`$SIDU[1]`.`$SIDU[4]`";
	elseif ($is_pg) $sql .="\"$SIDU[2]\".\"$SIDU[4]\"";
	elseif ($is_cb || $is_sl) $sql .="\"$SIDU[4]\"";
	else $sql.=$SIDU[4];
	$sql .=(!$imp['pk'] ? '('.implode(',',$cols).') VALUES ' : ' SET ');
	$numCM=$numC=count($cols);
	if (!$imp['merge']) $numCM++;
	$numR=count($imp['file']);
	$numL=($is_sl ? 1 : 200);//each insert max lines
	$numIns=0;
	for ($i=0;$i<$numR;$i++){
		$txt=trim($imp['file'][$i]);
		if ($txt) save_data_sql($i,$SQL,$imp,$txt,$numCM,$numC,$arrPK,$cols,$numIns,$numL,$sql,$tran);
	}
	if (!$imp['pk'] && $SQL){
		if (substr($SQL,-2)==",\n") $SQL=substr($SQL,0,-2);
		if ($SQL) save_data_sql_run($i,$SQL,$imp['stop']);
	}
	echo "<br><p class='ok'>",lang(2227),"</p>";
}
function save_data_sql($i,&$SQL,$imp,$txt,$numCM,$numC,$arrPK,$cols,&$numIns,$numL,$sql,$tran){
	$arr=explode($imp['sepC2'],$txt,$numCM);
	foreach ($arr as $j=>$v){
		$v=clean_str($v,$imp['rmEnc']);
		$arr[$j]=strtr($v,$tran);
	}
	for ($k=0;$k<$numC;$k++){
		$v=$arr[$k];
		if (strtoupper($v)=='NULL') $v='NULL';//this will be bug if real text=='null'
		else $v="'$v'";
		if (!$imp['pk']) $arrD[]=$v;
		elseif (in_array($k,$arrPK)) $arrWhere[]=$cols[$k].($v=='NULL' ? ' IS ' : '=').$v;
		else $arrD[]=$cols[$k]."=$v";
	}
	if (!$imp['pk']){
		$numIns++;
		if (!$SQL) $SQL=$sql;
		$SQL .='('.implode(',',$arrD).')';
		if ($numIns==$numL){
			save_data_sql_run($i,$SQL,$imp['stop']);
			$SQL='';
			$numIns=0;
		}else $SQL .=",\n";
	}else save_data_sql_run($i,$sql.implode(',',$arrD).' WHERE '.implode(' AND ',$arrWhere),$imp['stop']);
}
function save_data_sql_run($i,$SQL,$stop){
	tm_run($SQL);
	$err=sidu_err(1);
	if ($err){
		echo "<p class='err'>",lang(2228,$i),"<br>$err</p><pre>$SQL</pre><br>";
		if ($stop) die("<br><p class='err'>".lang(2229)."</p><p class='ok'>".lang(2230).'</p>');
	}
}
?>
