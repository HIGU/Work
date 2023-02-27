//////////////////////////////////////////////////////////////////////////////
// 長期滞留部品の照会 最終入庫日指定で在庫あり MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created    long_holding_parts.js                              //
// 2006/04/06 集合出庫の範囲及び回数(物の動き)の条件オプションを実装        //
//            checkbox は checked == true でチェック(valueは常にあり)       //
// 2008/03/11 増山増山部長依頼で最終入庫日の範囲を1年前から11ヶ月前に変更   //
//                                                                     大谷 //
// 2011/07/28 親機種を追加                                             大谷 //
// 2013/10/10 出庫範囲を60ヶ月前まで抽出できるように変更               大谷 //
// 2019/01/28 ツールを追加、バイモル・標準をコメント化                 大谷 //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     long_holding_parts class base_class の拡張クラスの定義            *
/****************************************************************************
class long_holding_parts extends base_class
*/
///// スーパークラスの継承
long_holding_parts.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function long_holding_parts()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "最終入庫日と製品グループを指定して下さい。";
                                                        //     〃      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    long_holding_parts.prototype.set_focus = function (obj, status)
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
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    long_holding_parts.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            // 初期値をプロパティで指定したため以下をコメント
            // this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    long_holding_parts.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    long_holding_parts.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, '', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    long_holding_parts.prototype.winActiveChk = function ()
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
    long_holding_parts.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(開始日・終了日・部品番号) *****/
    long_holding_parts.prototype.checkConditionForm = function (obj)
    {
        // obj.targetDate.value = obj.targetDate.value.toUpperCase();
        if (!obj.targetDate.value) {
            alert("入庫日 開始 月数が入力されていません！");
            obj.targetDate.focus();
            // obj.targetDate.select();
            return false;
        }
        if (!this.isDigit(obj.targetDate.value)) {
            alert("入庫日 開始 月数は数字で入力して下さい。");
            obj.targetDate.focus();
            // obj.targetDate.select();
            return false;
        }
        if (obj.targetDate.value < 11 || obj.targetDate.value > 84) {
            alert("入庫日 開始 月数は11ヶ月から84ヶ月です。");
            obj.targetDate.focus();
            // obj.targetDate.select();
            return false;
        }
        ///// 範囲月数
        if (!obj.targetDateSpan.value) {
            alert("入庫日 範囲 月数が入力されていません！");
            obj.targetDateSpan.focus();
            // obj.targetDateSpan.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateSpan.value)) {
            alert("入庫日 範囲 月数は数字で入力して下さい。");
            obj.targetDateSpan.focus();
            // obj.targetDateSpan.select();
            return false;
        }
        if (obj.targetDateSpan.value < 1 || obj.targetDateSpan.value > 120) {
            alert("入庫日 範囲 月数は1ヶ月から120ヶ月です。");
            obj.targetDateSpan.focus();
            // obj.targetDateSpan.select();
            return false;
        }
        ///// 集合出庫の範囲月数
        if (!obj.targetOutDate.value) {
            alert("集合出庫 範囲 月数が入力されていません！");
            obj.targetOutDate.focus();
            // obj.targetOutDate.select();
            return false;
        }
        if (!this.isDigit(obj.targetOutDate.value)) {
            alert("集合出庫 範囲 月数は数字で入力して下さい。");
            obj.targetOutDate.focus();
            // obj.targetOutDate.select();
            return false;
        }
        if (obj.targetOutDate.value < 1 || obj.targetOutDate.value > 60) {
            alert("集合出庫 範囲 月数は1ヶ月から60ヶ月です。");
            obj.targetOutDate.focus();
            // obj.targetOutDate.select();
            return false;
        }
        ///// 集合出庫の範囲内での回数
        if (!obj.targetOutCount.value) {
            alert("集合出庫 範囲内での 回数が入力されていません！");
            obj.targetOutCount.focus();
            // obj.targetOutCount.select();
            return false;
        }
        if (!this.isDigit(obj.targetOutCount.value)) {
            alert("集合出庫 範囲内での 回数は数字で入力して下さい。");
            obj.targetOutCount.focus();
            // obj.targetOutCount.select();
            return false;
        }
        if (obj.targetOutCount.value < 0 || obj.targetOutCount.value > 2) {
            alert("集合出庫 範囲内での 回数は０回から２回です。");
            obj.targetOutCount.focus();
            // obj.targetOutCount.select();
            return false;
        }
        switch (obj.targetDivision.value) {
        case "AL" :
        case "CA" :
        case "CH" :
        case "CS" :
        case "LA" :
        /*
        case "LH" :
        case "LB" :
        */
        case "TA" :
        case "OT" : // OTHER その他 完成入庫分
            obj.exec.focus();       // obj.targetDivision のフォーカスを外すため
            break;
        default :
            alert("製品区分が不正です。");
            obj.targetDivision.focus();
            return false;
        }
        this.parameter  = "&targetDate=" + obj.targetDate.value;
        this.parameter += "&targetDateSpan=" + obj.targetDateSpan.value;
        this.parameter += "&targetDivision=" + obj.targetDivision.value;
        if (obj.targetOutFlg.checked == true) {
            this.parameter += "&targetOutFlg=" + obj.targetOutFlg.value;
        } else {
            this.parameter += "&targetOutFlg=off";
        }
        this.parameter += "&targetOutDate=" + obj.targetOutDate.value;
        this.parameter += "&targetOutCount=" + obj.targetOutCount.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    long_holding_parts.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
        }
        // 点滅のメッセージを変更する
        this.blink_msg = "部品番号をクリックすれば在庫経歴を表示します。項目クリックでソートします。";
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    long_holding_parts.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        /***
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        ***/
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
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "long_holding_parts_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    long_holding_parts.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        this.blink_msg = "最終入庫日と製品グループを指定して下さい。";
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    long_holding_parts.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** long_holding_parts_ViewHeader.html用のソート項目 強調 表示 メソッド *****/
    long_holding_parts.prototype.highlight = function ()
    {
        if (location.search.substr(1, 9) == "item=tana") {
            // document.getElementById("tana").style.color = "white";
            document.getElementById("tana").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=parts") {
            // document.getElementById("parts").style.color = "white";
            document.getElementById("parts").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=name") {
            // document.getElementById("name").style.color = "white";
            document.getElementById("name").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=parent") {
            // document.getElementById("name").style.color = "white";
            document.getElementById("parent").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=date") {
            // document.getElementById("date").style.color = "white";
            document.getElementById("date").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 11) == "item=in_pcs") {
            // document.getElementById("in_pcs").style.color = "white";
            document.getElementById("in_pcs").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=stock") {
            // document.getElementById("stock").style.color = "white";
            document.getElementById("stock").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=tanka") {
            // document.getElementById("tanka").style.color = "white";
            document.getElementById("tanka").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=price") {
            // document.getElementById("price").style.color = "white";
            document.getElementById("price").style.backgroundColor = "#ffffc6";
        }
    }
    
    return this;    // Object Return
    
}   /* class long_holding_parts END  */


///// インスタンスの生成
var LongHoldingParts = new long_holding_parts();

