//////////////////////////////////////////////////////////////////////////////
// 部課長用会議スケジュール照会                MVC View部(JavaScriptクラス) //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created    meeting_schedule_manager.js                        //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     meeting_schedule_manager_show class テンプレートの拡張クラスの定義          *
/****************************************************************************
class meeting_schedule_manager_show extends base_class
*/
///// スーパークラスの継承
meeting_schedule_manager_show.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function meeting_schedule_manager_show()
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
    meeting_schedule_manager_show.prototype.set_focus = function (obj, status)
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
    meeting_schedule_manager_show.prototype.blink_disp = function (id_name)
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
    meeting_schedule_manager_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    meeting_schedule_manager_show.prototype.win_open = function (url, w, h)
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
    meeting_schedule_manager_show.prototype.winActiveChk = function ()
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
    meeting_schedule_manager_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ガントチャートのみを別ウィンドウで開き見出しを固定してスクロール  *****/
    meeting_schedule_manager_show.prototype.zoomGantt = function (url)
    {
        url += "&showMenu=ZoomGantt";
        this.win_open(url, 1024, 768);
    }
    
    /***** ラインの指定方法をチェックして getパラメーターを切替える *****/
    meeting_schedule_manager_show.prototype.targetLineExecute = function (url)
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
    meeting_schedule_manager_show.prototype.AjaxLoadTable = function (showMenu)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // tableのみ抽出
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += "&year="                + document.ControlForm.year.value;
        parm += "&month="               + document.ControlForm.month.value;
        parm += "&day="                 + document.ControlForm.day.value;
        parm += "&my_flg="              + document.ControlForm.my_flg.value;
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
            xmlhttp.open("GET", "meeting_schedule_manager_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** ユーザーのマウス操作によるAjaxリクエストメソッド *****/
    // 処理中メッセージに対応版
    // parameter : ListTable=日程計画一覧, GanttTable=ガントチャート
    meeting_schedule_manager_show.prototype.AjaxLoadTableMsg = function (showMenu, status)
    {
        var parm = "?";
        parm += "showMenu=" + showMenu  // tableのみ抽出
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        parm += "&year="                + document.ControlForm.year.value;
        parm += "&month="               + document.ControlForm.month.value;
        parm += "&day="                 + document.ControlForm.day.value;
        parm += "&my_flg="              + document.ControlForm.my_flg.value;
        
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
            xmlhttp.open("GET", "meeting_schedule_manager_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** トグルスイッチ式の未完成・完成済の表示切替 メソッド *****/
    meeting_schedule_manager_show.prototype.switchComplete = function (status)
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
    meeting_schedule_manager_show.prototype.switchAutoReLoad = function (targetFunction, mSec)
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
    meeting_schedule_manager_show.prototype.setLineMethod = function (flag)
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
    meeting_schedule_manager_show.prototype.zoomGanttReload = function (url)
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
    
    /***** ControlForm の Submit メソッド 二重送信対策 *****/
    meeting_schedule_manager_show.prototype.ControlFormSubmit = function (radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // ←これが二重Submitの対策
    }
    
    /***** お気に入りにアイコンを追加する 目的はデスクトップにアイコンを貼り付ける為 *****/
    meeting_schedule_manager_show.prototype.addFavoriteIcon = function (url, uid)
    {
        if (!confirm("お気に入りにアイコンを追加します。\n\n宜しいですか？")) return false;
        if (document.all && !window.opera) {
            if (uid >= 100 && uid <= 999999) {
                window.external.AddFavorite(url + "?calUid=" + uid, "部課長スケジュール");
            } else {
                window.external.AddFavorite(url, "部課長スケジュール");
            }
        }
        return false;       // ←これは二重 実行の対策
    }
    
    return this;    // Object Return
    
}   /* class assembly_schedule_show END  */


///// インスタンスの生成
var MeetingScheduleManager = new meeting_schedule_manager_show();

