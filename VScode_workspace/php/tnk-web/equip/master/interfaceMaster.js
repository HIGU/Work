//////////////////////////////////////////////////////////////////////////////
// 設備・機械のインターフェースマスター 照会＆メンテナンス                  //
//                      入力チェック JavaScript                             //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/14 Created   interfaceMasterjs                                   //
// 2005/08/03 interface は JavaScript の予約語(NN7.1)なので inter へ変更    //
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


function chk_interfaceMaster(obj) {
    if (!isDigit(obj.inter.value)) {
        alert("インターフェース番号に数字以外のデータがあります。");
        obj.inter.focus();
        obj.inter.select();
        return false;
    }
    if ( (obj.inter.value < 1) || (obj.inter.value > 9999) ) {
        alert("インターフェース番号は１～9999までです。");
        obj.inter.focus();
        obj.inter.select();
        return false;
    }
    if (obj.host.value.length == 0) {
        alert("HOST名がブランクです。");
        obj.host.focus();
        obj.host.select();
        return false;
    }
    if (obj.ip_address.value.length == 0) {
        alert("IPアドレスがブランクです。");
        obj.ip_address.focus();
        obj.ip_address.select();
        return false;
    }
    if (obj.ftp_user.value.length == 0) {
        alert("FTPのユーザー名がブランクです。");
        obj.ftp_user.focus();
        obj.ftp_user.select();
        return false;
    }
    if (obj.ftp_pass.value.length == 0) {
        alert("FTPのパスワードがブランクです。");
        obj.ftp_pass.focus();
        obj.ftp_pass.select();
        return false;
    }
    if ( (obj.ftp_active.value.toUpperCase() != 'T') && (obj.ftp_active.value.toUpperCase() != 'F') ) {
        alert("有効・無効の設定値が異常です！ 管理担当者へ連絡して下さい。");
        obj.ftp_active.focus();
        obj.ftp_active.select();
        return false;
    }
    return true;
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

