#!/usr/local/bin/php -q
<?php
//////////////////////////////////////////////////////////////////////////////
//  ����ǡ��� 1�쥳���ɤ��Ľ񤭹��ߥƥ���           ���ޥ�ɥ饤����       //
//    ����߸��å��������֤� pg_affected_rows() �����                    //
//  2002/12/09   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp             //
//  �ѹ�����                                                                //
//  2002/12/09 �ƥ����Ǥ� debug ��Τ��� ��꡼����                         //
//////////////////////////////////////////////////////////////////////////////
	require("../function.php");

// ��� ������� �������
$file_orign = "W#HIUURI.TXT";
// $file_test  = "hiuuri.txt";
if(file_exists($file_orign)){			// �ե������¸�ߥ����å�
	$fp = fopen($file_orign,"r");
	// $fpw = fopen($file_test,"w");		// TEST �ѥե�����Υ����ץ�
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
	$rec = 0;		// �쥳���ɭ�
	while(1){
		$data=fgets($fp,120);
		$data = mb_convert_encoding($data, "EUC-JP", "auto");		// auto��EUC-JP���Ѵ�
		// $data_KV = mb_convert_kana($data);			// Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
		// fwrite($fpw,$data_KV);
		if(feof($fp)){
			break;
		}
		$div[$rec]    = substr($data,0,1);		// ������
		$date_s[$rec] = substr($data,1,8);		// ������
		$date_k[$rec] = substr($data,9,8);		// �׾���
		$assyno[$rec] = substr($data,17,9);		// ���ʡ����ʭ�
		$sei_no[$rec] = substr($data,26,9);		// ���ʥ�����
		$planno[$rec] = substr($data,35,8);		// �ײ��
		$seizou[$rec] = substr($data,43,7);		// ��¤��
		$tyumon[$rec] = substr($data,50,7);		// ��ʸ��
		$hakkou[$rec] = substr($data,57,7);		// ȯ�ԭ�
		$nyuuko[$rec] = substr($data,64,2);		// ���˾��
		$kan_no[$rec] = substr($data,66,5);		// ��Ω��λ��
		$den_no[$rec] = substr($data,71,6);		// ��ɼ��
		$suryou[$rec] = substr($data,77,6);		// ����
		$tanka1[$rec]  = substr($data,83,7);	// ñ��(������)
		$tanka2[$rec]  = substr($data,90,2);	// ñ��(������)
		$tokusa[$rec] = substr($data,92,3);		// �ú�Ψ
		$datatp[$rec] = substr($data,95,1);		// datatype
		$tokuis[$rec] = substr($data,96,5);		// ������
		$bikou[$rec] = substr($data,101,15);	// ����
		$kubun[$rec] = substr($data,116,1);		// �����ʬ
	/* �ƥ����Ѥ˥ե��������Ȥ�
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
			�ƥ����� END */
		$rec++;
	}
	fclose($fp);
	// fclose($fpw);
}
$log_date = date("Y-m-d H:i:s"); 			// ��������
$fpa = fopen("/tmp/hiuuri_nippo.log","a"); // ���ե�����ؤν���ߤǥ����ץ�
if($rec >= 1){ // �쥳���ɿ��Υ����å�
	$res_chk = array();
	$query_chk = "select �׾��� from hiuuri where �׾���=" . $date_k[0];
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
			$query .= $tanka1[$i] . "."; // �����������
			$query .= $tanka2[$i] . ",";
			$query .= $tokusa[$i] . ",'";
			$query .= $datatp[$i] . "','";
			$query .= $tokuis[$i] . "','";
			$query .= $bikou[$i] . "','";
			$query .= $kubun[$i] . "')";
			if(query_affected($query) <= 0){     // �����ѥ����꡼�μ¹�
				fwrite($fpa,"$log_date �׾���:".$date_k[0].": $i:�쥳�����ܤν���ߤ˼��Ԥ��ޤ�����\n");
				echo ($i+1) . ":�쥳�����ܤν���ߤ˼��Ԥ��ޤ�����\n";
			}else
				echo ($i+1) . ":�쥳�����ܤν񤭹������� \n";
		}
		fwrite($fpa,"$log_date �׾���:" . $date_k[0] . ": " . $rec . " ����Ͽ���ޤ�����\n");
		echo $rec . " ����Ͽ���ޤ�����\n";
	}else{
		fwrite($fpa,"$log_date �׾���:" . $date_k[0] . " ������Ͽ����Ƥ��ޤ���\n");
		echo "�׾���:" . $date_k[0] . " ������Ͽ����Ƥ��ޤ���\n";
	}
}else{
	fwrite($fpa,"$log_date �쥳���ɤ�����ޤ���\n");
	echo "�쥳���ɤ�����ޤ���\n";
}
fclose($fpa);
?>
