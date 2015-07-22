<?php
//tab=0id 1db 2sch 3typ 4tab 5sort1 6sort2 7sort 8fm 9to 10num f g pk col data
include 'inc.page.php';
$SIDU['page']['xJS']=$SIDU['page']['nav']=1;
@set_cook_tab();
@uppe();
@main();
@down();

function navi(){
	global $SIDU;
	$conn=$SIDU['conn'][$SIDU[0]];
	$link=explode(',',$_GET['id']);
	init_tab($SIDU,$link,$conn);
	$tabs=table2tabs($link[4],$SIDU['page']['tree']);
	$url="tab.php?id=$link[0],$link[1],$link[2],$link[3],$link[4]".get_oidStr();
	echo "<a id='texp' href='exp.php?id=$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3]&#38;tab=$SIDU[4]' ",html_hkey('E',lang(3701))," class='xwin'></a> ";
	if ($link[3]=='r') echo "<a id='timp' href='imp.php?id=$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4]' ",html_hkey('I',lang(3703))," class='xwin'></a>
<a href='#' title='",lang(3738),"' id='objTool' class='show'></a>
<a id='tflush' href='$url&#38;objcmd=Empty' ",html_hkey('-',lang(3706))," onclick=\"return confirm('",lang(3707,$SIDU[4]),"?')\"></a>";
	if ($_GET['desc']) echo " <a id='tdel' href='$url&#38;objcmd=Drop' ",html_hkey('X',lang(3711))," onclick=\"return confirm('".lang(3710,$SIDU[4])."?')\"></a>";
	else{//note end of next line has a white space to make Chrome work -- chrome bug?
		echo "&nbsp;$SIDU[sep] 
<a href='#' title='",lang(3712),"' id='toolEye'></a>
<a id='tgrid' href='#' ",html_hkey('Z',lang(3713))," onclick=\"submitForm('gridMode',".($SIDU['gridMode'] ? 0 : 1).")\"></a>
<a id='tsave' href='#' ",html_hkey('S',lang(3714))," onclick=\"submitForm('cmd','data_save')\"></a>
<a href='#' ",html_hkey('=',lang(3716))," id='addRow'></a>
<a id='tdel' href='#' ",html_hkey('X',lang(3715))," onclick=\"submitForm('cmd','data_del')\"></a>
<input type='text' id='sidu8' value='$SIDU[8]' class='fmto'><input type='text' id='sidu9' value='$SIDU[9]' class='fmto' title='",lang(3717),"'><a id='tgo' href='#' ",html_hkey('G',lang(3718))," onclick=\"submitForm('cmd','Go')\"></a>";
		if ($SIDU[9]==-1 || !$SIDU[8]) echo "<b title='",lang(3719)," - Fn+[' class='grey' id='tarr1f'></b><b title='",lang(3720)," - Fn+<' class='grey' id='tarr2b'></b>";
		else echo "<a id='tarr1f' href='#' ",html_hkey('[',lang(3719))," onclick=\"submitForm('cmd','p1')\"></a><a id='tarr2b' href='#' ",html_hkey('<',lang(3720))," onclick=\"submitForm('cmd','pback')\"></a>";
		echo "<span title='",lang(3721,$SIDU[10]),"'>$SIDU[10]</span>";
		if ($SIDU[9]==-1 || $SIDU[8]+$SIDU[9]>=$SIDU[10]) echo "<b title='",lang(3722)," - Fn+>' class='grey' id='tarr2n'></b><b title='",lang(3723)," - Fn+]' class='grey' id='tarr1l'></b>";
		else echo "<a id='tarr2n' href='#' ",html_hkey('>',lang(3722))," onclick=\"submitForm('cmd','pnext')\"></a><a id='tarr1l' href='#' ",html_hkey(']',lang(3723))," onclick=\"submitForm('cmd','plast')\"></a>";
	}
	navi_obj($SIDU);//next line » need 2 white space --chrome bug?
	if ($_GET['desc']) echo " ($SIDU[10])",($SIDU[10]>999 ? " »  <a id='tchartV' href='$url&#38;desc=1&#38;showStats=1' title='".lang(3709)."'></a>" : '');
}
function main(){
	global $SIDU;
	$conn=$SIDU['conn'][$SIDU[0]];
	$link=explode(',',$_GET['id']);
	tab_tool();
	if ($_GET['desc']) main_desc($SIDU,$link,$conn);
	else cout_data($SIDU,$link,$conn);
}
function is_type_int($typ){//this function need to upgrade
	$ints=array('int','serial','bigserial','oid','float','numeric','real','double','smallint','bigint','tinyint','date','time','datetime','timestamp');
	$typ=strtolower($typ);
	foreach ($ints as $v){
		$pos=strpos($typ,$v);
		if ($pos!==false && !$pos) return 1;
	}
}
function main_desc($SIDU,$link,$conn){
	is_eng($conn[1],$is_my,$is_pg,$is_cb,$is_sl);
	if ($is_pg && $SIDU['tabinfo'][2]=='t') unset($SIDU['col'][0]);
	echo "<table class='grid'><tr class='th'>
<td>",lang(3724),'</td><td>',lang(3725),'</td><td>Null</td><td>',lang(3726),'</td><td>',lang(3727),'</td><td>',lang(3728),"</td><td title='",lang(3729),"'>",lang(3730),"</td><td title='",lang(3731),"'>",lang(3732),"</td><td title='",lang(3733),"'>",lang(3734),"</td><td title='",lang(3735),"'>",lang(3736),'</td><td>',lang(3737),'</td></tr>';
	if ($SIDU[10] && ($SIDU[10]<1000 || $_GET['showStats'])){
		foreach ($SIDU['col'] as $col){
			$coln=sql_kw($col[0]);
			$colz=(is_type_int($col[1]) ? $coln : "length($coln)");
			$sql .=',count('.($SIDU['eng']=='sl' ? '' : 'distinct ')."$coln),min($colz),max($colz),".($col[1]=='timestamp' || $col[1]=='date' ? 0 : "avg($colz)")."\n";
		}
		$sql='SELECT '.substr($sql,1).' FROM '.sql_kw($SIDU[4]);
		$stat=get_row($sql);
	}
	foreach ($SIDU['col'] as $i=>$col){
		echo "<tr><td><a href='sql.php?id=$SIDU[0]&#38;sql=STATScol:$col[0]'>$col[0]</a></td><td>";
		if (strlen($col[1])>50) echo "<input type='text' value='",html8($col[1]),"' size='1' style='width:100%' class='bg1'>";
		else echo $col[1];
		if ($is_cb && ($col[1]=='STRING' || $col[1]=='CHAR')) echo "($col[4])";
		echo '</td><td>',($col[2]=='YES' || $col[2]=='f' ? 'Null' : 'No'),'</td><td>',html8($col[3]),'</td><td>';
		if ($col[7]=='PRI' || $col[7]=='p') echo "<span class='blue'>PK</span>";
		elseif ($col[7]=='f') echo "<span class='red'>FK</span>";
		elseif ($col[7]=='u' || $col[7]=='UNI') echo "<span class='green'>UK</span>";
		else echo $col[7];
		$distinct=ceil($stat[4*$i]);
		echo "</td><td>$col[5]</td>
<td class='ar",($distinct<$SIDU[10] && $distinct ? ' green' : ''),"'>$distinct</td>
<td class='ar'>",ceil($stat[4*$i+1]),"</td>
<td class='ar'>",ceil($stat[4*$i+2]),"</td>
<td class='ar'>",ceil($stat[4*$i+3]),"</td>
<td>$col[6]</td></tr>";
	}
	echo '</table>';
	if ($link[3]=='v') return main_desc_view($link,$SIDU['tabinfo'][0],$is_my,$is_pg,$is_cb,$is_sl);
	if ($is_cb){
		$auto_inc=get_var("SELECT att_name FROM db_serial WHERE class_name='$link[4]'");
		$desc[1]="<i class='grey'>--CUBRID desc table is experimental</i>\nCREATE TABLE ".sql_kw($link[4]).'(';
		foreach ($SIDU['col'] as $i=>$v){
			$is_string=($v[1]=='CHAR' || $v[1]=='STRING');
			if ($v[1]=='STRING' && $v[4]<256) $v[1]='varchar';
			$desc[1] .="\n\t".sql_kw($v[0])." <i class='green'>$v[1]".($is_string && $v[4]<256 ? "($v[4])" : '').'</i>';
			if ($v[2]=='NULL') $desc[1] .=' NOT NULL';//funny as pg style
			if ($auto_inc==$v[0]) $desc[1] .=' AUTO_INCREMENT';
			if ($v[3]<>'') $desc[1] .=" <span class='blue'>DEFAULT</span> ".($is_string ? "'" : '').html8(str_replace("'","''",$v[3])).($is_string ? "'" : '');
			$desc[1] .=',';
		}
		$pufi=get_rows("SELECT * FROM db_index WHERE class_name='$link[4]'");
		$rows=get_rows("SELECT a.index_name,a.class_name,a.key_attr_name FROM db_index_key a,db_index b\nWHERE a.index_name=b.index_name AND b.class_name='$link[4]' and a.class_name=b.class_name ORDER BY a.key_order");
		foreach ($rows as $row) $pufi_col[$row['index_name']][]=$row['key_attr_name'];
		foreach ($pufi as $row){
			$col_list=implode(',',$pufi_col[$row['index_name']]);
			if ($row['is_primary_key']=='YES') $desc[1] .="\nCONSTRAINT <i class='green'>$row[index_name]</i> <b>PRIMARY KEY</b> ($col_list),";
			elseif ($row['is_foreign_key']=='NO') $desc[1] .="\nCONSTRAINT <i class='green'>$row[index_name]</i> <b>".($row['is_unique']=='YES' ? 'UNIQUE' : 'KEY')."</b> ($col_list),";
		}
		$cb_fk_act=array('CASCADE','RESTRICT','NO ACTION','SET NULL');
		$fks=cubrid_schema($SIDU['dbL'],CUBRID_SCH_IMPORTED_KEYS,$link[4]);
		foreach ($fks as $v) $desc[1] .="\nCONSTRAINT <i class='green'>$v[FK_NAME]</i> <b>FOREIGN KEY</b> ($v[FKCOLUMN_NAME]) <b>REFERENCES</b> ".sql_kw($v['PKTABLE_NAME'])."($v[PKCOLUMN_NAME]) ON DELETE {$cb_fk_act[$v[DELETE_RULE]]} ON UPDATE {$cb_fk_act[$v[UPDATE_RULE]]},";
		$desc[1]=substr($desc[1],0,-1)."\n);";
	}elseif ($is_my || $is_sl){
		if ($is_my){
			$desc=get_row('SHOW CREATE TABLE '.sql_kw($link[1]).'.'.sql_kw($link[4]));
			$arr=explode("\n",$desc[1]);
			foreach ($arr as $i=>$line) $arr[$i]=my_clean_keyw($line);
			$desc[1]=implode("\n",$arr);
		}else $desc=get_row("SELECT name,sql FROM sqlite_master WHERE name=tbl_name AND name='$link[4]' LIMIT 1");
		$typ=array('char','varchar','text','blob','tinyint','smallint','int','bigint','enum','unsigned','set','float','double','real','timestamp','datetime','date','time','mediumtext','longblob','longtext');
		foreach ($typ as $t) $mytran[" $t"]=" <span class='green'>$t</span>";
		$mytran[' DEFAULT NULL,']=',';
		$mytran[' DEFAULT NULL']=' ';
		$mytran[' DEFAULT ']=" <span class='blue'>DEFAULT</span> ";
		$mytran[' default ']=" <span class='blue'>default</span> ";
		$mytran[' CHARACTER SET ']=" <span class='red'>CHARACTER SET</span> ";
		$mytran['CURRENT_TIMESTAMP']="now()";//those need re-do it is not safe!!!
		$mytran[' decimal(']=" <span class='red'>numeric</span>(";
		$mytran[' int(11)']=" <span class='green'>int</span>";
		$mytran[' int(10) unsigned']=" <span class='green'>int unsigned</span>";
		$mytran[' smallint(6)']=" <span class='green'>smallint</span>";
		$mytran[' bigint(20)']=" <span class='green'>bigint</span>";
		$typ=array('PRIMARY KEY','UNIQUE KEY','KEY');
		foreach ($typ as $t) $mytran[$t]="<b>$t</b>";
		$desc[1]=strtr(html8($desc[1]),$mytran);
		if ($is_sl){
			$rows=get_row("SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name='$link[4]' AND sql IS NOT NULL",1);
			foreach ($rows as $row) $idx .="<i class='green'>$row;</i>\n";
		}
	}elseif ($is_pg){
		$tran=array("'"=>"''");//pg9.0- :,"\\"=>"\\\\"
		if ($SIDU['tabinfo'][3]) $comm="\n<b class='blue'>COMMENT ON TABLE ".sql_kw($link[4])." IS '".strtr($SIDU['tabinfo'][3],$tran)."';</b>";
		$desc[1]="<i class='grey'>--PG desc table is experimental--oid={$SIDU[tabinfo][0]}</i>\nCREATE TABLE ".sql_kw($link[4]).'(';
		foreach ($SIDU['col'] as $i=>$v){
			$desc[1] .="\n\t".sql_kw($v[0])." <i class='green'>$v[1]</i>";
			if ($v[2]=='t') $desc[1] .=' NOT NULL';
			if ($v[3]<>''){
				if (!is_numeric($v[3]) && substr($v[3],0,8)<>'nextval(' && $v[3]<>'now()' && $v[3]<>'true' && $v[3]<>'false') $v[3]="'".strtr($v[3],$tran)."'";
				$desc[1] .=" <span class='blue'>DEFAULT</span> ".html8($v[3]);
			}elseif ($v[11]) $desc[1] .=" <span class='blue'>DEFAULT</span> $v[11]";
			$desc[1] .=",";
			if ($v[6]<>'') $comm .="\n<i class='blue'>COMMENT ON COLUMN ".sql_kw($link[4]).'.'.sql_kw($v[0])." IS '".strtr($v[6],$tran)."';</i>";
		}
		$fkmatch=array('f'=>'FULL','p'=>'PARTIAL','u'=>'SIMPLE');
		$fkact=array('a'=>'NO ACTION','r'=>'RESTRICT','c'=>'CASCADE','n'=>'SET NULL','d'=>'SET DEFAULT');
		$rows=get_rows("SELECT *,pg_get_constraintdef(oid,TRUE) AS kstr FROM pg_constraint\nWHERE conrelid={$SIDU[tabinfo][0]} AND connamespace={$SIDU[tabinfo][1]}",'ASSOC');
		foreach ($rows as $row){
			$desc[1] .="\nCONSTRAINT <i class='green'>".sql_kw($row['conname'])."</i> <b>$row[kstr]</b>";
			if ($row['contype']=='f') $desc[1] .=" MATCH {$fkmatch[$row[confmatchtype]]}\n\tON UPDATE {$fkact[$row[confupdtype]]} ON DELETE {$fkact[$row[confdeltype]]},";
			else $desc[1] .=',';
		}
		$desc[1]=substr($desc[1],0,-1)."\n) WITH (OIDS=".($SIDU['tabinfo'][2]=='t' ? 'TRUE' : 'FALSE').');';
		$row=get_row("SELECT pg_get_indexdef(indexrelid) FROM pg_index\nWHERE indrelid={$SIDU[tabinfo][0]} AND indisprimary='f'",1);
		foreach ($row as $r) $idx .="<i class='green'>$r;</i>\n";
	}
	echo "<pre>\n\n$desc[1]$comm\n\n$idx\n********** Grants not ready in this version **********\n\n********** SQL HELP **********\n";
	$alt="ALTER TABLE <i class='green'>$SIDU[4]</i>";
	$addC='<b>ADD COLUMN</b>';
	$altC='<b>ALTER COLUMN</b>';
	$pk='PRIMARY KEY';
	$addI='CREATE INDEX';
	$altI='ALTER INDEX distributors';
	$delC='<b>DROP COLUMN</b>';
	$rn='RENAME';
	if ($is_my) echo "
<b>$rn</b> TABLE $SIDU[4] <b>TO</b> new_name
$alt $addC a INT(2),$addC b INT(3),$delC c
$alt <b>CHANGE</b> a newname VARCHAR(10) NOT NULL DEFAULT '' <b>AFTER</b> c
$alt <b>ADD $pk</b> (b)
$alt <b>DROP $pk</b>

$alt <b>ADD UNIQUE</b> uk (c)
$alt <b>DROP KEY</b> uk
$alt <b>ADD INDEX</b> idx (a,b)
$alt <b>DROP KEY</b> idx";
	elseif ($is_pg) echo "
$alt <b>$rn COLUMN</b> col TO new_col
$alt <b>$rn TO</b> new_name
$alt SET SCHEMA new_schema

$alt $addC col type
$alt $delC col [ RESTRICT | CASCADE ]
$alt $altC col TYPE type
$alt $altC col <b>SET DEFAULT</b> expression
$alt $altC col <b>DROP DEFAULT</b>
$alt $altC col { SET | DROP } <b>NOT NULL</b>
$alt <b>DROP CONSTRAINT</b> constraint_name [ RESTRICT | CASCADE ]
$alt SET WITH OIDS
$alt SET WITHOUT OIDS
$alt OWNER TO new_owner
$alt SET TABLESPACE new_tablespace

CREATE UNIQUE INDEX idx ON $SIDU[4] (col1,col2);
$addI lower_title_idx ON $SIDU[4] ((lower(title)));
$addI title_idx_nulls_low ON $SIDU[4] (title NULLS FIRST);
$addI code_idx ON $SIDU[4] (code) TABLESPACE indexspace;
DROP INDEX idx;

$altI $rn TO suppliers;
$altI SET TABLESPACE fasttablespace;

SELECT setval('$SIDU[4]_id_seq',(SELECT MAX(id) FROM $SIDU[4])+1);";
	elseif ($is_cb) echo "
<b>$rn</b> TABLE $SIDU[4] <b>TO</b> new_name
$alt $addC a INT [FIRST|AFTER colB]
$alt $altC a <b>SET DEFAULT</b> 'value'
$alt <b>$rn COLUMN</b> a <b>TO</b> b
$alt $delC a,b

$alt <b>ADD CONSTRAINT $pk</b> (b)
$alt <b>DROP $pk</b>
\n$alt <b>ADD CONSTRAINT UNIQUE</b> uk (c)
$alt <b>DROP CONSTRAINT</b> uk
\n$alt <b>ADD INDEX</b> idx (c)
$alt <b>DROP INDEX</b> idx
\n$alt <b>ADD CONSTRAINT</b> fk <b>FOREIGN KEY</b> (c) <b>REFERENCES</b> tab2(c)
$alt <b>DROP FOREIGN KEY</b> fk";
	elseif ($is_sl) echo "
$alt <b>$rn TO</b> new_name
$alt $addC col type";
	echo '</pre>';
}
function my_clean_keyw($txt){
	$arr=explode('`',$txt,3);
	if ($arr[1]=='') return $txt;
	return $arr[0].sql_kw($arr[1]).my_clean_keyw($arr[2]);
}
function main_desc_view($link,$oid,$is_my,$is_pg,$is_cb,$is_sl){
	if ($is_sl){
		$row=get_row("SELECT sql FROM sqlite_master WHERE type='view' AND name='$link[4]'");
		return print("<p class='web green'>$row[0]</p>");
	}
	if ($is_my){
		$def=get_var("SELECT VIEW_DEFINITION FROM information_schema.VIEWS\nWHERE TABLE_SCHEMA='$link[1]' AND TABLE_NAME='$link[4]'");
		$def=trim(str_replace('/* ALGORITHM=UNDEFINED */','',$def));
	}elseif ($is_pg) $def=get_var("SELECT pg_get_viewdef($oid)");
	elseif ($is_cb) $def=get_var("SELECT vclass_def FROM db_vclass WHERE vclass_name='$link[4]'");
	echo "<p class='web'><br><span class='green'>CREATE VIEW ".sql_kw($link[4])." AS</span><br>$def</p>";
}
function init_tab(&$SIDU,$link,$conn){
	$no_sidu_fk=&$_SESSION["no_sidu_fk_$SIDU[0]"]["$SIDU[1]_$SIDU[2]"];//possible bug of ses_name but not likely
	if (!$no_sidu_fk && !get_var('select 1 from sidu_fk limit 1')) $no_sidu_fk=1;
	if (!$no_sidu_fk){
		$rows=get_rows("SELECT col,ref_tab,ref_cols,where_sort FROM sidu_fk WHERE tab='$SIDU[4]'");
		foreach ($rows as $row) $fk[$row['col']]="$row[ref_tab]#$row[ref_cols]#$row[where_sort]";
	}
	$tabGood=sql_kw($SIDU[4]);
	is_eng($conn[1],$is_my,$is_pg,$is_cb,$is_sl);
	if ($is_my) $tabGood=sql_kw($SIDU[1]).".$tabGood";
	elseif ($is_pg) $tabGood=sql_kw($SIDU[2]).".$tabGood";
	if ($is_my){
//0name 1type 2null 3defa 4maxchar 5extra 6comm 7pk 8align 9pos 10pg_type 11pg_defa 12fk
		$rows=get_rows("SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,\nifnull(CHARACTER_MAXIMUM_LENGTH,NUMERIC_PRECISION),EXTRA,COLUMN_COMMENT,\nCOLUMN_KEY,if(NUMERIC_PRECISION IS NULL,'','i'),ORDINAL_POSITION\nFROM information_schema.COLUMNS\nWHERE TABLE_SCHEMA='$SIDU[1]' AND TABLE_NAME='$SIDU[4]'\nORDER BY ORDINAL_POSITION",'NUM');
		foreach ($rows as $row){
			$row[12]=$fk[$row[0]];
			$col[]=$row;
			if ($row[7]=='PRI') $SIDU['pk'][]=$row[9]-1;
		}
	}elseif ($is_sl){
		$rows=get_rows("pragma table_info($tabGood)");
		foreach ($rows as $row){
			$row[12]=$fk[$row[0]];
			if ($row['type']=='integer') $row['type']='int';
			if ($row['pk']) $SIDU['pk'][]=$row['cid'];
			$col[]=array($row['name'],$row['type'],($row['notnull'] ? 'NULL' : 'YES'),$row['dflt_value'],'','','',($row['pk'] ? 'PRI' : ''),'',$row['cid']+1,12=>$row[12]);
		}
	}elseif ($is_pg){
		$tab=get_row("SELECT a.oid,a.relnamespace,a.relhasoids,obj_description(a.oid,'pg_class')\nFROM pg_class a,pg_namespace b WHERE a.relkind='$link[3]' AND a.relnamespace=b.oid\nAND a.relname='$link[4]' AND b.nspname='$link[2]'",0,'NUM');
		$SIDU['tabinfo']=$tab;
		$defa=get_row("SELECT adnum,adsrc FROM pg_attrdef WHERE adrelid=$tab[0]",2);
		$defaRaw=$defa;
		foreach ($defa as $k=>$v){
			if (substr($v,0,9)<>"nextval('"){
				$rowx=explode('::',$v);
				if (substr($rowx[0],0,1)=="'" && substr($rowx[0],-1)=="'") $rowx[0]=substr($rowx[0],1,-1);
				$rowx[0]=str_replace("''","'",$rowx[0]);
				$defa[$k]=$rowx[0];
			}
		}
		if ($SIDU['page']['oid'] && $tab[2]=='t'){
			$col[0]=array('oid','oid','t');
			$hasOID='oid,';
		}else $colX=-1;
		$typ=get_row('SELECT oid,typname FROM pg_type',2);
		$rows=get_rows("SELECT attname,atttypid,attnotnull,atthasdef,\nCASE attlen WHEN -1 THEN atttypmod ELSE attlen END,\n'','','','',attnum,format_type(atttypid,atttypmod) FROM pg_attribute\nWHERE attrelid=$tab[0] AND attnum>0 AND attisdropped=FALSE ORDER BY attnum",'NUM');
		foreach ($rows as $row){
			$row[3]=($row[3]=='t' ? $defa[$row[9]] : '');
			$row[1]=$typ[$row[1]];
			if ($row[1]=='numeric') $row[1]=$row[10];
			elseif ($row[1]=='int2') $row[1]='smallint';
			elseif ($row[1]=='int4') $row[1]='int';
			elseif ($row[1]=='int8') $row[1]='bigint';
			elseif ($row[1]=='bpchar') $row[1]='char';
			if ($row[4]>4 && ($row[1]=='varchar' || $row[1]=='char')) $row[1] .='('.($row[4]-4).')';
			if (substr($row[3],0,9)=="nextval('") $row[1]=($row[1]=='int' ? 'serial' : 'bigserial');
			$row[11]=$defaRaw[$row[9]];//only used for default ''::varchar etc
			$row[12]=$fk[$row[0]];
			$col[]=$row;
		}
		$rows=get_rows("SELECT conkey,contype,pg_get_constraintdef(oid,TRUE) AS kstr FROM pg_constraint\nWHERE connamespace=$tab[1] AND conrelid=$tab[0]",'NUM');
		foreach ($rows as $row){//pk uk fk
			$pucf=explode(',',substr($row[0],1,-1));
			foreach ($pucf as $i=>$v){
				$colID=$v+$colX;
				$col[$colID][7]=($col[$colID][7] ? $col[$colID][7].",$row[1]" : $row[1]);
				if ($row[1]=='f' && !$col[$colID][12]){
					$pks=explode(' REFERENCES ',substr($row[2],0,-1),2);
					$pksT=explode('(',$pks[1],2);
					$pksC=explode(',',$pksT[1]);
					$col[$colID][12]="$pksT[0]#".trim($pksC[$i]);
				}
				if ($row[1]=='p') $SIDU['pk'][]=$colID;
			}
		}
	}elseif ($is_cb){
		$auto_inc=get_var("SELECT att_name FROM db_serial WHERE class_name='$link[4]'");
		$rows=get_rows("SELECT attr_name,data_type,prec,scale,is_nullable,default_value,def_order FROM db_attribute WHERE class_name='$link[4]' ORDER BY def_order");
		foreach ($rows as $row){
			$row[12]=$fk[$row[0]];
			$row[3]=str_replace("''","'",$row['default_value']);
			if ($row['data_type']=='INTEGER') $row['data_type']='int';
			elseif ($row['data_type']=='STRING' || $row['data_type']=='CHAR'){
				$row[4]=$row['prec'];
				if (substr($row[3],0,1)=="'" && substr($row[3],-1)=="'") $row[3]=substr($row[3],1,-1);
			}
			if ($auto_inc==$row['attr_name']) $row[5]='auto_increment';
			$col[]=array($row['attr_name'],$row['data_type'],($row['is_nullable']=='YES' ? 'YES' : 'NULL'),$row[3],$row[4],$row[5],9=>$row['def_order']+1,12=>$row[12]);
		}
		$rowsK=get_rows("SELECT is_unique,is_primary_key,is_foreign_key,c.def_order,key_order\nFROM db_index a,db_index_key b,db_attribute c\nWHERE a.class_name='$link[4]' AND a.index_name=b.index_name \nand b.key_attr_name=c.attr_name and a.class_name=c.class_name\nORDER BY a.index_name,b.key_order");
		foreach ($rowsK as $rowK){
			$id=$rowK['def_order'];
			unset($pufi);
			if ($rowK['is_primary_key']=='YES'){
				$pufi[]='p';
				$SIDU['pk'][]=$id;
			}elseif ($rowK['is_unique']=='YES') $pufi[]='u';
			if ($rowK['is_foreign_key']=='YES') $pufi[]='f';
			if ($rowK['is_primary_key']=='NO' && $rowK['is_unique']=='NO' && $rowK['is_foreign_key']=='NO') $pufi[]='i';
			$col[$id][7]=implode(',',$pufi);
		}
	}
	$SIDU[10]=get_var("SELECT COUNT(*) FROM $tabGood");
	$SIDU['col']=$col;
	if ($_GET['desc']){//desc
		if ($is_pg){//set col comm
			$rows=get_rows("SELECT objsubid,description FROM pg_description\nWHERE objoid=$tab[0] AND objsubid>0");
			foreach ($rows as $row) $SIDU['col'][$row[0]+$colX][6]=$row[1];
		}
		return;
	}
	$MODE=explode('.',$_COOKIE['SIDUMODE']);//0lang 1gridmode 2pgsize=sidu9
	$SIDU['gridMode']=$MODE[1];
	if ($MODE[2]<-1 || !$MODE[2]) $MODE[2]=15;
	$SIDU[9]=$MODE[2];
	$SIDU[8]=ceil($_POST['sidu8']);
	if ($SIDU[8]<0) $SIDU[8]=0;
	$SIDU[7]=$_POST['sidu7'];//sort
	if (substr($SIDU[7],0,4)=='del:'){
		$SIDU[7]=substr($SIDU[7],4);
		$SIDU[7]=$SIDU['col'][$SIDU[7]][0];
		if ($SIDU[5]==$SIDU[7] || $SIDU[5]=="$SIDU[7] desc") $SIDU[5]=$SIDU[7]='';
		elseif ($SIDU[6]==$SIDU[7] || $SIDU[6]=="$SIDU[7] desc") $SIDU[6]=$SIDU[7]='';
		else $SIDU[7]='';
	}else $SIDU[7]=$SIDU['col'][$SIDU[7]][0];
	$SIDU['f']=strip(isset($_GET['f']) ? $_GET['f'] : $_POST['f'],1);
	sidu_sort($SIDU[5],$SIDU[6],$SIDU[7],$SIDU['page']['sortData']);
	$strSort=($SIDU[5] ? " ORDER BY $SIDU[5]".($SIDU[6] ? ",$SIDU[6]" : '') : ($SIDU[6] ? " ORDER BY $SIDU[6]" : ''));
	foreach ($SIDU['f'] as $k=>$v){
		if ($k==='sql' && $v) $whereSQL=' AND '.$SIDU['f']['sql'];
		elseif ($v<>'') $where .=' AND '.$SIDU['col'][$k][0]." $v";
	}
	$where .=$whereSQL;
	if (!$strSort && (!$where || !stripos($where,' order by '))) $strSort=' ORDER BY 1 DESC';
	if ($where){
		$where=' WHERE '.substr($where,5);
		$SIDU[10]=get_var('SELECT COUNT(*) FROM '.$tabGood.$where);
	}
	if ($_POST['cmd']=='p1') $SIDU[8]=0;
	elseif ($SIDU[9]<>-1){
		if ($_POST['cmd']=='pback') $SIDU[8] -=$SIDU[9];
		elseif ($_POST['cmd']=='pnext') $SIDU[8] +=$SIDU[9];
		elseif ($_POST['cmd']=='plast') $SIDU[8]=$SIDU[10]-$SIDU[9];
		if ($SIDU[8]>$SIDU[10]) $SIDU[8]=$SIDU[10]-$SIDU[9];
		if ($SIDU[8]<0) $SIDU[8]=0;
	}
	if ($SIDU[9]<>-1 && $SIDU['eng']=='cb') $limit=' LIMIT '.($SIDU[8] ? "$SIDU[8]," : '').$SIDU[9];
	elseif ($SIDU[9]<>-1) $limit=" LIMIT $SIDU[9]".($SIDU[8] ? " OFFSET $SIDU[8]" : '');
	$SIDU['data']=get_rows("SELECT $hasOID* FROM $tabGood$where$strSort$limit",'NUM');
	if (!$is_my) init_pg_col_align($SIDU['data'],$SIDU['col']);
	if ($_POST['hideCol']<>'') $_POST['g'][$_POST['hideCol']]=-1;
	init_col_width($SIDU);
}
function save_data($SIDU,$eng,$cmd){
	foreach ($_POST as $k=>$v){if (substr($k,0,5)=='data_' || substr($k,0,10)=='cbox_data_'){
		$arr=explode('_',$k,3);
		$data[$arr[0]][$arr[1]][$arr[2]]=$v;
	}}
	foreach ($SIDU['col'] as $i=>$v) $col[]=sql_kw($v[0]);
	is_eng($eng,$is_my,$is_pg,$is_cb,$is_sl);
	$tab=($is_pg ? sql_kw($SIDU[2]).'.' : '').sql_kw($SIDU[4]);
	$addSlash=sidu_slash($eng);
	foreach ($data['cbox']['data'] as $i=>$v){//only need i
		unset($COL); unset($VAL); $where='';
		$is_new=substr($i,0,3)==='new';
		if (!$is_new || $cmd=='data_del'){
			foreach ($_POST['pkV'][$i] as $j=>$v){
				$colname=sql_kw($col[$j]);
				$v8=str_replace("'","''",$v);
				if ($addSlash) $v8=str_replace('\\','\\\\',$v8);
				$where .=' and '.(strtoupper($v)==='NULL' ? "$colname IS NULL" : (substr($v,0,11)=='::md5BLOG::' && strlen($v)==43 && is_blob($SIDU['col'][$j]) ? "md5($colname)='".substr($v,11)."'" : "$colname='$v8'"));
			}
			$where='WHERE '.substr($where,5);
		}
		if ($cmd=='data_save'){
			foreach ($data['data'][$i] as $j=>$v){
				$v=trim($v);
				if (!$is_new || $v<>'' || ((!$is_pg || substr($SIDU['col'][$j][3],0,8)<>'nextval(') && $SIDU['col'][$j][5]<>'auto_increment')){
					if ($is_pg && $SIDU['page']['dataEasy']){
						if ($SIDU['col'][$j][1]=='smallint' || $SIDU['col'][$j][1]=='int') $v=ceil($v);
						elseif ((substr($SIDU['col'][$j][1],0,8)=='varchar(' || substr($SIDU['col'][$j][1],0,5)=='char(') && strtoupper($v)<>'NULL') $v=trim(substr($v,0,$SIDU['col'][$j][4]-4));
					}
					$COL[]=$col[$j]; $VAL[]=$v;
				}//above logic too complex - even myself forgot :D
			}
			if ($is_new && isset($COL)) $res=tm('insert',$tab,$COL,$VAL);
			elseif (!$is_new) $res=tm('update',$tab,$COL,$VAL,$where);
		}elseif ($cmd=='data_del') $res=tm('delete',$tab,null,null,$where);
		$errno=sidu_err(1);
		if ($errno) $err .=($is_pg ? $errno : "Err $errno")."\\n";
		elseif ($cmd=='data_save') echo html_js("parent.document.dataTab.cbox_data_$i.checked=''");
		elseif ($cmd=='data_del') echo html_js("parent.document.getElementById('tr_$i').style.display='none'");
	}
	if ($err) echo html_js("alert('".strtr($err,array("'"=>"\'","\""=>"\\\"","\n"=>"\\n"))."')");
}
function set_cook_tab(){
	global $SIDU;
	$MODE=explode('.',$_COOKIE['SIDUMODE']);//0lang 1gridMode 2pgsize ...
	if (isset($_POST['gridMode'])) $MODE[1]=$_POST['gridMode'];
	if (isset($_POST['sidu9'])) $MODE[2]=ceil($_POST['sidu9']);
	if (!$MODE[0]) $MODE[0]='en';
	if (!$MODE[1]) $MODE[1]=0;
	$cook=implode(".",$MODE);
	if ($cook<>$_COOKIE['SIDUMODE']){
		$_COOKIE['SIDUMODE']=$cook;
		setcookie('SIDUMODE',$cook,time()+311040000);
	}
	$cook=$SIDU['cook'][$SIDU[0]];
	if ($SIDU[1]==$cook[1] && $SIDU[2]==$cook[2] && $SIDU[3]==$cook[3] && $SIDU[4]==$cook[4]) return;
	update_sidu_cook(array($SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4]));
}
?>
