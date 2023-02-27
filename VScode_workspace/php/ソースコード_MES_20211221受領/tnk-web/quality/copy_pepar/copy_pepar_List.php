<?php
//////////////////////////////////////////////////////////////////////////////////////////
// 納入予定グラフ・検査仕掛明細の照会(検査の仕事量把握)  Listフレーム                   //
// Copyright (C) 2004-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp                  //
// Changed history                                                                      //
// 2021/07/07 Created  order_schedule_List.php -> copy_pepar_List.php                   //
//////////////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
require_once ('copy_pepar_function.php');   // copy_pepar 関係の共通 function
require_once ('../../tnk_func.php');        // TNK date_offset()で使用
//////////// セッションのインスタンスを登録
$session = new Session();
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(70, 72);                   // site_index=70(品質・環境メニュー) site_id=72(部署別コピー用紙使用量)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'copy_pepar/copy_pepar.php');
//////////// 呼出先のaction名とアドレス設定

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('部署別コピー用紙使用量');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///////// パラメーターチェックと設定
if (isset($_REQUEST['tnk_ki'])) {
    $div = $_REQUEST['tnk_ki'];                // 事業部
    $_SESSION['tnk_ki'] = $_REQUEST['tnk_ki'];    // セッションに保存
} else {
    if (isset($_SESSION['tnk_ki'])) {
        $div = $_SESSION['tnk_ki'];            // Default(セッションから)
    } else {
        $div = getTnkKi();                         // 初期値
    }
}

if (isset($_REQUEST['input_mode'])) {
    $select = 'input_mode';                      // 未検収リスト
    $_SESSION['select'] = 'input_mode';          // セッションに保存
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // 納入予定グラフ
    $_SESSION['select'] = 'graph';          // セッションに保存
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(セッションから)
    } else {
        $select = 'graph';                  // 初期値(納入予定グラフ)あまり意味は無い
    }
}

if( isset($_REQUEST['update']) ) {
    $request = new Request;
    updateKiInfo($request, $div);   // 更新処理
}

if( isset($_REQUEST['rec_add']) ) {
    addRecord($div);                // レコード追加処理
}

if( isset($_REQUEST['busyo_copy']) ) {
    setBusyoRec($div);              // 前期の部署名をコピー
}

/////////// 
$uniq = 'id=' . uniqid('copy_paper');    // キャッシュ防止用ユニークID

/////////// クライアントのホスト名(又はIP Address)の取得
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);

////////// SQL Statement を取得
$tbl_rows = getKiInfo($div, $tbl);
if( $tbl_rows <= 0 ) {
    $view = 'NG';
} else {
    $view = 'OK'; // 初期設定 エラー対応の為
}

if( $select == 'input_mode' ) {
    ;
} elseif ($select == 'graph') {
    $tbl_bef_rows = getKiInfo($div-1, $tbl_bef);

    $c_rows = getColumn($column);   // x軸に表示する情報

    require_once ('../../../jpgraph.php');
    require_once ('../../../jpgraph_bar.php');

    for( $v_cnt=0; $v_cnt<$tbl_rows; $v_cnt++ ) {
        $graph_title = $tbl[$v_cnt][2] . ' コピー用紙使用量';

        $datax = array(); $datay = array();
        $datax[0] = mb_convert_encoding('前期', 'UTF-8');
        $datax_color[0] = 'blue';

        $datay[0] = 0;  // 初期値 前期の枚数
        for( $cnt=0; $cnt<$tbl_bef_rows; $cnt++ ) {
            if( $tbl[$v_cnt][2] != $tbl_bef[$cnt][2]) continue;
            $datay[0] = $tbl_bef[$cnt][3];// 前期の枚数
            break;
        }

        $datax[1] = mb_convert_encoding('今期', 'UTF-8');
//        $datay[1] = mb_convert_encoding(number_format($tbl[$v_cnt][3], 0), 'UTF-8');    // 今期の合計
        $datay[1] = $tbl[$v_cnt][3];    // 今期の合計
        $datax_color[1] = 'darkred';

        for ($r=0, $c=1; $r<$c_rows-1; $r++, $c++) {
            $datax[$r+2] = mb_convert_encoding($column[$c][0], 'UTF-8'); // ４月〜３月をセット
            $datax_color[$r+2] = 'black';
        }

        for( $r=0, $f=4; $r<12; $r++, $f++ ) {
            if( $tbl[0][$f] != 0 ) {
                $datay[$r+2] = $tbl[$v_cnt][$f];    // 各月の使用枚数をセット
            } else {
                $datay[$r+2] = '';    // 各月の使用枚数をセット
            }
        }
/**
        require_once ('../../../jpgraph.php');
        require_once ('../../../jpgraph_bar.php');
/**/
//        $graph = new Graph(820, 360);               // グラフの大きさ X/Y
        $graph[$v_cnt] = new Graph(820, 360);               // グラフの大きさ X/Y
        $graph[$v_cnt]->SetScale('textlin'); 
        $graph[$v_cnt]->img->SetMargin(50, 30, 40, 70);    // グラフ位置のマージン 左右上下
        $graph[$v_cnt]->SetShadow(); 
        $graph[$v_cnt]->title->SetFont(FF_GOTHIC, FS_NORMAL, 14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
        $graph[$v_cnt]->title->Set(mb_convert_encoding($graph_title, 'UTF-8')); 
        $graph[$v_cnt]->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph[$v_cnt]->yaxis->title->Set(mb_convert_encoding('使用枚数', 'UTF-8'));
        $graph[$v_cnt]->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph[$v_cnt]->xaxis->SetTickLabels($datax, $datax_color); // 項目設定
        // $graph[$v_cnt]->xaxis->SetFont(FF_FONT1);     // フォントはボールドも指定できる。
        $graph[$v_cnt]->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);     // フォントはボールドも指定できる。
        $graph[$v_cnt]->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot = new BarPlot($datay); 
//        $bplot[$v_cnt] = new BarPlot($datay);
        $bplot->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot->SetColor('navy');
        $bplot->value->SetFormat('%d');     // 整数フォーマット
        $bplot->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/26 追加
        $bplot->value->Show();              // 数値表示
        $targ = array();
        $alts = array();

        $targ[0] = "JavaScript:dummy()";
//        $alts[0] = '前期の累計使用枚数＝%d';
        if( $cnt >= $tbl_bef_rows ) {
            $alts[0] = '前期の累計使用枚数＝%d';
        } else {
            $work = number_format($tbl_bef[$cnt][3], 0);
            $alts[0] = "前期の累計使用枚数＝{$work} 枚";
        }
        $targ[1] = "JavaScript:dummy()";
//        $alts[1] = '今期の累計使用枚数＝%d';
        $work = number_format($tbl[$v_cnt][3], 0);
        $alts[1] = "今期の累計使用枚数＝{$work} 枚";
        for ($r=0; $r<$c_rows-1; $r++) {
            $targ[$r+2] = "JavaScript:dummy()";
//            $alts[$r+2] = "{$tbl[$v_cnt][2]}：{$column[$r+1][0]}の使用枚数＝%d 枚";
            $work = number_format($tbl[$v_cnt][$r+4], 0);
            $alts[$r+2] = "{$tbl[$v_cnt][2]}：{$column[$r+1][0]}の使用枚数＝{$work} 枚";
        }
        $bplot->SetCSIMTargets($targ, $alts); 
        $graph[$v_cnt]->Add($bplot);
        $graph_name = "graph/copy_paper_{$_SESSION['User_ID']}_{$v_cnt}_.png";
        $graph[$v_cnt]->Stroke($graph_name);
        chmod($graph_name, 0666);                   // fileを全てrwモードにする
    }
}

/////////// 自動更新と手動更新の条件切換え
$auto_reload = 'off';

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php // if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
.pt10b {
    font-size:   0.8em;
    font-weight: bold;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left:    20px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_gray {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
    color: gray;
}
.winbox_mark {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#eaeaee;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
<script type='text/javascript' language='JavaScript'>
<!--
function init() {
     setInterval('document.reload_form.submit()', 60000);   // 60秒
     //  onLoad='init()' ←これを <body>タグへ入れればOK
}
function win_open(url) {
    var w = 820;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open2(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open3(url) {
    var w = 1100;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win3', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open4(url) {
    var w = 900;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win3', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function input_mode() {
    document.input_mode_form.submit();
}
function dummy() {
    ;
}
// 数字チェック
function num_check(obj) {
//    alert('TEST' + obj.value.replace(/[^0-9]+/i,''));
    return obj.value.replace(/[^0-9]+/i,'');
}
// 指定月の合計を計算
function month_sum(row, month) {
    var total = 0;
    var name = "";
    for( var r=1; r<row; r++ ) {
        name = r + '-' + month;
        total = total + Number(document.getElementsByName(name)[0].value);
    }
    var id = "0_0-" + month;    // span用
    var obj = document.getElementById(id);

    total = total.toLocaleString().split('.')[0];   // 3桁(,)区切り後、(.)で分割し、整数部を取得
    if( obj.innerHTML != total ) {
        obj.style.color = 'white';          // 文字色を〇色にする。
        obj.style.backgroundColor = 'red';  // 背景色を〇色にする。
    }

    obj.innerHTML = total;
    return ;
}
// Enter キー押下時、セルの移動
function enter_key( obj, row, month ) {
    if( event.keyCode == 13 ) { // Enter キーが押された
        var max = document.getElementsByName('tbl_rows')[0].value;
        if( event.shiftKey ) {  // Shift キー 押されてるなら上の行
            var name = (row-=1) + '-' + month;
            if( row == 0 ) {
                name = (max-1) + '-' + month;    // 最終行なら 先頭行へ
            }
        } else {
            var name = (row+=1) + '-' + month;
            if( row >= max ) {  // Shift キー 押されてないなら下の行
                name = '1-' + month;    // 最終行なら 先頭行へ
            }
        }

        document.getElementsByName(name)[0].focus();
        document.getElementsByName(name)[0].select();
    }
}
//alert('TEST:');
// -->
</script>
<form name='reload_form' action='copy_pepar_List.php' method='get' target='_self'>
</form>
<form name='rec_add_form' action='copy_pepar_List.php' method='get' target='_self'>
    <input type='hidden' name='rec_add' value='on'>
</form>
<form name='busyo_copy_form' action='copy_pepar_List.php' method='get' target='_self'>
    <input type='hidden' name='busyo_copy' value='on'>
</form>
<form name='input_mode_form' action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
    <input type='hidden' name='input_mode' value='入力'>
    <input type='hidden' name='tnk_ki' value='<?php echo $div?>'>
</form>
</head>

<body <?php if ($auto_reload == 'on') echo "onLoad='init()'"; ?>>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>　データがありません！</b>
                </td>
            </tr>
        </table>
        <?php if( $select == 'input_mode' ) { ?>
        <BR><BR><BR><BR><BR><BR><BR><BR><BR>
        <input type="button" value="データを入力する。" name="rec_add" onClick='document.rec_add_form.submit()'>　
        <?php } ?>
        <?php } elseif ($select == 'input_mode') { ?>
<form name='update_form' action='copy_pepar_List.php' method='get' target='_self'>
    <input type='hidden' name='update' value='on'>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
        <?php
        for( $r=1; $r<$tbl_rows; $r++ ) {
        echo "<tr>";
        echo "  <td width='88' align='center' nowrap>";
                    if( $r<$tbl_rows ) {
                        if( $tbl[$r][2] != "" ) {
        echo "      <input type='text' size='14' maxlength='7' name='{$r}-2' value='{$tbl[$r][2]}' onkeyup='enter_key(this, {$r}, 2);'>";
                        } else {
        echo "      <input type='text' size='14' maxlength='7' name='{$r}-2' onkeyup='enter_key(this, {$r}, 2);'>>";
                        }
                    } else {
        echo "　";
                    }
        echo "  </td>";
                for( $f=4; $f<16; $f++ ) {
        echo "  <td width='61' align='right' nowrap>";
                    if( $r<$tbl_rows ) {
                        if( $tbl[0][$f] != 0 ) {
        echo "      <input type='text' style='text-align: right;' size='9' maxlength='7' name='{$r}-{$f}' value='{$tbl[$r][$f]}' onkeyup='value = num_check(this); month_sum($tbl_rows, $f); enter_key(this, {$r}, {$f});'>";
                        } else {
        echo "      <input type='text' style='text-align: right;' size='9' maxlength='7' name='{$r}-{$f}' value='' onkeyup='value = num_check(this); month_sum($tbl_rows, $f); enter_key(this, {$r}, {$f});'>";
                        }
                    } else {
        echo "      <input type='text' style='text-align: right;' size='9' maxlength='7' name='{$r}-{$f}' value='' onkeyup='value = num_check(this); month_sum($tbl_rows, $f); enter_key(this, {$r}, {$f});'>";
                    }
        echo "  </td>";
                }
        echo "</tr>";
        }
        // 合計行の処理
        echo "<tr>";
        echo "  <td width='88' align='center' nowrap>";
        echo "      <input type='hidden' name='0-2' value='{$tbl[0][2]}'>";
        echo        $tbl[0][2]; // 合計
        echo "  </td>";
        for( $f=4; $f<16; $f++ ) {
        echo "  <td width='61' align='right' nowrap>";
        echo "      <input type='hidden' name='0-{$f}' value='{$tbl[0][$f]}'>";
                        if( $tbl[0][$f] != 0 ) {
        echo "      <span id='0_0-{$f}'>" . number_format($tbl[0][$f], 0) . "</span>";  // 各月の合計値
                        } else {
        echo "      <span id='0_0-{$f}'>---</span>";  // 各月の合計値
                        }
        echo "  </td>";
        }
        echo "</tr>";

        echo "<input type='hidden' name='tbl_rows' value='$tbl_rows'>";
        ?>
        </table> <!----- ダミー End ----->
<!--
        <BR>　<input type="button" value="行　追加" name="rec_add" onClick='document.rec_add_form.submit()'>　
-->
        <BR>　<input type="button" value="入力行追加" name="rec_add" onClick='document.rec_add_form.submit()'>　
            ※ 入力用の行を追加します。（更新前に実行した場合、変更内容は保存されません。）
<!--
        <BR><BR>　<input type="button" value="　更 新　" name="update" onClick='document.update_form.submit()'>　
-->
        <BR><BR>　<input type="button" value="更新（保存）" name="update" onClick='document.update_form.submit()'>　
            ※ 入力内容を保存します。（部署が空欄の行は、削除されます。）
        <?php if( $tbl_rows == 1 && $tbl[0][2] == "合　計" ) { ?>
<!--
        <BR><BR>　<input type="button" value="前期部署" name="busyo_copy" onClick='document.busyo_copy_form.submit()'>　
-->
        <BR><BR>　<input type="button" value="部署　コピー" name="busyo_copy" onClick='document.busyo_copy_form.submit()'>　
            ※ 前期の部署名をコピーします。（入力行がない時のみ使用可）
        <?php } ?>
<!-- -->
        <BR><BR><font color='red'>※操作説明（<a href="download_file.php/部署別コピー用紙使用量_マニュアル(入力).pdf" align='center'>マニュアル</a>）</font>
<!-- -->
            </td></tr>
        </table>
</form>
        <?php } elseif ($select == 'graph') { ?>
        <?php 
        // グラフ 表示の処理
        for( $v_cnt=1; $v_cnt<$tbl_rows; $v_cnt++ ) {
            if( $tbl[$v_cnt][2] != "" ) {
                echo "<table width='100%' border='0'>";
                echo "  <tr>";
                echo "      <td align='center'>";
                                $name = 'copy_pepar_map' . $v_cnt;
                echo            $graph[$v_cnt]->GetHTMLImageMap($name);
                                $graph_name = "graph/copy_paper_{$_SESSION['User_ID']}_{$v_cnt}_.png";
                echo "          <img src='{$graph_name}?{$uniq}' ismap usemap='#{$name}' alt='{$tbl[$v_cnt][2]} コピー用紙使用量比較グラフ' border='0'>\n";
                echo "      </td>";
                echo "  </tr>";
                echo "</table>";
            }
        }
        echo "<table width='100%' border='0'>";
        echo "  <tr>";
        echo "      <td align='center'>";
                        $name = 'copy_pepar_map' . '0';
        echo            $graph[0]->GetHTMLImageMap($name);
                        $graph_name = "graph/copy_paper_{$_SESSION['User_ID']}_0_.png";
        echo "          <img src='{$graph_name}?{$uniq}' ismap usemap='#{$name}' alt='{$tbl[0][2]} コピー用紙使用量比較グラフ' border='0'>\n";
        echo "      </td>";
        echo "  </tr>";
        echo "</table>";
        ?>
        <?php } ?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // リロード用１０秒
// -->
</script>
<?php echo $menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
