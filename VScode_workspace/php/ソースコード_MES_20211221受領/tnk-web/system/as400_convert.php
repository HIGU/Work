#!/usr/local/bin/php -q
<?php
//////////////////////////////////////////////////////////////////////////////
//  ����ǡ��� ��ưFTP Download File convert (AS�Υ���)   ���ޥ�ɥ饤����  //
//  AS/400 ----> Web Server (PHP)                    �ƥ�����               //
//  2002/03/11   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp             //
//  �ѹ�����                                                                //
//  2002/11/28 �ƥ����Ǥ� debug �� �Τ��������˥�꡼����                   //
//               MIITEM �� AS/400 Ⱦ�ѥ��ʤ��Ѵ��������Τ������ޤ������ʤ�  //
//////////////////////////////////////////////////////////////////////////////
//	require("../function.php");
// ���ͥ���������(FTP��³�Υ����ץ�)
if($ftp_stream = ftp_connect("10.1.1.252")){
	if(ftp_login($ftp_stream,"FTPUSR","AS400FTP")){
		if(ftp_get($ftp_stream,"W#HIUURI.TXT","UKWLIB/W#HIUURA",FTP_ASCII)){
			echo "ftp_get download ���� W#HIUURA �� W#HIUURI.TXT \n";
		}else{
			echo "ftp_get() error UKWLIB/W#HIUURA \n";
		}
		if(ftp_get($ftp_stream,"W#MIITEM.TXT","UKWLIB/W#MIITEM",FTP_ASCII)){
			echo "ftp_get download ���� W#MIITEM �� W#MIITEM.TXT \n";
		}else{
			echo "ftp_get() error UKWLIB/W#MIITEM \n";
		}
	}else{
		echo "ftp_login() error \n";
	}
	ftp_quit($ftp_stream);
}else{
	echo "ftp_connect() error \n";
}



// ��� ������� �������
$file_orign = "W#MIITEM.TXT";
$file_eucjp = "miitem.euc";
if(file_exists($file_orign)){			// �ե������¸�ߥ����å�
	$fp = fopen($file_orign,"r");
	$fpw = fopen($file_eucjp,"w");
	$rec = 0;		// �쥳���ɭ�
	while(1){
		$data=fgets($fp,120);
		$data = mb_convert_encoding($data, "EUC-JP", "auto");		// auto��EUC-JP���Ѵ�
		                             /* "auto" �ϡ�"ASCII,JIS,UTF-8,EUC-JP,SJIS" ��Ÿ������� */
		$data_KV = mb_convert_kana($data);			// Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
		fwrite($fpw,$data_KV);
		if(feof($fp)){
			break;
		}
		$rec++;
	}
	fclose($fp);
	fclose($fpw);
}
?>
