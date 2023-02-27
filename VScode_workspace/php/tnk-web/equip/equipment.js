//////////////////////////////////////////////////////////////////////////////
// 機械運転日報 データ編集チェックルーチン & マスターメンテ確認 新版        //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/14 Created   equipment.js                                        //
// 2003/06/19 csv_flg の値が 0～2 → 0～3(その他)に変更                     //
// 2005/07/05 isDigitDot()数字チェック(小数点対応)を追加機械マスターに反映  //
//////////////////////////////////////////////////////////////////////////////

/* 入力文字が数字かどうかチェック */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return false;
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

function isABC(str) {
    // var str = str.toUpperCase();    // 必要に応じて大文字に変換
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

/* 初期データ入力 内容をチェック */
function chk_equip_inst(obj){
    if(obj.init_siji_no.value.length!=5){
        alert("加工指示番号の桁数は５桁です。");
        obj.init_siji_no.focus();
        obj.init_siji_no.select();
        return false;
    }
    if(!isDigit(obj.init_siji_no.value)){
        alert("加工指示番号に数字以外のデータがあります。");
        obj.init_siji_no.focus();
        obj.init_siji_no.select();
        return false;
    }
    return true;
    /****** 以下はやっても無駄
    if (obj.init_data_input.value == '確認') {
        document.siji_form.init_data_input.click();
        return true;
    } else {
        return false;
    }
    **********/
}

/* 初期データ入力 内容をチェック */
function chk_equipment_nippou(obj){
    if(obj.mac_no.value.length!=4){
        alert("設備・機械番号の桁数は４桁です。");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if(!isDigit(obj.mac_no.value)){
        alert("設備・機械番号に数字以外のデータがあります。");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if(obj.siji_no.value.length!=5){
        alert("加工指示番号の桁数は５桁です。");
        obj.siji_no.focus();
        obj.siji_no.select();
        return false;
    }
    if(!isDigit(obj.siji_no.value)){
        alert("加工指示番号に数字以外のデータがあります。");
        obj.siji_no.focus();
        obj.siji_no.select();
        return false;
    }
    if(obj.parts_no.value.length!=9){
        alert("部品番号の桁数は９桁です。");
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }else{
        obj.parts_no.value = obj.parts_no.value.toUpperCase();
    }
    if(!obj.koutei.value.length) {
        alert("工程番号の欄がブランクです。");
        obj.koutei.focus();
        obj.koutei.select();
        return false;
    }
    if(!isDigit(obj.koutei.value)) {
        alert("工程番号に数字以外のデータがあります。");
        obj.koutei.focus();
        obj.koutei.select();
        return false;
    }
    if(obj.koutei.value < 1){
        alert("工程番号は１番から順にスタートです。");
        obj.koutei.focus();
        obj.koutei.select();
        return false;
    }
    if(!obj.plan_cnt.value.length) {
        alert("生産計画数の欄がブランクです。");
        obj.plan_cnt.focus();
        obj.plan_cnt.select();
        return false;
    }
    if(!isDigit(obj.plan_cnt.value)){
        alert("生産計画数に数字以外のデータがあります。");
        obj.plan_cnt.focus();
        obj.plan_cnt.select();
        return false;
    }
    if(obj.plan_cnt.value<1){
        alert("生産数は最低でも１個以上です。");
        obj.plan_cnt.focus();
        obj.plan_cnt.select();
        return false;
    }
}

function chk_equip_plan_mnt(obj){
    if(obj.m_no.value.length!=4){
        alert("設備・機械番号の桁数は４桁です。");
        obj.m_no.focus();
        obj.m_no.select();
        return false;
    }
    if(!isDigit(obj.m_no.value)){
        alert("設備・機械番号に数字以外のデータがあります。");
        obj.m_no.focus();
        obj.m_no.select();
        return false;
    }
    if(obj.s_no.value.length!=5){
        alert("製造指示№の桁数は５桁です。");
        obj.s_no.focus();
        obj.s_no.select();
        return false;
    }
    if(!isDigit(obj.s_no.value)){
        alert("製造指示№に数字以外のデータがあります。");
        obj.s_no.focus();
        obj.s_no.select();
        return false;
    }
    if(obj.b_no.value.length!=9){
        alert("部品番号の桁数は９桁です。");
        obj.b_no.focus();
        obj.b_no.select();
        return false;
    }else{
        obj.b_no.value = obj.b_no.value.toUpperCase();
    }
    if(!isDigit(obj.k_no.value)){
        alert("工程番号に数字以外のデータがあります。");
        obj.k_no.focus();
        obj.k_no.select();
        return false;
    }
    if(!isDigit(obj.k_no.value)){
        alert("工程番号に数字以外のデータがあります。");
        obj.k_no.focus();
        obj.k_no.select();
        return false;
    }
    if(!isDigit(obj.p_no.value)){
        alert("生産計画数に数字以外のデータがあります。");
        obj.p_no.focus();
        obj.p_no.select();
        return false;
    }
    if(obj.p_no.value<1){
        alert("生産数は最低でも１個以上です。");
        obj.p_no.focus();
        obj.p_no.select();
        return false;
    }
    if(!isDigit(obj.s_date.value)){
        alert("開始予定日に数字以外のデータがあります。");
        obj.s_date.focus();
        obj.s_date.select();
        return false;
    }
    if(obj.s_date.value<20020201 || obj.s_date.value>20201231){
        alert("開始予定日の日付が無効です。");
        obj.s_date.focus();
        obj.s_date.select();
        return false;
    }
    if(!isDigit(obj.e_date.value)){
        alert("終了予定日に数字以外のデータがあります。");
        obj.e_date.focus();
        obj.e_date.select();
        return false;
    }
    if(obj.e_date.value<20020201 || obj.e_date.value>20201231){
        alert("終了予定日の日付が無効です。");
        obj.e_date.focus();
        obj.e_date.select();
        return false;
    }
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
    if (obj.maker_name.value.length==0) {
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
    if (obj.factory.value < 1 || obj.factory.value > 6) {
        alert("工場区分は 1～6 です。");
        obj.factory.focus();
        obj.factory.select();
        return false;
    }
    if (obj.survey.value.toUpperCase() != "Y" && obj.survey.value.toUpperCase() != "N") {
        alert("有効・無効は Y / N です。");
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

function set_post_date(rec){
    document.getElementById('id_m_no').value = document.getElementById('id_m_no'+rec).value;
    document.getElementById('id_m_name').value = document.getElementById('id_m_name'+rec).value;
    document.getElementById('id_plan_no').value = document.getElementById('id_plan_no'+rec).value;
}
