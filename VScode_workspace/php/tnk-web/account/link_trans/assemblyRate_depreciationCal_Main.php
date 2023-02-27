<?php
//////////////////////////////////////////////////////////////////////////////
// 組立賃率 減価償却費表示メニュー assemblyRate_depreciationCal_Main.php    //
//                                 (旧 wage_depreciation_cal.php)           //
// Copyright (C) 2007-2013 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/14 Created  assemblyRate_depreciationCal_Main.php                //
//            旧ファイルより各処理を関数化 コメントの位置の調整             //
//            余分な<font>タグの削除                                        //
// 2007/12/29 日付データの戻り値を設定                                      //
// 2008/01/09 固定資産の並び順に固定資産Noでのソートを追加                  //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
// 2012/10/05 均等償却のロジックを変更                                      //
// 2013/01/10 コンプライアンス室指摘により、大幅変更                        //
//            前期末簿価に相違があるものはDB final_book_valueに簿価を登録し //
//            そこを基準に計算するようにした(2012/03末簿価)                 //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                                    // Script Name は自動取得

main();

function main()
{
    ////////// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                          // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    ////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('減価償却費の参照');

    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    
    set_date($request, $result);                        // 日付データの取得
    get_group_master($result, $request);                // グループマスターの取得
    get_leased_master($result, $request);               // リース資産マスターの取得
    get_capital_master($result, $request);              // 固定資産マスターの取得
    
    depreciationCal_main ($result);                     // 減価償却費計算メイン
    
    outViewListHTML($request, $menu, $result);          // HTML作成
    
    display($menu, $request, $result);                  // 画面表示
}

////////////// 画面表示
function display($menu, $request, $result)
{       
    ////////// ブラウザーのキャッシュ対策用
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// メッセージ出力フラグ
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
    
    ////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();
    
    ////////// Viewの処理
    require_once ("assemblyRate_depreciationCal_View.php");

    ob_end_flush(); 
}

////////////// 日付データの取得
function set_date($request, $result)
{
    if ($request->get('wage_ym') == '') {
        $wage_ym = date('Ym');           // データがない場合の初期値(前月)
        if (substr($wage_ym, 4, 2) != 01) {
            $wage_ym--;
        } else {
            $wage_ym = $wage_ym - 100;
            $wage_ym = $wage_ym + 11;    // 前年の12月にセット
        }
    } else {
        $wage_ym = $request->get('wage_ym');
    }
    $result->add('wage_ym', $wage_ym);
    date_cal($result);                   // 日付データの計算
}

////////////// 日付データの計算
function date_cal($result)
{
    ////////// 対象の年と月を取得
    $wage_y = substr($result->get('wage_ym'), 0, 4);   // 対象年を抜出
    $wage_m = substr($result->get('wage_ym'), 4, 2);   // 対象月を抜出
    ////////// 仮締め月数の計算
    $tsuki = substr($result->get('wage_ym'), 4, 2);
    if($tsuki > 3) {
        if($tsuki < 10) {
            $provisional_month = $tsuki - 3;
        } else {
            $provisional_month = $tsuki - 9;
        }
    } else {
        $provisional_month = $tsuki + 3;
    }
    $result->add('wage_y', $wage_y);
    $result->add('wage_m', $wage_m);
    $result->add('provisional_month', $provisional_month);
}

////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function get_group_master ($result, $request)
{
    $query = "
        SELECT  groupm.group_no                AS グループ番号     -- 0
            ,   groupm.group_name              AS グループ名       -- 1
        FROM
            assembly_machine_group_master AS groupm
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "グループの登録がありません！";
        $field[0]   = "グループ番号";
        $field[1]   = "グループ名";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', '');
        $result->add('num_g', 2);
        $result->add('rows_g', '');
    } else {
        $num = count($field);
        $result->add_array2('res_g', $res);
        $result->add_array2('field_g', $field);
        $result->add('num_g', $num);
        $result->add('rows_g', $rows);
    }
}

////////////// 前期末簿価をSQLで取得
function get_book_value ($result, $this_asset_no)
{
    $before_book_ym = 0;
    $before_book_ym = $result->get('wage_ym');
    $query = "
        SELECT  book_value                AS 期末簿価金額     -- 0
            ,   book_ym                   AS 期末年月         -- 1
        FROM
            final_book_value
        WHERE 
            asset_no = '{$this_asset_no}'
            AND book_ym  < $before_book_ym
        ORDER BY
            book_ym DESC
        LIMIT 1
    ";

    //$res_v = array();
    //if (getResult($query, $res_v) > 0) {   //////// 登録あり
    //    $this_book_value = $res_v[0][0];
    //}
    //return $this_book_value;
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $result->add('before_book_value', 0);
        $result->add('before_book_ym', 0);
    } else {
        $result->add('before_book_value', $res[0][0]);
        $result->add('before_book_ym', $res[0][1]);
    }
}

////////////// 表示用(一覧表)の固定資産データをSQLで取得
function get_capital_master ($result, $request)
{
    $wage_ym = $result->get('wage_ym');
    $query = "
        SELECT  groupc.group_no                AS グループ番号     -- 0
            ,   groupc.asset_no                AS 固定資産No       -- 1
            ,   cmaster.asset_name             AS 資産名称         -- 2
            ,   cmaster.acquisition_money      AS 取得金額         -- 3
            ,   cmaster.acquisition_date       AS 取得年月         -- 4
            ,   cmaster.durable_years          AS 耐用年数         -- 5
            ,   cmaster.annual_rate            AS 年間率           -- 6
            ,   cmaster.end_date               AS 除却年月         -- 7
        FROM
            assembly_machine_group_capital_asset AS groupc
        LEFT OUTER JOIN
            capital_asset_master AS cmaster
        ON (groupc.asset_no = cmaster.asset_no)
        WHERE
            cmaster.acquisition_date <= $wage_ym
            AND cmaster.end_date = 0
            OR cmaster.end_date IS NULL
            OR cmaster.end_date > $wage_ym
        ORDER BY
            group_no ASC, cmaster.asset_no ASC
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $result->add_array2('res_c', '');
        $result->add_array2('field_c', '');
        $result->add('num_c', '');
        $result->add('rows_c', '');
    } else {
        $num = count($field);
        $result->add_array2('res_c', $res);
        $result->add_array2('field_c', $field);
        $result->add('num_c', $num);
        $result->add('rows_c', $rows);
    }
    $res_g = $result->get_array2('res_g');
    for ($r=0; $r<$rows; $r++) {    // グループ番号とグループ名の置き換え(固定資産）
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_name[$r] = $res_g[$i][1];
            }
        }
    }
    $result->add_array2('group_name_c', $group_name);
}

////////////// 表示用(一覧表)のリース資産データをSQLで取得
function get_leased_master ($result, $request)
{
    $wage_ym = $result->get('wage_ym');
    $query = "
        SELECT  groupl.group_no                AS グループ番号     -- 0
            ,   groupl.asset_no                AS リース資産No     -- 1
            ,   lmaster.asset_name             AS リース名称       -- 2
            ,   lmaster.acquisition_money      AS 取得金額         -- 3
            ,   lmaster.acquisition_date       AS 取得年月         -- 4
            ,   lmaster.annual_lease_money     AS 年間リース料     -- 5
            ,   lmaster.end_date               AS 終了年月         -- 6
        FROM
            assembly_machine_group_leased_asset AS groupl
        LEFT OUTER JOIN
            leased_asset_master AS lmaster
        ON (groupl.asset_no = lmaster.asset_no)
        WHERE
            lmaster.acquisition_date <= $wage_ym
            AND lmaster.end_date = 0
            OR lmaster.end_date IS NULL
            OR lmaster.end_date > $wage_ym
        ORDER BY
            group_no
    ";
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "登録がありません！";
    } else {
        $num = count($field);
        $result->add_array2('res_l', $res);
        $result->add_array2('field_l', $field);
        $result->add('num_l', $num);
        $result->add('rows_l', $rows);
    }
    $res_g = $result->get_array2('res_g');
    for ($r=0; $r<$rows; $r++) {    // グループ番号とグループ名の置き換え(固定資産）
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_name[$r] = $res_g[$i][1];
            }
        }
    }
    $result->add_array2('group_name_l', $group_name);
}

////////////// 償却額計算メイン
function depreciationCal_main ($result)
{
    repaymentAmount_cal ($result);        // 償却額・期末帳簿金額計算
    //repaymentAmount_cal_min ($result);    // 償却額・期末帳簿金額計算（税法改正版）
    repaymentAmount_group ($result);      // 計算結果をグループ別に振分
    repaymentAmount_entry ($result);      // 減価償却費の登録
}

////////////// 償却額・期末帳簿金額計算
function repaymentAmount_cal ($result)
{
    for ($r=0; $r<$result->get('rows_c'); $r++) {                    // 個数分繰り返し
        $year5_y = 0;                                                // 均等償却用のカウント
        $res = $result->get_array2('res_c');
        $book_value[$r] = $res[$r][3];                               // 期末帳簿価格の初期値設定
        if ($res[$r][4] > $result->get('wage_ym')) {
            $acquisition_month[$r] = 0;
        } else {
            $acquisition_year[$r]  = substr($res[$r][4], 0, 4);      // 取得年月より取得年を抜出
            $acquisition_month[$r] = substr($res[$r][4], 4, 2);      // 取得年月より取得月を抜出
            if ($result->get('wage_y') == $acquisition_year[$r]) {
                if ($acquisition_month[$r] < 4 && $result->get('wage_m') > 3) {           // 同じ取得年度でも期をまたいだ場合
                    $y = 2;
                } else {
                    $y = 1;
                }
            } else if ($result->get('wage_m') < 4) { 
                // $y = $result->get('wage_y') - $acquisition_year[$r];     //償却額計算用年数
                // 上が元プログラム、下が変更
                if ($acquisition_month[$r] < 4) { 
                    $y = $result->get('wage_y') - $acquisition_year[$r] + 1;     //償却額計算用年数
                } else {
                    $y = $result->get('wage_y') - $acquisition_year[$r];     //償却額計算用年数
                }
            } else {
                // $y = $result->get('wage_y') - $acquisition_year[$r] + 1; // 償却額計算用年数
                // 上が元プログラム、下が変更
                if ($acquisition_month[$r] < 4) { 
                    $y = $result->get('wage_y') - $acquisition_year[$r] + 2; // 償却額計算用年数
                } else {
                    $y = $result->get('wage_y') - $acquisition_year[$r] + 1; // 償却額計算用年数
                }
            }
            $min_money[$r] = ceil($res[$r][3] * 0.05);              // 帳簿価格最低額計算
            if ($acquisition_month[$r] > 3) {                        // 取得年度の支払月数計算
                $repayment_month[$r] = 16 - $acquisition_month[$r];
            } else {
                $repayment_month[$r] = 4 - $acquisition_month[$r];
            }
            // ここの分岐で固定資産登録を途中変更し前期末簿価が合わないものに関して
            // 前期末簿価から償却を行うロジックを追加している。
            $this_asset_no = $res[$r][1];
            get_book_value ($result, $this_asset_no);
            $before_book_value = $result->get('before_book_value');
            $before_book_ym    = $result->get('before_book_ym');
            if ($before_book_value > 0) {
                $book_value[$r] = $before_book_value;                    // 期末帳簿価格の初期設定を期末簿価に
                // 取得日を便宜上期末簿価の(yyyy/03)の次の期の4月に設定
                $before_book_year  = substr($before_book_ym, 0, 4);      // 前期末より仮の取得年を抜出
                $before_book_month = '04';                               // 仮の取得月は4月で固定
                if ($result->get('wage_m') < 4) { 
                    $y = $result->get('wage_y') - $before_book_year;     //償却額計算用年数
                } else {
                    $y = $result->get('wage_y') - $before_book_year + 1; // 償却額計算用年数
                }
                for ($i=0; $y>$i; $i++) {
                    if ($min_money[$r] >= $book_value[$r]) {             // 期末簿価が最低額より小さい場合簿価は最低値に
                        if ($year5_y==0) {                                                      // 処理一回目のみの処理
                            $standard_repayment     = floor(($book_value[$r] - 1) / 5);         // 計算の基準となる5分の1にした減価償却費
                            $temp_book              = $book_value[$r] - $standard_repayment;    // 初年度の期末簿価
                            $repayment_amount[$r]   = $standard_repayment;                      // 今期償却額
                            $book_value[$r]         = $temp_book;                               // 当期期末簿価
                        }
                        if ($year5_y > 0 && $year5_y < 5) {                                     // 2年目以降の処理
                            $repayment_amount[$r]   = $standard_repayment;                      // 今期償却額
                            $book_value[$r]         = $book_value[$r] - $standard_repayment;    // 当期期末簿価
                        }
                        if ($year5_y > 4) {                                                     // 6年目以降の処理
                            if ($book_value[$r] > 1) {
                                $repayment_amount[$r] = $book_value[$r] - 1;
                                $book_value[$r]       = $book_value[$r] - $repayment_amount[$r];
                            } elseif($book_value[$r] == 1) {
                                $repayment_amount[$r] = 0;
                                $book_value[$r] = $book_value[$r];
                            }
                        }
                        $year5_y = $year5_y + 1;
                    } else {
                        $repayment_amount[$r] = floor($book_value[$r] * $res[$r][6]); // 償却額計算    
                        $book_value[$r] = $book_value[$r] - $repayment_amount[$r];    // 期末帳簿金額計算
                        if ($min_money[$r] > $book_value[$r]) {
                            $book_value[$r] = $min_money[$r];
                            if ($y - 1 > $i) {
                                $repayment_amount[$r] = 0;
                            }
                        }
                    }
                }
            } else {
                for ($i=0; $y>$i; $i++) {
                    if ($acquisition_month[$r] > 3) {
                        $now_y = $acquisition_year[$r] + $i;
                    } else {
                        $now_y = $acquisition_year[$r] + $i;
                    }
                    if ($min_money[$r] >= $book_value[$r]) {             // 期末簿価が最低額より小さい場合簿価は最低値に
                        if ($now_y > 2006) {
                            if ($year5_y==0) {                                                      // 処理一回目のみの処理
                                $standard_repayment     = floor(($book_value[$r] - 1) / 5);         // 計算の基準となる5分の1にした減価償却費
                                $temp_book              = $book_value[$r] - $standard_repayment;    // 初年度の期末簿価
                                $repayment_amount[$r]   = $standard_repayment;                      // 今期償却額
                                $book_value[$r]         = $temp_book;                               // 当期期末簿価
                            }
                            if ($year5_y > 0 && $year5_y < 5) {                                     // 2年目以降の処理
                                $repayment_amount[$r]   = $standard_repayment;                      // 今期償却額
                                $book_value[$r]         = $book_value[$r] - $standard_repayment;    // 当期期末簿価
                            }
                            if ($year5_y > 4) {                                                     // 6年目以降の処理
                                if ($book_value[$r] > 1) {
                                    $repayment_amount[$r] = $book_value[$r] - 1;
                                    $book_value[$r]       = $book_value[$r] - $repayment_amount[$r];
                                } elseif($book_value[$r] == 1) {
                                    $repayment_amount[$r] = 0;
                                    $book_value[$r] = $book_value[$r];
                                }
                            }
                            $year5_y = $year5_y + 1;
                        } else {
                            $book_value[$r] = $min_money[$r];
                        }
                    } else {
                        switch ($i) {
                            case 0:                                                           // 対象月と同じ年に資産登録されたものの場合
                                $repayment_amount[$r] = floor($res[$r][3] * $res[$r][6] * $repayment_month[$r] / 12); // 償却額計算
                                $book_value[$r] = $res[$r][3] - $repayment_amount[$r];        // 期末帳簿金額計算
                                if ($min_money[$r] > $book_value[$r]) {
                                    $book_value[$r] = $min_money[$r];
                                    if ($y - 1 > $i) {
                                        $repayment_amount[$r] = 0;
                                    }
                                }
                            break;
                            default:
                                $repayment_amount[$r] = floor($book_value[$r] * $res[$r][6]); // 償却額計算    
                                $book_value[$r] = $book_value[$r] - $repayment_amount[$r];    // 期末帳簿金額計算
                                if ($min_money[$r] > $book_value[$r]) {
                                    $book_value[$r] = $min_money[$r];
                                    if ($y - 1 > $i) {
                                        $repayment_amount[$r] = 0;
                                    }
                                }
                            break;
                        }
                    }
                }
            }
        }
        //$repayment_amount[$r] = $now_y;
        //$book_value[$r] = $year5_y;
    }
    $result->add_array2('repayment_amount', $repayment_amount);
    $result->add_array2('book_value', $book_value);
}

////////////// 以降200704税法改正後の対応 現在登録されている資産が200704時に簿価が5%に達している場合
////////////// 以降5年間で5分の1ずつ償却し最後は簿価が１になるように計算する。
////////////// 端数が出る場合は以下の処理で6年目に償却する。
////////////// 端数切捨て
//////////////（１００，０００－１）÷５＝１９，９９９   １９，９９９×５＝９９，９９５
////////////// なので、６年目に４円を償却して１円を残す
 
function repaymentAmount_cal_min ($result)
{
    if ($result->get('wage_ym') >= 200704) {
        for ($r=0; $r<$result->get('rows_c'); $r++) {                            // 個数分繰り返し
            $res = $result->get_array2('res_c');
            $repayment_amount = $result->get_array2('repayment_amount');
            $book_value = $result->get_array2('book_value');
            $min_money[$r] = ceil($res[$r][3] * 0.05);                          // 帳簿価格最低額計算
            if ($min_money[$r] >= $book_value[$r]) {
                if ($book_value[$r] != 1) {
                    $y = $result->get('wage_y') - 2006;                              // 償却額計算用年数
                    for ($i=0; $y>$i; $i++) { 
                        if ($i==0) {                                                            // 処理一回目のみの処理
                            $standard_repayment     = floor(($book_value[$r] - 1) / 5);         // 計算の基準となる5分の1にした減価償却費
                            $temp_book              = $book_value[$r] - $standard_repayment;    // 初年度の期末簿価
                            $repayment_amount_m[$r] = $standard_repayment;                      // 今期償却額
                            $book_value_m[$r]       = $temp_book;                               // 当期期末簿価
                        }
                        if ($i > 0 && $i < 5) {                                                 // 2年目以降の処理
                            $repayment_amount_m[$r] = $standard_repayment;                      // 今期償却額
                            $book_value_m[$r]       = $book_value_m[$r] - $standard_repayment;  // 当期期末簿価
                        }
                        if ($i > 4) {                                                           // 6年目以降の処理
                            if ($book_value_m[$r] > 1) {
                                $repayment_amount_m[$r] = $book_value_m[$r] - 1;
                                $book_value_m[$r]       = $book_value_m[$r] - $repayment_amount_m[$r];
                            } elseif($book_value_m[$r] == 1) {
                                $repayment_amount_m[$r] = 0;
                                $book_value_m[$r] = $book_value[$r];
                            }
                        }
                        //switch ($i) {
                        //    case 0:                                                  // 処理一回目のみの処理
                        //        $standard_repayment = ceil($book_value[$r] / 5);     // 計算の基準となる5分の1にした減価償却費
                        //        $temp_repayment = $standard_repayment;               // 仮の減価償却費を設定
                        //        $temp_book      = $book_value[$r] - $temp_repayment; // 仮の前期末簿価を設定
                        //        if ($temp_book <= 0) { 
                        //            $temp_repayment = $book_value[$r] - 1;
                        //            $temp_book = 1;
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        } else {
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        }
                        //    break;
                        //    default:
                        //        $temp_repayment = $standard_repayment;               // 仮の減価償却費を設定
                        //        $temp_book      = $book_value[$r] - $temp_repayment; // 仮の前期末簿価を設定
                        //        if ($temp_book <= 0) { 
                        //            $temp_repayment = $book_value[$r] - 1;
                        //            $temp_book = 1;
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        } else {
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        }
                        //    break;
                        //}
                    }
                } else {
                    $repayment_amount_m[$r] = 0;
                    $book_value_m[$r] = $book_value[$r];
                }
            } else {
                $repayment_amount_m[$r] = $repayment_amount[$r];
                $book_value_m[$r] = $book_value[$r];
            }
        }
        $result->add_array2('repayment_amount', $repayment_amount_m);
        $result->add_array2('book_value', $book_value_m);
    }
}

////////////// 計算結果をグループ別に振替
function repaymentAmount_group ($result)
{
    $res_g = $result->get_array2('res_g');
    $res_l = $result->get_array2('res_l');
    $res = $result->get_array2('res_c');
    ////////// データの初期化
    $group_money = array();
    for ($i=0; $i<$result->get('rows_c'); $i++) {
        $group_money[$i] = 0;
    }
    $group_capital = array();
    for ($i=0; $i<$result->get('rows_c'); $i++) {
        $group_capital[$i] = 0;
    }
    $group_lease = array();
    for ($i=0; $i<$result->get('rows_c'); $i++) {
        $group_lease[$i] = 0;
    }
    $repayment_amount = $result->get_array2('repayment_amount');
    for ($r=0; $r<$result->get('rows_c'); $r++) {    // 償却額をグループ別に振分
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_money[$i] += $repayment_amount[$r];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_c'); $r++) {    // グループ別履歴に渡すデータの計算用
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_capital[$i] = $group_capital[$i] + $repayment_amount[$r];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_l'); $r++) {    // 年間リース料をグループ別に振分
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res_l[$r][0] == $res_g[$i][0]) {
                $group_money[$i] = $group_money[$i] + $res_l[$r][5];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_l'); $r++) {    // グループ別履歴に渡すデータの計算用
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res_l[$r][0] == $res_g[$i][0]) {
                $group_lease[$i] = $group_lease[$i] + $res_l[$r][5];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_g'); $r++) {    // グループ別減価償却費計算
        $group_money[$r] = $group_money[$r] * $result->get('provisional_month') / 12;
        $group_capital[$r] = $group_capital[$r] * $result->get('provisional_month') / 12;
        $group_lease[$r] = $group_lease[$r] * $result->get('provisional_month') / 12;
    }
    $result->add_array2('group_money', $group_money);
    $result->add_array2('group_capital', $group_capital);
    $result->add_array2('group_lease', $group_lease);
}

////////////// グループ別の減価償却費をDB登録
function repaymentAmount_entry ($result)
{
    $res_g = $result->get_array2('res_g');
    $group_capital = $result->get_array2('group_capital');
    $group_lease = $result->get_array2('group_lease');
    $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $result->get('wage_ym'));
    $res_check = array();
    $rows_check = getResult($query,$res_check);
    if ($rows_check <= 0) {      // 賃率が登録済みかチェック賃率が登録済みの場合は減価償却費は登録しない
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                $res_g[$i][0], $result->get('wage_ym'));
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                if (query_affected($query) <= 0) {
                }
            } else {                                    //////// 登録なし INSERT 新規   
                $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                  VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                    $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
    } else {
        if ($res_check[0]['group_machine_rate'] == '') {      // 賃率が登録済みかチェック賃率が登録済みの場合は減価償却費は登録しない
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                    $res_g[$i][0], $result->get('wage_ym'));
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                    $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                    if (query_affected($query) <= 0) {
                    }
                } else {                                    //////// 登録なし INSERT 新規   
                    $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                      VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                        $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                    }
                }
            }
        }
    }
    
    $group_capital = $result->get_array2('group_capital');
    $group_lease = $result->get_array2('group_lease');
    $res_g = $result->get_array2('res_g');
    $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $result->get('wage_ym'));
    $res_check = array();
    $rows_check = getResult($query,$res_check);
    if ($rows_check <= 0) {      // 賃率が登録済みかチェック賃率が登録済みの場合は減価償却費は登録しない
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                $res_g[$i][0], $result->get('wage_ym'));
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                if (query_affected($query) <= 0) {
                }
            } else {                                    //////// 登録なし INSERT 新規   
                $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                  VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                    $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
    } else {
        if ($res_check[0]['group_machine_rate'] == '') {    // 賃率が登録済みかチェック賃率が登録済みの場合は減価償却費は登録しない
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                    $res_g[$i][0], $result->get('wage_ym'));
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                    $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                    if (query_affected($query) <= 0) {
                    }
                } else {                                    //////// 登録なし INSERT 新規   
                    $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                      VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                        $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                    }
                }
            }
        }
    }
}

////////////// 減価償却費照会画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<style type='text/css'>\n";
    $listTable .= "<!--\n";
    $listTable .= "th {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              yellow;\n";
    $listTable .= "    font-size:          10pt;\n";
    $listTable .= "    font-weight:        bold;\n";
    $listTable .= "    font-family:        monospace;\n";
    $listTable .= "}\n";
    $listTable .= "a:hover {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              white;\n";
    $listTable .= "}\n";
    $listTable .= "a:active {\n";
    $listTable .= "    background-color:   gold;\n";
    $listTable .= "    color:              black;\n";
    $listTable .= "}\n";
    $listTable .= "a {\n";
    $listTable .= "    color:   blue;\n";
    $listTable .= "}\n";
    $listTable .= "-->\n";
    $listTable .= "</style>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <!--------------- ここから本文の表を表示する -------------------->\n";
    $listTable .= "    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "    <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='7'>\n";
    $listTable .= "                ". format_date6_kan($result->get('wage_ym')) ."\n";
    $listTable .= "                    リース資産\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->\n";
    $field_l = $result->get_array2('field_l');
    for ($i=0; $i<$result->get('num_l')-1; $i++) {             // フィールド数分繰返し
        $listTable .= "                <th class='winbox' nowrap>". $field_l[$i] ."</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
    $listTable .= "            <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res_l = $result->get_array2('res_l');
    $group_name_l = $result->get_array2('group_name_l');
    for ($r=0; $r<$result->get('rows_l'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'><div class='pt10b'>". ($r + 1) ."</div></td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$result->get('num_l'); $i++) {         // レコード数分繰返し
            switch ($i) {
                case 0:     // グループ
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $group_name_l[$r] ."</div></td>\n";
                    break;
                case 1:     // 資産No.
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_l[$r][$i] ."</div></td>\n";
                    break;
                case 2:     // 名称
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_l[$r][$i] ."</div></td>\n";
                    break;
                case 3:     // 取得金額
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($res_l[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:     // 取得年月
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res_l[$r][$i]) ."</div></td>\n";
                    break;
                case 5:     // 年間リース料
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($res_l[$r][$i], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "    </table>\n";
    $listTable .= "        </td></tr>\n";
    $listTable .= "    </table> <!----------------- ダミーEnd ------------------>\n";
    $listTable .= "    <!--------------- ここから本文の表を表示する -------------------->\n";
    $listTable .= "    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "    <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='900' bgcolor='#ffffc6' align='center' colspan='10'>\n";
    $listTable .= "             ". format_date6_kan($result->get('wage_ym')) ."\n";
    $listTable .= "                固定資産\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->\n";
    $field = $result->get_array2('field_c');
    for ($i=0; $i<$result->get('num_c')-1; $i++) {             // フィールド数分繰返し
        $listTable .= "            <th class='winbox' nowrap>". $field[$i] ."</th>\n";
    }
    $listTable .= "                <th class='winbox' nowrap>償却額</th>\n";
    $listTable .= "                <th class='winbox' nowrap>期末簿価金額</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
    $listTable .= "            <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_c');
    $group_name = $result->get_array2('group_name_c');
    $repayment_amount = $result->get_array2('repayment_amount');
    $book_value = $result->get_array2('book_value');
    $group_money = $result->get_array2('group_money');
    for ($r=0; $r<$result->get('rows_c'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'><div class='pt10b'>". ($r + 1) ."</div></td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$result->get('num_c'); $i++) {         // レコード数分繰返し
            switch ($i) {
                case 0:     // グループ
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                    break;
                case 1:     // 資産No.
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:     // 名称
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:     // 取得金額
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:     // 取得年月
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res[$r][$i]) ."</div></td>\n";
                    break;
                case 5:     // 耐用年数
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 6:    // 年間率
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 7:    //除却年月
                    break;
                case 8:     // 償却額
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($repayment_amount[$r], 0) ."</div></td>\n";
                    break;
                case 9:    // 期末帳簿価格
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($book_value[$r], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "            <td class='winbox' nowrap align='center'><div class='pt9'>". number_format($repayment_amount[$r], 0) ."</div></td>\n"; //償却額の表示
        $listTable .= "            <td class='winbox' nowrap align='center'><div class='pt9'>". number_format($book_value[$r], 0) ."</div></td>\n"; //期末簿価金額の表示
        $listTable .= "        </tr>\n";
    }
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox_field' colspan='4' rowspan='10' align='right' border='1' cellspacing='0' cellpadding='3'>各グループ減価償却費合計</th>\n";
    $res_g = $result->get_array2('res_g');
    for ($i=0; $i<$result->get('rows_g'); $i++) {             // グループ数分繰返し
        $listTable .= "            <tr>\n";
        $listTable .= "<th class='winbox_field' colspan='2' align='right' border='1' cellspacing='0' cellpadding='3'>". $res_g[$i][1] ."</th>\n";
        $listTable .= "<th class='winbox_field' colspan='2' align='right' border='1' cellspacing='0' cellpadding='3'>". number_format($group_money[$i], 0) ."</th>\n";
        $listTable .= "                <th class='winbox_field' colspan='2' rowspan='1' align='right' border='1' cellspacing='0' cellpadding='3'></th>\n";
        $listTable .= "            </tr>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </TBODY>\n";
    $listTable .= "    </table>\n";
    $listTable .= "        </td></tr>\n";
    $listTable .= "    </table> <!----------------- ダミーEnd ------------------>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////// 減価償却費照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/assemblyRate_depreciationCal_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
