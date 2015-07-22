<?php
//sql=0id 5ttl_time 6Rows 7num_sql 8num_err
//cookie=0id 1db 2sch 3typ 4tab
//RES 0sql 1err 2aff-row 3sele-rows 4time 5col 6data
include 'inc.page.php';
$SIDU['page']['xJS']=$SIDU['page']['nav']=1;
@run_sqls();//inc reset cookie
@uppe();
@main();
@down();

function navi(){
	global $SIDU;
	$sql=urlencode($SIDU['RES'][$SIDU[6]][0]);
	echo "<a id='texp' href='exp.php?id=$SIDU[0]&#38;sql=$sql' ",html_hkey('E',lang(3101))," class='xwin'></a>
<a id='tchart' href='sql.php?id=$SIDU[0]&#38;chart=sql&#38;sql=$sql' title='",lang(3107),"'></a>
<a id='tchartV2' href='sql.php?id=$SIDU[0]&#38;chart=sqlV2&#38;sql=$sql' ",html_hkey('D',lang(3108)),"></a>
<a id='tchartV' href='sql.php?id=$SIDU[0]&#38;chart=sqlV&#38;sql=$sql' ",html_hkey('C',lang(3102)),"></a>$SIDU[sep] $SIDU[7]sql &nbsp;",
		($SIDU[8] ? "<b class='red none'>$SIDU[8]</b>" : $SIDU[8]+0),'err &nbsp;',($SIDU[5]>1000 ? round($SIDU[5]/1000,1) : "$SIDU[5]m").'s ';
	if (isset($SIDU[6])) echo '&nbsp;'.$SIDU['RES'][$SIDU[6]][3].'r ';
	navi_obj($SIDU['cook'][$SIDU[0]]);//pg lost oid -- well -- fix later :D
	echo " $SIDU[sep] ",date('Y-m-d H:i:s');
}
function main(){
//RES 0sql 1err 2aff-row 3sele-rows 4time 5col 6data
	global $SIDU;
	$conn=$SIDU['conn'][$SIDU[0]];
	$link=explode(',',$_GET['id']);
	$SIDU['gridMode']=$_POST['sqlmore'];
	foreach ($SIDU['RES'] as $i=>$res){
		echo "<div style='padding:0 2px 30px'>";
		$str="<i class='grey hideP hand' title='".lang(3103)."'>".html8(substr($res[0],0,200)).'</i>';
		if ($res[1]) echo "$str<pre class='red fm'>\n$res[1]</pre>";
		else{
			if (isset($res[2]) || $res[5][0][0]=='') echo "$str<br>",lang(3104,'<u>'.($res[2]+0).'</u>');
			else{
				if ($_POST['sqlmore'] || (isset($SIDU[6]) && $SIDU[6]==$i)) cout_sql_res($SIDU,$res,++$j);
				echo "$str<br>",lang(3105,'<u>'.($res[3]+0).'</u>');
			}
			echo ' : ',lang(3106,($res[4]>1000 ? "<u class='blue'>".round($res[4]/1000,1).'</u>s' : "<u>$res[4]</u>ms"));
		}
		echo '</div>';
	}
}
function cout_sql_res($SIDU,$res,$j){
	$SIDU['col']=$res[5];
	$SIDU['data']=$res[6];
	init_pg_col_align($SIDU['data'],$SIDU['col']);
	if ($_GET['chart']) cout_sql_chart($SIDU);
	init_col_width($SIDU);
	cout_data($SIDU,$link,$conn,$j);
}
function cout_sql_chart($SIDU){
	$init['sepCol']='«';
	$init['sepRow']='»';
	$init['w']='fix';
	$init['valShow']=1;
	$init['valAngle']=90;
	$init['xAngle']=-90;
	$init['gapB']=35;
	$init['fmt']=($_GET['chart']=='sql' ? 'str' : ($_GET['chart']=='sqlV2' ? 'strV2' : 'strV'));
	$num=count($SIDU['col']);
	if ($init['fmt']=='strV2'){//auto clean
		if ($num>3){
			for ($i=2;$i<$num;$i++){
				if ($SIDU['col'][$i][8]=='i'){
					$col=$i; break;
				}
			}
			$num=3;
		}
		if ($col && $col>2){
			$SIDU['col'][2]=$SIDU['col'][$col];
			foreach ($SIDU['data'] as $k=>$v) $SIDU['data'][$k][2]=$SIDU['data'][$k][$col];
		}
		if ($num==3 && $SIDU['col'][2][8]<>'i') $num=2;
		if ($num<3) $init['fmt']='strV';
	}
	if ($num==2 && $SIDU['col'][1][8]<>'i') $num=1;
	for ($i=0;$i<$num;$i++){
		if (!$i || ($i==1 && $init['fmt']=='strV2') || $SIDU['col'][$i][8]=='i'){
			if ($i) $data .=$init['sepCol'];
			$data .=$SIDU['col'][$i][0];
		}
	}
	foreach ($SIDU['data'] as $v){
		$data .=$init['sepRow'];
		for ($i=0;$i<$num;$i++){
			if (!$i || ($i==1 && $init['fmt']=='strV2') || $SIDU['col'][$i][8]=='i'){
				if ($i) $data .=$init['sepCol'];
				$data .=$v[$i];
			}
		}
	}
	$init['data']=$data;
	$_SESSION['init']=$init;
	echo "<img src='bChart.php?ses=init'>";
}
function run_sqls(){
	global $SIDU;
	$conn=$SIDU['conn'][$SIDU[0]];
	$eng=$conn[1];
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	$is_PDO=substr($eng,0,4)=='PDO_';
	$cook=$SIDU['cook'][$SIDU[0]];
	tm_use_db($cook[1],$cook[2]);
	if ($_GET['sql']=='show vars') $_POST['sqlcur']=($is_pg ? 'SHOW ALL' : 'SHOW VARIABLES');
	elseif (substr($_GET['sql'],0,6)=='FLUSH '){
		if ($_GET['sql']=='FLUSH ALL') $_POST['sqlcur']="FLUSH LOGS;\nFLUSH HOSTS;\nFLUSH PRIVILEGES;\nFLUSH TABLES;\nFLUSH STATUS;\nFLUSH DES_KEY_FILE;\nFLUSH QUERY CACHE;\nFLUSH USER_RESOURCES;\nFLUSH TABLES WITH READ LOCK";
		else $_POST['sqlcur']=$_GET['sql'];
	}elseif (substr($_GET['sql'],0,9)=='STATScol:') $_POST['sqlcur']='SELECT '.sql_kw(substr($_GET['sql'],9)).',count(*) FROM '.sql_kw($cook[4]).' GROUP BY 1 ORDER BY 2 DESC,1 LIMIT 20';
	elseif ($_GET['sql']) $_POST['sqlcur']=$_GET['sql'];
	$arr=explode(";\r\n",trim($_POST['sqlcur']));
	$txt=implode(";\n",$arr);
	$arr=explode(";\r",$txt);
	$txt=implode(";\n",$arr);
	$arr=explode(";\n",$txt);
	foreach ($arr as $v){
		$v=trim($v);
		if ($v) $arr2[]=$v;
	}
	foreach ($arr2 as $i=>$sql){
		$time_start=microtime(true);
		$res=tm_run($sql);
		$time_end=microtime(true);
		$time=round(($time_end-$time_start)*1000);
		$SIDU[5] +=$time;
		$err=sidu_err(1);
		$RES[$i][0]=$sql;
		if ($err){
			$RES[$i][1]=$err;
			$SIDU[8]++;
		}else{
			run_sqls_num($res,$eng,$RES[$i]);
			if (!isset($SIDU[6])){
				$SIDU[6]=$i;
				run_sqls_data($res,$eng,$RES[$i],$is_sl);
			}elseif ($_POST['sqlmore']) run_sqls_data($res,$eng,$RES[$i],$is_sl);
			run_sqls_pdo($RES[$i],$is_PDO);
			$RES[$i][4]=$time;
		}
		tm_his_log('S',$sql,$time,$err);
	}
	$SIDU[7]= ++$i;
	$SIDU['RES']=$RES;
	//reset cookie
	if ($is_my){
		$db=get_var('SELECT database()');
		if ($db<>$cook[1]) $ck=array($conn[0],$db);
	}elseif ($is_pg){
		$sch=get_var('SHOW search_path');
		if (substr($sch,0,8)=='"$user",') $sch=substr($sch,8);
		$sch=str_replace('"','',$sch);
		if ($sch<>$cook[2]) $ck=array($conn[0],$cook[1],$sch);
	}
	if (isset($ck)) update_sidu_cook($ck);
}
function run_sqls_num($res,$eng,&$Res){
	$Res[3]=sidu_num_rows($res,$eng);
	if (!$Res[3]) $Res[2]=sidu_affected_rows($res,$eng);
}
function run_sqls_data($res,$eng,&$Res,$is_sl){
	if ($Res[3]!==false || $is_sl){
		$Res[5]=get_sql_col($res,$eng);
		if ($Res[3] || $eng=='PDO_sqlite'){
			$Res[6]=get_row_data($res,4,'NUM');
			$numRow=count($Res[6]);
//code via test result, i forgot why is following. anyway works for all
			if ($eng=='sl3') $Res[3]=$numRow;
			if ($eng=='PDO_sqlite' && $numRow){
				$Res[3]=$numRow; unset($Res[2]);
			}
		}
	}
}
function run_sqls_pdo(&$Res,$is_PDO){//fix pdo insert,delete,update bug:return 0 affected
	if (!$is_PDO || !$Res[3] || $Res[2] || is_array($Res[5])) return;
	$Res[2]=$Res[3]; unset($Res[3]);
}
?>
