#!/usr/local/bin/php -q
<?php
//////////////////////////////////////////////////////////////////////////////
//  日報データ 1レコードずつ書き込みテスト           コマンドライン版       //
//    書込み後メッセージを返す pg_affected_rows() を使用                    //
//  2002/12/09   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp             //
//  変更経歴                                                                //
//  2002/12/09 テスト版で debug 中のため リリース前                         //
//////////////////////////////////////////////////////////////////////////////
	require("../function.php");

// 売上 日報処理 準備作業
$file_orign = "W#HIUURI.TXT";
// $file_test  = "hiuuri.txt";
if(file_exists($file_orign)){			// ファイルの存在チェック
	$fp = fopen($file_orign,"r");
	// $fpw = fopen($file_test,"w");		// TEST 用ファイルのオープン
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
	$rec = 0;		// レコード№
	while(1){
		$data=fgets($fp,120);
		$data = mb_convert_encoding($data, "UTF-8", "auto");		// autoをEUC-JPへ変換
		// $data_KV = mb_convert_kana($data);			// 半角カナを全角カナに変換
		// fwrite($fpw,$data_KV);
		if(feof($fp)){
			break;
		}
		$div[$rec]    = substr($data,0,1);		// 事業部
		$date_s[$rec] = substr($data,1,8);		// 処理日
		$date_k[$rec] = substr($data,9,8);		// 計上日
		$assyno[$rec] = substr($data,17,9);		// 部品・製品№
		$sei_no[$rec] = substr($data,26,9);		// 製品コード
		$planno[$rec] = substr($data,35,8);		// 計画№
		$seizou[$rec] = substr($data,43,7);		// 製造№
		$tyumon[$rec] = substr($data,50,7);		// 注文№
		$hakkou[$rec] = substr($data,57,7);		// 発行№
		$nyuuko[$rec] = substr($data,64,2);		// 入庫場所
		$kan_no[$rec] = substr($data,66,5);		// 組立完了№
		$den_no[$rec] = substr($data,71,6);		// 伝票№
		$suryou[$rec] = substr($data,77,6);		// 数量
		$tanka1[$rec]  = substr($data,83,7);	// 単価(整数部)
		$tanka2[$rec]  = substr($data,90,2);	// 単価(小数部)
		$tokusa[$rec] = substr($data,92,3);		// 特採率
		$datatp[$rec] = substr($data,95,1);		// datatype
		$tokuis[$rec] = substr($data,96,5);		// 得意先
		$bikou[$rec] = substr($data,101,15);	// 備考
		$kubun[$rec] = substr($data,116,1);		// 日報区分
	/* テスト用にファイルに落とす
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
			テスト用 END */
		$rec++;
	}
	fclose($fp);
	// fclose($fpw);
}
$log_date = date("Y-m-d H:i:s"); 			// ログの日時
$fpa = fopen("/tmp/hiuuri_nippo.log","a"); // ログファイルへの書込みでオープン
if($rec >= 1){ // レコード数のチェック
	$res_chk = array();
	$query_chk = "select 計上日 from hiuuri where 計上日=" . $date_k[0];
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
			$query .= $tanka1[$i] . "."; // 小数点に注意
			$query .= $tanka2[$i] . ",";
			$query .= $tokusa[$i] . ",'";
			$query .= $datatp[$i] . "','";
			$query .= $tokuis[$i] . "','";
			$query .= $bikou[$i] . "','";
			$query .= $kubun[$i] . "')";
			if(query_affected($query) <= 0){     // 更新用クエリーの実行
				fwrite($fpa,"$log_date 計上日:".$date_k[0].": $i:レコード目の書込みに失敗しました｡\n");
				echo ($i+1) . ":レコード目の書込みに失敗しました｡\n";
			}else
				echo ($i+1) . ":レコード目の書き込み成功 \n";
		}
		fwrite($fpa,"$log_date 計上日:" . $date_k[0] . ": " . $rec . " 件登録しました。\n");
		echo $rec . " 件登録しました。\n";
	}else{
		fwrite($fpa,"$log_date 計上日:" . $date_k[0] . " 既に登録されています｡\n");
		echo "計上日:" . $date_k[0] . " 既に登録されています｡\n";
	}
}else{
	fwrite($fpa,"$log_date レコードがありません｡\n");
	echo "レコードがありません｡\n";
}
fclose($fpa);
?>
