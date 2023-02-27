//////////////////////////////////////////////////////////////////////////////
// ＴＮＫ規程メニュー専用 入力フォーム等のチェック用 JavaScript             //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/17 Created   authenticate.js                                     //
//////////////////////////////////////////////////////////////////////////////
/* 入力文字が数字かどうかチェック */
/* false=数字でない  ture=数字である */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            return false;
        }
    }
    return true;
}
/* 入力情報のチェック & 補正 */
function inpConf(obj) {
    if (!obj.userid.value.length) {
        alert("[ユーザーID]の入力欄が空白です。ログインできません。");
        obj.userid.focus();
        return false;
    }
    if (!obj.passwd.value.length) {
        alert("[パスワード]の入力欄が空白です。ログインできません。");
        obj.passwd.focus();
        return false;
    }
    return true;
}

function ini_focus(){
    document.login_form.userid.focus();
    document.login_form.userid.select();
}
function next_focus(){
    document.login_form.passwd.focus();
    document.login_form.passwd.select();
}
