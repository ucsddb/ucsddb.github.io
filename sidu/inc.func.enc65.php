<?php
//share func with inc.page.php:swap() SIDU_PK()
function enc65($str='',$sesIp=0){//any str ascii 10-254
	$key=benkey65($sesIp);
	$len=strlen($str);
	if ($len<32) $str .=str_repeat(' ',32-$len);
	for ($i=0;$i<$len || $i<32;$i++) $res .=str_pad(ord($str[$i])+$key[$i%32],3,0,STR_PAD_LEFT);
	$len=strlen($res);
	if ($len%6>0) $res .='032';
	for ($i=0;$i<$len;$i +=6) $res2 .=base65(substr($res,$i,6),$key[32]);
	$res2=substr($res2,$key[0]).substr($res2,0,$key[0]);
	$res2=substr($res2,$key[5]).substr($res2,0,$key[5]);
	$res2=substr($res2,$key[9]).substr($res2,0,$key[9]);
	return $res2;
}
function dec65($str='',$sesIp=0){
	$key=benkey65($sesIp);
	$str=substr($str,0-$key[9]).substr($str,0,0-$key[9]);
	$str=substr($str,0-$key[5]).substr($str,0,0-$key[5]);
	$str=substr($str,0-$key[0]).substr($str,0,0-$key[0]);
	$len=strlen($str);
	for ($i=0;$i<$len;$i +=3) $res .=str_pad(strpos($key[32],$str[$i])*65*65 + strpos($key[32],$str[$i+1])*65 + strpos($key[32],$str[$i+2]),6,0,STR_PAD_LEFT);
	if (substr($res,-3)=='032') $res=substr($res,0,-3);
	$len=strlen($res)/3;
	for ($i=0;$i<$len;$i++) $res2 .=chr(substr($res,$i*3,3)-$key[$i%32]);
	return trim($res2);
}
function benkey65($sesIp=0){//sesIp=0,1[sesIp],randStr[since sidu4.0]
	$b65='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.-_';
	$b30=substr($b65,0,32);
	if ($sesIp && strlen($sesIp)>1) $key=md5($sesIp);//since sidu4.0 used for conn.php
	else{
		$key=md5(SIDU_PK());
		if ($sesIp){
			$ses=session_id();
			$key=md5($key.str_repeat($ses,substr(ord($key[0]),0,1)+1));
			$key=md5($key.str_repeat($_SERVER['REMOTE_ADDR'],substr(ord($key[0]),-1)+1));
		}
	}
	$key=strtoupper($key);
	$key=str_replace(array('U','V','W','X','Y','Z'),'0',$key);//md5 max F--this line no need
	for ($i=0;$i<32;$i++){
		$cur=strpos($b30,$key[$i])-10;
		$arr[]=$cur;
		$b65=cook65($b65,$i,$cur);
	}
	$arr[]=$b65;
	return $arr;
}
function cook65($b65,$fm,$to){
	$pos=$fm+$to;
	if ($pos<0 || $pos>64 || $fm==$pos) return $b65;
	if ($fm>$pos) swap($fm,$pos);
	$b65=substr($b65,0,$fm).$b65[$pos].substr($b65,$fm+1,$pos-$fm-1).$b65[$fm].substr($b65,$pos+1);
	return substr($b65,$pos).substr($b65,0,$pos);
}
function base65($int,$b65){
	$b=$int%(65*65);
	return $b65[floor($int/65/65)].$b65[floor($b/65)].$b65[$b%65];
}
?>
