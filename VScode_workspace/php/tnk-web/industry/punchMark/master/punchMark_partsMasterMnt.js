//////////////////////////////////////////////////////////////////////////////
// 刻印管理部品番号マスターメンテナンス   照会＆メンテナンス                //
//                      入力チェック JavaScript                             //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/07/30 Created   punchMark_partsMasterMnt.js                         //
// 2007/10/20 削除の確認メッセージとpunchMark_codeの入力チェックを追加 小林 //
// 2007/11/10 キーフィールド削除 function を追加                       小林 //
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
    if (obj.parts_no.value.length == 0) {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    
    if (obj.punchMark_code.value.length == 0) {
        alert('刻印コードが入力されていません！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    return true;
}
/* 追加入力時の内容チェック関数 */
function checkEdit(obj) {
    if (obj.parts_no.value.length == 0) {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    } else if (obj.parts_no.value.length > 9) {
        alert('部品番号は９桁までです！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.punchMark_code.value.length == 0) {
        alert('刻印コードが入力されていません！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    } else if (obj.punchMark_code.value.length > 6) {
        alert('部品番号は６桁までです！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    document.entry_form.entry.value = "追加";
    document.entry_form.submit();
    return true;
}
/* 変更入力時の内容チェック関数 */
function checkChange(obj) {
    if (obj.parts_no.value.length == 0) {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    } else if (obj.parts_no.value.length > 9) {
        alert('部品番号は９桁までです！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.punchMark_code.value.length == 0) {
        alert('刻印コードが入力されていません！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    } else if (obj.punchMark_code.value.length > 6) {
        alert('部品番号は６桁までです！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    document.entry_form.change.value = "変更";
    document.entry_form.submit();
    return true;
}
/* 削除時の内容チェック関数 */
function checkDelete(obj) {
    if (!obj.parts_no.value.length) {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (!obj.punchMark_code.value.length) {
        alert('刻印コードが入力されていません！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (confirm("削除します。よろしいですか？")) {
        document.entry_form.del.value = "削除";
        document.entry_form.submit();
    }
    return true;
}

/* キーフィールドを削除 */
function clearKeyValue(obj) {
    obj.parts_no.value       = "";
    obj.punchMark_code.value = "";
    return true;
}

