<?php
$pin['uppe']=-1;
include 'inc.page.php';
if (isset($init)) @main();
else @main_init($id,$SIDU['conn'][$SIDU[0]],$SIDU['page']['tree']);

function main_init($id,$conn,$tree){
	$arr=explode(',',$id,7);
	$str=dec65($arr[6]);
	$id="$arr[0],$arr[1],$arr[2],$arr[3],";
	$id2=$arr[4].'_';
	$eng=$conn[1];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	if ($is_my) $arr2=menu_my($arr[1],$arr[3]);
	else{
		$arr2=menu_pg($conn,$arr[5],$arr[3]);//5:db-oid 3typ
		if ($arr[3]=='S') die('***'.$arr2[0]);
	}
	if ($is_pg && $arr[3]=='f') return main_init_cout_pgF($arr2,$arr[5],$str,$id,$id2);
	foreach ($arr2 as $v) menu_tree_init($arr3,$v,$tree);
	if ($is_pg) $oid=$arr[5];
	main_init_cout($arr3,$arr[3],$str,$id,$id2,$oid);
}
function main_init_cout_pgF($arr,$oid,$str,$id,$id2){
	$num=count($arr)-1;
	foreach ($arr as $k=>$v){
		$last=($i++==$num ? 'last' : '');
		$res.="\n<br>$str<b class='tr_join$last'></b><b class='zf'></b><a target='main' href='db.php?id=$id$k&#38;oid=$oid'>$k</a> <i>($v)</i>";
		$ttl+=$v;
	}
	echo "$res***$ttl";
}
function main_init_cout($arr,$typ,$str,$id,$id2,$oid,&$slTTL){
	foreach ($arr as $k=>$v){
		if (substr($k,0,4)=='log_' && count($v)==1){//what about pg_* fix later
			$arr['log'][]=$v[0];
			unset($arr[$k]);
		}
	}
	if (isset($arr['log'])) sort($arr['log']);
	if ($oid) $oidStr="&#38;oid=$oid";
	$num=count($arr)-1;
	foreach ($arr as $k=>$v){
		$numX=count($v);
		$last=($i++==$num ? 'last' : '');
		if ($num && $numX<>1) $res .="\n<br>$str<b class='tr_$last' onclick=\"showHideTree('$id2$k','$last')\" id='p$id2$k'></b><b class='tr_f'></b><a href='db.php?id=$id$k$oidStr' target='main'>$k</a> <i>($numX)</i>\n<span class='hide' id='t$id2$k'>";
		foreach ($v as $k2=>$v2){
			$lastX=($k2==$numX-1 ? 'last' : '');
			$res .="\n<br>$str".($num && $numX<>1 ? "<b class='tr_line$last'></b>" : '')."<b class='tr_join".($numX==1 ? $last : $lastX)."'></b>".
				"<a href='tab.php?id=$id$v2&#38;desc=1$oidStr' title='info' target='main'><b class='x$typ'></b></a> <a href='tab.php?id=$id$v2$oidStr' target='main' title='$v2'>$v2</a>";
		}
		if ($num && $numX<>1) $res .='</span>';
		$ttl+=$numX;
	}
	if ($slTTL){
		$slTTL=$ttl;
		return $res;
	}
	echo $res.'***'.$ttl;
}
function main(){
	global $SIDU;
	unset($_SESSION["no_sidu_fk_$SIDU[0]"]);//each refresh win will reset this
	$conn=$SIDU['conn'][$SIDU[0]];
	$res=explode(';',trim($conn[6]));
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	foreach ($res as $v){
		$v=trim($v);
		if ($v) $dbs[]=($is_sl || $is_cb ? $v : "$v%");
	}
	if (!isset($dbs)) $dbs[]='%';
	$conn[6]=$dbs;
	$tree=$SIDU['page']['tree'];
	if ($is_my) $arr=menu_my($dbs);
	elseif ($is_pg) $arr=menu_pg($conn);
	elseif ($is_sl) $arr=menu_sl($conn,$tree);
	elseif ($is_cb) $arr=menu_cb($conn,$tree);
	menu_tree_cout($arr,$conn);
}
function menu_my($dbs,$typ){
	if ($typ){
		$ver=mysql_get_server_info();
		if ($ver<'5.0.2') return get_row("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='$dbs' and TABLE_TYPE".($typ=='v' ? "=" : "<>")."'VIEW'",1);
		return get_row("SHOW FULL TABLES FROM $dbs WHERE Table_type='".($typ=='v' ? 'VIEW' : ($dbs=='information_schema' ? 'SYSTEM VIEW' : 'BASE TABLE'))."'",1);
	}
	foreach ($dbs as $db){
		$res=get_row('SHOW DATABASES'.($db<>'%' ? " LIKE '$db'" : ''),1);
		foreach ($res as $d) $arr[$d][0]=array('r'=>'','v'=>'');
	}
	return $arr;
}
function menu_pg($conn,$db,$typ){
	if ($db){
		//db_conn($conn,$db);//this line cause problem for pdo,turned off, audit later
		if ($typ=='S') return get_row("SELECT count(*) FROM pg_class WHERE relnamespace=$db AND relkind='S'",1);
		if ($typ=='f') return get_row("SELECT substr(proname,1,1),count(*) FROM pg_proc\nWHERE pronamespace=$db GROUP BY 1 ORDER BY 1",2);
		return get_row("SELECT relname FROM pg_class\nWHERE relnamespace=$db AND relkind='$typ' ORDER BY 1",1);
	}
	if ($conn[6][0]<>'%'){
		foreach ($conn[6] as $db) $where .=" OR datname LIKE '$db'";
		$where="\nAND (".substr($where,4).")";
	}
	$dbs=get_row("SELECT datname FROM pg_database WHERE datistemplate=false$where ORDER BY 1",1);
	if (!$dbs[0] && $conn[6][0]<>'%'){
		foreach ($conn[6] as $db) $dbs[]=substr($db,0,-1);
	}
	foreach ($dbs as $db){
		db_conn($conn,$db);
		$res=get_row("SELECT oid,nspname FROM pg_namespace\nWHERE nspname NOT LIKE 'pg_toast%' AND nspname NOT LIKE 'pg_temp%' ORDER BY 2",2);
		foreach ($res as $id=>$d) $arr[$db][$d]=array('f'=>$id,'S'=>$id,'r'=>$id,'v'=>$id);
	}
	return $arr;
	//operator temp parser dict domain conversion aggregate -- not available at the moment
}
function menu_sl($conn,$tree){
	global $SIDU;
	foreach ($conn[6] as $db){
		$SIDU['dbL']=db_conn($conn,$db);
		$arr[$db][0]['r']=array();
		$rows=get_rows('SELECT type,name FROM sqlite_master ORDER BY 2');
		foreach ($rows as $row){
			if ($row[0]=='table') menu_tree_init($arr[$db][0]['r'],$row[1],$tree);
			elseif ($row[0]=='view') menu_tree_init($arr[$db][0]['v'],$row[1],$tree);
		}
		menu_tree_init($arr[$db][0]['r'],'sqlite_master',$tree);
	}
	return $arr;
}
function menu_cb($conn,$tree){
	global $SIDU;
	foreach ($conn[6] as $db){
		$SIDU['dbL']=db_conn($conn,$db);
		$rows=get_rows("SELECT class_name,class_type,is_system_class FROM db_class ORDER BY 3 desc,1");
		foreach ($rows as $row){
			$sch=($row['is_system_class']=='NO' ? 0 : 'sys');
			$typ=($row['class_type']=='CLASS' ? 'r' : ($row['class_type']=='VCLASS' ? 'v' : 'other'));
			menu_tree_init($arr[$db][$sch][$typ],$row[0],$tree);
		}
	}
	return $arr;
}
function menu_tree_init(&$arr,$str,$tree){
	$tabs=table2tabs($str,$tree);
	if ($tabs=='') $tabs=$str;
	$arr[$tabs][]=$str;
}
function menu_tree_cout($arr,$conn){
	$eng=$conn[1];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	echo "<b class='eng_",get_engC($eng),"'></b><a href='db.php?id=$conn[0]'>",($is_sl ? 'SQLite' : "$conn[3]@$conn[2]"),(substr($eng,0,4)=='PDO_' ? '(PDO)' : ''),'</a>';
	$arrT=array('r'=>lang(2404),'v'=>lang(2405),'f'=>lang(2406),'p'=>lang(2407),'t'=>lang(2408),'S'=>lang(2409));
	$ndb=count($arr);
	foreach ($arr as $db=>$sch){
		$last=(++$i==$ndb ? 'last' : '');
		$tc1="<b class='tr_line$last'></b>";
		$nS=count($sch);$k=0;
		echo "\n<br><b class='tr_$last' id='p$i' onclick=\"showHideTree($i,'$last')\"></b><b class='db'></b><a href='db.php?id=$conn[0],$db'>$db</a>",($is_pg ? " <i>($nS)</i>" : ''),"\n<span class='hide' id='t$i'>";
		foreach ($sch as $s=>$Sch){
			if ($is_pg || $is_cb) $lastS=(++$k==$nS ? 'last' : '');
			if ($is_pg){
				echo "\n<br>$tc1<b class='tr_$lastS' id='p$i-$k' onclick=\"showHideTree('$i-$k','$lastS')\"></b><b class='sch'></b><a href='db.php?id=$conn[0],$db,$s",($is_pg ? "&#38;oid=$Sch[r]" : ''),"' target='main'>$s</a>\n<span class='hide' id='t$i-$k'>";
				$tc2="$tc1<b class='tr_line$lastS'></b>";
			}else $tc2=$tc1;
			$nT=count($Sch);$j=0;
			foreach ($Sch as $t=>$typ){
				$lastT=(++$j==$nT ? 'last' : '');
				$tc3="$tc2<b class='tr_line$lastT'></b>";
				echo "\n\t<br>$tc2<b class='tr_$lastT' id='p$i-$k-$j' onclick=\"showHideTree('$i-$k-$j','$lastT','$conn[0],$db,$s,$t,$i-$k-$j,$typ,".enc65($tc3)."')\"></b><b class='z$t'></b><a href='db.php?id=$conn[0],$db,$s,$t",($is_pg ? "&#38;oid=$typ" : ''),"' target='main'>",($is_cb && $s ? 'SYS ' : ''),"{$arrT[$t]}</a> ";
				if ($is_sl || $is_cb){
					$slTTL=1;//must >0 to call next fn
					$res=main_init_cout($typ,$t,$tc3,"$conn[0],$db,$s,$t,",'','',$slTTL);
					echo "<i>($slTTL)</i><span class='hide' id='t$i-$k-$j'>$res</span>";
				}else echo "<span class='hide load' id='t$i-$k-$j'><br><b class='load'></b></span>";
			}
			if ($is_pg) echo "\n</span>";
		}
		echo "\n</span>";
	}
}
?>
