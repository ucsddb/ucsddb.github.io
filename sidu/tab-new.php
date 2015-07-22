<?php
include 'inc.page.php';
@uppe();
@main($txt,$cmd);
@down();
	
function main($txt,$cmd){
	global $SIDU;
	is_eng($SIDU['eng'],$is_my,$is_pg,$is_cb,$is_sl);
	$txt=trim($txt);
	if ($txt && $cmd) $err=save_data($txt);
	if (!$txt) $txt='CREATE TABLE '.($is_my ? sql_kw($SIDU[1]).'.' : ($is_pg ? sql_kw($SIDU[2]).'.' : '')).'tabname'
		."(\ncolname int".($is_sl ? '' : ' NOT NULL DEFAULT 0')." PRIMARY KEY,\n\n)";
	echo "<div class='web'><p class='b dot'>",lang(4101)," <span class='red'>",($is_pg ? "$SIDU[1].$SIDU[2]" : $SIDU[1]),'</span></p>';
	if ($err) echo "<p class='err'>$err</p>";
	echo "<form action='tab-new.php?id=$SIDU[0],$SIDU[1],$SIDU[2],$SIDU[3],$SIDU[4],$SIDU[5],$SIDU[6]' method='post' name='myform'>
<table><tr><td valign='top'>",html_form('textarea','txt',$txt,420,320,"spellcheck='false' class='box'"),"
<br><br>",html_form('submit','cmd',lang(4102)),"</td><td valign='top' style='padding-left:10px'>";
	$str="9|0|smallint|smallint
0|1|32768|smallint unsigned NOT NULL DEFAULT 0
1|0|int|int
0|1|2,147,483,647|int unsigned NOT NULL DEFAULT 0
1|0|numeric|numeric(7,2)
0|1|(7,2)|numeric(7,2) unsigned NOT NULL DEFAULT 0.00
2|0|char|char(255)
0|1|255|char(255) NOT NULL DEFAULT \'\'
0|0|binary|char(255) binary NOT NULL DEFAULT \'\'
1|0|varchar|varchar(255)
0|1|255|varchar(255) NOT NULL DEFAULT \'\'
0|0|binary|varchar(255) binary NOT NULL DEFAULT \'\'
1|0|text|text
0|1|65535|text NOT NULL DEFAULT \'\'
2|0|date|date
0|1|YYYY-MM-DD|date NOT NULL DEFAULT \'0000-00-00\'
1|0|timestamp|timestamp
0|1|YmdHis|timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\'
0|0|now|timestamp NOT NULL DEFAULT now()
2|0|auto|auto_increment
0|1|!null|NOT NULL
0|0|PK|NOT NULL auto_increment PRIMARY KEY
1|0|PK|PRIMARY KEY
0|1|PK(a)|PRIMARY KEY (col1,col2)
0|0|UK|UNIQUE uk (col1,col2)
0|1|idx|INDEX idx (col1,col2)
2|0|MyISAM|ENGINE = MyISAM
0|1|InnoDB|ENGINE = InnoDB";
	if ($is_pg) $str=strtr($str,array(" DEFAULT \'0000-00-00\'"=>''," DEFAULT \'0000-00-00 00:00:00\'"=>'','auto|auto_increment'=>'serial|serial','NOT NULL auto_increment'=>'serial NOT NULL',"0|0|binary|char(255) binary NOT NULL DEFAULT \'\'"=>'',"0|0|binary|varchar(255) binary NOT NULL DEFAULT \'\'"=>'','MyISAM|ENGINE = MyISAM'=>'With OID|WITH (OIDS=TRUE)','0|1|InnoDB|ENGINE = InnoDB'=>'',' unsigned'=>'','PRIMARY KEY ('=>'CONSTRAINT pk PRIMARY KEY (','UNIQUE uk ('=>'CONSTRAINT uk UNIQUE (','idx|INDEX idx (col1,col2)'=>"FK|CONSTRAINT fk FOREIGN KEY (col) REFERENCES tab(pk) MATCH SIMPLE\\n\\tON UPDATE NO ACTION ON DELETE NO ACTION"));
	elseif ($is_sl) $str="9|0|int|int,\\n
0|1|PK|int PRIMARY KEY
1|0|text|text,\\n
1|0|real|real,\\n";
	elseif ($is_cb) $str=strtr($str,array('1|0|text|text'=>'','0|1|65535|text NOT NULL DEFAULT \\\'\\\''=>''," DEFAULT \'0000-00-00\'"=>''," DEFAULT \'0000-00-00 00:00:00\'"=>'',"0|0|binary|char(255) binary NOT NULL DEFAULT \'\'"=>'',"0|0|binary|varchar(255) binary NOT NULL DEFAULT \'\'"=>'','MyISAM|ENGINE = MyISAM'=>'','0|1|InnoDB|ENGINE = InnoDB'=>'',' unsigned'=>'','PRIMARY KEY ('=>'CONSTRAINT pk PRIMARY KEY (','UNIQUE uk ('=>'CONSTRAINT uk UNIQUE (','idx|INDEX idx (col1,col2)'=>"FK|CONSTRAINT fk FOREIGN KEY (col) REFERENCES tab(pk)"));
	$arr=explode("\n",$str);
	foreach ($arr as $v) main_add_txt(trim($v));
	if ($is_my) main_add_txt("2|0|enum(Y,N)|enum(\'Y\',\'N\') NOT NULL DEFAULT \'Y\',\\n");
	echo "</td></tr></table></form></div>";
}
function main_add_txt($str){
	if (!$str) return;
	$arr=explode('|',$str,4);
	if ($arr[0]=='0') echo ' ';
	elseif ($arr[0]=='1') echo '<br>';
	elseif ($arr[0]=='2') echo '<br><br>';
	echo "<a href='#'".($arr[1] ? " class='red'" : '')." onclick=\"replaceTxt(' $arr[3]".($arr[0]=="0" ? ",\\n" : '')."',document.myform.txt)\" title=\"".stripslashes(str_replace(",\\n",'',$arr[3]))."\">$arr[2]</a>";
}
function save_data($txt){
	$txt=trim($txt);
	if (substr($txt,-1)==')'){
		$txt=trim(substr($txt,0,-1));
		if (substr($txt,-1)==',') $txt=substr($txt,0,-1);
		$txt .=')';
	}
	$res=tm_his($txt);
	$err=sidu_err(1);
	if ($err) return $err;
	echo html_js('self.close()');
	exit;
}
?>
