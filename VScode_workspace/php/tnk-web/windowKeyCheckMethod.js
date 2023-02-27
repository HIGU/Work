//////////////////////////////////////////////////////////////////////////////
// 共用 単体版(メニューの戻りが無いWindow版)用の大文字変換  JavaScriptクラス//
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/19 Created   windowKeyCheckMethod.js                             //
//////////////////////////////////////////////////////////////////////////////

var _KEYUPPERFLG = false;

/***** キーボード入力イベント処理 共通キー割当て *****/
/***** 単体版 *****/
function evt_key_chk(evt)
{
    // グローバル変数の backward_obj が戻り先のコントロールオブジェクト
    var browser = navigator.appName;
    if (browser.charAt(0) == 'M') {         // IEの場合
        var chk_key = event.keyCode;        // IEではキーコードを調べるには event.keyCode を使う。
    } else {                                // NNの場合を想定
        var chk_key = evt.which;            // NNでは evt.which を使う。(evtはイベントによって呼び出される関数のカッコ内に入れる)
    }
    switch (chk_key) {
    case 113:   // F2
    case 123:   // F12
        // 単体版のためここに戻りのsubmit()は省略する。
    case 112:   // F1   ← これを無効にするには(onHelp='return false')IEのみ
    case 114:   // F3   検索
    case 116:   // F5   更新ボタン
    case 117:   // F6   google
        if (browser.charAt(0) == 'M') {         // IEの場合
            event.keyCode = null;
        } else {                                // NNの場合を想定
            evt.which = null;
        }
        return false;
    default:
        if (chk_key >= 65 && chk_key <= 90) {   // A(a) ～ Z(z)まで、大文字小文字の区別が出来ない
            _KEYUPPERFLG = true;
        } else {
            _KEYUPPERFLG = false;
        }
    }
    return true;
}
function keyInUpper(obj)
{
    if (_KEYUPPERFLG) obj.value = obj.value.toUpperCase();
    return true;
        // http://msdn.microsoft.com/library/default.asp?url=/workshop/author/dhtml/reference/methods/findtext.asp
        var rangeObj = obj.createTextRange();
        rangeObj.collapse(true);
        rangeObj.text = obj.value.toUpperCase();
}

document.onkeydown = evt_key_chk;

