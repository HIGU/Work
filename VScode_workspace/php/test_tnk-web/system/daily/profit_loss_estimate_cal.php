#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 損益予測の自動計算・登録 as400get_ftp.php処理で実行                      //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/15 Created   profit_loss_estimate_cal.php                        //
// 2011/07/20 全体の登録を追加                                              //
//            試修・商管は暫定的に過去１年間の平均より算出                  //
// 2011/07/21 損益予測が自動実行されないため１行目を追加                    //
// 2011/07/22 daoInterfaceClass.phpは不要で                                 //
//                              エラー発生(リンクミス？)のため削除          //
// 2011/07/25 変数の大文字小文字を訂正                                      //
// 2011/10/04 リニア売上高がカプラに合計されているミスを修正                //
// 2011/11/22 売上高と棚卸高の取得で材料費計算にミスがあったのを修正        //
// 2018/04/17 リニアの損益データがリニア標準に変わっているので訂正          //
// 2018/09/26 日付指定を消し忘れていたので修正                              //
// 2018/09/27 予定金額分に日程変更で完了しているもが含まれていたので除外    //
//            変なLIMITが残っていたので修正                                 //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
//ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "損益予測データの計算・更新\n");
fwrite($fpb, "/var/www/html/system/daily/profit_loss_estimate_cal.php\n");
echo "/var/www/html/system/daily/profit_loss_estimate_cal.php\n";

/////////// 日付データの取得
$target_ym = date('Ym');
// 過去データを作り直す際はここ以外に過去データで検索し、コメントを入れ替える
// （当日の作り直しはそのままで問題なし）
//$target_ym = 201809;
$today     = date('Ymd');
//$today     = 20180926;
        
        // 売上高の取得
        // getQueryStatement1：当月完成予定のうち、全打ち切り、完成済分以外。使用材料を総材料費より。
        $div   = 'C';
        $query = getQueryStatement1($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       = 0;                   // カプラ売上高
            $c_endinv    = 0;                   // カプラ期末棚卸高１
        } else {
            // 各データの初期化
            $c_uri       = 0;                   // カプラ売上高
            $c_endinv    = 0;                   // カプラ期末棚卸高１
            for ($r=0; $r<$rows_t; $r++) {
                $c_uri     += $res_t[$r][9];
                $c_endinv  -= $res_t[$r][7];
            }
        }
        // getQueryStatement17：前日までの売上高（完成のみ）、使用材料を総材料費より。
        $query = getQueryStatement17($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       += 0;
            $c_endinv    -= 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_uri     += $res_t[$r][9];
                $c_endinv  -= $res_t[$r][7];
            }
        }
        // getQueryStatement15：部品売上高（区分：2以上）前月から6か月分の売上高の平均、材料費も合わせて。
        $query = getQueryStatement15($target_ym, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       += 0;
            $c_endinv    -= 0;
        } else {
            $c_uri     += $res_t[0][0];
            $c_endinv  -= $res_t[0][3];
        }
        
        $div   = 'L';
        $query = getQueryStatement1($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       = 0;                   // リニア売上高
            $l_endinv    = 0;                   // リニア期末棚卸高１
        } else {
            // 各データの初期化
            $l_uri       = 0;                   // リニア売上高
            $l_endinv    = 0;                   // リニア期末棚卸高１
            for ($r=0; $r<$rows_t; $r++) {
                $l_uri     += $res_t[$r][9];
                $l_endinv  -= $res_t[$r][7];
            }
        }
        $query = getQueryStatement17($target_ym, $today, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       += 0;
            $l_endinv    -= 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_uri     += $res_t[$r][9];
                $l_endinv  -= $res_t[$r][7];
            }
        }
        $query = getQueryStatement15($target_ym, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       += 0;
            $l_endinv    -= 0;
        } else {
            $l_uri     += $res_t[0][0];
            $l_endinv  -= $res_t[0][3];
        }
        
        // 期首棚卸高の取得
        // getQueryStatement2：期首棚卸高＝前月の期末棚卸高
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = getQueryStatement2($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_invent = 0;
        } else {
            $c_invent = -$res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = getQueryStatement2($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_invent = 0;
        } else {
            $l_invent = -$res_t[0][0];
        }
        // 材料費の取得
        // getQueryStatement3：買掛実績より科目5以上、栃木日東工器(01111)と特注(00222)の内作は除く
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = getQueryStatement3($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial = 0;
        } else {
            $c_metarial = $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement4($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement5($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement6($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_metarial += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement7($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement8($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_metarial += $res_t[$r][2];
            }
        }
        
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = getQueryStatement3($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial = 0;
        } else {
            $l_metarial = $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement4($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement5($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement6($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_metarial += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement7($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement8($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_metarial += $res_t[$r][2];
            }
        }
        // 期末棚卸高の取得
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = getQueryStatement9($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement10($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement11($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement12($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_endinv += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement13($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement14($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_endinv += $res_t[$r][2];
            }
        }
        
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = getQueryStatement9($target_ym, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement10($div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement11($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement12($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_endinv += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement13($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = getQueryStatement14($target_ym, $today, $div);
        if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_endinv += $res_t[$r][2];
            }
        }
        
        // 各種経費の計算
        $div      = 'C';
        $rate_c   = array();
        $note     = array();
        $note[0]  = 'カプラ労務費';
        $note[1]  = 'カプラ製造経費';
        $note[2]  = 'カプラ人件費';
        $note[3]  = 'カプラ経費';
        $note[4]  = 'カプラ業務委託収入';
        $note[5]  = 'カプラ仕入割引';
        $note[6]  = 'カプラ営業外収益その他';
        $note[7]  = 'カプラ支払利息';
        $note[8]  = 'カプラ営業外費用その他';
        $uri_note = 'カプラ売上高';
        $num = count($note);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $note[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $kei_tmp = 0;
            } else {
                $kei_tmp = $res_t[0][0];
            }
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $uri_note);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $uri_tmp = 0;
            } else {
                $uri_tmp = $res_t[0][0];
            }
            if ($uri_tmp != 0) {
                $rate_c[$r] = round($kei_tmp / $uri_tmp, 4);
            } else {
                $rate_c[$r] = 0;
            }
            $kei_kin   = round($c_uri * $rate_c[$r], 0);
            if ($r == 0) {
                $c_roumu    = $kei_kin;     // 製造経費-労務費
            } elseif ($r == 1) {
                $c_expense  = $kei_kin;     // 製造経費-経費
            } elseif ($r == 2) {
                $c_han_jin  = $kei_kin;     // 販管費-人件費
            } elseif ($r == 3) {
                $c_han_kei  = $kei_kin;     // 販管費-経費
            } elseif ($r == 4) {
                $c_gyoumu   = $kei_kin;     // 業務委託収入
            } elseif ($r == 5) {
                $c_swari    = $kei_kin;     // 仕入割引
            } elseif ($r == 6) {
                $c_pother   = $kei_kin;     // 営業外収益その他
            } elseif ($r == 7) {
                $c_srisoku  = $kei_kin;     // 支払利息
            } elseif ($r == 8) {
                $c_lother   = $kei_kin;     // 営業外費用その他
            }
        }
                
        $div      = 'L';
        $rate_l   = array();
        $note     = array();
        $note[0]  = 'リニア標準労務費';
        $note[1]  = 'リニア標準製造経費';
        $note[2]  = 'リニア標準人件費';
        $note[3]  = 'リニア標準経費';
        $note[4]  = 'リニア標準業務委託収入';
        $note[5]  = 'リニア標準仕入割引';
        $note[6]  = 'リニア標準営業外収益その他';
        $note[7]  = 'リニア標準支払利息';
        $note[8]  = 'リニア標準営業外費用その他';
        $uri_note = 'リニア標準売上高';
        /*
        $note[0]  = 'リニア労務費';
        $note[1]  = 'リニア製造経費';
        $note[2]  = 'リニア人件費';
        $note[3]  = 'リニア経費';
        $note[4]  = 'リニア業務委託収入';
        $note[5]  = 'リニア仕入割引';
        $note[6]  = 'リニア営業外収益その他';
        $note[7]  = 'リニア支払利息';
        $note[8]  = 'リニア営業外費用その他';
        $uri_note = 'リニア売上高';
        */
        $num = count($note);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $note[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $kei_tmp = 0;
            } else {
                $kei_tmp = $res_t[0][0];
            }
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $uri_note);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $uri_tmp = 0;
            } else {
                $uri_tmp = $res_t[0][0];
            }
            if ($uri_tmp != 0) {
                $rate_l[$r] = round($kei_tmp / $uri_tmp, 4);
            } else {
                $rate_l[$r] = 0;
            }
            $kei_kin   = round($l_uri * $rate_l[$r], 0);
            if ($r == 0) {
                $l_roumu    = $kei_kin;     // 製造経費-労務費
            } elseif ($r == 1) {
                $l_expense  = $kei_kin;     // 製造経費-経費
            } elseif ($r == 2) {
                $l_han_jin  = $kei_kin;     // 販管費-人件費
            } elseif ($r == 3) {
                $l_han_kei  = $kei_kin;     // 販管費-経費
            } elseif ($r == 4) {
                $l_gyoumu   = $kei_kin;     // 業務委託収入
            } elseif ($r == 5) {
                $l_swari    = $kei_kin;     // 仕入割引
            } elseif ($r == 6) {
                $l_pother   = $kei_kin;     // 営業外収益その他
            } elseif ($r == 7) {
                $l_srisoku  = $kei_kin;     // 支払利息
            } elseif ($r == 8) {
                $l_lother   = $kei_kin;     // 営業外費用その他
            }
        }
        // 商品管理（過去１年間の平均）暫定
        $item_b = array();
        $item_b[0]  = '商品管理売上高';
        $item_b[1]  = '商品管理期首材料仕掛品棚卸高';
        $item_b[2]  = '商品管理材料費(仕入高)';
        $item_b[3]  = '商品管理労務費';
        $item_b[4]  = '商品管理製造経費';
        $item_b[5]  = '商品管理期末材料仕掛品棚卸高';
        $item_b[6]  = '商品管理売上原価';
        $item_b[7]  = '商品管理売上総利益';
        $item_b[8]  = '商品管理人件費';
        $item_b[9]  = '商品管理経費';
        $item_b[10] = '商品管理販管費及び一般管理費計';
        $item_b[11] = '商品管理営業利益';
        $item_b[12] = '商品管理業務委託収入';
        $item_b[13] = '商品管理仕入割引';
        $item_b[14] = '商品管理営業外収益その他';
        $item_b[15] = '商品管理営業外収益計';
        $item_b[16] = '商品管理支払利息';
        $item_b[17] = '商品管理営業外費用その他';
        $item_b[18] = '商品管理営業外費用計';
        $item_b[19] = '商品管理経常利益';
        $num = count($item_b);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $item_b[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $b_uri = 0;
                } elseif ($r == 1) {
                    $b_invent = 0;
                } elseif ($r == 2) {
                    $b_metarial = 0;
                } elseif ($r == 3) {
                    $b_roumu = 0;
                } elseif ($r == 4) {
                    $b_expense = 0;
                } elseif ($r == 5) {
                    $b_endinv = 0;
                } elseif ($r == 6) {
                    $b_urigen = 0;
                } elseif ($r == 7) {
                    $b_gross_profit = 0;
                } elseif ($r == 8) {
                    $b_han_jin = 0;
                } elseif ($r == 9) {
                    $b_han_kei = 0;
                } elseif ($r == 10) {
                    $b_han_all = 0;
                } elseif ($r == 11) {
                    $b_ope_profit = 0;
                } elseif ($r == 12) {
                    $b_gyoumu = 0;
                } elseif ($r == 13) {
                    $b_swari = 0;
                } elseif ($r == 14) {
                    $b_pother = 0;
                } elseif ($r == 15) {
                    $b_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $b_srisoku = 0;
                } elseif ($r == 17) {
                    $b_lother = 0;
                } elseif ($r == 18) {
                    $b_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $b_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $b_uri = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 1) {
                    $b_invent = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 2) {
                    $b_metarial = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 3) {
                    $b_roumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 4) {
                    $b_expense = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 5) {
                    $b_endinv = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 6) {
                    $b_urigen = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 7) {
                    $b_gross_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 8) {
                    $b_han_jin = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 9) {
                    $b_han_kei = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 10) {
                    $b_han_all = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 11) {
                    $b_ope_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 12) {
                    $b_gyoumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 13) {
                    $b_swari = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 14) {
                    $b_pother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 15) {
                    $b_nonope_profit_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 16) {
                    $b_srisoku = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 17) {
                    $b_lother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 18) {
                    $b_nonope_loss_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 19) {
                    $b_current_profit = round(($res_t[0][0] / 12), 0);
                }
            }
        }
        // 試験修理
        $item_s = array();
        $item_s[0]  = '試験修理売上高';
        $item_s[1]  = '試験修理期首材料仕掛品棚卸高';
        $item_s[2]  = '試験修理材料費(仕入高)';
        $item_s[3]  = '試験修理労務費';
        $item_s[4]  = '試験修理製造経費';
        $item_s[5]  = '試験修理期末材料仕掛品棚卸高';
        $item_s[6]  = '試験修理売上原価';
        $item_s[7]  = '試験修理売上総利益';
        $item_s[8]  = '試験修理人件費';
        $item_s[9]  = '試験修理経費';
        $item_s[10] = '試験修理販管費及び一般管理費計';
        $item_s[11] = '試験修理営業利益';
        $item_s[12] = '試験修理業務委託収入';
        $item_s[13] = '試験修理仕入割引';
        $item_s[14] = '試験修理営業外収益その他';
        $item_s[15] = '試験修理営業外収益計';
        $item_s[16] = '試験修理支払利息';
        $item_s[17] = '試験修理営業外費用その他';
        $item_s[18] = '試験修理営業外費用計';
        $item_s[19] = '試験修理経常利益';
        $num = count($item_s);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = getQueryStatement16($target_ym, $item_s[$r]);
            if (($rows_t = getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $s_uri = 0;
                } elseif ($r == 1) {
                    $s_invent = 0;
                } elseif ($r == 2) {
                    $s_metarial = 0;
                } elseif ($r == 3) {
                    $s_roumu = 0;
                } elseif ($r == 4) {
                    $s_expense = 0;
                } elseif ($r == 5) {
                    $s_endinv = 0;
                } elseif ($r == 6) {
                    $s_urigen = 0;
                } elseif ($r == 7) {
                    $s_gross_profit = 0;
                } elseif ($r == 8) {
                    $s_han_jin = 0;
                } elseif ($r == 9) {
                    $s_han_kei = 0;
                } elseif ($r == 10) {
                    $s_han_all = 0;
                } elseif ($r == 11) {
                    $s_ope_profit = 0;
                } elseif ($r == 12) {
                    $s_gyoumu = 0;
                } elseif ($r == 13) {
                    $s_swari = 0;
                } elseif ($r == 14) {
                    $s_pother = 0;
                } elseif ($r == 15) {
                    $s_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $s_srisoku = 0;
                } elseif ($r == 17) {
                    $s_lother = 0;
                } elseif ($r == 18) {
                    $s_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $s_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $s_uri = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 1) {
                    $s_invent = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 2) {
                    $s_metarial = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 3) {
                    $s_roumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 4) {
                    $s_expense = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 5) {
                    $s_endinv = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 6) {
                    $s_urigen = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 7) {
                    $s_gross_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 8) {
                    $s_han_jin = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 9) {
                    $s_han_kei = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 10) {
                    $s_han_all = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 11) {
                    $s_ope_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 12) {
                    $s_gyoumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 13) {
                    $s_swari = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 14) {
                    $s_pother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 15) {
                    $s_nonope_profit_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 16) {
                    $s_srisoku = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 17) {
                    $s_lother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 18) {
                    $s_nonope_loss_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 19) {
                    $s_current_profit = round(($res_t[0][0] / 12), 0);
                }
            }
        }
        // 商管・試修は暫定的に過去１年間の平均で計算
        // 率は、計算式を変更したときのために初期化して戻す。
        $rate_s     = array();
        $rate_s[0]  = 0;
        $rate_s[1]  = 0;
        $rate_s[2]  = 0;
        $rate_s[3]  = 0;
        $rate_s[4]  = 0;
        $rate_s[5]  = 0;
        $rate_s[6]  = 0;
        $rate_s[7]  = 0;
        $rate_s[8]  = 0;
        $rate_b     = array();
        $rate_b[0]  = 0;
        $rate_b[1]  = 0;
        $rate_b[2]  = 0;
        $rate_b[3]  = 0;
        $rate_b[4]  = 0;
        $rate_b[5]  = 0;
        $rate_b[6]  = 0;
        $rate_b[7]  = 0;
        $rate_b[8]  = 0;
        
        // 期末棚卸高の計算
        $c_endinv = -($c_invent + $c_endinv);
        $l_endinv = -($l_invent + $l_endinv);
        // 売上原価の計算
        $c_urigen = $c_invent + $c_metarial + $c_roumu + $c_expense + $c_endinv;
        $l_urigen = $l_invent + $l_metarial + $l_roumu + $l_expense + $l_endinv;
        $s_urigen = $s_invent + $s_metarial + $s_roumu + $s_expense + $s_endinv;
        $b_urigen = $b_invent + $b_metarial + $b_roumu + $b_expense + $b_endinv;
        // 売上総利益の計算
        $c_gross_profit = $c_uri - $c_urigen;
        $l_gross_profit = $l_uri - $l_urigen;
        $s_gross_profit = $s_uri - $s_urigen;
        $b_gross_profit = $b_uri - $b_urigen;
        // 販管費合計の計算
        $c_han_all = $c_han_jin + $c_han_kei;
        $l_han_all = $l_han_jin + $l_han_kei;
        $s_han_all = $s_han_jin + $s_han_kei;
        $b_han_all = $b_han_jin + $b_han_kei;
        // 営業利益の計算
        $c_ope_profit = $c_gross_profit - $c_han_all;
        $l_ope_profit = $l_gross_profit - $l_han_all;
        $s_ope_profit = $s_gross_profit - $s_han_all;
        $b_ope_profit = $b_gross_profit - $b_han_all;
        // 営業外収益計の計算
        $c_nonope_profit_sum = $c_gyoumu + $c_swari + $c_pother;
        $l_nonope_profit_sum = $l_gyoumu + $l_swari + $l_pother;
        $s_nonope_profit_sum = $s_gyoumu + $s_swari + $s_pother;
        $b_nonope_profit_sum = $b_gyoumu + $b_swari + $b_pother;
        // 営業外費用計の計算
        $c_nonope_loss_sum = $c_srisoku + $c_lother;
        $l_nonope_loss_sum = $l_srisoku + $l_lother;
        $s_nonope_loss_sum = $s_srisoku + $s_lother;
        $b_nonope_loss_sum = $b_srisoku + $b_lother;
        // 経常利益の計算
        $c_current_profit = $c_ope_profit + $c_nonope_profit_sum - $c_nonope_loss_sum;
        $l_current_profit = $l_ope_profit + $l_nonope_profit_sum - $l_nonope_loss_sum;
        $s_current_profit = $s_ope_profit + $s_nonope_profit_sum - $s_nonope_loss_sum;
        $b_current_profit = $b_ope_profit + $b_nonope_profit_sum - $b_nonope_loss_sum;
        
        // 各合計の計算
        $all_uri               = $c_uri + $l_uri + $s_uri + $b_uri;                         // 売上高合計
        $all_invent            = $c_invent + $l_invent + $s_invent + $b_invent;             // 期首棚卸高合計
        $all_metarial          = $c_metarial + $l_metarial + $s_metarial + $b_metarial;     // 材料費合計
        $all_roumu             = $c_roumu + $l_roumu + $s_roumu + $b_roumu;                 // 製造経費-労務費合計
        $all_expense           = $c_expense + $l_expense + $s_expense + $b_expense;         // 製造経費-経費合計
        $all_endinv            = $c_endinv + $l_endinv + $s_endinv + $b_endinv;             // 期末棚卸高合計
        $all_urigen            = $c_urigen + $l_urigen + $s_urigen + $b_urigen;             // 売上原価合計
        $all_gross_profit      = $c_gross_profit + $l_gross_profit + $s_gross_profit + $b_gross_profit;                     // 売上総利益合計
        $all_han_jin           = $c_han_jin + $l_han_jin + $s_han_jin + $b_han_jin;         // 販管費-人件費合計
        $all_han_kei           = $c_han_kei + $l_han_kei + $s_han_kei + $b_han_kei;         // 販管費-経費合計
        $all_han_all           = $c_han_all + $l_han_all + $s_han_all + $b_han_all;         // 販管費計 合計
        $all_ope_profit        = $c_ope_profit + $l_ope_profit + $s_ope_profit + $b_ope_profit;                             // 営業利益合計
        $all_gyoumu            = $c_gyoumu + $l_gyoumu + $s_gyoumu + $b_gyoumu;             // 営業外収益-業務委託収入合計
        $all_swari             = $c_swari + $l_swari + $s_swari + $b_swari;                 // 営業外収益-仕入割引合計
        $all_pother            = $c_pother + $l_pother + $s_pother + $b_pother;             // 営業外収益-その他合計
        $all_nonope_profit_sum = $c_nonope_profit_sum + $l_nonope_profit_sum + $s_nonope_profit_sum + $b_nonope_profit_sum; // 営業外収益計 合計
        $all_srisoku           = $c_srisoku + $l_srisoku + $s_srisoku + $b_srisoku;         // 営業外費用-支払利息合計
        $all_lother            = $c_lother + $l_lother + $s_lother + $b_lother;             // 営業外費用-その他
        $all_nonope_loss_sum   = $c_nonope_loss_sum + $l_nonope_loss_sum + $s_nonope_loss_sum + $b_nonope_loss_sum;         // 営業外費用計 合計
        $all_current_profit    = $c_current_profit + $l_current_profit + $s_current_profit + $b_current_profit;             // 経常利益 合計
        
// DB登録用アイテムの設定
// カプラ
$item_c = array();
$item_c[0]  = 'カプラ売上高';
$item_c[1]  = 'カプラ期首材料仕掛品棚卸高';
$item_c[2]  = 'カプラ材料費(仕入高)';
$item_c[3]  = 'カプラ労務費';
$item_c[4]  = 'カプラ製造経費';
$item_c[5]  = 'カプラ期末材料仕掛品棚卸高';
$item_c[6]  = 'カプラ売上原価';
$item_c[7]  = 'カプラ売上総利益';
$item_c[8]  = 'カプラ人件費';
$item_c[9]  = 'カプラ経費';
$item_c[10] = 'カプラ販管費及び一般管理費計';
$item_c[11] = 'カプラ営業利益';
$item_c[12] = 'カプラ業務委託収入';
$item_c[13] = 'カプラ仕入割引';
$item_c[14] = 'カプラ営業外収益その他';
$item_c[15] = 'カプラ営業外収益計';
$item_c[16] = 'カプラ支払利息';
$item_c[17] = 'カプラ営業外費用その他';
$item_c[18] = 'カプラ営業外費用計';
$item_c[19] = 'カプラ経常利益';
// リニア
$item_l = array();
$item_l[0]  = 'リニア売上高';
$item_l[1]  = 'リニア期首材料仕掛品棚卸高';
$item_l[2]  = 'リニア材料費(仕入高)';
$item_l[3]  = 'リニア労務費';
$item_l[4]  = 'リニア製造経費';
$item_l[5]  = 'リニア期末材料仕掛品棚卸高';
$item_l[6]  = 'リニア売上原価';
$item_l[7]  = 'リニア売上総利益';
$item_l[8]  = 'リニア人件費';
$item_l[9]  = 'リニア経費';
$item_l[10] = 'リニア販管費及び一般管理費計';
$item_l[11] = 'リニア営業利益';
$item_l[12] = 'リニア業務委託収入';
$item_l[13] = 'リニア仕入割引';
$item_l[14] = 'リニア営業外収益その他';
$item_l[15] = 'リニア営業外収益計';
$item_l[16] = 'リニア支払利息';
$item_l[17] = 'リニア営業外費用その他';
$item_l[18] = 'リニア営業外費用計';
$item_l[19] = 'リニア経常利益';
// 商品管理
$item_b = array();
$item_b[0]  = '商品管理売上高';
$item_b[1]  = '商品管理期首材料仕掛品棚卸高';
$item_b[2]  = '商品管理材料費(仕入高)';
$item_b[3]  = '商品管理労務費';
$item_b[4]  = '商品管理製造経費';
$item_b[5]  = '商品管理期末材料仕掛品棚卸高';
$item_b[6]  = '商品管理売上原価';
$item_b[7]  = '商品管理売上総利益';
$item_b[8]  = '商品管理人件費';
$item_b[9]  = '商品管理経費';
$item_b[10] = '商品管理販管費及び一般管理費計';
$item_b[11] = '商品管理営業利益';
$item_b[12] = '商品管理業務委託収入';
$item_b[13] = '商品管理仕入割引';
$item_b[14] = '商品管理営業外収益その他';
$item_b[15] = '商品管理営業外収益計';
$item_b[16] = '商品管理支払利息';
$item_b[17] = '商品管理営業外費用その他';
$item_b[18] = '商品管理営業外費用計';
$item_b[19] = '商品管理経常利益';
// 試験修理
$item_s = array();
$item_s[0]  = '試験修理売上高';
$item_s[1]  = '試験修理期首材料仕掛品棚卸高';
$item_s[2]  = '試験修理材料費(仕入高)';
$item_s[3]  = '試験修理労務費';
$item_s[4]  = '試験修理製造経費';
$item_s[5]  = '試験修理期末材料仕掛品棚卸高';
$item_s[6]  = '試験修理売上原価';
$item_s[7]  = '試験修理売上総利益';
$item_s[8]  = '試験修理人件費';
$item_s[9]  = '試験修理経費';
$item_s[10] = '試験修理販管費及び一般管理費計';
$item_s[11] = '試験修理営業利益';
$item_s[12] = '試験修理業務委託収入';
$item_s[13] = '試験修理仕入割引';
$item_s[14] = '試験修理営業外収益その他';
$item_s[15] = '試験修理営業外収益計';
$item_s[16] = '試験修理支払利息';
$item_s[17] = '試験修理営業外費用その他';
$item_s[18] = '試験修理営業外費用計';
$item_s[19] = '試験修理経常利益';
// 全体
$item_a = array();
$item_a[0]  = '全体売上高';
$item_a[1]  = '全体期首材料仕掛品棚卸高';
$item_a[2]  = '全体材料費(仕入高)';
$item_a[3]  = '全体労務費';
$item_a[4]  = '全体製造経費';
$item_a[5]  = '全体期末材料仕掛品棚卸高';
$item_a[6]  = '全体売上原価';
$item_a[7]  = '全体売上総利益';
$item_a[8]  = '全体人件費';
$item_a[9]  = '全体経費';
$item_a[10] = '全体販管費及び一般管理費計';
$item_a[11] = '全体営業利益';
$item_a[12] = '全体業務委託収入';
$item_a[13] = '全体仕入割引';
$item_a[14] = '全体営業外収益その他';
$item_a[15] = '全体営業外収益計';
$item_a[16] = '全体支払利息';
$item_a[17] = '全体営業外費用その他';
$item_a[18] = '全体営業外費用計';
$item_a[19] = '全体経常利益';
// DB登録用データの設定
// カプラ
$pl_data_c = array();
$pl_data_c[0]  = $c_uri;
$pl_data_c[1]  = $c_invent;
$pl_data_c[2]  = $c_metarial;
$pl_data_c[3]  = $c_roumu;
$pl_data_c[4]  = $c_expense;
$pl_data_c[5]  = $c_endinv;
$pl_data_c[6]  = $c_urigen;
$pl_data_c[7]  = $c_gross_profit;
$pl_data_c[8]  = $c_han_jin;
$pl_data_c[9]  = $c_han_kei;
$pl_data_c[10] = $c_han_all;
$pl_data_c[11] = $c_ope_profit;
$pl_data_c[12] = $c_gyoumu;
$pl_data_c[13] = $c_swari;
$pl_data_c[14] = $c_pother;
$pl_data_c[15] = $c_nonope_profit_sum;
$pl_data_c[16] = $c_srisoku;
$pl_data_c[17] = $c_lother;
$pl_data_c[18] = $c_nonope_loss_sum;
$pl_data_c[19] = $c_current_profit;
// リニア
$pl_data_l = array();
$pl_data_l[0]  = $l_uri;
$pl_data_l[1]  = $l_invent;
$pl_data_l[2]  = $l_metarial;
$pl_data_l[3]  = $l_roumu;
$pl_data_l[4]  = $l_expense;
$pl_data_l[5]  = $l_endinv;
$pl_data_l[6]  = $l_urigen;
$pl_data_l[7]  = $l_gross_profit;
$pl_data_l[8]  = $l_han_jin;
$pl_data_l[9]  = $l_han_kei;
$pl_data_l[10] = $l_han_all;
$pl_data_l[11] = $l_ope_profit;
$pl_data_l[12] = $l_gyoumu;
$pl_data_l[13] = $l_swari;
$pl_data_l[14] = $l_pother;
$pl_data_l[15] = $l_nonope_profit_sum;
$pl_data_l[16] = $l_srisoku;
$pl_data_l[17] = $l_lother;
$pl_data_l[18] = $l_nonope_loss_sum;
$pl_data_l[19] = $l_current_profit;
// 商品管理
$pl_data_b = array();
$pl_data_b[0]  = $b_uri;
$pl_data_b[1]  = $b_invent;
$pl_data_b[2]  = $b_metarial;
$pl_data_b[3]  = $b_roumu;
$pl_data_b[4]  = $b_expense;
$pl_data_b[5]  = $b_endinv;
$pl_data_b[6]  = $b_urigen;
$pl_data_b[7]  = $b_gross_profit;
$pl_data_b[8]  = $b_han_jin;
$pl_data_b[9]  = $b_han_kei;
$pl_data_b[10] = $b_han_all;
$pl_data_b[11] = $b_ope_profit;
$pl_data_b[12] = $b_gyoumu;
$pl_data_b[13] = $b_swari;
$pl_data_b[14] = $b_pother;
$pl_data_b[15] = $b_nonope_profit_sum;
$pl_data_b[16] = $b_srisoku;
$pl_data_b[17] = $b_lother;
$pl_data_b[18] = $b_nonope_loss_sum;
$pl_data_b[19] = $b_current_profit;
// 試験修理
$pl_data_s = array();
$pl_data_s[0]  = $s_uri;
$pl_data_s[1]  = $s_invent;
$pl_data_s[2]  = $s_metarial;
$pl_data_s[3]  = $s_roumu;
$pl_data_s[4]  = $s_expense;
$pl_data_s[5]  = $s_endinv;
$pl_data_s[6]  = $s_urigen;
$pl_data_s[7]  = $s_gross_profit;
$pl_data_s[8]  = $s_han_jin;
$pl_data_s[9]  = $s_han_kei;
$pl_data_s[10] = $s_han_all;
$pl_data_s[11] = $s_ope_profit;
$pl_data_s[12] = $s_gyoumu;
$pl_data_s[13] = $s_swari;
$pl_data_s[14] = $s_pother;
$pl_data_s[15] = $s_nonope_profit_sum;
$pl_data_s[16] = $s_srisoku;
$pl_data_s[17] = $s_lother;
$pl_data_s[18] = $s_nonope_loss_sum;
$pl_data_s[19] = $s_current_profit;
// 全体
$pl_data_a = array();
$pl_data_a[0]  = $all_uri;
$pl_data_a[1]  = $all_invent;
$pl_data_a[2]  = $all_metarial;
$pl_data_a[3]  = $all_roumu;
$pl_data_a[4]  = $all_expense;
$pl_data_a[5]  = $all_endinv;
$pl_data_a[6]  = $all_urigen;
$pl_data_a[7]  = $all_gross_profit;
$pl_data_a[8]  = $all_han_jin;
$pl_data_a[9]  = $all_han_kei;
$pl_data_a[10] = $all_han_all;
$pl_data_a[11] = $all_ope_profit;
$pl_data_a[12] = $all_gyoumu;
$pl_data_a[13] = $all_swari;
$pl_data_a[14] = $all_pother;
$pl_data_a[15] = $all_nonope_profit_sum;
$pl_data_a[16] = $all_srisoku;
$pl_data_a[17] = $all_lother;
$pl_data_a[18] = $all_nonope_loss_sum;
$pl_data_a[19] = $all_current_profit;
$last_date = date('Y-m-d H:i:s');
$last_user = '000000';

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date 損益予測 db_connect() error \n";
    fwrite($fpa,"$log_date 損益予測 db_connect() error \n");
    fwrite($fpb,"$log_date 損益予測 db_connect() error \n");
    exit();
}

/////////// カプラ予測データ登録
$up_flg = 0;
$num = count($item_c);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_c[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[0], $item_c[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[1], $item_c[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[2], $item_c[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[3], $item_c[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[4], $item_c[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[5], $item_c[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[6], $item_c[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[7], $item_c[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_c[$r], $rate_c[8], $item_c[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_c[$r], $item_c[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date カプラ損益予測:$target_ym : $today 分の$item_c[$r]の書込みに失敗しました!\n");
            fwrite($fpb,"$log_date カプラ損益予測:$target_ym : $today 分の$item_c[$r]の書込みに失敗しました!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[0], $target_ym, $today, $item_c[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[1], $target_ym, $today, $item_c[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[2], $target_ym, $today, $item_c[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[3], $target_ym, $today, $item_c[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[4], $target_ym, $today, $item_c[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[5], $target_ym, $today, $item_c[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[6], $target_ym, $today, $item_c[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[7], $target_ym, $today, $item_c[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $rate_c[8], $target_ym, $today, $item_c[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user='000000' WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_c[$r], $target_ym, $today, $item_c[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date カプラ損益予測:$target_ym : $today 分の$item_c[$r]のUPDATEに失敗しました!\n");
            fwrite($fpb,"$log_date カプラ損益予測:$target_ym : $today 分の$item_c[$r]のUPDATEに失敗しました!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date カプラ損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    fwrite($fpb,"$log_date カプラ損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    echo "$log_date カプラ損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n";
} else {
    fwrite($fpa,"$log_date カプラ損益予測:$target_ym : $today 分登録しました。\n");
    fwrite($fpb,"$log_date カプラ損益予測:$target_ym : $today 分登録しました。\n");
    echo "$log_date カプラ損益予測:$target_ym : $today 分登録しました。\n";
}

/////////// リニア予測データ登録
$up_flg = 0;
$num = count($item_l);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_l[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[0], $item_l[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[1], $item_l[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[2], $item_l[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[3], $item_l[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[4], $item_l[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[5], $item_l[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[6], $item_l[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[7], $item_l[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_l[$r], $rate_l[8], $item_l[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_l[$r], $item_l[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date リニア損益予測:$target_ym : $today 分の$item_l[$r]の書込みに失敗しました!\n");
            fwrite($fpb,"$log_date リニア損益予測:$target_ym : $today 分の$item_l[$r]の書込みに失敗しました!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[0], $target_ym, $today, $item_l[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[1], $target_ym, $today, $item_l[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[2], $target_ym, $today, $item_l[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[3], $target_ym, $today, $item_l[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[4], $target_ym, $today, $item_l[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[5], $target_ym, $today, $item_l[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[6], $target_ym, $today, $item_l[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[7], $target_ym, $today, $item_l[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $rate_l[8], $target_ym, $today, $item_l[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_l[$r], $target_ym, $today, $item_l[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date リニア損益予測:$target_ym : $today 分の$item_l[$r]のUPDATEに失敗しました!\n");
            fwrite($fpb,"$log_date リニア損益予測:$target_ym : $today 分の$item_l[$r]のUPDATEに失敗しました!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date リニア損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    fwrite($fpb,"$log_date リニア損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    echo "$log_date リニア損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n";
} else {
    fwrite($fpa,"$log_date リニア損益予測:$target_ym : $today 分登録しました。\n");
    fwrite($fpb,"$log_date リニア損益予測:$target_ym : $today 分登録しました。\n");
    echo "$log_date リニア損益予測:$target_ym : $today 分登録しました。\n";
}

/////////// 商品管理予測データ登録
$up_flg = 0;
$num = count($item_b);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_b[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[0], $item_b[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[1], $item_b[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[2], $item_b[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[3], $item_b[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[4], $item_b[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[5], $item_b[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[6], $item_b[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[7], $item_b[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_b[$r], $rate_b[8], $item_b[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_b[$r], $item_b[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date 商品管理損益予測:$target_ym : $today 分の$item_b[$r]の書込みに失敗しました!\n");
            fwrite($fpb,"$log_date 商品管理損益予測:$target_ym : $today 分の$item_b[$r]の書込みに失敗しました!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[0], $target_ym, $today, $item_b[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[1], $target_ym, $today, $item_b[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[2], $target_ym, $today, $item_b[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[3], $target_ym, $today, $item_b[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[4], $target_ym, $today, $item_b[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[5], $target_ym, $today, $item_b[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[6], $target_ym, $today, $item_b[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[7], $target_ym, $today, $item_b[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $rate_b[8], $target_ym, $today, $item_b[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_b[$r], $target_ym, $today, $item_b[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date 商品管理損益予測:$target_ym : $today 分の$item_b[$r]のUPDATEに失敗しました!\n");
            fwrite($fpb,"$log_date 商品管理損益予測:$target_ym : $today 分の$item_b[$r]のUPDATEに失敗しました!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date 商品管理損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    fwrite($fpb,"$log_date 商品管理損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    echo "$log_date 商品管理損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n";
} else {
    fwrite($fpa,"$log_date 商品管理損益予測:$target_ym : $today 分登録しました。\n");
    fwrite($fpb,"$log_date 商品管理損益予測:$target_ym : $today 分登録しました。\n");
    echo "$log_date 商品管理損益予測:$target_ym : $today 分登録しました。\n";
}

/////////// 試験修理予測データ登録
$up_flg = 0;
$num = count($item_s);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_s[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        if ($r == 3) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[0], $item_s[$r]);
        } elseif($r == 4) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[1], $item_s[$r]);
        } elseif($r == 8) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[2], $item_s[$r]);
        } elseif($r == 9) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[3], $item_s[$r]);
        } elseif($r == 12) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[4], $item_s[$r]);
        } elseif($r == 13) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[5], $item_s[$r]);
        } elseif($r == 14) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[6], $item_s[$r]);
        } elseif($r == 16) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[7], $item_s[$r]);
        } elseif($r == 17) {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, allo, note) VALUES (%d, %d, %d, %1.4f, '%s')", $target_ym, $today, $pl_data_s[$r], $rate_s[8], $item_s[$r]);
        } else {
            $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_s[$r], $item_s[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date 試験修理損益予測:$target_ym : $today 分の$item_s[$r]の書込みに失敗しました!\n");
            fwrite($fpb,"$log_date 試験修理損益予測:$target_ym : $today 分の$item_s[$r]の書込みに失敗しました!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        if ($r == 3) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[0], $target_ym, $today, $item_s[$r]);
        } elseif($r == 4) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[1], $target_ym, $today, $item_s[$r]);
        } elseif($r == 8) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[2], $target_ym, $today, $item_s[$r]);
        } elseif($r == 9) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[3], $target_ym, $today, $item_s[$r]);
        } elseif($r == 12) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[4], $target_ym, $today, $item_s[$r]);
        } elseif($r == 13) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[5], $target_ym, $today, $item_s[$r]);
        } elseif($r == 14) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[6], $target_ym, $today, $item_s[$r]);
        } elseif($r == 16) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[7], $target_ym, $today, $item_s[$r]);
        } elseif($r == 17) {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, allo=%1.4f, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $rate_s[8], $target_ym, $today, $item_s[$r]);
        } else {
            $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_s[$r], $target_ym, $today, $item_s[$r]);
        }
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date 試験修理損益予測:$target_ym : $today 分の$item_s[$r]のUPDATEに失敗しました!\n");
            fwrite($fpb,"$log_date 試験修理損益予測:$target_ym : $today 分の$item_s[$r]のUPDATEに失敗しました!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date 試験修理損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    fwrite($fpb,"$log_date 試験修理損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    echo "$log_date 試験修理損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n";
} else {
    fwrite($fpa,"$log_date 試験修理損益予測:$target_ym : $today 分登録しました。\n");
    fwrite($fpb,"$log_date 試験修理損益予測:$target_ym : $today 分登録しました。\n");
    echo "$log_date 試験修理損益予測:$target_ym : $today 分登録しました。\n";
}
/////////// 全体予測データ登録
$up_flg = 0;
$num = count($item_a);
for ($r=0; $r<$num; $r++) {
    $query = sprintf("SELECT kin FROM act_pl_estimate WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $target_ym, $today, $item_a[$r]);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        ////////// Insert Start
        $query = sprintf("INSERT INTO act_pl_estimate (target_ym, cal_ymd, kin, note) VALUES (%d, %d, %d, '%s')", $target_ym, $today, $pl_data_a[$r], $item_a[$r]);
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date 全体損益予測:$target_ym : $today 分の$item_a[$r]の書込みに失敗しました!\n");
            fwrite($fpb,"$log_date 全体損益予測:$target_ym : $today 分の$item_a[$r]の書込みに失敗しました!\n");
            $up_flg = 1;
        }
    } else {
        ////////// UPDATE Start
        $query = sprintf("UPDATE act_pl_estimate SET kin=%d, last_date=CURRENT_TIMESTAMP, last_user={$last_user} WHERE target_ym=%d AND cal_ymd=%d AND note='%s'", $pl_data_a[$r], $target_ym, $today, $item_a[$r]);
        if (query_affected_trans($con, $query) <= 0) {
            fwrite($fpa,"$log_date 全体損益予測:$target_ym : $today 分の$item_a[$r]のUPDATEに失敗しました!\n");
            fwrite($fpb,"$log_date 全体損益予測:$target_ym : $today 分の$item_a[$r]のUPDATEに失敗しました!\n");
            $up_flg = 1;
        }
    }
}
if ($up_flg == 1) {
    fwrite($fpa,"$log_date 全体損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    fwrite($fpb,"$log_date 全体損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n");
    echo "$log_date 全体損益予測:$target_ym : $today 分の一部が正しく登録されませんでした。\n";
} else {
    fwrite($fpa,"$log_date 全体損益予測:$target_ym : $today 分登録しました。\n");
    fwrite($fpb,"$log_date 全体損益予測:$target_ym : $today 分登録しました。\n");
    echo "$log_date 全体損益予測:$target_ym : $today 分登録しました。\n";
}

/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

exit();

    ///// List部   一覧表のSQLステートメント取得
    // 売上高と期末棚卸高の一部を取得(CL共通) 対象月の売上計画より未完成分を取得
    // getQueryStatement1：当月完成予定のうち、全打ち切り、完成済分以外。使用材料を総材料費より。
    function getQueryStatement1($target_ym, $today, $div)
    {
        //$str_date = $target_ym . '01';
        // 2011/08/30 予測精度向上の為 売上高の取得方法を変更
        // これまでは、組立日程計画のみで予測していたが
        // 前日までの売上実績＋当日～月末までの組立日程計画の合算へ変更
        // 未完成分の開始日は月初とし、完了分は計算から除外する
        $str_date  = $target_ym . '01';
        // 過去データを作り直す際は上記と入れ替える
        //$str_date = $today;
        $end_date = $target_ym . '31';
        /*if ($div == 'C') {
            if ($target_ym < 200710) {
                $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
            } elseif ($target_ym < 201104) {
                $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
            } else {
                $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
            }
        } elseif ($div == 'L') {
            if ($target_ym < 200710) {
                $rate = 37.00;  // リニア 2008/10/01価格改定以前
            } elseif ($target_ym < 201104) {
                $rate = 44.00;  // リニア 2008/10/01価格改定以降
            } else {
                $rate = 53.00;  // リニア 2011/04/01価格改定以降
            }
        } else {
            $rate = 65.00;
        }*/
        /*$query = "SELECT  
                    a.plan_no       AS 計画番号,
                    a.parts_no      AS 部品番号,
                    a.kanryou       AS 完了予定日,
                    a.plan          AS 計画数,
                    a.cut_plan      AS 打切数,
                    a.kansei        AS 完成数,
                    (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 最新総材料費,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * (a.plan-a.cut_plan), 0)
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (a.plan-a.cut_plan), 0) 
                    END             AS 材料費金額,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS 最新仕切単価,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan), 0)
                    END
                                    AS 売上高
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}'
                    AND (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
        ";
        */
        // 2011/08/30 仕切単価が存在しない場合、売上が計算されなかった為
        // その際は最真相材料費の1.13倍で仕切単価を計算し、売上を計算するように変更
        // また最新総材料費の取得時、WHEN時に対象月末までの最新を抜き出しているがplan_no = u.計画番号に変更
        // 2011/09/05 材料費は在庫に入るときに管理費が追加されるため、1.026を掛けて計算する
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "SELECT
                    a.plan_no       AS 計画番号,
                    a.parts_no      AS 部品番号,
                    a.kanryou       AS 完了予定日,
                    a.plan          AS 計画数,
                    a.cut_plan      AS 打切数,
                    a.kansei        AS 完成数,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                 ELSE
                                     Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                             END
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                    END             AS 最新総材料費,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan-a.kansei), 0)
                                 ELSE
                                     Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan-a.kansei), 0)
                             END
                        ELSE
                             Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * (a.plan-a.cut_plan-a.kansei), 0) 
                    END             AS 材料費金額,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)
                            END

                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS 最新仕切単価,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN 
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan-a.kansei), 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan-a.kansei), 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)  * (a.plan-a.cut_plan-a.kansei), 0) 
                            END
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan-a.kansei), 0)
                    END
                                    AS 売上高
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}'
                    AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F'
        ";
        return $query;
    }
    
    // 期首棚卸高の取得(前月の期末棚卸高 CL共通)
    function getQueryStatement2($target_ym, $div)
    {
        if ($div == 'C') {
            $div_note = 'カプラ期末材料仕掛品棚卸高';
        } else {
            $div_note = 'リニア標準期末材料仕掛品棚卸高';
            //$div_note = 'リニア期末材料仕掛品棚卸高';
        }
        if (substr($target_ym,4,2)!=01) {
            $p1_ym = $target_ym - 1;
        } else {
            $p1_ym = $target_ym - 100;
            $p1_ym = $p1_ym + 11;
        }
        $query = "
            SELECT kin FROM profit_loss_pl_history
            WHERE pl_bs_ym={$p1_ym} AND note='{$div_note}'
        ";
        return $query;
    }
    
    // 材料費の取得１(CL共通) 
    // getQueryStatement3：買掛実績より科目5以上、栃木日東工器(01111)と特注(00222)の内作は除く
    function getQueryStatement3($target_ym, $div)
    {
        $str_date = $target_ym . '01';
        $end_date = $target_ym . '31';
        // 科目６以上が入っていたため５まで変更
        /*
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222'
        ";
        */
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222' AND kamoku<=5
        ";
        return $query;
    }
    
    // 材料費の取得２(CL共通)
    // getQueryStatement4：検査仕掛分(未検収件数)の合計を取得 内作は除く
    function getQueryStatement4($div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%' and vendor !='01111' and vendor !='00222'
        ";
        return $query;
    }
    
    // 材料費の取得３(CL共通)
    // getQueryStatement5：検査仕掛分(納期遅れ)の合計を取得 内作は除く
    function getQueryStatement5($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today;
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
        ";
        return $query;
    }
    
    // 材料費の取得４(CL共通)
    // getQueryStatement6：検査仕掛分(未納入分)の合計を取得 内作は除く
    function getQueryStatement6($target_ym, $today, $div)
    {
        $end_date = $target_ym;
        $end_date = $end_date . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > $str_date
                    AND
                    proc.delivery <= $end_date
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // 材料費の取得５(CL共通)
    // getQueryStatement7：検査仕掛分 次工程品(注文書未発行) 定納期遅れ分
    function getQueryStatement7($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today;
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
        ";
        return $query;
    }
    
    // 材料費の取得６(CL共通)
    // getQueryStatement7：検査仕掛分 次工程品(注文書未発行) 未納入分
    function getQueryStatement8($target_ym, $today, $div)
    {
        $end_date = $target_ym . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // 期末棚卸高の取得１(CL共通)   // 前日までの買掛金額
    function getQueryStatement9($target_ym, $div)
    {
        $str_date = $target_ym . '01';
        $end_date = $target_ym . '31';
        // 科目６以上が入っていたため５まで変更
        /*
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' 
        ";
        */
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' and kamoku<=5
        ";
        return $query;
    }
    
    // 期末棚卸高の取得２(CL共通) 検査仕掛分(未検収件数)の合計を取得
    function getQueryStatement10($div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%'
        ";
        return $query;
    }
    
    // 期末棚卸高の取得３(CL共通) 納期遅れ分の合計を取得
    function getQueryStatement11($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today - 1;
        $query = "
            SELECT sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
        ";
        return $query;
    }
    
    // 期末棚卸高の取得４(CL共通) 本日以降のサマリーを取得
    function getQueryStatement12($target_ym, $today, $div)
    {
        $end_date = $target_ym . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    // 期末棚卸高の取得５(CL共通) 次工程品(注文書未発行) 納期遅れ分の合計を取得
    function getQueryStatement13($target_ym, $today, $div)
    {
        $str_date = $target_ym - 200;
        $str_date = $str_date . '01';
        $end_date = $today - 1;
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
        ";
        return $query;
    }
    // 期末棚卸高の取得６(CL共通) 次工程品(注文書未発行) 本日以降のサマリーを取得
    function getQueryStatement14($target_ym, $today, $div)
    {
        $end_date = $target_ym . '31';
        $str_date = $today;
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    ///// 部品・その他売上高、材料費の取得
    // getQueryStatement15：部品売上高（区分：2以上）前月から6か月分の売上高の平均、材料費も合わせて。
    function getQueryStatement15($target_ym, $div)
    {
        $end_date = $target_ym;
        $str_date = $target_ym;
        if (substr($str_date,4,2)>=07) {
            $str_date = $str_date - 6;
            $str_date = $str_date . '01';
        } else {
            $str_date = $str_date - 100;
            $str_date = $str_date + 6;
            $str_date = $str_date . '01';
        }
        if (substr($end_date,4,2)!=01) {
            $end_date = $end_date - 1;
            $end_date = $end_date . '31';
        } else {
            $end_date = $end_date - 100;
            $end_date = $end_date + 11;
            $end_date = $end_date . '31';
        }
        $query = "
            SELECT
                Uround(sum(Uround(数量*単価, 0)) / 6, 0)         AS 部品売上高
                ,
                Uround(sum(Uround(数量*ext_cost, 0)) / 6, 0)       AS 外作部品費
                ,
                Uround(sum(Uround(数量*int_cost, 0)) / 6, 0)      AS 内作部品費
                ,
                Uround(sum(Uround(数量*unit_cost, 0)) / 6, 0)      AS 合計部品費
                ,
                count(*)                            AS 総件数
                ,
                count(*)-count(unit_cost)
                                                    AS 未登録
            FROM
                hiuuri
            LEFT OUTER JOIN
                sales_parts_material_history ON (assyno=parts_no AND 計上日=sales_date)
            WHERE 計上日 >= {$str_date} AND 計上日 <= {$end_date}
             AND 事業部 = '{$div}' AND (assyno not like 'NKB%%') AND (assyno not like 'SS%%')
             AND datatype >= '2' 
        ";
        return $query;
    }
    ///// 労務費・経費金額取得
    function getQueryStatement16($target_ym, $note_name)
    {
        
            $end_date = $target_ym;
            $str_date = $target_ym;
            if (substr($str_date,4,2)==12) {
                $str_date = $str_date - 11;
            } else {
                $str_date = $str_date - 99;
            }
            if (substr($end_date,4,2)!=01) {
                $end_date = $end_date - 1;
            } else {
                $end_date = $end_date - 100;
                $end_date = $end_date + 11;
            }
            $query = "
                SELECT sum(kin) FROM profit_loss_pl_history
                    WHERE pl_bs_ym<={$end_date} AND pl_bs_ym>={$str_date} AND note='{$note_name}'
        ";
        return $query;
    }
    // 売上高と期末棚卸高の一部を取得(CL共通) 売上明細より
    // getQueryStatement17：前日までの売上高（完成のみ）、使用材料を総材料費より。
    function getQueryStatement17($target_ym, $today, $div)
    {
        $str_date  = $target_ym . '01';
        $end_date  = $today;
        if (substr($end_date,6,2)!=01) {
            $end_date  = $end_date - 1;
        }
        $cost_date = $target_ym . '31';
        /*if ($div == 'C') {
            if ($target_ym < 200710) {
                $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
            } elseif ($target_ym < 201104) {
                $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
            } else {
                $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
            }
        } elseif ($div == 'L') {
            if ($target_ym < 200710) {
                $rate = 37.00;  // リニア 2008/10/01価格改定以前
            } elseif ($target_ym < 201104) {
                $rate = 44.00;  // リニア 2008/10/01価格改定以降
            } else {
                $rate = 53.00;  // リニア 2011/04/01価格改定以降
            }
        } else {
            $rate = 65.00;
        }*/
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "select
                        u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '完成'
                                WHEN u.datatype=2 THEN '個別'
                                WHEN u.datatype=3 THEN '手打'
                                WHEN u.datatype=4 THEN '調整'
                                WHEN u.datatype=5 THEN '移動'
                                WHEN u.datatype=6 THEN '直納'
                                WHEN u.datatype=7 THEN '売上'
                                WHEN u.datatype=8 THEN '振替'
                                WHEN u.datatype=9 THEN '受注'
                                ELSE u.datatype
                            END             as 区分,                    -- 1
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 3
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 4
                            u.数量          as 数量,                    -- 5
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                            END             AS 最新総材料費,            -- 6
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * u.数量, 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * u.数量, 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * u.数量, 0)
                            END             AS 材料費金額,              -- 7
                            u.単価          as 仕切単価,                -- 8
                            Uround(u.数量 * u.単価, 0) as 金額          -- 9
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.計画番号=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.計画番号=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.計上日=pmate.sales_date) 
                      where 計上日>={$str_date} and 計上日<={$end_date} and 事業部='{$div}' and datatype='1'
                      order by u.計上日, assyno
        ";
        return $query;
    }
?>
