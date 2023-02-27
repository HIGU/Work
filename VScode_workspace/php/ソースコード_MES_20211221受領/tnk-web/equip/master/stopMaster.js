//////////////////////////////////////////////////////////////////////////////
// 設備・機械の停止の定義(ストップ) マスター 照会＆メンテナンス             //
//                      入力チェック JavaScript                             //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   stopMaster.js                                       //
//////////////////////////////////////////////////////////////////////////////

/* 入力文字が数字かどうかチェック */
function isDigit(str){
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


function chk_stopMaster(obj) {
    if (obj.mac_no.value.length == 0) {
        alert("機械番号が選択されていません！");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if (!isDigit(obj.mac_no.value)) {
        alert("機械番号に数字以外のデータがあります。");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if ( (obj.mac_no.value < 1000) || (obj.mac_no.value > 9999) ) {
        alert("機械番号は1000〜9999までです。");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if (obj.parts_no.value.length == 0) {
        alert("部品(製品)番号がブランクです。");
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.parts_no.value.length != 9) {
        alert("部品(製品)番号は９桁です。");
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    } else {
        obj.parts_no.value = obj.parts_no.value.toUpperCase();
    }
    if (obj.stop.value.length == 0) {
        alert("停止と判断する時間(秒)がブランクです。");
        obj.stop.focus();
        obj.stop.select();
        return false;
    }
    if (!isDigit(obj.stop.value)) {
        alert("停止と判断する時間(秒)に数字以外のデータがあります。");
        obj.stop.focus();
        obj.stop.select();
        return false;
    }
    if ( (obj.stop.value < 1) || (obj.stop.value > 9999) ) {
        alert("停止と判断する時間(秒)は１〜９９９９までです。");
        obj.stop.focus();
        obj.stop.select();
        return false;
    }
    return true;
}

function apend_checkbox(value) {
    if (value) {
        document.apend_form.parts_no.value = '000000000';
    } else {
        document.apend_form.parts_no.value = '';
    }
}

function edit_checkbox(value) {
    if (value) {
        document.edit_form.parts_no.value = '000000000';
    } else {
        document.edit_form.parts_no.value = '';
    }
}


function chk_del_interfaceMaster(){
    var res = confirm("削除したデータは元に戻せません。よろしいですか？");
    return res;
}


function chk_end_inst(obj) {
    var mac_no   = obj.m_no.value;
    var name     = obj.m_name.value;
    var siji_no  = obj.s_no.value;
    var parts_no = obj.b_no.value;
    return confirm(   "機械番号：" + mac_no + "\n\n"
                    + "機 械 名：" + name + "\n\n"
                    + "指示番号：" + siji_no + "\n\n"
                    + "部品番号：" + parts_no + "\n\n"
                    + "を完了します。宜しいですか？");
}

function chk_cut_form(obj) {
    var mac_no   = obj.m_no.value;
    var name     = obj.m_name.value;
    var siji_no  = obj.s_no.value;
    var parts_no = obj.b_no.value;
    return confirm(   "機械番号：" + mac_no + "\n\n"
                    + "機 械 名：" + name + "\n\n"
                    + "指示番号：" + siji_no + "\n\n"
                    + "部品番号：" + parts_no + "\n\n"
                    + "を中断します。宜しいですか？");
}

function chk_break_del(mac_no, name, siji_no, parts_no) {
    return confirm(   "機械番号：" + mac_no + "\n\n"
                    + "機 械 名：" + name + "\n\n"
                    + "指示番号：" + siji_no + "\n\n"
                    + "部品番号：" + parts_no + "\n\n"
                    + "を完全削除します。宜しいですか？");
}

function chk_break_restart(mac_no, name, siji_no, parts_no) {
    return confirm(   "機械番号：" + mac_no + "\n\n"
                    + "機 械 名：" + name + "\n\n"
                    + "指示番号：" + siji_no + "\n\n"
                    + "部品番号：" + parts_no + "\n\n"
                    + "を再開します。宜しいですか？");
}

