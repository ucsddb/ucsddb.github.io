<?php
include 'inc.page.php';
@uppe();
echo "<div class='web'>";
@main($fm,$to,$cmd);
echo '</div>';
@down();

function main($fm,$to,$cmd){
	global $SIDU;
	$CONN=$SIDU['conn'];
	foreach ($CONN as $conn){
		if (!$fm['host'] || !isset($CONN[$fm['host']])) $fm=array('host'=>$conn[0]);
		is_eng($conn[1],$is_my,$is_pg,$is_cb,$is_sl);
		$host[$conn[0]]="[$conn[1]] $conn[3]@$conn[2]".(!$is_my && !$is_pg ? ' {'.$conn[6].'}' : '').($conn[5] ? " : $conn[5]" : '');
	}
	$fm_dbs=main_host_db($fm['host']);
	if (!$fm['db'] || !in_array($fm['db'],$fm_dbs)) $fm['db']=$fm_dbs[0];
	if (!$to['host'] || !isset($CONN[$to['host']])) $to=array('host'=>$fm['host']);
	elseif ($to['host']<>$fm['host']) $to_dbs=main_host_db($to['host']);
	if (!isset($to_dbs)) $to_dbs=$fm_dbs;
	if (!$to['db']) $to['db']=$to_dbs[0];
	main_conn($fm['host'],$fm['db']);
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	echo "<h1 class='dot'>",lang(1451),"</h1>",html_form('form','',"?id=$SIDU[0]#cmp"),"
<p>FM: ",html_form('select','fm[host]',$fm['host'],250,$host),html_form('select','fm[db]',$fm['db'],120,main_db_arr($fm_dbs)),"
<br>TO: ",html_form('select','to[host]',$to['host'],250,$host),html_form('select','to[db]',$to['db'],120,main_db_arr($to_dbs)),"</p>
<h2>",lang(1460)," <b class='red'>{$SIDU[conn][$SIDU[0]][2]}</b> . <b class='green'>$fm[db]",($is_pg ? ' . public' : ''),"</b></h2>
<div style='height:200px;overflow:auto' class='bg box'>";
	$arr_tab=main_db_tab($fm['db'],$is_my,$is_pg,$is_sl,$is_cb);
	$tree=$SIDU['page']['tree'];
	foreach ($arr_tab as $t){//these 2 func from menu.php
		$sep=table2tabs($t,$tree);
		if ($sep=='') $sep=$t;
		$tabs[$sep][]=$t;
	}
	foreach ($tabs as $k=>$v){
		if (substr($k,0,4)=='log_' && count($v)==1){//what about pg_* fix later
			$tabs['log'][]=$v[0];
			unset($tabs[$k]);
		}
	}
	if ($fm['tab']) echo "<p class='b ok'>» ",(substr($fm['tab'],0,1)==':' ? substr($fm['tab'],1).'_*' : $fm['tab']),"</p>";
	echo '<p>',html_form('radio','fm[tab]',$fm['tab'],'',array("<i".(!$fm['tab'] ? " class='b red'" : '').'>'.lang(1461).'</i>')),"</p>";
	foreach ($tabs as $tab=>$arr){
		if (count($arr)>1){
			echo "<p>",html_form('radio','fm[tab]',$fm['tab'],'',array(":$tab"=>'<b'.($fm['tab']==":$tab" ? " class='green'" : '').">$tab".'_*</b>'));
			foreach ($arr as $t) echo "<br>&nbsp; &nbsp; ",html_form('radio','fm[tab]',$fm['tab'],'',array($t=>$t));
			echo "</p>";
		}else echo "<p>",html_form('radio','fm[tab]',$fm['tab'],'',array($arr[0]=>$arr[0])),"</p>";
	}
	echo "</div><br><h2>",lang(1452),"</h2><p><br>";
	if (!is_array($fm['task'])) $fm['task'][]=1;
	if (in_array(4,$fm['task'])) $fm['task']=array(4);
	if (in_array(3,$fm['task']) || in_array(2,$fm['task'])) $fm['task'][]=1;
	$fm['sort']=strip($fm['sort']);
	if (!$fm['sort']) $fm['sort']=1;
	echo html_form('checkbox','fm[task][]',$fm['task'],'',array(1=>lang(1453),lang(1454),lang(1455).'<br>',lang(1456))),
		html_form('text','to[tab]',$to['tab'],100),' ',lang(1457),"
		<br>&nbsp; &nbsp; ",lang(1458),' ',html_form('text','fm[sort]',$fm['sort'],142)," eg. 1,2 ",lang(1462),"</p>
		<p id='waitHide'>",html_form('submit','cmd',lang(1463)),
		html_form('submit','cmd',lang(1464),'','',"id='impCMD'"),' ',
		html_form('checkbox','fm[err]',$fm['err'],'',array(1=>'')),lang(1459),"</p></form>
		<p class='ac hide' id='wait'>",html_img('loading.gif'),"<br><br><span class='green'>",lang(1478),"</span><br><br><span class='red'>",lang(1479),"</span></p>";
	if ($cmd==lang(1464)) main_cmp($fm,$to,$arr_tab,$tabs,$tree);
}
function main_cmp($fm,$to,$arr_tab,$tabs,$tree){
	echo "<h1 id='cmp' class='dot'>",lang(1465),"</h1><br>";
	if (in_array(4,$fm['task'])) main_cmp_data($fm,$to);
	else main_cmp_db($fm,$to,$arr_tab,$tabs,$tree);
}
function main_cmp_data($fm,$to){
	global $SIDU;
	if (!$fm['tab'] || substr($fm['tab'],0,1)==':') return print("<p class='err'>".lang(1466)."</p>");
	if ($to['tab']) $to_t=strip($to['tab'],1,1,1);
	if (!$to_t) $to_t=$fm['tab'];
	main_conn($to['host'],$to['db']);
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	$info_to[0]=get_tab_info($to_t,$is_my,$is_pg,$is_cb,$is_sl);
	if (!$info_to[0]) return print("<p class='err'>".lang(1467,$to_t)."</p>");
	$info_to[1]=get_var("select count(*) from ".sql_kw($to_t));
	$fm['pgsize']=ceil($fm['pgsize']);
	if ($fm['pgsize']<10 || $fm['pgsize']>9999) $fm['pgsize']=100;
	$fm['num']=ceil($fm['num']);
	if ($fm['num']<0) $fm['num']=0;
	$data_to=get_rows("SELECT * FROM $to_t ORDER BY $fm[sort] LIMIT ".($is_cb ? "$fm[num],$fm[pgsize]" : "$fm[pgsize] OFFSET $fm[num]"),'ASSOC');
	main_conn($fm['host'],$fm['db']);
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	$info[0]=get_tab_info($fm['tab'],$is_my,$is_pg,$is_cb,$is_sl);
	$info[1]=get_var("select count(*) from ".sql_kw($fm['tab']));
	$data=get_rows("SELECT * FROM $fm[tab] ORDER BY $fm[sort] LIMIT ".($is_cb ? "$fm[num],$fm[pgsize]" : "$fm[pgsize] OFFSET $fm[num]"),'ASSOC');
	echo "<p class='dot'>FM <b>$fm[tab]</b> TO <b>$to_t</b></p>";
	if ($info[1]!=$info_to[1]) echo "<p class='red'>...",lang(1468),": $info[1] : $info_to[1]</p>";
	elseif (!$fm['err']) echo "<p class='grey'>...",lang(1469),": $info[1]</p>";
	if ($info[0]!=$info_to[0]) echo "<p class='red'>...",lang(1470),"</i><br><br><b>FM</b>: $info[0]<br><br><b>TO</b>: $info_to[0]</p>";
	elseif (!$fm['err']) echo "<p class='grey'>...",lang(1471),"</p>";
	echo "<p class='dot'>",lang(1472)," FM <b>$fm[tab]</b> TO <b>$to_t</b></p>";
	$num_next=$fm['num']+$fm['pgsize'];
	$ttl=count($data);
	if ($data==$data_to) echo "<p class='ok'>",lang(1473),": $fm[num] - ",($ttl>$num_next ? $num_next : $ttl+$fm['num']),"</p>";
	else{
		foreach ($data as $r=>$row){
			if ($row<>$data_to[$r]){
				echo "<p class='grey'><b>FM:</b>";
				foreach ($row as $k=>$v) echo " <i class='",($v==$data_to[$r][$k] ? '' : 'green'),"'>$k:",html8($v),"</i>;";
				echo "<br><b>TO:</b>";
				foreach ($data_to[$r] as $k=>$v) echo " <i class='",($v==$data[$r][$k] ? '' : 'red'),"'>$k:",html8($v),"</i>;";
				echo "</p>";
			}elseif (!$fm['err']) echo "<i class='grey'>OK</i><br>";
		}
	}
	if ($ttl==$fm['pgsize']){
		$url="?id=$SIDU[0]&cmd=".lang(1464)."&fm[num]=$num_next";
		foreach ($fm as $k=>$v){
			if (is_array($v)){
				foreach ($v as $k2=>$v2){
					if ($v2) $url.="&fm[$k][$k2]=$v2";
				}
			}elseif ($k<>'num' && $v) $url.="&fm[$k]=$v";
		}
		foreach ($to as $k=>$v){
			if ($v) $url.="&to[$k]=$v";
		}
		echo "<p><a href='$url#cmp'>",lang(1474,$fm['pgsize']),"</a></p>";
	}else echo "<p class='b dot'>",lang(1475),"</p>";
}
function get_db_tabs($eng,$tree,$fm,$arr_tab,$is_task2,$is_task3){
	foreach ($arr_tab as $k=>$t){
		if (main_db_range($fm['tab'],$t,$tree)) $info[$t][0]='';
	}
	if ($is_task2){
		is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
		foreach ($info as $t=>$v) $info[$t][0]=get_tab_info($t,$is_my,$is_pg,$is_cb,$is_sl);
	}
	if ($is_task3){
		foreach ($info as $t=>$v) $info[$t][1]=get_var("select count(*) from ".sql_kw($t));
	}
	return $info;
}
function get_tab_info($t,$is_my,$is_pg,$is_cb,$is_sl){
	if ($is_my){
		$desc=get_row('SHOW CREATE TABLE '.sql_kw($t));
		return $desc[1];
	}
	if ($is_pg || $is_cb){//lazy...upgrade later
		return "$t(".implode(', ',array_keys(get_row("SELECT * FROM public.$t LIMIT 1",0,'ASSOC'))).')';
	}
	if ($is_sl) return get_var("SELECT sql FROM sqlite_master WHERE name=tbl_name AND name='$t' LIMIT 1");
}
function main_cmp_db($fm,$to,$arr_tab,$tabs,$tree){
	global $SIDU;
	$is_task2=in_array(2,$fm['task']);
	$is_task3=in_array(3,$fm['task']);
	$fm_tabs=get_db_tabs($SIDU['eng'],$tree,$fm,$arr_tab,$is_task2,$is_task3);
	main_conn($to['host'],$to['db']);//now switch to to.host===
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	$arr_tab_to=main_db_tab($to['db'],$is_my,$is_pg,$is_sl,$is_cb);
	$to_tabs=get_db_tabs($SIDU['eng'],$tree,$fm,$arr_tab_to,$is_task2,$is_task3);
	$my_cut=') ENGINE=';
	foreach ($fm_tabs as $t=>$v){
		if (in_array($t,$arr_tab_to)){
			$err='';
			$info=$fm_tabs[$t]; $info_to=$to_tabs[$t];
			if ($is_task3){
				if ($info[1]!=$info_to[1]) $err="<br><i class='red'>...".lang(1468).": $info[1] : $info_to[1]</i>";
				elseif (!$fm['err']) $err="<br><i class='grey'>...".lang(1469).": $info[1]</i>";
			}
			if ($is_task2){
				if ($info[0]!=$info_to[0]){
					$err.="<br><i class='red'>...".lang(1470)."</i><br><br><b>FM</b>: <i class='grey'>$info[0]</i><br><br><b>TO</b>: ";
					if (!$is_my) $err.=$info_to[0];
					else{
						$inf=explode($my_cut,$info[0],2);//only for is_my
						$inf_to=explode($my_cut,$info_to[0],2);//only for is_my
						$err.="<i".($inf[0]==$inf_to[0] ? " class='grey'" : '').">$inf_to[0]$my_cut</i><i".($inf[1]==$inf_to[1] ? " class='grey'" : '').">$inf_to[1]</i>";
					}
				}elseif (!$fm['err']) $err.="<br><i class='grey'>...".lang(1471)."</i>";
			}
			if ($err) echo "<p class='dot'><i class='grey'>",lang(1476),": $t</i>$err</p>";
		}else echo "<p class='red dot'>",lang(1477)," TO: $t</p>";
	}
	foreach ($to_tabs as $t=>$v){
		if (!in_array($t,$arr_tab)) echo "<p class='dot green'>",lang(1477)," FM: $t</p>";
	}
}
function main_db_range($tab,$t,$tree){
	if (!$tab || $tab==$t) return 1;
	if (substr($tab,0,1)<>':') return;
	$tab=substr($tab,1).$tree;
	if (substr($t,0,strlen($tab))==$tab) return 1;
}
function main_host_db($host){
	global $SIDU;
	if ($SIDU[0]<>$host) main_conn($host);
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	if ($is_my) return get_row('SHOW DATABASES',1);
	if ($is_pg) return get_row("SELECT datname FROM pg_database WHERE datistemplate=false ORDER BY 1",1);
	if ($is_sl || $is_cb) return explode(';',$SIDU['conn'][$host][6]);
}
function main_conn($host,$db){
	global $SIDU;
	$conn=$SIDU['conn'][$host];
	$SIDU[0]=$conn[0];
	$SIDU['eng']=$conn[1];
	$SIDU['dbL']=db_conn($conn,$db);
}
function main_db_arr($arr){
	foreach ($arr as $v) $res[$v]=$v;
	return $res;
}
function main_db_tab($db,$is_my,$is_pg,$is_sl,$is_cb){
	if ($is_my) return get_row("SHOW FULL TABLES FROM $db WHERE Table_type='".($db=='information_schema' ? 'SYSTEM VIEW' : 'BASE TABLE')."'",1);
	if ($is_pg){
		$ns=get_var("SELECT oid,nspname FROM pg_namespace WHERE nspname='public'");
		return get_row("SELECT relname FROM pg_class\nWHERE relnamespace=$ns AND relkind='r' ORDER BY 1",1);
	}
	if ($is_sl) return get_row("SELECT name FROM sqlite_master WHERE type='table' ORDER BY 1",1);
	if ($is_cb) return get_row("SELECT class_name FROM db_class WHERE class_type='CLASS' and owner_name='PUBLIC' ORDER BY 1",1);
}
?>
