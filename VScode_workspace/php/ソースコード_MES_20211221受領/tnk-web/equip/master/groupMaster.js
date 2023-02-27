//////////////////////////////////////////////////////////////////////////////
// 設備・機械のグループ(工場)区分 マスター 照会＆メンテナンス               //
//                      入力チェック JavaScript                             //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   groupMaster.js                                      //
//////////////////////////////////////////////////////////////////////////////

/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for(var i=0; i<len; i++) {
        c = str.charAt(i);
        if( ('0' > c) || (c > '9') ) return false;
    }
    return true;
}

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (var i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1個目かチェック
                cnt_dot++;
            } else {
                return false;       // 2個目は false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

function isABC(str) {
    // var str = str.toUpperCase();    // 必要に応じて大文字に変換
    var len = str.length;
    var c;
    for (var i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // スペースはOK
            return false;
        }
    }
    return true;
}


function chk_groupMaster(obj) {
    if (obj.group_no.value.length == 0) {
        alert("工場区分(グループコード)がブランクです。");
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    if (!isDigit(obj.group_no.value)) {
        alert("工場区分(グループコード)に数字以外のデータがあります。");
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    if ( (obj.group_no.value < 1) || (obj.group_no.value > 999) ) {
        alert("工場区分(グループコード)は１〜999までです。");
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    if (obj.group_name.value.length == 0) {
        alert("工場名(グループ名)がブランクです。");
        obj.group_name.focus();
        obj.group_name.select();
        return false;
    }
    if ( (obj.active.value.toUpperCase() != 'T') && (obj.active.value.toUpperCase() != 'F') ) {
        alert("有効・無効の設定値が異常です！ 管理担当者へ連絡して下さい。");
        obj.active.focus();
        obj.active.select();
        return false;
    }
    return true;
}


