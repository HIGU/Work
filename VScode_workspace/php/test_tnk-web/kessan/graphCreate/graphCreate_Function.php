<?php
//////////////////////////////////////////////////////////////////////////////
// 損益関係のグラフ作成メニュー  共用 Function Include File                 //
// Copyright (C) 2007 - 2013  Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp   //
// Changed history                                                          //
// 2007/10/04 Created   graphCreate_Function.php                            //
// 2007/10/07 グラフの値表示・非表示追加。Y軸１個(共用)・２個(別々)を追加   //
// 2007/10/09 凡例のマークの横線の長さを長くした SetMarkAbsHSize(12)        //
// 2007/10/10 セグメント別(C標準・C特注・L製品・ﾊﾞｲﾓﾙ)の損益計算書dataを追加//
//            getGraphData()の戻り値をチェックしてグラフ作成の制御を追加    //
//            preg_match()を使用して未設定とセグメント名を黄色表示          //
// 2007/10/13 X軸の年月をprot1とprot2別々に設定できるオプションを追加       //
// 2007/10/16 compositionXaxis()にisset($p2[$i])のチェックを追加            //
//            getGraphStrYM($end_ym)を新規に追加しX軸１２ヶ月固定表示へ     //
// 2007/10/17 SetLabelAlign('center', 'top') SetLabelMargin(1)をX軸に追加   //
// 2013/01/29 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                   大谷 //
//            バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
//////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している

//////////// グラフの描画項目のリテラル値
function getGraphItemArray()
{
    return array(
        '未設定',
        '--以下は全体--',
        '全体売上高',                       '全体材料費(仕入高)',             '全体労務費',
        '全体製造経費',                     '全体期末材料仕掛品棚卸高',       '全体売上原価',
        '全体売上総利益',                   '全体人件費',                     '全体経費',
        '全体販管費及び一般管理費計',       '全体営業利益',                   '全体経常利益',
        '--以下はカプラ--',
        'カプラ売上高',                     'カプラ材料費(仕入高)',           'カプラ労務費',
        'カプラ製造経費',                   'カプラ期末材料仕掛品棚卸高',     'カプラ売上原価',
        'カプラ売上総利益',                 'カプラ人件費',                   'カプラ経費',
        'カプラ販管費及び一般管理費計',     'カプラ営業利益',                 'カプラ経常利益',
        '--以下はリニア--',
        'リニア売上高',                     'リニア材料費(仕入高)',           'リニア労務費',
        'リニア製造経費',                   'リニア期末材料仕掛品棚卸高',     'リニア売上原価',
        'リニア売上総利益',                 'リニア人件費',                   'リニア経費',
        'リニア販管費及び一般管理費計',     'リニア営業利益',                 'リニア経常利益',
        '--以下は試験修理--',
        '試験修理売上高',                   '試験修理材料費(仕入高)',         '試験修理労務費',
        '試験修理製造経費',                 '試験修理期末材料仕掛品棚卸高',   '試験修理売上原価',
        '試験修理売上総利益',               '試験修理人件費',                 '試験修理経費',
        '試験修理販管費及び一般管理費計',   '試験修理営業利益',               '試験修理経常利益',
        '--以下は商品管理--',
        '商品管理売上高',                   '商品管理材料費(仕入高)',         '商品管理労務費',
        '商品管理製造経費',                 '商品管理期末材料仕掛品棚卸高',   '商品管理売上原価',
        '商品管理売上総利益',               '商品管理人件費',                 '商品管理経費',
        '商品管理販管費及び一般管理費計',   '商品管理営業利益',               '商品管理経常利益',
        '--以下はC標準--',
        'カプラ標準売上高',                 'カプラ標準材材料費(仕入高)',     'カプラ標準労務費',
        'カプラ標準製造経費',               'カプラ標準期末材料仕掛品棚卸高', 'カプラ標準売上原価',
        'カプラ標準売上総利益',             'カプラ標準人件費',               'カプラ標準経費',
        'カプラ標準販管費及び一般管理費計', 'カプラ標準営業利益',             'カプラ標準経常利益',
        '--以下はC特注--',
        'カプラ特注売上高',                 'カプラ特注材料費(仕入高)',       'カプラ特注労務費',
        'カプラ特注製造経費',               'カプラ特注期末材料仕掛品棚卸高', 'カプラ特注売上原価',
        'カプラ特注売上総利益',             'カプラ特注人件費',               'カプラ特注経費',
        'カプラ特注販管費及び一般管理費計', 'カプラ特注営業利益',             'カプラ特注経常利益',
        '--以下はL標準--',
        'リニア標準売上高',                 'リニア標準材料費(仕入高)',       'リニア標準労務費',
        'リニア標準製造経費',               'リニア標準期末材料仕掛品棚卸高', 'リニア標準売上原価',
        'リニア標準売上総利益',             'リニア標準人件費',               'リニア標準経費',
        'リニア標準販管費及び一般管理費計', 'リニア標準営業利益',             'リニア標準経常利益',
        '--以下は液体ポンプ--',
        '液体ポンプ売上高',                 '液体ポンプ材料費(仕入高)',       '液体ポンプ労務費',
        '液体ポンプ製造経費',               '液体ポンプ期末材料仕掛品棚卸高', '液体ポンプ売上原価',
        '液体ポンプ売上総利益',             '液体ポンプ人件費',               '液体ポンプ経費',
        '液体ポンプ販管費及び一般管理費計', '液体ポンプ営業利益',             '液体ポンプ経常利益'
    );
}

//////////// 表示する年月の選択フォーム生成
function ymFormCreate($dataxFlg, $yyyymm, $name='yyyymm1', $event='')
{
    // $ym_form = "<select name='yyyymm' onChange='document.ym_form.submit()'>\n";
    if ($name == 'yyyymm2' && $dataxFlg == 'on') {
        $ym_form = "<select name='{$name}' {$event} disabled>\n";
    } else {
        $ym_form = "<select name='{$name}' {$event}>\n";
    }
            ///// 年月の範囲を指定しているのはバックアップ用のデータに一桁多いものがあるため
    $query = "
        SELECT pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym >= 200010 AND pl_bs_ym <= 203003 GROUP BY pl_bs_ym ORDER BY pl_bs_ym DESC
    ";
    $res = array(
        ["202211"],
        ["202211"],
        ["202211"],
        ["202211"],
        ["202211"],
    );
    for ($i=0; $i<3; $i++) {
        if ($yyyymm == $res[$i][0]) {
            $ym_form .= "    <option value='202211' selected>202211</option>\n";
        } else {
            $ym_form .= "    <option value='202211'>202211</option>\n";
        }
    }
    $ym_form .= "    </select>\n";
    return $ym_form;
}

//////////// グラフのプロット項目 選択フォーム生成
function graphSelectForm($name, $plotItem)
{
    $select = "<select name='{$name}'>\n";
    // $query = "SELECT note FROM act_pl_history WHERE pl_bs_ym >= 200010 AND pl_bs_ym <= 203003 GROUP BY note ORDER BY note DESC";
    // 項目が多すぎるので上記はやめる。かわりに配列を使用する。
    $res = getGraphItemArray();     // リテラル配列値で取得
    foreach ($res as $value) {
        if ($plotItem == $value) {
            if (preg_match('/^未設定/', $value) || preg_match('/^--/', $value)) {
                $select .= "    <option value='{$value}' style='color:yellow;' selected>{$value}</option>\n";
            } else {
                $select .= "    <option value='{$value}' selected>{$value}</option>\n";
            }
        } elseif (preg_match('/^未設定/', $value) || preg_match('/^--/', $value)) {
            $select .= "    <option value='{$value}' style='color:yellow;'>{$value}</option>\n";
        } else {
            $select .= "    <option value='{$value}'>{$value}</option>\n";
        }
    }
    $select .= "</select>\n";
    return $select;
}

//////////// メインコントロール
function mainController($menu, $request, $session)
{
    //////////// グラフのプロット項目の取得
    $session->add_local('g1plot1', '全体売上高');
    $session->add_local('g1plot2', 'カプラ売上高');
    //////////// グラフの値表示・非表示の取得
    $session->add_local('plot1_value', 'on');
    $session->add_local('plot2_value', 'on');
    //////////// 指定年月の取得
    $session->add_local('yyyymm1', '202111');
    $session->add_local('yyyymm2', '202211');
}

//////////// Y軸１個(共用)・２個の取得をしてラジオボタンのチェックを返す
function getRadioChecked($request, $name, $value)
{
    if ($request->get($name) == $value) {
        return ' checked';
    } elseif ($request->get($name) == '' && $value == 1) {
        return ' checked';
    } else {
        return '';
    }
}

//////////// 条件指定フォームへ戻すデータ設定
function setReturnData($menu, $session)
{
    $menu->set_retPOST('yyyymm1', $session->get_local('yyyymm1'));
    $menu->set_retPOST('yyyymm2', $session->get_local('yyyymm2'));
    $menu->set_retPOST('dataxFlg', $session->get_local('dataxFlg'));
    $menu->set_retPOST('g1plot1', $session->get_local('g1plot1'));
    $menu->set_retPOST('g1plot2', $session->get_local('g1plot2'));
    $menu->set_retPOST('g2plot1', $session->get_local('g2plot1'));
    $menu->set_retPOST('g2plot2', $session->get_local('g2plot2'));
    $menu->set_retPOST('g3plot1', $session->get_local('g3plot1'));
    $menu->set_retPOST('g3plot2', $session->get_local('g3plot2'));
    $menu->set_retPOST('plot1_value', $session->get_local('plot1_value'));
    $menu->set_retPOST('plot2_value', $session->get_local('plot2_value'));
    $menu->set_retPOST('yaxis', $session->get_local('yaxis'));
}

//////////// グラフのプロット項目 選択
function graphCreate($session, $result)
{
    $graph_name1 = "graph/graphCreate1.png";  
    $graph_name2 = "graph/graphCreate2.png";
    $graph_name3 = "graph/graphCreate3.png";
    $result->add('graph_name1', $graph_name1);
    $result->add('graph_name2', $graph_name2);
    $result->add('graph_name3', $graph_name3);
    if ($session->get_local('g1plot1') != '未設定' && $session->get_local('g1plot2') != '未設定') {
        $rows = getGraphData($session, $result, 'g1plot1');
        getGraphData($session, $result, 'g1plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name1, 'g1plot1', 'g1plot2');
    } elseif ($session->get_local('g1plot1') != '未設定') {
        $rows = getGraphData($session, $result, 'g1plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name1, 'g1plot1');
    }
    if ($session->get_local('g2plot1') != '未設定' && $session->get_local('g2plot2') != '未設定') {
        $rows = getGraphData($session, $result, 'g2plot1');
        getGraphData($session, $result, 'g2plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name2, 'g2plot1', 'g2plot2');
    } elseif ($session->get_local('g2plot1') != '未設定') {
        $rows = getGraphData($session, $result, 'g2plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name2, 'g2plot1');
    }
    if ($session->get_local('g3plot1') != '未設定' && $session->get_local('g3plot2') != '未設定') {
        $rows = getGraphData($session, $result, 'g3plot1');
        getGraphData($session, $result, 'g3plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name3, 'g3plot1', 'g3plot2');
    } elseif ($session->get_local('g3plot1') != '未設定') {
        $rows = getGraphData($session, $result, 'g3plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name3, 'g3plot1');
    }
}

//////////// グラフの開始年月の取得 基本は年度でX軸個数可変 旧タイプ
function getGraphStrYM_old($end_ym)
{
    $yyyy   = substr($end_ym, 0, 4);
    $mm     = substr($end_ym, 4, 2);
    if ($mm >= 4 && $mm <= 10) {
        $mm = '01';
    } elseif ($mm >= 1 && $mm <= 3) {
        $mm = '04';
        $yyyy--;
    } else {
        $mm = '04';
    }
    $str_ym = $yyyy . $mm;
    return $str_ym;
}

//////////// グラフの開始年月の取得 １２ヶ月固定 新タイプ
function getGraphStrYM($end_ym)
{
    $yyyy   = substr($end_ym, 0, 4);
    $mm     = substr($end_ym, 4, 2);
    $month = $mm - 23;
    if ($month <= 0) {
        $yyyy -= 1;
        $mm = sprintf('%02d', $month + 12);
    } else {
        $mm = sprintf('%02d', $month);
    }
    $str_ym = $yyyy . $mm;
    return $str_ym;
}

//////////// グラフデータの取得
function getGraphData($session, $result, $plot)
{
    ///// リクエスト年月をグラフの終了年月にして、開始年月を算出
    if (substr($plot, 2, 5) == 'plot1') {
        $end_ym = $session->get_local('yyyymm1');
    } else {
        $end_ym = $session->get_local('yyyymm2');
    }
    $str_ym = getGraphStrYM($end_ym);
    $plot_change = str_replace('液体ポンプ','バイモル', $session->get_local($plot));
    ///// プロット項目取得
    $query = "
        SELECT kin, pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym >= {$str_ym} AND pl_bs_ym <= {$end_ym}
        AND note = '{$plot_change}' ORDER BY pl_bs_ym ASC
    ";
    $rows = 5;
    $res = array(
        [1000000, "202207"],
        [2000000, "202208"],
        [3000000, "202209"],
        [4000000, "202210"],
        [5000000, "202211"],
    );
    $data  = array();
    $datax = array();   // X軸項目名(年月)
    for ($i=0; $i<$rows; $i++) {
        $data[$i]  = Uround($res[$i][0] / 1000000, 1);   // 単位を百万円へ
        $datax[$i] = $res[$i][1];
    }
    $result->add($plot.'_data', $data);     // プロットデータセット
    $result->add($plot.'_datax', $datax);   // X軸項目セット

    $result->add($plot.'_rows', $rows);  // プロットデータのレコード数をセット ０以下ならば描画しない
    return $rows;
}

//////////// グラフ作成実行
function graphCreateExecute($session, $result, $graph_name, $plot1, $plot2='')
{
    require_once ('../../../jpgraph-4.4.1/src/jpgraph.php'); 
    require_once ('../../../jpgraph-4.4.1/src/jpgraph_line.php'); 
    // A nice graph with anti-aliasing 
    $graph = new Graph(1200, 350, 'auto');       // グラフの大きさ X/Y
    if ($session->get_local('dataxFlg') == 'on') {
        $graph->img->SetMargin(40, 50, 30, 85);    // グラフ位置のマージン 左右上下
    } else {
        $graph->img->SetMargin(40, 50, 30, 95);    // グラフ位置のマージン 左右上下
    }
    $graph->SetScale('textlin'); 
    $graph->SetShadow(); 
    // Slightly adjust the legend from it's default position in the 
    // top right corner. 
    // $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // 凡例の位置指定(左右マージン,上下マージン,"right","center")
    $graph->legend->Pos(0.5, 0.97, 'center', 'bottom');
    $graph->legend->SetLayout(LEGEND_HOR);
    $graph->legend->SetFont(FF_GOTHIC, FS_NORMAL, 14);  // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
    $graph->legend->SetMarkAbsHSize(12);                // マークの横線の長さを長くする
    
    // $graph->title->Set("Line plot with null values"); 
    $graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
    $graph->yaxis->SetColor('blue');
    $graph->yaxis->SetWeight(2);
    
    // グラフのタイトル設定
    if ($session->get_local($plot1) != '' && $session->get_local($plot2) != '') {
        $title = $session->get_local($plot1) . 'と' . $session->get_local($plot2);
    } else {
        $title = $session->get_local($plot1);
    }
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
    $graph->title->Set(mb_convert_encoding("{$title} 推移グラフ", 'UTF-8')); 
    $text = new Text(mb_convert_encoding('単位：百万円', 'UTF-8'));
    $text->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $text->SetPos(880, 10);
    $text->SetColor('teal');
    $graph->AddText($text);
    // Setup X-scale
    if ($session->get_local('dataxFlg') == 'on') {
        $graph->xaxis->SetTickLabels($result->get($plot1.'_datax'));
    } else {
        $xaxis = compositionXaxis($result, $plot1, $plot2); // 複合型のX軸を使用する
        $graph->xaxis->SetTickLabels($xaxis, $result->get('xaxis_color'));
        $graph->xaxis->SetLabelAlign('center', 'top');  // 初期値のright→centerへ変更
    }
    $graph->xaxis->SetLabelMargin(1);   // X軸の年月を縦マージン初期値 7→1 へ変更
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 9);   // フォントはボールドも指定できる。
    $graph->xaxis->SetLabelAngle(35);
    $graph->xaxis->SetPos('min');   // 常にX軸をグラフの最下部に表示する
    // プロット１とプロット２のデータをチェックして描画する
    if ($result->get($plot1.'_rows') > 0) {
        // Create the first line
        $p1 = new LinePlot($result->get($plot1.'_data'));
        $p1->mark->SetType(MARK_FILLEDCIRCLE);
        $p1->mark->SetFillColor('blue');
        $p1->mark->SetWidth(3);
        $p1->mark->Show();              // マーク表示
        $p1->SetColor('blue');
        $p1->SetCenter(); 
        $p1->SetWeight(1);              // プロット線の太さ(2ドット→1へ)
        $p1->SetLegend(mb_convert_encoding($session->get_local($plot1), 'UTF-8'));
        // $p1->value->SetFormat('%01.1f'); // 整数部が無い時は0、小数部１桁を指定
        $p1->value->SetFormatCallback('userFormat');    // 上記では３桁のカンマに対応できないため
        $p1->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
        if ($session->get_local('plot1_value') == 'on') {
            $p1->value->Show();
        }
        $graph->Add($p1); 
    }
    if ($result->get($plot2.'_rows') > 0) {
        // ... and the second 
        if ($session->get_local('yaxis') == '2') {
            $graph->SetY2Scale('lin');      // Y2スケールを追加
            $graph->y2axis->SetColor('red');// Y2スケールの色
            $graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
            $graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
        }
        $p2 = new LinePlot($result->get($plot2.'_data'));  // 二つ目のラインプロットクラスの宣言
        $p2->mark->SetType(MARK_IMG_STAR, 'red', 0.7);  // プロットマークの形, 色, 大きさ
        $p2->mark->SetFillColor('red'); // プロットマークの色
        $p2->mark->SetWidth(4);         // プロットマークの大きさ
        $p2->mark->Show();              // マーク表示
        $p2->SetColor('red');           // プロット線の色
        $p2->SetCenter();               // プロットを中央へ
        $p2->SetWeight(1);              // プロット線の太さ(2ドット→1へ)
        $p2->SetLegend(mb_convert_encoding($session->get_local($plot2), 'UTF-8')); 
        // $p2->value->SetFormat('%01.1f'); // 整数部が無い時は0、小数部１桁を指定
        $p2->value->SetFormatCallback('userFormat');    // 上記では３桁のカンマに対応できないため
        $p2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
        $p2->value->SetColor('red');                    // 値のの色
        if ($session->get_local('plot2_value') == 'on') {
            $p2->value->Show();
        }
        if ($session->get_local('yaxis') == '2') {
            $graph->AddY2($p2);             // Y2スケール用のプロット２を追加
        } else {
            $graph->Add($p2);           // 同じY軸でのプロット
        }
    }
    // Output line 
    $graph->Stroke($graph_name); 
    // echo $graph->GetHTMLImageMap("myimagemap"); 
    // echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 
}
////////// グラフのコールバック関数(３桁カンマに小数部１)
function userFormat($aLabel)
{
    return number_format($aLabel, 1);
}
////////// グラフのX軸のprot1とprot2の複合年月の配列を返す
function compositionXaxis($result, $plot1, $plot2)
{
    $p1 = $result->get($plot1.'_datax');
    $p2 = $result->get($plot2.'_datax');
    $xaxis = array();
    $color = array();
    for ($i=0; $i<count($result->get($plot1.'_datax')); $i++) {
        if (isset($p2[$i])) {
            $xaxis[$i] = "{$p1[$i]}\n{$p2[$i]}";
        } else {
            $xaxis[$i] = $p1[$i];
        }
        $color[$i] = 'darkred';
    }
    $result->add('xaxis_color', $color);
    return $xaxis;
}
//////////// グラフの値表示ON/OFFアンカーのソース生成
function getPlotValueOnOff($session, $menu, $uniq)
{
    ///// ソース生成
    $anchor = '';
    if ($session->get_local('plot1_value') == 'on') {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm1={$session->get_local('yyyymm1')}&yyyymm2={$session->get_local('yyyymm2')}&plot1_value=off&{$uniq}'>プロット1金額非表示</a>　\n";
    } else {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm1={$session->get_local('yyyymm1')}&yyyymm2={$session->get_local('yyyymm2')}&plot1_value=on&{$uniq}'>プロット1金額表示</a>　\n";
    }
    if ($session->get_local('plot2_value') == 'on') {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm2={$session->get_local('yyyymm2')}&yyyymm1={$session->get_local('yyyymm1')}&plot2_value=off&{$uniq}' style='color:red;'>プロット2金額非表示</a>　\n";
    } else {
        $anchor .= "<a href='{$menu->out_self()}?yyyymm2={$session->get_local('yyyymm2')}&yyyymm1={$session->get_local('yyyymm1')}&plot2_value=on&{$uniq}' style='color:red;'>プロット2金額表示</a>　\n";
    }
    return $anchor;
}

//////////// 前月・次月のページ制御 データ設定
function setPageData($yyyymm, $name, $result)
{
    $yyyy = substr($yyyymm, 0, 4);
    $mm   = substr($yyyymm, 4, 2);
    if ($mm == 1) {
        $next_yyyymm = $yyyy . '02';
        $yyyy--;
        $pre_yyyymm = $yyyy . '12';
    } elseif ($mm == 12) {
        $pre_yyyymm = $yyyy . '11';
        $yyyy++;
        $next_yyyymm = $yyyy . '01';
    } else {
        $pre_yyyymm = $yyyymm - 1;
        $next_yyyymm = $yyyymm + 1;
    }
    $query = "SELECT pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym = {$pre_yyyymm}";
    $result->add('backward', ' disabled');
    $query = "SELECT pl_bs_ym FROM profit_loss_pl_history WHERE pl_bs_ym = {$next_yyyymm}";
    $result->add('forward', ' disabled');
    if ($name == 'yyyymm1') {
        $result->add('pre_yyyymm1', $pre_yyyymm);
        $result->add('next_yyyymm1', $next_yyyymm);
    } else {
        $result->add('pre_yyyymm2', $pre_yyyymm);
        $result->add('next_yyyymm2', $next_yyyymm);
    }
}

?>
