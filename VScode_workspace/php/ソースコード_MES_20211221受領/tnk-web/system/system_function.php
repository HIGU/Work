<?php
//////////////////////////////////////////////////////////////////////////////
//                                                                          //
// システム管理 用 ファンクッション ファイル                                //
// Copyright (C) 2001 Kazuhiro Kobayashi all rights reserved.               //
//                                           2002/03/23                     //
// 変更経歴                                                                 //
// 2002/03/23                                                               //
//////////////////////////////////////////////////////////////////////////////


require("../function.php");		// 内部でdefine.php pgsql.php を require()している。

define("HIUURI","/home/www/html/daily/W#HIUURI.TXT");

// 売上データのテキストをフィールドごとに分けてレコード数を返す。
function hiuuri_get_field(&$f01,&$f02,&$f03,&$f04,&$f05,&$f06,&$f07,&$f08,&$f09,&$f10,&$f11,&$f12,&$f13,&$f14,&$f15,&$f16,&$f17,&$f18,&$f19){
	if($fp = fopen(HIUURI,'r')){
		$c = 0;
		while(!feof($fp)){
			$data = fgets($fp,200);
			$f01[$c] = substr($data,0,1);		// 事業部
			$f02[$c] = substr($data,1,8);		// 処理日
			$f03[$c] = substr($data,9,8);		// 計上日
			$f04[$c] = substr($data,17,9);	// assyno
			$f05[$c] = substr($data,26,9);	// 製品コード
			$f06[$c] = substr($data,35,8);	// 計画番号
			$f07[$c] = substr($data,43,7);	// 製造番号
			$f08[$c] = substr($data,50,7);	// 注文番号
			$f09[$c] = substr($data,57,7);	// 発行番号
			$f10[$c] = substr($data,64,2);	// 入庫場所
			$f11[$c] = substr($data,66,5);	// 組立完了番号
			$f12[$c] = substr($data,71,6);	// 伝票番号
			$f13[$c] = substr($data,77,6);	// 数量
			$f14[$c] = substr($data,83,9);	// 単価 後処理で/100
			$f15[$c] = substr($data,92,3);	// 特採率
			$f16[$c] = substr($data,95,1);	// datatype
			$f17[$c] = substr($data,96,5);	// 得意先
			$f18[$c] = substr($data,101,15);	// 備考
			$f19[$c] = substr($data,116,1);	// 日報区分
			$c += 1;
		}
		fclose($fp);
		return ($c-1);
	}else
		return FALSE;
}

