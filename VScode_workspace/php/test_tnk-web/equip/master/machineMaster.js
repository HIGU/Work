//////////////////////////////////////////////////////////////////////////////
// 設備・機械のインターフェースマスター 照会＆メンテナンス                  //
//                      入力チェック JavaScript                             //
// Copyright (C) 2005-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/14 Created   interfaceMasterjs                                   //
// 2018/12/25 7工場を真鍮とSUSに分離した為、工場区分を1～8に変更       大谷 //
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


function chk_equip_mac_mst_mnt(obj) {
    if (obj.mac_no.value.length != 4) {
        alert("設備・機械番号の桁数は４桁です。");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if (!isDigit(obj.mac_no.value)) {
        alert("設備・機械番号に数字以外のデータがあります。");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if (obj.mac_name.value.length == 0) {
        alert("機械名称がブランクです。");
        obj.mac_name.focus();
        obj.mac_name.select();
        return false;
    }
    if (obj.maker_name.value.length == 0) {
        alert("メーカー型式がブランクです。");
        obj.maker_name.focus();
        obj.maker_name.select();
        return false;
    }
    if (obj.maker.value.length == 0) {
        alert("メーカーがブランクです。");
        obj.maker.focus();
        obj.maker.select();
        return false;
    }
    if (obj.factory.value < 1 || obj.factory.value > 8) {
        alert("工場区分は 1～8 です。");
        obj.factory.focus();
        obj.factory.select();
        return false;
    }
    if ( (obj.survey.value.toUpperCase() != 'Y') && (obj.survey.value.toUpperCase() != 'N') ) {
        alert("有効・無効の設定値が異常です！ 管理担当者へ連絡して下さい。");
        obj.survey.focus();
        obj.survey.select();
        return false;
    } else {
        obj.survey.value = obj.survey.value.toUpperCase();
    }
    if (obj.csv_flg.value < 0 || obj.csv_flg.value > 201) {
        alert("インターフェースは 0=なし 1=Netmoni 2=FWS1 3=FWS2 4=FWS3 ... 101=Net&FWS 201=その他 です。");
        obj.csv_flg.focus();
        obj.csv_flg.select();
        return false;
    }
    if (obj.sagyouku.value.length != 3) {
        alert("作業区コードは３桁です。");
        obj.sagyouku.focus();
        obj.sagyouku.select();
        return false;
    }
    if (!isDigit(obj.sagyouku.value)) {
        alert("作業区コードに数字以外のデータは入れられません。");
        obj.sagyouku.focus();
        obj.sagyouku.select();
        return false;
    }
    if (!isDigitDot(obj.denryoku.value)) {
        alert("使用電力に数字以外のデータは入れられません。");
        obj.denryoku.focus();
        obj.denryoku.select();
        return false;
    }
    if (!isDigitDot(obj.keisuu.value)) {
        alert("電力係数に数字以外のデータは入れられません。");
        obj.keisuu.focus();
        obj.keisuu.select();
        return false;
    }
    return true;
}

function chk_del_equip_mac_mst(){
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

