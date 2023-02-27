//////////////////////////////////////////////////////////////////////////////
// 刻印管理刻印マスターメンテナンス   照会＆メンテナンス                    //
//                      入力チェック JavaScript                             //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/07/26 Created   punchMark_MasterMnt.js                              //
// 2007/10/18 sortItem()関数を追加  小林                                    //
// 2007/10/19 未設定の追加で形状コード・サイズコードの入力チェックを追加小林//
// 2007/10/20 削除の確認メッセージとshelf_noの入力チェックを追加        小林//
// 2007/11/10 キーフィールド削除 function を追加                        小林//
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

/* 追加入力時の内容チェック関数 */
function checkEdit(obj) {
    if (obj.punchMark_code.value.length == 0) {
        alert('刻印コードが入力されていません！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (obj.shelf_no.value.length == 0) {
        alert('棚番が入力されていません！');
        obj.shelf_no.focus();
        obj.shelf_no.select();
        return false;
    }
    if (obj.mark.value.length == 0) {
        alert('刻印内容が入力されていません！');
        obj.mark.focus();
        obj.mark.select();
        return false;
    }
    if (!obj.shape_code.value.length) {
        alert('形状コードが選択されていません！');
        obj.shape_code.focus();
        return false;
    }
    if (!obj.size_code.value.length) {
        alert('サイズコードが選択されていません！');
        obj.size_code.focus();
        return false;
    }
    document.entry_form.entry.value = "追加";
    document.entry_form.submit();
    return true;
}
/* 変更入力時の内容チェック関数 */
function checkChange(obj) {
    if (obj.punchMark_code.value.length == 0) {
        alert('刻印コードが入力されていません！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (obj.shelf_no.value.length == 0) {
        alert('棚番が入力されていません！');
        obj.shelf_no.focus();
        obj.shelf_no.select();
        return false;
    }
    if (obj.mark.value.length == 0) {
        alert('刻印内容が入力されていません！');
        obj.mark.focus();
        obj.mark.select();
        return false;
    }
    if (!obj.shape_code.value.length) {
        alert('形状コードが選択されていません！');
        obj.shape_code.focus();
        return false;
    }
    if (!obj.size_code.value.length) {
        alert('サイズコードが選択されていません！');
        obj.size_code.focus();
        return false;
    }
    document.entry_form.change.value = "変更";
    document.entry_form.submit();
    return true;
}
/* 削除時の内容チェック関数 */
function checkDelete(obj) {
    if (!obj.punchMark_code.value.length) {
        alert('刻印コードが入力されていません！');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (!obj.shelf_no.value.length) {
        alert('棚番が入力されていません！');
        obj.shelf_no.focus();
        obj.shelf_no_code.select();
        return false;
    }
    if (confirm("削除します。よろしいですか？")) {
        document.entry_form.del.value = "削除";
        document.entry_form.submit();
    }
    return true;
}

/***** 指定項目での並び替え *****/
function sortItem(item)
{
    document.entry_form.targetSortItem.value = item;
    document.entry_form.submit();
}

/* キーフィールドを削除 */
function clearKeyValue(obj) {
    obj.punchMark_code.value = "";
    obj.shelf_no.value       = "";
    return true;
}

