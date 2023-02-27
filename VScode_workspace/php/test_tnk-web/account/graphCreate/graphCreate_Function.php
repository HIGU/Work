<?php
//////////////////////////////////////////////////////////////////////////////
// 経費内訳のグラフ作成メニュー  共用 Function Include File                 //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
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
// 2007/11/05 損益グラフ作成メニューを経費内訳グラフ作成メニューへ改造      //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している

//////////// グラフの描画項目のリテラル値
function getGraphItemArray()
{
    return array(
        '未設定' => 'blank',
        
        '--以下は製造経費の労務費--' => 'noItem',
        '製造経費の役員報酬'      =>  1,
        '製造経費の給料手当'      =>  2,
        '製造経費の賞与手当'      =>  3,
        '製造経費の顧問料'        =>  4,
        '製造経費の法定福利費'    =>  5,
        '製造経費の厚生福利費'    =>  6,
        '製造経費の賞与引当金繰入'=>  7,
        '製造経費の退職給付費用'  =>  8,
        '--以下は製造経費の経費--' => 'noItem',
        '製造経費の旅費交通費'    =>  9,
        '製造経費の海外出張'      => 10,
        '製造経費の通　信　費'    => 11,
        '製造経費の会　議　費'    => 12,
        '製造経費の交際接待費'    => 13,
        '製造経費の広告宣伝費'    => 14,
        '製造経費の求　人　費'    => 15,
        '製造経費の運賃荷造費'    => 16,
        '製造経費の図書教育費'    => 17,
        '製造経費の業務委託費'    => 18,
        '製造経費の事　業　等'    => 36,      // ←注意
        '製造経費の諸税公課'      => 19,
        '製造経費の試験研究費'    => 20,
        '製造経費の雑　費'        => 21,
        '製造経費の修　繕　費'    => 22,
        '製造経費の保障修理費'    => 23,
        '製造経費の事務用消耗品費'=> 24,
        '製造経費の工場消耗品費'  => 25,
        '製造経費の車　両　費'    => 26,
        '製造経費の保　険　料'    => 27,
        '製造経費の水道光熱費'    => 28,
        '製造経費の諸　会　費'    => 29,
        '製造経費の支払手数料'    => 30,
        '製造経費の地代家賃'      => 31,
        '製造経費の寄　付　金'    => 32,
        '製造経費の倉　敷　料'    => 33,
        '製造経費の賃　借　料'    => 34,
        '製造経費の減価償却費'    => 35,
        
        '--以下は販管費の人件費--' => 'noItem',
        '販管費の役員報酬'      => 37,
        '販管費の給料手当'      => 38,
        '販管費の賞与手当'      => 39,
        '販管費の顧問料'        => 40,
        '販管費の法定福利費'    => 41,
        '販管費の厚生福利費'    => 42,
        '販管費の賞与引当金繰入'=> 43,
        '販管費の退職給付費用'  => 44,
        '--以下は販管費の経費--' => 'noItem',
        '販管費の旅費交通費'    => 45,
        '販管費の海外出張'      => 46,
        '販管費の通　信　費'    => 47,
        '販管費の会　議　費'    => 48,
        '販管費の交際接待費'    => 49,
        '販管費の広告宣伝費'    => 50,
        '販管費の求　人　費'    => 51,
        '販管費の運賃荷造費'    => 52,
        '販管費の図書教育費'    => 53,
        '販管費の業務委託費'    => 54,
        '販管費の事　業　等'    => 72,      // ←注意
        '販管費の諸税公課'      => 55,
        '販管費の試験研究費'    => 56,
        '販管費の雑　費'        => 57,
        '販管費の修　繕　費'    => 58,
        '販管費の保障修理費'    => 59,
        '販管費の事務用消耗品費'=> 60,
        '販管費の工場消耗品費'  => 61,
        '販管費の車　両　費'    => 62,
        '販管費の保　険　料'    => 63,
        '販管費の水道光熱費'    => 64,
        '販管費の諸　会　費'    => 65,
        '販管費の支払手数料'    => 66,
        '販管費の地代家賃'      => 67,
        '販管費の寄　付　金'    => 68,
        '販管費の倉　敷　料'    => 69,
        '販管費の賃　借　料'    => 70,
        '販管費の減価償却費'    => 71
    );
}

//////////// グラフ アイテムのvalueから連想キーを取得
function getItemArrayKey($plot)
{
    $res = getGraphItemArray();     // リテラル配列値で取得
    foreach ($res as $key => $value) {
        if ($plot == $value) {
            return $key;
            // if ($plot <= 36) return "製造経費の{$key}"; else return "販管費の{$key}";
        }
    }
    return '';
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
        SELECT pl_bs_ym FROM pl_bs_summary WHERE pl_bs_ym >= 200010 AND pl_bs_ym <= 203003 GROUP BY pl_bs_ym ORDER BY pl_bs_ym DESC
    ";
    $res = array(
        ["202211"],
        ["202211"],
        ["202211"],
        ["202211"],
        ["202211"],
    );
    for ($i=0; $i<count($res); $i++) {
        if ($yyyymm == $res[$i][0]) {
            $ym_form .= "    <option value='{$res[$i][0]}' selected>{$res[$i][0]}</option>\n";
        } else {
            $ym_form .= "    <option value='{$res[$i][0]}'>{$res[$i][0]}</option>\n";
        }
    }
    $ym_form .= "    </select>\n";
    return $ym_form;
}

//////////// グラフのプロット項目 選択フォーム生成
function graphSelectForm($name, $plotItem)
{
    $select = "<select name='{$name}'>\n";
    $res = getGraphItemArray();     // リテラル配列値で取得
    foreach ($res as $key => $value) {
        if ($plotItem == $value) {
            if (preg_match('/^未設定/', $key) || preg_match('/^--/', $key)) {
                $select .= "    <option value='{$value}' style='color:yellow;' selected>{$key}</option>\n";
            } else {
                $select .= "    <option value='{$value}' selected>{$key}</option>\n";
            }
        } elseif (preg_match('/^未設定/', $key) || preg_match('/^--/', $key)) {
            $select .= "    <option value='{$value}' style='color:yellow;'>{$key}</option>\n";
        } else {
            $select .= "    <option value='{$value}'>{$key}</option>\n";
        }
    }
    $select .= "</select>\n";
    return $select;
}

//////////// メインコントロール
function mainController($menu, $request, $session)
{
    //////////// グラフのプロット項目の取得
    if ($request->get('g1plot1') != '') $session->add_local('g1plot1', $request->get('g1plot1'));
    if ($request->get('g1plot2') != '') $session->add_local('g1plot2', $request->get('g1plot2'));
    if ($request->get('g2plot1') != '') $session->add_local('g2plot1', $request->get('g2plot1'));
    if ($request->get('g2plot2') != '') $session->add_local('g2plot2', $request->get('g2plot2'));
    if ($request->get('g3plot1') != '') $session->add_local('g3plot1', $request->get('g3plot1'));
    if ($request->get('g3plot2') != '') $session->add_local('g3plot2', $request->get('g3plot2'));
    //////////// グラフの値表示・非表示の取得
    if ($request->get('plot1_value') != '') $session->add_local('plot1_value', $request->get('plot1_value'));
    if ($request->get('plot2_value') != '') $session->add_local('plot2_value', $request->get('plot2_value'));
    //////////// グラフ ２個目のプロットY軸１個(共用)・２個の取得
    if ($request->get('yaxis') != '') $session->add_local('yaxis', $request->get('yaxis'));
    //////////// グラフのX軸(年月)を共用するか別々にするか
    if ($request->get('dataxFlg') != '') $session->add_local('dataxFlg', $request->get('dataxFlg'));
    //////////// 指定年月の取得
    if ($request->get('yyyymm1') != '' || $request->get('yyyymm2') != '') {
        $session->add_local('yyyymm1', $request->get('yyyymm1'));
        $session->add_local('yyyymm2', $request->get('yyyymm2'));
        // header("Location: http:" . WEB_HOST . "processing_msg.php?script=". SALES ."uriage_graph_all_niti.php");
        header('Location: ' . H_WEB_HOST . '/processing_msg.php?script=' . $menu->out_self());
        exit(); ////////// これがないとスクリプトを最後までチェックするので時間がかかる。
    }
    
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
    if ($session->get_local('g1plot1') != 'blank' && $session->get_local('g1plot2') != 'blank') {
        $rows = getGraphData($session, $result, 'g1plot1');
        getGraphData($session, $result, 'g1plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name1, 'g1plot1', 'g1plot2');
    } elseif ($session->get_local('g1plot1') != 'blank') {
        $rows = getGraphData($session, $result, 'g1plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name1, 'g1plot1');
    }
    if ($session->get_local('g2plot1') != 'blank' && $session->get_local('g2plot2') != 'blank') {
        $rows = getGraphData($session, $result, 'g2plot1');
        getGraphData($session, $result, 'g2plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name2, 'g2plot1', 'g2plot2');
    } elseif ($session->get_local('g2plot1') != 'blank') {
        $rows = getGraphData($session, $result, 'g2plot1');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name2, 'g2plot1');
    }
    if ($session->get_local('g3plot1') != 'blank' && $session->get_local('g3plot2') != 'blank') {
        $rows = getGraphData($session, $result, 'g3plot1');
        getGraphData($session, $result, 'g3plot2');
        if ($rows > 0) graphCreateExecute($session, $result, $graph_name3, 'g3plot1', 'g3plot2');
    } elseif ($session->get_local('g3plot1') != 'blank') {
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
    $month = $mm - 11;
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
    ///// プロット項目取得
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
    for ($i=0; $i<count($res); $i++) {
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
    $query = "SELECT pl_bs_ym FROM act_pl_history WHERE pl_bs_ym = {$pre_yyyymm}";
    $query = "SELECT pl_bs_ym FROM act_pl_history WHERE pl_bs_ym = {$next_yyyymm}";
    if ($name == 'yyyymm1') {
        $result->add('pre_yyyymm1', $pre_yyyymm);
        $result->add('next_yyyymm1', $next_yyyymm);
    } else {
        $result->add('pre_yyyymm2', $pre_yyyymm);
        $result->add('next_yyyymm2', $next_yyyymm);
    }
}

?>
