//////////////////////////////////////////////////////////////////////////////
// 刻印管理形状マスターメンテナンス   照会＆メンテナンス                    //
//                      入力チェック JavaScript                             //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/07/24 Created   punchMark_shapeMasterMnt.js                         //
// 2007/10/20 削除の確認メッセージを追加                               小林 //
//////////////////////////////////////////////////////////////////////////////

/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* 入力文字がアルファベットかどうかチェック isDigit()の逆 */
function isABC(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // スペースはOK
            return false;
        }
    }
    return true;
}

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
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

/* 入力内容のチェック関数 */
function chk_entry(obj) {
    if (obj.shape_code.value.length == 0) {
        alert('サイズコードが入力されていません！');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if ( !(isDigit(obj.shape_code.value)) ) {
        alert('サイズコードは数字以外入力出来ません！');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    }
    
    if (obj.shape_name.value.length == 0) {
        alert('サイズ名が入力されていません！');
        obj.shape_name.focus();
        obj.shape_name.select();
        return false;
    }
    return true;
}
/* 追加入力時の内容チェック関数 */
function checkEdit(obj) {
    if (obj.shape_code.value.length == 0) {
        alert('サイズコードが入力されていません！');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if (obj.shape_code.value.length > 3) {
        alert('サイズコードは３桁までです！');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if ( !(isDigit(obj.shape_code.value)) ) {
        alert('サイズコードは数字以外入力出来ません！');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    }
    
    if (obj.shape_name.value.length == 0) {
        alert('サイズ名が入力されていません！');
        obj.shape_name.focus();
        obj.shape_name.select();
        return false;
    }
    document.entry_form.entry.value = "追加";
    document.entry_form.submit();
    return true;
}
/* 削除時の内容チェック関数 */
function checkDelete(obj) {
    if (obj.shape_code.value.length == 0) {
        alert('サイズコードが入力されていません！');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if ( !(isDigit(obj.shape_code.value)) ) {
        alert('サイズコードは数字以外入力出来ません！');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    }
    if (confirm("削除します。よろしいですか？")) {
        document.entry_form.del.value = "削除";
        document.entry_form.submit();
    }
    return true;
}
