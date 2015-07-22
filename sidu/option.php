<?php
include 'inc.page.php';
$SIDU['page']['nav']='defa';
@save_data($opt,$cmd);
@uppe();
@main();
@down();

function save_data($opt,$cmd){
	global $SIDU;
	if (!$cmd) return;
	$opt['pgSize']=ceil($opt['pgSize']);
	if ($opt['pgSize']<-1 || !$opt['pgSize']) $opt['pgSize']=15;
	$opt['nav']='defa';
	$SIDU['page']=$opt;
	$cook=explode('.',$_COOKIE['SIDUMODE']);
	$cook="$opt[lang].$cook[1].$opt[pgSize].$opt[tree].$opt[sortObj].$opt[sortData].$opt[menuTextSQL].$opt[menuText].$opt[his].$opt[hisErr].$opt[hisSQL].$opt[hisData].$opt[dataEasy].$opt[oid].$opt[btn]";
	//0lang.1gridMode.2pgSize.3tree.4sortObj.5sortData.6menuTextSQL.7menuText.8his.9hisErr.10hisSQL.11hisData.12dataEasy(pg).13oid(pg)
	setcookie('SIDUMODE',$cook,time()+311040000);
	$_COOKIE['SIDUMODE']=$cook;
}
function main(){
	global $SIDU;
	$opt=$SIDU['page'];
	$opt['pgSize']=ceil($opt['pgSize']);
	if ($opt['pgSize']<-1 || !$opt['pgSize']) $opt['pgSize']=15;
//	$arr_lang = array('cn'=>'中文','de'=>'Deutsch','en'=>'English','es'=>'Espanol','fr'=>'Francais','it'=>'Italiano');
	$arr_lang=array('cn'=>'中文','en'=>'English');
	echo "<div class='web'><h1 class='dot'>",lang(2700),'</h1>';
	if ($err) echo "<p class='err'>$err</p>";
	echo "<form name='myform' action='option.php?id=$SIDU[0]' method='post'><table>
<tr class='bg'><td>",lang(2701),':</td><td>',html_form('select','opt[lang]',$opt['lang'],0,$arr_lang),"</td></tr>
<tr class='bg'><td>",lang(2702),':</td><td>',html_form('text','opt[pgSize]',$opt['pgSize'],40,3),' ',lang(2703)."</td></tr>
<tr class='bg'><td>",lang(2704),':</td><td>',html_form('text','opt[tree]',$opt['tree'],40,1)," eg. _ 0...9</td></tr>
<tr class='bg'><td>",lang(2705),':</td><td>'.html_form('radio','opt[sortObj]',$opt['sortObj'],'',array(1=>lang(2706),lang(2707))),"</td></tr>
<tr class='bg'><td>",lang(2708),':</td><td>'.html_form('radio','opt[sortData]',$opt['sortData'],'',array(1=>lang(2706),lang(2707))),"</td></tr>
<tr><td class='grey'>",lang(2709),':</td><td>'.html_form('radio','opt[menuTextSQL]',$opt['menuTextSQL'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr><td class='grey'>",lang(2712),':</td><td>'.html_form('radio','opt[menuText]',$opt['menuText'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr class='bg'><td>",lang(2713),':</td><td>'.html_form('radio','opt[his]',$opt['his'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr class='bg'><td>",lang(2714),':</td><td>'.html_form('radio','opt[hisErr]',$opt['hisErr'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr class='bg'><td>",lang(2715),':</td><td>'.html_form('radio','opt[hisSQL]',$opt['hisSQL'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr class='bg'><td>",lang(2716),':</td><td>'.html_form('radio','opt[hisData]',$opt['hisData'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr><td><br>Postgres: ",lang(2717),':</td><td><br>'.html_form('radio','opt[dataEasy]',$opt['dataEasy'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr><td>Postgres: ",lang(2718),':</td><td>'.html_form('radio','opt[oid]',$opt['oid'],'',array(lang(2710),lang(2711))),"</td></tr>
<tr class='bg'><td>",lang(2720),':</td><td>'.html_form('checkbox','opt[btn]',$opt['btn'],'',array(1=>'')),"</td></tr>
<tr><td></td><td>",html_form('submit','cmd',lang(2719)),"</td></tr>
</table></form>
<br><h1>Howto customize framesizes of menu tree and SQL windows</h1>
<p>Edit file: index.php</p>
<p>find the following lines:</p>
<p class='green'>&lt;p id='menuCur'&gt;100&lt;/p&gt;&lt;p id='menuSize'&gt;0.100.200.300&lt;/p&gt;
<br>&lt;p id='sqlsCur'&gt;100&lt;/p&gt;&lt;p id='sqlsSize'&gt;0.100.200.300&lt;/p&gt;</p>
<p>....Cur = default size<br>....Size=0.100.200.300 means changing sizes at each click</p>
</div>";
}
?>
