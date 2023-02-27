#!/usr/local/bin/php -q
<?php
//////////////////////////////////////////////////////////////////////////////
//  ÆüÊó¥Ç¡¼¥¿ 1¥ì¥³¡¼¥É¤º¤Ä½ñ¤­¹þ¤ß¥Æ¥¹¥È           ¥³¥Þ¥ó¥É¥é¥¤¥óÈÇ       //
//    ½ñ¹þ¤ß¸å¥á¥Ã¥»¡¼¥¸¤òÊÖ¤¹ pg_affected_rows() ¤ò»ÈÍÑ                    //
//  2002/12/09   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp             //
//  ÊÑ¹¹·ÐÎò                                                                //
//  2002/12/09 ¥Æ¥¹¥ÈÈÇ¤Ç debug Ãæ¤Î¤¿¤á ¥ê¥ê¡¼¥¹Á°                         //
//////////////////////////////////////////////////////////////////////////////
	require("../function.php");

// Çä¾å ÆüÊó½èÍý ½àÈ÷ºî¶È
$file_orign = "W#HIUURI.TXT";
// $file_test  = "hiuuri.txt";
if(file_exists($file_orign)){			// ¥Õ¥¡¥¤¥ë¤ÎÂ¸ºß¥Á¥§¥Ã¥¯
	$fp = fopen($file_orign,"r");
	// $fpw = fopen($file_test,"w");		// TEST ÍÑ¥Õ¥¡¥¤¥ë¤Î¥ª¡¼¥×¥ó
	$div    = array();
	$date_s = array();
	$date_k = array();
	$assyno = array();
	$sei_no = array();
	$planno = array();
	$seizou = array();
	$tyumon = array();
	$hakkou = array();
	$nyuuko = array();
	$kan_no = array();
	$den_no = array();
	$suryou = array();
	$tanka1 = array();
	$tanka2 = array();
	$tokusa = array();
	$datatp = array();
	$tokuis = array();
	$bikou  = array();
	$kubun  = array();
	$rec = 0;		// ¥ì¥³¡¼¥É­â
	while(1){
		$data=fgets($fp,120);
		$data = mb_convert_encoding($data, "EUC-JP", "auto");		// auto¤òEUC-JP¤ØÊÑ´¹
		// $data_KV = mb_convert_kana($data);			// È¾³Ñ¥«¥Ê¤òÁ´³Ñ¥«¥Ê¤ËÊÑ´¹
		// fwrite($fpw,$data_KV);
		if(feof($fp)){
			break;
		}
		$div[$rec]    = substr($data,0,1);		// »ö¶ÈÉô
		$date_s[$rec] = substr($data,1,8);		// ½èÍýÆü
		$date_k[$rec] = substr($data,9,8);		// ·×¾åÆü
		$assyno[$rec] = substr($data,17,9);		// ÉôÉÊ¡¦À½ÉÊ­â
		$sei_no[$rec] = substr($data,26,9);		// À½ÉÊ¥³¡¼¥É
		$planno[$rec] = substr($data,35,8);		// ·×²è­â
		$seizou[$rec] = substr($data,43,7);		// À½Â¤­â
		$tyumon[$rec] = substr($data,50,7);		// ÃíÊ¸­â
		$hakkou[$rec] = substr($data,57,7);		// È¯¹Ô­â
		$nyuuko[$rec] = substr($data,64,2);		// Æþ¸Ë¾ì½ê
		$kan_no[$rec] = substr($data,66,5);		// ÁÈÎ©´°Î»­â
		$den_no[$rec] = substr($data,71,6);		// ÅÁÉ¼­â
		$suryou[$rec] = substr($data,77,6);		// ¿ôÎÌ
		$tanka1[$rec]  = substr($data,83,7);	// Ã±²Á(À°¿ôÉô)
		$tanka2[$rec]  = substr($data,90,2);	// Ã±²Á(¾®¿ôÉô)
		$tokusa[$rec] = substr($data,92,3);		// ÆÃºÎÎ¨
		$datatp[$rec] = substr($data,95,1);		// datatype
		$tokuis[$rec] = substr($data,96,5);		// ÆÀ°ÕÀè
		$bikou[$rec] = substr($data,101,15);	// È÷¹Í
		$kubun[$rec] = substr($data,116,1);		// ÆüÊó¶èÊ¬
	/* ¥Æ¥¹¥ÈÍÑ¤Ë¥Õ¥¡¥¤¥ë¤ËÍî¤È¤¹
		fwrite($fpw,$div[$rec]    . "\n");
		fwrite($fpw,$date_s[$rec] . "\n");
		fwrite($fpw,$date_k[$rec] . "\n");
		fwrite($fpw,$assyno[$rec] . "\n");
		fwrite($fpw,$sei_no[$rec] . "\n");
		fwrite($fpw,$planno[$rec] . "\n");
		fwrite($fpw,$seizou[$rec] . "\n");
		fwrite($fpw,$tyumon[$rec] . "\n");
		fwrite($fpw,$hakkou[$rec] . "\n");
		fwrite($fpw,$nyuuko[$rec] . "\n");
		fwrite($fpw,$kan_no[$rec] . "\n");
		fwrite($fpw,$den_no[$rec] . "\n");
		fwrite($fpw,$suryou[$rec] . "\n");
		fwrite($fpw,$tanka1[$rec]  . ".");
		fwrite($fpw,$tanka2[$rec]  . "\n");
		fwrite($fpw,$tokusa[$rec] . "\n");
		fwrite($fpw,$datatp[$rec] . "\n");
		fwrite($fpw,$tokuis[$rec] . "\n");
		fwrite($fpw,$bikou[$rec]  . "\n");
		fwrite($fpw,$kubun[$rec]  . "\n");
			¥Æ¥¹¥ÈÍÑ END */
		$rec++;
	}
	fclose($fp);
	// fclose($fpw);
}
$log_date = date("Y-m-d H:i:s"); 			// ¥í¥°¤ÎÆü»þ
$fpa = fopen("/tmp/hiuuri_nippo.log","a"); // ¥í¥°¥Õ¥¡¥¤¥ë¤Ø¤Î½ñ¹þ¤ß¤Ç¥ª¡¼¥×¥ó
if($rec >= 1){ // ¥ì¥³¡¼¥É¿ô¤Î¥Á¥§¥Ã¥¯
	$res_chk = array();
	$query_chk = "select ·×¾åÆü from hiuuri where ·×¾åÆü=" . $date_k[0];
	if(getResult($query_chk,$res_chk)<=0){
		for($i=0;$i<$rec;$i++){
			$query = "insert into hiuuri values('";
			$query .= $div[$i] . "',";
			$query .= $date_s[$i] . ",";
			$query .= $date_k[$i] . ",'";
			$query .= $assyno[$i] . "','";
			$query .= $sei_no[$i] . "','";
			$query .= $planno[$i] . "',";
			$query .= $seizou[$i] . ",";
			$query .= $tyumon[$i] . ",";
			$query .= $hakkou[$i] . ",'";
			$query .= $nyuuko[$i] . "',";
			$query .= $kan_no[$i] . ",'";
			$query .= $den_no[$i] . "',";
			$query .= $suryou[$i] . ",";
			$query .= $tanka1[$i] . "."; // ¾®¿ôÅÀ¤ËÃí°Õ
			$query .= $tanka2[$i] . ",";
			$query .= $tokusa[$i] . ",'";
			$query .= $datatp[$i] . "','";
			$query .= $tokuis[$i] . "','";
			$query .= $bikou[$i] . "','";
			$query .= $kubun[$i] . "')";
			if(query_affected($query) <= 0){     // ¹¹¿·ÍÑ¥¯¥¨¥ê¡¼¤Î¼Â¹Ô
				fwrite($fpa,"$log_date ·×¾åÆü:".$date_k[0].": $i:¥ì¥³¡¼¥ÉÌÜ¤Î½ñ¹þ¤ß¤Ë¼ºÇÔ¤·¤Þ¤·¤¿Ž¡\n");
				echo ($i+1) . ":¥ì¥³¡¼¥ÉÌÜ¤Î½ñ¹þ¤ß¤Ë¼ºÇÔ¤·¤Þ¤·¤¿Ž¡\n";
			}else
				echo ($i+1) . ":¥ì¥³¡¼¥ÉÌÜ¤Î½ñ¤­¹þ¤ßÀ®¸ù \n";
		}
		fwrite($fpa,"$log_date ·×¾åÆü:" . $date_k[0] . ": " . $rec . " ·ïÅÐÏ¿¤·¤Þ¤·¤¿¡£\n");
		echo $rec . " ·ïÅÐÏ¿¤·¤Þ¤·¤¿¡£\n";
	}else{
		fwrite($fpa,"$log_date ·×¾åÆü:" . $date_k[0] . " ´û¤ËÅÐÏ¿¤µ¤ì¤Æ¤¤¤Þ¤¹Ž¡\n");
		echo "·×¾åÆü:" . $date_k[0] . " ´û¤ËÅÐÏ¿¤µ¤ì¤Æ¤¤¤Þ¤¹Ž¡\n";
	}
}else{
	fwrite($fpa,"$log_date ¥ì¥³¡¼¥É¤¬¤¢¤ê¤Þ¤»¤óŽ¡\n");
	echo "¥ì¥³¡¼¥É¤¬¤¢¤ê¤Þ¤»¤óŽ¡\n";
}
fclose($fpa);
?>
