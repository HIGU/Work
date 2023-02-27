<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 実績 照会  new version   sales_view.php                             //
// Copyright (C) 2001-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   sales_view.php                                      //
// 2002/08/07 セッション管理を追加                                          //
// 2002/09/26 select文を left outer join on u.assyno=m.mipn に変更          //
// 2003/01/10 substr($res[$r][$n],0,38)→mb_substr($res[$r][$n],0,12)       //
//                   マルチバイトに対応させてX軸の画面内に収める            //
// 2003/06/16 合計金額・件数・数量を SQL で取得 明細は１ページ分のみ        //
//              取得に Logic を大幅に変更   事業部にバイモルを追加          //
// 2003/09/05 購買単価の登録が０の場合を考慮したロジックへ変更              //
//            総材料費の登録が０の場合も同様(既に対応済み)                  //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/10/31 個別 製品番号 指定 追加  事業部にカプラ特注を追加             //
// 2003/11/26 デザインとロジックを一新 view_uriage.php → sales_view.php    //
// 2003/11/28 Ｃ特注の販売価格を追加 left outerをassymblyに対してjoinし     //
//            on結合は plan_noだけで行う indexも plan_no だけに変更         //
// 2003/12/11 Ｃ特注の場合の製品名 width='150' → width='170' へ変更        //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用し管理する      //
// 2003/12/17 Ｃ特注の総材料費のチェックロジックを追加 (総材料費入力中)     //
// 2003/12/19 総材料費照会のリンクロジックを作成 現在はＣ特注のみ           //
//            $_SESSION['offset']→$_SESSION['sales_offset']に  元の頁へ戻る//
// 2003/12/22 製品名の全角カナ英数字を半角カナ英数字へtest的にコンバート    //
//            Ｃ特注以外も総材料費・率 照会のリンクロジックを作成           //
// 2003/12/23 販売単価・率 及び 総材料費・率 が０の場合は '-'に変換して表示 //
// 2003/12/24 ob_gzhandlerをＸ 使用すると１頁１００件の時にGETが戻らないため//
//            order by 計上日 に , assynoを追加 １頁の行数を変更しても OK   //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/11/01 特注以外の総材料費を計画番号の登録がなければ最後の登録を使う  //
// 2004/11/09 部門を全グループ・カプラ全体・特注・標準・リニア全体等に分けた//
// 2005/01/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//              set_focus()に document.focus()を使い F2/F12キーを有効にした //
// 2005/02/01 総材料費のmate.sum_priceが0の物があり計画番号=C1261631その対応//
//             mate.sum_price <= 0    具体的には部品は支給品だけで組立費のみ//
//                     ↓                                                   //
//            (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 //
// 2005/05/27 PAGE > 25 により style='overflow:hidden;' の制御を追加        //
// 2005/06/03 regdate DESC → assy_no DESC, regdate DESC へindex変更による  //
// 2005/09/06 グループ(事業部)が無いのもがあるのでチェック出来るように追加  //
// 2005/09/21 日付チェックの検証用にcheckdate(month, day, year)を使用       //
// 2006/01/24 WHEN m.midsc IS NULL THEN '&nbsp;' を追加                     //
// 2006/02/01 特注以外の照会時に部品の材料費を表示し率も追加 105未満は赤字  //
//            parts_cost_history より取得 継続のみにする場合はkubun=1を追加 //
// 2006/02/02 上記のリンク先を単価登録照会追加 &reg_noは文字化け→& reg_no  //
// 2006/02/12 部品の材料費取得SQL文を SUB→JOIN へ変更しスピードアップ      //
// 2006/03/22 総材料費等のリンクをクリックして戻った時に行マーカー追加      //
// 2006/09/21 sales/details ディレクトリの下に再配置                        //
// 2007/04/18 率2・計画番号2 に AND regdate<=計上日 が抜けていたのを修正    //
// 2007/09/28 Uround(assy_time * assy_rate, 2) →    自動機賃率を計算に追加 //
//    Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) //
// 2008/11/11 カプラ賃率変更25.6→57.00変更ロジック追加(コメント化)    大谷 //
// 2009/04/16 製品番号の頭がSSの時リニア修理として抜出すように追加     大谷 //
// 2009/08/04 製品番号の頭がNKBの時物流として抜出すように追加          大谷 //
// 2009/08/19 物流を商品管理に名称変更                                 大谷 //
// 2009/09/16 リニア標準の場合試験・修理を抜くように変更               大谷 //
// 2009/10/01 カプラ全体の場合商品管理を抜くように変更                 大谷 //
// 2009/11/10 総材料費の表示を契約賃率と社内賃率を切換えられるよう          //
//            フラグを設置（初期値は契約賃率）→おいおいformに組込？   大谷 //
// 2009/11/13 $shanai_flgの位置を変更 これを1にすれば社内賃率表示           //
//            初期値は０                                               大谷 //
// 2009/11/25 部品材料費の取得でsum_price=NULLの時取得していたが            //
//            うまく取れない部品があったため条件を外した               大谷 //
// 2009/12/02 カプラ・リニア試験修理抜出しに対応。現在はデータ無し     大谷 //
// 2010/05/21 CSV出力をしようとしたが、直納・調整が化けるので保留      大谷 //
// 2010/12/14 試作(00222 TRI)を追加。試作は全体・カプラ全体・標準           //
//            および試作で集計される                                   大谷 //
// 2010/12/20 試作のCSV出力用ファイル名が設定されていなかったのを修正  大谷 //
// 2010/12/24 直納・調整の文字化けに対応 本格リリース                  大谷 //
// 2011/03/11 試作売上の条件にdatatype='7'を追加                            //
//            3で自動計上分を抜出し、7で手動売上を抜出す               大谷 //
// 2011/05/19 生管中山さん依頼により、リスト下部にも前次頁を追加       大谷 //
// 2011/11/10 テストでNKCTとNKTを追加 → 正式追加 全体公開             大谷 //
// 2011/11/21 CSVファイル名を〜売上明細.csvに変更の為調整              大谷 //
// 2011/11/30 カプラ標準とカプラ特注にはNKCTを含まないように変更            //
//            ただし、カプラ全体には含む。またリニアのみとバイモルも        //
//            同様にNKTを含まないよう変更。ただしリニア全体には含む    大谷 //
// 2013/01/29 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                   大谷 //
//            バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2013/01/31 リニアのみのDPE抜出SQLを訂正                             大谷 //
// 2013/05/28 2013/05よりNKCT/NKTの売上げを抜き出さないように修正      大谷 //
// 2013/05/28 得意先の指定を追加                                       大谷 //
// 2014/11/19 特注の場合は工事番号を出力するように変更                      //
// 2016/08/08 mouseoverを追加                                          大谷 //
// 2016/11/15 得意先の指定をすると部門選択が効かなくなるのを訂正       大谷 //
// 2018/03/29 集計画面表示を追加                                       大谷 //
// 2018/03/30 明細のページ遷移で集計に移動してしまうバグを修正         大谷 //
// 2019/10/09 メドテックとメドー産業のファイル名変換が被っていたので修正大谷//
// 2020/02/04 SSとNKBの振分を部門コードなしでできるように変更          大谷 //
// 2020/03/12 NKCT/NKTの売上を2011/11より抜き出すように変更            大谷 //
// 2020/12/07 売上予定照会に達成率追加による変更                       和氣 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();

if( isset($_REQUEST['start_date']) ) {
    $d_start = $_REQUEST['start_date'];
} else {
    $d_start = 20201201;    // テスト固定
}

if( isset($_REQUEST['end_date']) ) {
    $d_end = $_REQUEST['end_date'];
} else {
    $d_end = 20201231;      // テスト固定
}

access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

$err_flg = false;

///// day のチェック
if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
///// 最終日をチェックしてセットする
if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
    $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
    if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
        $_SESSION['s_sysmsg'] = '日付の指定が不正です！';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
}
///// day のチェック
if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
///// 最終日をチェックしてセットする
if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
    $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
    if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
        $_SESSION['s_sysmsg'] = '日付の指定が不正です！';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
}

$_SESSION['s_d_start'] = $d_start;
$_SESSION['s_d_end']   = $d_end  ;

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("select
                        a.kanryou                     AS 完了予定日,  -- 0
                        CASE
                            WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE a.plan_no
                        END                           AS 計画番号,    -- 1
                        CASE
                            WHEN trim(a.parts_no) = '' THEN '---'
                            ELSE a.parts_no
                        END                           AS 製品番号,    -- 2
                        CASE
                            WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                            WHEN m.midsc IS NULL THEN '&nbsp;'
                            ELSE substr(m.midsc,1,38)
                        END                           AS 製品名,      -- 3
                        a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                        (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                      AS 仕切単価,    -- 5
                        Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                      AS 金額,        -- 6
                        sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                      AS 総材料費,    -- 7
                        CASE
                            WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                            ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                        END                           AS 率％,        -- 8
                        (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                      AS 総材料費2,   -- 9
                        (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                      AS 率２,        --10
                        (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                      AS 計画番号2,   --11
                        (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                      AS 部品材料費,  --12
                        (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                      AS 単価登録番号, --13
                        CASE
                            WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE substr(a.plan_no,4,5)
                        END                           AS 計画番号3    -- 14
                  FROM
                        assembly_schedule as a
                  left outer join
                        miitem as m
                  on a.parts_no=m.mipn
                  left outer join
                        material_cost_header as mate
                  on a.plan_no=mate.plan_no
                  LEFT OUTER JOIN
                        sales_parts_material_history AS pmate
                  ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                  left outer join
                        product_support_master AS groupm
                  on a.parts_no=groupm.assy_no
                  WHERE a.kanryou>=%d AND a.kanryou<=%d AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0
                  order by a.kanryou, 計画番号3
                  ", $d_start, $d_end);
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上予定のデータがありません。<br>%s〜%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    for ($r=0; $r<$rows; $r++) {
        $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'EUC-JP');   // 全角カナを半角カナへテスト的にコンバート
    }
}

for ($r=0; $r<$rows; $r++) {
    $query = "SELECT * FROM month_sales_plan WHERE plan_no='{$res[$r][1]}' AND parts_no='{$res[$r][2]}'";
    if( getResult2($query, $res_chk) > 0 ) {
//        continue;
        $_SESSION['s_sysmsg'] .= "月初予定は既に登録されている可能性があります。";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }

    $set_arr = "";  // 登録情報収集用
    for ($i=0; $i<$num; $i++) {    // レコード数分繰返し
        if ($i >= 9) break;
        switch ($i) {
        case 7:     // 総材料費
            if ($res[$r][$i] == 0) {
                if ($res[$r][9]) {
                    $set_arr[$i] = $res[$r][9];
                } elseif ($res[$r][12]) {   // 部品の材料費をチェックして表示する
                    $set_arr[$i] = $res[$r][12];
                }
            } else {
                $set_arr[$i] = $res[$r][$i];
            }
            break;
        case 8:    // 率(総材料費)
            if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                $set_arr[$i] = $res[$r][$i];
            } elseif ($res[$r][$i] <= 0) {
                if ($res[$r][10]) {
                    $set_arr[$i] = $res[$r][10];
                } elseif ($res[$r][12]) {
                    $set_arr[$i] = number_format($res[$r][5]/$res[$r][12]*100);
                }
            } else {
                $set_arr[$i] = $res[$r][$i];
            }
            break;
        default:    // その他
            $set_arr[$i] = $res[$r][$i];
        }
    }

    if( $set_arr[5] == 0 ) {
        if( empty($set_arr[7]) ) {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan ) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}');";
        } else {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan, materials_price ) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[7]}');";
        }
    } else {
        $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan, partition_price, price, materials_price, rate) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[5]}', '{$set_arr[6]}', '{$set_arr[7]}', '{$set_arr[8]}');";
    }
    if( query_affected($insert_qry) <= 0 ) {
        $err_flg = true;
//        $_SESSION['s_sysmsg'] .= "月初予定登録失敗。({$r}){$set_arr[5]}";
//        $_SESSION['s_sysmsg'] .= $insert_qry;
    }

}
if( $err_flg ) {
    $_SESSION['s_sysmsg'] .= "月初予定の登録に失敗しているレコードがあります。";
} else {
    $_SESSION['s_sysmsg'] .= "月初予定の登録に成功しました。";
}

header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
exit();

