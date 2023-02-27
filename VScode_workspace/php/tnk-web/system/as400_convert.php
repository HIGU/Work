#!/usr/local/bin/php -q
<?php
//////////////////////////////////////////////////////////////////////////////
//  日報データ 自動FTP Download File convert (ASのカナ)   コマンドライン版  //
//  AS/400 ----> Web Server (PHP)                    テスト用               //
//  2002/03/11   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp             //
//  変更経歴                                                                //
//  2002/11/28 テスト版で debug 中 のため正式にリリース前                   //
//               MIITEM の AS/400 半角カナを変換したいのだがうまくいかない  //
//////////////////////////////////////////////////////////////////////////////
//	require("../function.php");
// コネクションを取る(FTP接続のオープン)
if($ftp_stream = ftp_connect("10.1.1.252")){
	if(ftp_login($ftp_stream,"FTPUSR","AS400FTP")){
		if(ftp_get($ftp_stream,"W#HIUURI.TXT","UKWLIB/W#HIUURA",FTP_ASCII)){
			echo "ftp_get download 成功 W#HIUURA → W#HIUURI.TXT \n";
		}else{
			echo "ftp_get() error UKWLIB/W#HIUURA \n";
		}
		if(ftp_get($ftp_stream,"W#MIITEM.TXT","UKWLIB/W#MIITEM",FTP_ASCII)){
			echo "ftp_get download 成功 W#MIITEM → W#MIITEM.TXT \n";
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



// 売上 日報処理 準備作業
$file_orign = "W#MIITEM.TXT";
$file_eucjp = "miitem.euc";
if(file_exists($file_orign)){			// ファイルの存在チェック
	$fp = fopen($file_orign,"r");
	$fpw = fopen($file_eucjp,"w");
	$rec = 0;		// レコード№
	while(1){
		$data=fgets($fp,120);
		$data = mb_convert_encoding($data, "UTF-8", "auto");		// autoをEUC-JPへ変換
		                             /* "auto" は、"ASCII,JIS,UTF-8,UTF-8,SJIS" に展開される */
		$data_KV = mb_convert_kana($data);			// 半角カナを全角カナに変換
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
