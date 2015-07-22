$(document).ready(function(){
	$('#p-').click(function(){resizeMenu(resizeFrm('menu'));getMenuTree('-',$('#connCur').text(),1)});
	$('#refW').click(function(){$('#t-').addClass('load');getMenuTree('-',$('#connCur').html(),1)});
	$('#resizeSQL').click(function(){resizeSQLs(resizeFrm('sqls'))});
	$('#toolV').click(function(){
		$('body').toggleClass('toolV');
		resizeSQLs($('#sqlsCur').html());
		resizeMenu(0);/*0 is better than menuCur? maybe*/
	});
	$('#menu a').live('click',function(){$('#ifr').attr('src',this.href);return false});
	$('.show').click(function(){$('#'+this.id+'SH').toggle()});
	$('.Hpop').live('click',function(){$('.pop').hide();$(this).next().toggle()});
	$('.pop').prepend("<b class='xpop' title='Close'></b>");
	$('.xpop').live('click',function(){$(this).parent().hide()});
	$('.hideP').click(function(){$(this).parent().hide()});
	$('#checkAll').click(function(){$(this).parent().parent().parent().find(':checkbox').attr('checked',this.checked)});
	$('#run_a,#run_r,#run_m').click(function(){sidu_sql(this.id.substr(4))});
	$('#fsqlSub').click(function(){$('#SQLoadSH').hide()});
	$('#encPwd').click(function(){if ($('#penc').attr('checked')){
		$('#cmd').val($(this).attr('value'));
		$.get('conn.php?cmd=get_login_salt').success(function(salt){
			$('#sidu_login_salt').html(salt);
			$('#pwd').val(enc65($('#pwd').val()));
			$('#connf').submit();
		});
		return false;
	}});
	$('#addRow').click(function(event){$('#addRowAfter').after('<tr>'+addRow()+'</tr>')});
	$('#impCMD').click(function(){$('#waitHide').hide();$('#wait').show()});
	$('.sendFm').click(function(){sendFm(this.id)});
	$('#toolEye').click(function(){$('.colTool').toggle()});
	$('.xwin').click(function(){xwin(this.href);return false});
	$('.colTool td').not(':first-child').html("<b title='"+$('.colTool td:first-child').attr('title')+"' class='toolS xSort'></b> <b class='toolS delSort colHide'></b>");
	$('.colHide').click(function(){
		idx=$(this).parent().prevAll().length;
		x='tr td:nth-child('+(idx+1)+')';
		$('#g'+(idx-1)).val(-1);
		tab=$(this).parents('table');
		tab.find(x).addClass('hide');
		tab.prev().find(x).show();
	});
	$('.colShow td').click(function(){
		idx=$(this).prevAll().length;
		x='tr td:nth-child('+(idx+1)+')';
		$('#g'+(idx-1)).val(0);
		$(this).parents('table').next().find(x).removeClass('hide');
		$(this).hide();
	});
	$('.xSort,.colSort').click(function(){
		idx=$(this).parents("td").prevAll().length-1;
		submitForm('sidu7',($(this).is('.xSort') ? 'del:' : '')+idx);
	});
	$('#dataTable td:not(.cbox) *').live('change',function(){$(this).closest('tr').find('td.cbox input').attr('checked','checked')});
	$('.updateP').change(function(){$(this).parent().parent().children('.Hpop').val($(this).val().substr(0,200))});
});
function enc65(str){
	var key=$('#sidu_login_salt').text().split('|');
	var len=str.length;
	while (str.length<32) str+=' ';
	len=str.length;
	var res='';
	for (var i=0;i<len;i++) res +=pad0(str.charCodeAt(i)+parseInt(key[i%32]));
	len=res.length;
	if (len%6>0) res +='032';
	var res2='';
	for (i=0;i<len;i +=6) res2 +=base65(res.substr(i,6),key[32]);
	var k0=parseInt(key[0]),k5=parseInt(key[5]),k9=parseInt(key[9]);
	len=res2.length;
	if (k0<0) k0+=len;
	if (k5<0) k5+=len;
	if (k9<0) k9+=len;
	res2=res2.substr(k0)+res2.substr(0,k0);
	res2=res2.substr(k5)+res2.substr(0,k5);
	res2=res2.substr(k9)+res2.substr(0,k9);
	return res2;
}
function pad0(s){
	s=s.toString();
	if (s.length<3) return pad0('0'+s);
	return s;
}
function base65(int,b65){
	var b=int%(65*65);
	return b65.charAt(Math.floor(int/65/65))+b65.charAt(Math.floor(b/65))+b65.charAt(b%65);
}
function resizeSQLs(h){
	if (h==0) h2=0;
	else{
		$('#sqls>div').css('height',h+'px');
		h2=parseInt(h)+5;
	}
	if ($('body').hasClass('toolV')) h2-=30;
	else if ($('html').hasClass('LargeBtn')) h2+=24;//largebtn fix later
	$('#main').css('top',(30+h2)+'px');
}
function resizeMenu(w){
	if (w==0){
		w2=0; $('#menu').hide();
		if ($('body').hasClass('toolV')) w2-=3;
	}else{
		w2=parseInt(w)+3; $('#menu').show().css('width',w+'px');
	}
	if ($('body').hasClass('toolV')) w2+=($('html').hasClass('LargeBtn') ? 56 : 28);//largebtn fix later
	$('#sqls').css('left',(w2+3)+'px');
	$('#main').css('left',(w2+3)+'px');
}
function showHideTree(id,last,conn){
	$('#t'+id).toggle();
	$('#p'+id).toggleClass('tr_'+last+' tr_open'+last);
	getMenuTree(id,conn);
}
function getMenuTree(id,conn,init){
	if ($('#t'+id).hasClass('load')){
		$.post('menu.php?id='+conn+(init==1 ? '&init=1' : '')).success(function(data){
			pos=data.indexOf('***');
			if (pos>-1){
				num='<i>('+data.substr(pos+3)+')</i>';
				data=data.substr(0,pos);
			}
			if (init==1) num='';
			$('#t'+id).html(data).before(num);
		});
		$('#t'+id).removeClass('load');
	}
}
function resizeFrm(frm){
	var cur=$('#'+frm+'Cur');
	arr=$('#'+frm+'Size').html().split('.');
	n=0; w=cur.html(); len=arr.length;
	for (i=0;i<len;i++){if (w==arr[i]){
		n=i+1;
		if (n==len) n=0;
	}}
	w=arr[n]; cur.html(w);
	return w;
}
function id(x){return document.getElementById(x)}
function val(x,v){
	if (v==null) return id(x).value;
	id(x).value=v;
}
function getSelectedText(x){//sqls.php
	if (x.setSelectionRange) return x.value.substring(x.selectionStart,x.selectionEnd);//Mozilla
	else if (document.selection) return document.selection.createRange().text;//IE
}
function replaceTxt(text,textarea){
	if (typeof(textarea.selectionStart)!='undefined'){
		var begin=textarea.value.substr(0, textarea.selectionStart);
		var end=textarea.value.substr(textarea.selectionEnd);
		var scrollPos=textarea.scrollTop;
		textarea.value=begin+text+end;
		if (textarea.setSelectionRange){
			textarea.focus();
			textarea.setSelectionRange(begin.length + text.length, begin.length + text.length);
		}
		textarea.scrollTop = scrollPos;
	}else{//Just put it on the end.
		textarea.value += text;
		textarea.focus(textarea.value.length-1);
	}
}
function sidu_sql(mode){//sqls.php
	var sql;
	if (mode=='r' || mode=='m') sql=getSelectedText(id('sqltxt'));
	if (!sql || mode=='a') sql=val('sqltxt');
	if (sql){
		$('#ifr').contents().find('#sqlwait').show();
		val('sqlcur',sql);
		if (mode=='m') val('sqlmore',1);
		document.sqlrun.submit();
		val('sqlmore',0); id('sqltxt').focus();
	}
}
function xwin(URL,w,h){
	var pop; if(w==null) w=700; if(h==null) h=600;
	if (pop!=null && !pop.closed) pop.close();
	var L=(screen.width-w)/2; T=(screen.height-h)/2;
	pop=window.open(URL,'sidu','scrollbars=yes,resizable=yes,left='+L+',top='+T+',width='+w+',height='+h);
	pop.focus();
}
function dbexp(id,o){
	var tabs='';
	var len=document.dataTab[o].length;
	if (len==null) return xwin('exp.php?id='+id+'&tab='+document.dataTab[o].value);
	for (i=0;i<len;i++){
		if (document.dataTab[o][i].checked) tabs=tabs+','+document.dataTab[o][i].value;
	}
	xwin('exp.php?id='+id+'&tab='+tabs.substr(1));
}
function submitForm(id1,v1){//tab.php and sql.php
	eval('document.dataTab.'+id1).value=v1;
	if (id1=='cmd' && (v1=='data_save' || v1=='data_del')) document.dataTab.target='hiddenfr';
	document.dataTab.sidu8.value=(id1=='sidu7' ? 0 : val('sidu8'));
	document.dataTab.sidu9.value=val('sidu9');
	document.dataTab.submit();
	document.dataTab.target='ifr';
	document.dataTab.cmd.value='';
}
function sendFm(c){val('cmd',c);$('#dataTab').submit()}
