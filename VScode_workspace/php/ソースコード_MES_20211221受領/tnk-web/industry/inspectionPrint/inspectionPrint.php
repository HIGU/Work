<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ特注の完成品検査成績書 印刷                                        //
// テンプレートエンジンはsimplate, クライアント印刷はPXDoc を使用           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/06 Created  inspectionPrint.php                                  //
// 2007/12/07 製品名・製品番号等の確認画面を出力するように追加              //
// 2007/12/10 ホンタイ材質・ゴム材質のデータ取得で getMaterial()を追加      //
// 2007/12/18 templateを改定1へ変更。 製品名とユーザー名の文字数を増やした  //
// 2007/12/20 templateを改定2へ変更。 栗原さんが様式番号等を修正した        //
// 2007/12/25 標準品の対応でSC工番にSCが入っていない場合は--------を入れる  //
// 2007/12/26 上記のチェックを更にctype_alnum()へ変更 -> SCｶｲﾊﾂ の対応      //
// 2007/12/28 本体材質とゴム材質を修正できるように機能追加し、印刷履歴を    //
//            残し、次回の印刷時に最終履歴のデータを使用する。前回印刷も追加//
// 2007/12/29 履歴に保存に計画番号も追加 $result->get('prePlanNo')          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_cache_limiter('public');            // PXDocを使用する場合のおまじない(出力ファイルをキャッシュさせる)
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
// access_log();                               // Script Name は自動取得
define('START_TIME', microtime(true));

//////////// リクエストのインスタンスを作成
$request = new Request();
//////////// リザルトのインスタンスを作成
$result = new Result();
///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('カプラ特注 完成品検査成績書の印刷');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('svgUpload', TEST . '/pxd/svgUpload.php');

main($request, $menu, $result);

function main($request, $menu, $result)
{
    switch( $request->get('showMenu') ){
    case 'preView':
        if (!printPXDoc($request, 1, $result)) inputForm($menu, $request, $result);
        break;
    case 'execPrint':
        if (!printPXDoc($request, 2, $result)) inputForm($menu, $request, $result);
        break;
    case 'inputForm':
    default:
        inputForm($menu, $request, $result);
    }
}
ob_end_flush();                 // 出力バッファをgzip圧縮 END


function printPXDoc($request, $flg=0, $result)
{
    if ($flg == 0) return;
    $baseName = basename($_SERVER['SCRIPT_NAME'], '.php');
    
    // if(!extension_loaded('simplate')) { dl('simplate.so'); }
    $smarty = new simplate();
    
    $header  = '<?xml version="1.0" encoding="EUC-JP"?>' . "\n";
    $header .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
    
    if ($flg == 2) {
        $strPXD = "<pxd name='自動印刷' title='カプラ特注 完成品検査成績書の印刷' paper-type='A4' paper-name='A4-カット紙' orientation='portrait' delete='yes' save='no' print='yes'>\n";
    } else {
        $strPXD = "<pxd name='プレビュー' title='カプラ特注 完成品検査成績書の印刷プレビュー' paper-type='A4' paper-name='A4-カット紙' orientation='portrait' delete='yes' save='no' print='yes' tool-fullscreen='no'>\n";
    }
    $endPXD = "</pxd>\n";
    
    if (!getPlanData($request, $result)) {
        return false;
    }
    ////////// リクエストで本体材質とゴム材質の変更チェックと設定
    setMaterial($request, $result);
    
    $smarty->assign('planNo', $request->get('targetPlanNo'));
    $smarty->assign('partsNo', $result->get('assyNo'));
    $smarty->assign('partsName', $result->get('assyName'));
    $smarty->assign('plan', $result->get('plan'));
    $smarty->assign('scNo', $result->get('scNo'));
    $smarty->assign('material', $result->get('material'));
    $smarty->assign('material2', $result->get('material2'));
    $smarty->assign('userName', $result->get('userName'));
    // $end_time = sprintf("%01.05f",microtime(true)-START_TIME);
    // $smarty->assign('cdNo', "作成時間：$end_time 秒");
    $smarty->assign('cdNo', $result->get('cdNo'));
    
    $output  = $header;
    $output .= $strPXD;
    $output .= "<page>\n";
    $output .= "<chapter name='１ページ' id='1' parent='' />\n";
    // $output .= $smarty->fetch('特注カプラ完成品検査成績書.tpl');
    $output .= $smarty->fetch('特注カプラ完成品検査成績書-改定3.tpl');
    $output .= "</page>\n";
    $output .= $endPXD;
    /******************* デバッグ用 **************************/
    if ($request->get('DEBUG') == 'yes') {
        $fp = fopen("{$baseName}-debug.txt", 'w');
        fwrite($fp, $output);
        fclose($fp);
        chmod("{$baseName}-debug.txt", 0666);
    }
    /*********************************************************/
    
    header('Content-type: application/pxd;');
    header("Content-Disposition:inline;filename=\"{$baseName}.pxd\"");
    echo $output;
    ///// 印刷履歴保存
    setPrintHistory($request, $result);
    return true;
}

function inputForm($menu, $request, $result)
{
    ////////// ブラウザーのキャッシュ対策用
    $uniq = $menu->set_useNotCache('target');
    ////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();
    ////////// リクエストがあればデータを取得
    if ($request->get('targetPlanNo') != '') getPlanData($request, $result);
    ////////// リクエストで本体材質とゴム材質の変更チェックと設定
    setMaterial($request, $result);
    ////////// 入力フォームの表示
    require_once ('inputForm.php');
}

function getPlanData($request, $result)
{
    $query = "
        SELECT
            parts_no                        -- 0
            ,
            plan - cut_plan                 -- 1
            ,
            substr(note15, 1, 8)            -- 2 工事番号は８桁
            ,
            sche.user_name                  -- 3 これは現在使用できない
            ,
            substr(midsc, 1, 38)            -- 4 (旧18)
            ,
            mzist                           -- 5
            ,
            substr(devuser.user_name, 1, 26)-- 6 (旧17)
            ,
            dev_no                          -- 7
            ,
            midsc                           -- 8
        FROM
            assembly_schedule AS sche
        LEFT OUTER JOIN
            miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            assy_develop_user ON (parts_no = assy_no)
        LEFT OUTER JOIN
            assy_develop_user_code AS devuser USING (user_no)
        WHERE
            plan_no = '{$request->get('targetPlanNo')}'
    ";
    $res = array();
    if (getResult2($query, $res) < 1) {
        $_SESSION['s_sysmsg'] = '計画番号が正しくありません。';
        return false;
    } else {
        $result->add('assyNo', $res[0][0]);
        $result->add('plan', $res[0][1]);
        if (ctype_alnum($res[0][2])) {
            $result->add('scNo', $res[0][2]);
        } else {
            $result->add('scNo', '--------');
        }
        // $result->add('userName', $res[0][3]);    // 以下は半角26文字までに変更
        $result->add('assyName', mb_substr(mb_convert_kana($res[0][4], 'k'), 0, 26) );
        $result->add('material', $res[0][5]);
        $result->add('material2', '');
        $result->add('userName', $res[0][6]);
        $result->add('cdNo', $res[0][7]);
        if ($res[0][5] == '') {
            $result->add('material', getMaterial($result, $res[0][8], 1));
        }
        $result->add('material2', getMaterial($result, $res[0][8], 2));
        return true;
    }
}
///// アイテムマスターの製品名から本体材質とゴム材質を取得(約束事でASSYの次にスペースで本体材質スペースでゴム材質)
function getMaterial($result, $data, $flg)
{
    ///// 履歴から取得
    if ($flg == 1) {
        $query = "
            SELECT material, regdate, plan_no FROM inspection_print_history WHERE assy_no = '{$result->get('assyNo')}'
            ORDER BY assy_no DESC, regdate DESC LIMIT 1
        ";
    } else {
        $query = "
            SELECT material2, regdate, plan_no FROM inspection_print_history WHERE assy_no = '{$result->get('assyNo')}'
            ORDER BY assy_no DESC, regdate DESC LIMIT 1
        ";
    }
    $res = array();
    if (getResult2($query, $res) > 0) {
        $result->add('prePrintDate', $res[0][1]);
        $result->add('prePlanNo', $res[0][2]);
        return $res[0][0];
    } else {
        $result->add('prePrintDate', '初回');
    }
    
    ///// マスターから取得
    $arrayData = explode(' ', $data);
    $count = count($arrayData);
    for ($i=0; $i<$count; $i++) {
        if ($arrayData[$i] == 'ASSY') {
            if (isset($arrayData[$i+$flg])) return $arrayData[$i+$flg];
            break;
        } elseif (substr($arrayData[$i], -4) == 'ASSY') {
            if (isset($arrayData[$i+$flg])) return $arrayData[$i+$flg];
            break;
        }
    }
    return '';
}
///// 本体材質とゴム材質の修正チェック及び設定
function setMaterial($request, $result)
{
    if ($request->get('targetMaterial') != '') $result->add('material', $request->get('targetMaterial'));
    if ($request->get('targetMaterial2') != '') $result->add('material2', $request->get('targetMaterial2'));
}
///// 印刷履歴の保存
function setPrintHistory($request, $result)
{
    $query = "
        INSERT INTO inspection_print_history (assy_no, material, material2, plan_no)
        VALUES ('{$result->get('assyNo')}', '{$result->get('material')}', '{$result->get('material2')}', '{$request->get('targetPlanNo')}')
    ";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] = '履歴の保存に失敗しました！ 担当者へ連絡して下さい。';
    }
}
?>
