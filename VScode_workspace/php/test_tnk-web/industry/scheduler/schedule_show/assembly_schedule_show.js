//////////////////////////////////////////////////////////////////////////////
// 組立日程計画(スケジュール)照会 日程計画     MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/24 Created    assembly_schedule_show.js                          //
// 2006/01/31 メソッドをfunction()無名関数(擬似関数)の記述スタイルへ変更    //
// 2006/02/15 トグルスイッチ式の自動更新ON/OFF設定メソッドを追加            //
// 2006/03/03 switchComplete()メソッドを追加 (完成分と未完成分の切替表示)   //
// 2006/03/15 win_open()メソッドを window名なし resizable=yes へ変更        //
// 2006/04/11 トグルスイッチ表示の未完成と完成済 → 予定品と完了品 へ変更   //
// 2006/06/16 ガントチャートのみを別ウィンドウで開く機能を追加 zoomGantt()  //
// 2006/06/22 ズームで開くpageParameter追加に伴いzoomGantt()の?→&に変更    //
// 2006/10/16 ラインの選択方式追加 プロパティlineMethod, setLineMethod()追加//
// 2006/11/09 metaのRefreshをやめてsetInterval()でzoomGanttReload()を呼出し //
// 2007/03/23 win_open()メソッドを menubar=yes へ変更 (印刷プレビュー対応)  //
// 2007/08/21 win_open()メソッドの名前をブランクから'schedule'へ変更        //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_schedule_show class テンプレートの拡張クラスの定義          *
/****************************************************************************
class assembly_schedule_show extends base_class
*/
///// スーパークラスの継承
assembly_schedule_show.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function assembly_schedule_show()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag     = 1;                            // blink_disp()メソッド内で使用する
    this.blink_msg      = "";                           //      〃
    this.AutoReLoad     = "";                           // 自動更新フラグの初期値
    this.AutoReLoadID   = "";                           //    〃   setIntervalのID(戻り値)
    this.CompleteStatus = "";                           // 未完成分か完成済分かの状態 初期値はサーバーサイドで決定
    this.Parameter      = "";                           // Ajax用GETパラメーター
    this.lineMethod     = "1";                          // ラインの指定方法(1=個別選択, 2=複数選択)
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    assembly_schedule_show.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
        // document.body.focus();   // F2/F12キーを有効化する対応
        // document.mhForm.backwardStack.focus();  // 上記はIEのみのため、こちらに変更しNN対応
        // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
        // document.form_name.element_name.select();
    }
    
    /***** 点滅表示メソッド *****/
    /***** blink_flg Private property 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\", \"メッセージ\")", 500)'> *****/
    assembly_schedule_show.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    assembly_schedule_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    assembly_schedule_show.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, 'schedule', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    assembly_schedule_show.prototype.winActiveChk = function ()
    {
        if (document.all) {     // IEなら
            if (document.hasFocus() == false) {     // IE5.5以上で使える
                window.focus();
                return;
            }
            return;
        } else {                // NN ならとワリキッテ
            window.focus();
            return;
        }
    }
    
    /***** 指定の大きさのモーダルダイアログを表示する *****/
    /***** IE 専用なのと Windows XP SP2 ではセキュリティの警告が出る *****/
    /***** ダイアログ内でリクエストを出す場合はフレームを切って行う *****/
    assembly_schedule_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ガントチャートのみを別ウィンドウで開き見出しを固定してスクロール  *****/
    assembly_schedule_show.prototype.zoomGantt = function (url)
    {
        url += "&showMenu=ZoomGantt";
        this.win_open(url, 1024, 768);
    }
    
    /***** ラインの指定方法をチェックして getパラメーターを切替える *****/
    assembly_schedule_show.prototype.targetLineExecute = function (url)
    {
        if (this.lineMethod == "1") {
            location.replace(url + "&targetLineMethod=1");
        } else {
            location.replace(url + "&targetLineMethod=2");
        }
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // Ajaxを使用した日程計画一覧又はガントチャート リロード用実行メソッド
    // parameter : ListTable=日程計画一覧, GanttTable=ガントチャート
    assembly_schedule_show.prototype.AjaxLoadTable = function (showMenu)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // tableのみ抽出
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += this.Parameter;
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            }
        }
        try {
            xmlhttp.open("GET", "assembly_schedule_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** ユーザーのマウス操作によるAjaxリクエストメソッド *****/
    // 処理中メッセージに対応版
    // parameter : ListTable=日程計画一覧, GanttTable=ガントチャート
    assembly_schedule_show.prototype.AjaxLoadTableMsg = function (showMenu, status)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // tableのみ抽出
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += this.Parameter;
        if (status == "page_keep") parm += "&page_keep=on"; // リンク先からの戻り用
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了するまでWaitMessageを出力。
                document.getElementById("showAjax").innerHTML = "<br><br><br><br><br><br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "assembly_schedule_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** トグルスイッチ式の未完成・完成済の表示切替 メソッド *****/
    assembly_schedule_show.prototype.switchComplete = function (status)
    {
        if (this.CompleteStatus == "yes") {
            this.CompleteStatus = "no";
            this.Parameter = "&targetCompleteFlag=no";
            if (status == "Gantt") {
                document.getElementById("CompleteFlag").innerHTML = "予定品";
                this.AjaxLoadTableMsg("GanttTable");
            } else {
                // document.getElementById("CompleteFlag").innerHTML = "計画残数";
                this.AjaxLoadTable("ListTable");
            }
        } else {
            this.CompleteStatus = "yes";
            this.Parameter = "&targetCompleteFlag=yes";
            if (status == "Gantt") {
                document.getElementById("CompleteFlag").innerHTML = "完了品";
                this.AjaxLoadTableMsg("GanttTable");
            } else {
                // document.getElementById("CompleteFlag").innerHTML = "完成数";
                this.AjaxLoadTable("ListTable");
            }
        }
    }
    
    /***** トグルスイッチ式の自動更新ON/OFF設定メソッド *****/
    assembly_schedule_show.prototype.switchAutoReLoad = function (targetFunction, mSec)
    {
        if (this.AutoReLoad == 'ON') {      // ON → OFF
            if (this.AutoReLoadID) {
                clearInterval(this.AutoReLoadID);
                this.AutoReLoad = "OFF";
                document.getElementById("toggleView").innerHTML = "MAN";
                alert("\n画面 更新 を MAN(手動) にしました。\n");
            }
        } else {                            // OFF → ON
            if (mSec >= 15000 && mSec <= 300000) {  // 15秒以上で300秒(5分)以下
                this.AutoReLoadID = setInterval(targetFunction, mSec);
                document.getElementById("toggleView").innerHTML = "AUT";
                if (this.AutoReLoad != "") {        // 初回の場合はMessageを表示しない
                    alert("\n画面 更新 を AUT(自動) にしました。\n");
                }
                this.AutoReLoad = "ON";
            }
        }
    }
    
    /***** トグルスイッチ式のラインの選択方法セットメソッド *****/
    assembly_schedule_show.prototype.setLineMethod = function (flag)
    {
        if (flag != "") {
            this.lineMethod = flag;
            if (this.lineMethod == "1") {
                document.getElementById("lineMethod1").style.color = "red";
                document.getElementById("lineMethod2").style.color = "black";
            } else {
                document.getElementById("lineMethod2").style.color = "blue";
                document.getElementById("lineMethod1").style.color = "black";
            }
            return;
        }
        if (this.lineMethod == "1") {
            this.lineMethod = "2";
            document.getElementById("lineMethod2").style.color = "blue";
            document.getElementById("lineMethod1").style.color = "black";
        } else {
            this.lineMethod = "1";
            document.getElementById("lineMethod1").style.color = "red";
            document.getElementById("lineMethod2").style.color = "black";
        }
        return;
    }
    
    /***** ズームガントチャートのリロードメソッド *****/
    assembly_schedule_show.prototype.zoomGanttReload = function (url)
    {
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        // var urlHeader = "assembly_schedule_show_ViewZoomGanttHeader.php?" + Date.parse();
        // var urlBody   = "assembly_schedule_show_ViewZoomGanttBody.php?" + Date.parse();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    // これで更新が出来たが画面がチラツクので中止 Header.phpとBody.php内部でsetAttribute()を使用して対処
                // window.header.location.reload(true);
                // window.list.location.reload(true);
                     // これは更新しない(なぜ？)
                // document.getElementById("frameHeader").setAttribute("src", urlHeader);
                // document.getElementById("frameBody").setAttribute("src", urlBody);
                      // これも更新しない
                // document.getElementById("frameHeader").src = urlHeader;
                // document.getElementById("frameBody").src = urlBody;
            }
        }
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    return this;    // Object Return
    
}   /* class assembly_schedule_show END  */


///// インスタンスの生成
var AssemblyScheduleShow = new assembly_schedule_show();

