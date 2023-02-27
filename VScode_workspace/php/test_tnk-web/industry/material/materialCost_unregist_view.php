<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費 未登録の照会  半期別の選択form・一覧表照会 標準品               //
//             (カプラの標準品/特注品とリニア/バイモル/ツール/全部門)       //
// Copyright (C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/08 Created   metarialCost_unregist_view.php                      //
//            現在の課題はASSY番号joinしているため対象範囲外の経歴も拾って  //
//            しまうため半期以前に登録されている物まで登録済みになる。      //
// 2004/04/09 上記の問題はSQL文の絞込み指定で解決したが速度が遅いため検討要 //
// 2004/04/12 半期間の間1度も登録がない物が対象 <-- メッセージを追加        //
// 2004/04/19 登録時に計画番号が必要なためgroup->orderへ変更し計画番号 追加 //
// 2004/05/05 JavaScriptのchk_assy_entry()は使用していないので中をコメント化//
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/05/25 計上日（売上日）の表示項目を追加                              //
// 2004/10/25 部門をグループ名称に変更し標準と特注等を明確に分けた          //
// 2004/12/22 カプラ標準と特注が半期間内の条件から特注は計画番号が絶対条件へ//
// 2004/12/22 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/01/07 呼出時に&material=1 リターン時にそれをチェックし$plan_noを設定//
// 2005/03/02 1頁の表示行数をdefault 25→20 へ変更                          //
// 2005/06/07 未登録画面から１クリックで登録画面へジャンプ機能追加          //
//            半期で絞り込むのをやめて特注と同じへ 前の物はbackupへ移動     //
//            SQL文はコメントアウトしてある                                 //
// 2006/08/02 C特注だけand mate.plan_no IS NULL → and mate.assy_no IS NULL //
//            完了入力忘れに対応 C標準やリニアは元々OK                      //
// 2007/03/24 material/allo_conf_parts_view.php →                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/05/10 画面メッセージ 半期間 → ３ヶ月間 へ変更                      //
// 2007/05/23 リンク先を新しい登録画面へmaterialCost_entry_main.php 大谷    //
// 2007/08/31 製品番号クリックで総材料費の履歴照会を追加 セッション変数の   //
//            mate_offset 行マーカーをローカルセッションへ変更  小林        //
// 2007/09/04 画面メッセージ３ヶ月間→１ヶ月間(2007/08/09より実施) 小林     //
// 2007/09/05 materialCost_view_assy.phpに引数plan_noを追加 小林            //
// 2013/01/28 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                   大谷 //
//            バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2013/01/31 リニアのみのDPE抜出SQLを訂正                             大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 24);          // site_index=30(生産メニュー) site_id=24(総材料費の未登録)
// $_SESSION['site_index'] = 30;            // 生産メニュー=30 最後のメニュー = 99   システム管理用は９９番
// $_SESSION['site_id']    = 24;            // 下位メニュー無し <= 0    テンプレートファイルは６０番
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 未 登 録 照 会');
//////////// 表題の設定
$menu->set_caption('部門を選択して下さい');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('引当構成表の表示',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('引当構成表の表示',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('総材料費用引当構成表の表示',   INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
$menu->set_action('総材料費用引当構成表の表示TEST',   INDUST . 'parts/allocate_config_test/allo_conf_parts_Main.php');
$menu->set_action('総材料費の登録',     INDUST . 'material/material_entry/materialCost_entry_main.php');
$menu->set_action('総材料費の履歴',     INDUST . 'material/materialCost_view_assy.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

if (isset($_SESSION['stock_parts'])) {
    unset($_SESSION['stock_parts']);    // 初期化
}

//////////// 初回時のセッションデータ保存(POSTデータと合計レコード数) 次頁・前頁を軽くするため
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    if (isset($_REQUEST['div'])) {
        // ここに POST データを解析して セッションに保存と
        $span = $_REQUEST['span'];
        $tuki = date('m');
        $year = (int) date('Y');
        if ($span == 0) {       // 今期の半期分を対象
            if ($tuki >= 4 && $tuki <= 9) {
                $str_date = ($year . '0401');
                $end_date = ($year . '0931');
            } else if ($tuki >= 10 && $tuki <= 12) {
                $str_date = ($year . '1001');
                $end_date = ($year . '1231');       // 本来は0331だが必要ないので1231にした。
            } else {
                $str_date = (($year-1) . '1001');
                $end_date = ($year . '0331');
            }
        } else {                // 前半気分を対象
            if ($tuki >= 4 && $tuki <= 9) {
                $str_date = (($year-1) . '1001');
                $end_date = ($year . '0331');
            } else if ($tuki >= 10 && $tuki <= 12) {
                $str_date = ($year . '0401');
                $end_date = ($year . '0931');
            } else {
                $str_date = (($year-1) . '0401');
                $end_date = (($year-1) . '0931');
            }
        }
        // $str_date = '20031001';     // テスト用
        // $end_date = '20040331';     // テスト用
        $_SESSION['mate_span']  = $span;            // ポストデータをセッションに保存
        $_SESSION['mate_sdate'] = $str_date;        // ポストデータをセッションに保存
        $_SESSION['mate_edate'] = $end_date;        // ポストデータをセッションに保存
        $div = $_REQUEST['div'];
        $_SESSION['mate_div'] = $_REQUEST['div'];      // ポストデータをセッションに保存
        switch ($div) {
        case ' ':   // 全部門→全グループ
            $search_div = '';
            break;
        case 'C':   // カプラ
            $search_div = "and uri.事業部='C' and sch.note15 not like 'SC%'";
            break;
        case 'S':   // カプラ特注
            $search_div = "and uri.事業部='C' and sch.note15 like 'SC%'";
            break;
        case 'L':   // リニア
            //$search_div = "and uri.事業部='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%')";
            $search_div = "and uri.事業部='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%') and CASE WHEN uri.assyno = '' THEN uri.事業部='L' ELSE item.midsc not like 'DPE%%' END";
            break;
        case 'B':   // バイモル
            //$search_div = "and uri.事業部='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%')";
            $search_div = "and uri.事業部='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%' or item.midsc like 'DPE%%')";
            break;
        case 'T':   // 機工
            $search_div = "and uri.事業部='T'";
            break;
        default:
            $search_div = '';
        }
        if ($div != 'S' && $div != 'C') {      // カプラ以外
            $query = "
                select count(*) from
                (
                    select uri.assyno
                    from
                        hiuuri as uri
                    left outer join
                        -- (select assy_no
                        --     from
                        --         material_cost_header
                        --     left outer join
                        --         hiuuri as uri
                        --     on(plan_no=計画番号)
                        --     where
                        --             計上日>={$str_date}
                        --         and 計上日<={$end_date}
                        --         and datatype='1'
                        --         $search_div
                        -- ) as mate
                        material_cost_header as mate -- 半期で絞り込むため上記を追加
                    on (uri.計画番号 = mate.plan_no)
                    -- on (uri.assyno = mate.assy_no)
                    left outer join
                        miitem as item
                    on (uri.assyno = item.mipn)
                    where 
                        uri.計上日>={$str_date}
                        and uri.計上日<={$end_date}
                        and uri.datatype='1'
                        and mate.assy_no is NULL
                        $search_div
                    order by uri.assyno
                )
                as assy_no
            ";
        } elseif ($div == 'C') {    // カプラ標準なら
            $query = "
                select count(*) from
                (
                    select uri.assyno
                    from
                        hiuuri as uri
                    left outer join
                        -- (select assy_no
                        --     from
                        --         material_cost_header
                        --     left outer join
                        --         hiuuri as uri
                        --     on(plan_no=計画番号)
                        --     where
                        --             計上日>={$str_date}
                        --         and 計上日<={$end_date}
                        --         and datatype='1'
                        -- ) as mate
                        material_cost_header as mate -- 半期で絞り込むため上記を追加
                    on (uri.計画番号 = mate.plan_no)
                    -- on (uri.assyno = mate.assy_no)
                    left outer join
                          assembly_schedule as sch
                    on (uri.計画番号=sch.plan_no)
                    left outer join
                        miitem as item
                    on (uri.assyno = item.mipn)
                    where 
                        uri.計上日>={$str_date}
                        and uri.計上日<={$end_date}
                        and uri.datatype='1'
                        and mate.assy_no is NULL
                        $search_div
                    order by uri.assyno
                )
                as assy_no
            ";
        } elseif ($div == 'S') {    // カプラ特注なら
            $query = "
                select count(*) from
                (
                    select uri.assyno
                    from
                        hiuuri as uri
                    left outer join
                        material_cost_header as mate -- 特注は半期で絞り込まない
                    on (uri.計画番号 = mate.plan_no)
                    left outer join
                          assembly_schedule as sch
                    on (uri.計画番号=sch.plan_no)
                    left outer join
                        miitem as item
                    on (uri.assyno = item.mipn)
                    where 
                        uri.計上日>={$str_date}
                        and uri.計上日<={$end_date}
                        and uri.datatype='1'
                        and mate.assy_no is NULL
                        -- and mate.plan_no IS NULL
                        $search_div
                    order by uri.assyno
                )
                as assy_no
            ";
        }
        if (getUniResult($query, $maxrows) <= 0) {
            $_SESSION['s_sysmsg'] = '合計レコード数の取得に失敗';
        } else {
            $_SESSION['material_max'] = $maxrows;
        }
    }
    $plan_no = '';  // 初期化のみ
} else {        // 次頁・前頁・頁保存 の時は
    if (isset($_SESSION['mate_div'])) {
        $_REQUEST['div'] = $_SESSION['mate_div'];       // ポストデータをエミュレート
        $div      = $_REQUEST['div'];
        $maxrows  = $_SESSION['material_max'];          // 合計レコード数を復元
        $span     = $_SESSION['mate_span'];             // 対象期のradioボタンを復元
        $str_date = $_SESSION['mate_sdate'];            // 開始日付を復元
        $end_date = $_SESSION['mate_edate'];            // 終了日付を復元
        switch ($div) {
        case ' ':   // 全部門
            $search_div = '';
            break;
        case 'C':   // カプラ
            $search_div = "and uri.事業部='C' and sch.note15 not like 'SC%'";
            break;
        case 'S':   // カプラ特注
            $search_div = "and uri.事業部='C' and sch.note15 like 'SC%'";
            break;
        case 'L':   // リニア
            //$search_div = "and uri.事業部='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%')";
            $search_div = "and uri.事業部='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%') and (item.midsc not like 'DPE%%')";
            break;
        case 'B':   // バイモル
            //$search_div = "and uri.事業部='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%')";
            $search_div = "and uri.事業部='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%' or item.midsc like 'DPE%%')";
            break;
        case 'T':   // 機工
            $search_div = "and uri.事業部='T'";
            break;
        default:
            $search_div = '';
        }
    }
    if (isset($_SESSION['material_plan_no'])) {
        $plan_no = $_SESSION['material_plan_no'];
    } else {
        $plan_no = '';
    }
}

//////////// 一頁の行数
if (isset($_SESSION['material_page'])) {                // １頁の表示行数を呼出元で設定できるようにするため
    define('PAGE', $_SESSION['material_page']);
} else {
    define('PAGE', 20);
}

//////////// ページオフセット設定
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // 初期化
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                 // 次頁が押された
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する POST & GETデータ
    $offset = $offset;
} else {
    $offset = 0;                           // 初回の場合は０で初期化
}
$session->add_local('offset', $offset);

////////////// 表示用(一覧表)の未登録データをSQLで取得
if (isset($_REQUEST['div'])) {
    if ($div != 'S' && $div != 'C') {      // カプラ以外
        $query = "
            select  uri.assyno                      as 製品番号         -- 0
                ,   trim(substr(item.midsc, 1, 32)) as 製品名           -- 1
                ,   uri.計画番号                    as 計画番号         -- 2
                ,   uri.数量                        as 売上数           -- 3
                ,   uri.計上日                      as 売上日           -- 4
            from
                hiuuri as uri
            left outer join
                miitem as item
            on (uri.assyno = item.mipn)
            left outer join
                -- (select assy_no
                --     from
                --         material_cost_header
                --     left outer join
                --         hiuuri as uri
                --     on(plan_no=計画番号)
                --     where
                --             計上日>={$str_date}
                --         and 計上日<={$end_date}
                --         and datatype='1'
                --         $search_div
                -- ) as mate
                material_cost_header as mate -- 半期で絞り込むため上記を追加
            -- on (uri.assyno = mate.assy_no)
            on (uri.計画番号 = mate.plan_no)
            where 
                uri.計上日>={$str_date}
                and uri.計上日<={$end_date}
                and uri.datatype='1'
                and mate.assy_no is NULL
                $search_div
            order by uri.assyno ASC
            offset $offset limit
        " . PAGE;
    } elseif ($div == 'C') {    // カプラ標準なら
        $query = "
            select  uri.assyno                      as 製品番号         -- 0
                ,   trim(substr(item.midsc, 1, 32)) as 製品名           -- 1
                ,   uri.計画番号                    as 計画番号         -- 2
                ,   uri.数量                        as 売上数           -- 3
                ,   uri.計上日                      as 売上日           -- 4
            from
                hiuuri as uri
            left outer join
                miitem as item
            on (uri.assyno = item.mipn)
            left outer join
                -- (select assy_no
                --     from
                --         material_cost_header
                --     left outer join
                --         hiuuri as uri
                --     on(plan_no=計画番号)
                --     where
                --             計上日>={$str_date}
                --         and 計上日<={$end_date}
                --         and datatype='1'
                -- ) as mate
                material_cost_header as mate -- 半期で絞り込むため上記を追加
            -- on (uri.assyno = mate.assy_no)
            on (uri.計画番号 = mate.plan_no)
            left outer join
                  assembly_schedule as sch
            on (uri.計画番号=sch.plan_no)
            where 
                uri.計上日>={$str_date}
                and uri.計上日<={$end_date}
                and uri.datatype='1'
                and mate.assy_no is NULL
                $search_div
            order by uri.assyno ASC
            offset $offset limit
        " . PAGE;
    } elseif ($div == 'S') {    // カプラ特注なら
        $query = "
            select  uri.assyno                      as 製品番号         -- 0
                ,   trim(substr(item.midsc, 1, 32)) as 製品名           -- 1
                ,   uri.計画番号                    as 計画番号         -- 2
                ,   uri.数量                        as 売上数           -- 3
                ,   uri.計上日                      as 売上日           -- 4
            from
                hiuuri as uri
            left outer join
                miitem as item
            on (uri.assyno = item.mipn)
            left outer join
                material_cost_header as mate -- 特注は半期で絞り込まない(計画番号が絶対の条件)
            on (uri.計画番号 = mate.plan_no)
            left outer join
                  assembly_schedule as sch
            on (uri.計画番号=sch.plan_no)
            where 
                uri.計上日>={$str_date}
                and uri.計上日<={$end_date}
                and uri.datatype='1'
                and mate.assy_no is NULL
                -- and mate.plan_no IS NULL
                $search_div
            order by uri.assyno ASC
            offset $offset limit
        " . PAGE;
    }
    $res = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "未登録はありません！";
        unset($_REQUEST['div']);      // 照会の実行をリセット
    } else {
        $num = count($field);       // フィールド数取得
        for ($r=0; $r<$rows; $r++) {
            $res[$r][1] = mb_convert_kana($res[$r][1], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
        }
        $div = $_REQUEST['div'];
    }
} else {
    $div  = ' ';    // default値の保存
    $span = 0;      // default値の保存 0=今期 1=前半期
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<!--    ファイル指定の場合
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}

function chk_assy_entry(obj) {
    // obj.assy.value = obj.assy.value.toUpperCase();
    // 現在は使用していない
    return true;
}

/* 初期入力フォームのエレメントにフォーカスさせる */
<?php if (!isset($_REQUEST['div'])) { ?>
function set_focus(){
    document.entry_form.div.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.entry_form.div.select();
}
<?php } else { ?>
function set_focus(){
    document.page_form.confirm.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.entry_form.div.select();
}
<?php } ?>

/* selectを変更したときに即実行 */
function select_send(obj)
{
    document.mac_form.submit();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.ki_non {
    font-size:      11pt;
    font-weight:    normal;
    font-family:    monospace;
}
.ki_chk {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.assy_font {
    font-size:      13pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a:active {
    background-color:   gold;
    color:              black;
}
a {
    color:   blue;
}
p {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>部門を選択して下さい</div>
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' class='assy_font' onChange='document.entry_form.submit()'>
                            <!-- <option value=" "<?php if($div==" ") echo("selected"); ?>>全グループ</option> -->
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>カプラ標準</option>
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>カプラ特注</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>リニア標準</option>
                            <option value="B"<?php if($div=="B") echo("selected"); ?>>液体ポンプ</option>
                            <option value="T"<?php if($div=="T") echo("selected"); ?>>ツール</option>
                        </select>
                        <input class='pt11b' type='submit' name='execute' value='実行'>
                    </td>
                    <?php if ($span == 0) { ?>
                    <td class='winbox' nowrap>
                        <div class='ki_chk'>
                        <input type='radio' name='span' value='0' id='konki' checked><label for='konki'>今期分
                        </div>
                    </td>
                    <td class='winbox' nowrap>
                        <div class='ki_non'>
                        <input type='radio' name='span' value='1' id='zenki'><label for='zenki'>前半期
                        </div>
                    </td>
                    <?php } else { ?>
                    <td class='winbox' nowrap>
                        <div class='ki_non'>
                        <input type='radio' name='span' value='0' id='konki'><label for='konki'>今期分
                        </div>
                    </td>
                    <td class='winbox' nowrap>
                        <div class='ki_chk'>
                        <input type='radio' name='span' value='1' id='zenki' checked><label for='zenki'>前半期
                        </div>
                    </td>
                    <?php } ?>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <?php if (!isset($_REQUEST['div'])) { ?>
        <br>
        <table style='border: 2px solid #CCBBAA;'>
            <tr>
                <td align='center'>
                    <p>検索には多少時間がかかりますのでお待ち下さい。</p>
                </td>
            </tr>
            <tr>
                <td align='left'>
                    <p>
                        この照会は標準品をメインにしていますので総材料費は半期ベースでの登録状況です。
                        <br>
                        （１ヶ月間の間１度も登録がない物が表示されます。）
                        <br>
                        ＊カプラ特注に関しては上記は適用されず売上の計画番号で総材料費が登録されてない
                        <br>
                        　全ての物が表示されます。
                    </p>
                </td>
            </tr>
        </table>
        <?php } ?>
        
        <?php if (isset($_REQUEST['div'])) { ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='confirm' value=' O K '>
                            </td>
                        </table>
                    </td>
                    <!--
                    <td nowrap align='center' class='caption_font'>
                    </td>
                    -->
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    echo "<th class='winbox' nowrap>{$field[$i]}</th>\n";
                }
                ?>
                    <th class='winbox' nowrap>登録画面へ</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                $res[-1][0] = '';  // ダミー
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:     // 製品番号
                            if ($res[$r][$i] != $res[$r-1][$i]) {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費の履歴'), "?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][2]), "\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='80' align='center'><div class='pt10'>〃</div></td>\n";
                            }
                            break;
                        case 1:     // 製品名
                            if ($res[$r][0] != $res[$r-1][0]) {
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt10'>", $res[$r][$i], "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='270' align='center'><div class='pt10'>〃</div></td>\n";
                            }
                            break;
                        case 2:     // 計画番号
                            if ($_SESSION['User_ID'] == '300667') {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費用引当構成表の表示'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
//                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費用引当構成表の表示TEST'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
/**
                            } else if( $_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '970352' ) {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費用引当構成表の表示'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
/**/
                            } else {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費用引当構成表の表示'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
//                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('引当構成表の表示'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            }
                            break;
                        case 3:     // 売上数
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt10'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:     // 売上日
                            echo "<td class='winbox' nowrap width='80' align='center'><div class='pt10'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        default:    // その他
                            echo "<td class='winbox' nowrap align='center'><div class='pt10'>", $res[$r][$i], "</div></td>\n";
                        }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                        echo "<td class='winbox' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費の登録'), "?plan_no=", urlencode($res[$r][2]), "&assy_no=", urlencode($res[$r][0]), "\")' target='application' style='text-decoration:none;'>登録</a></td>\n";
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <table width='100%' cellspacing="0" cellpadding="0" border='0'> <!-- ダミーStart -->
            <form name='confirm_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <table align='center' border='3' cellspacing='0' cellpadding='0'>
                    <tr>
                    <td align='right'>
                        <input class='pt10b' type='submit' name='confirm' value=' O K '>
                    </td>
                    </tr>
                </table>
            </form>
        </table> <!----------------- ダミーEnd ------------------>
        <?php } ?>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
