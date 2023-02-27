//////////////////////////////////////////////////////////////////////////////
// ������ž���� �ǡ����Խ������å��롼���� & �ޥ��������Ƴ�ǧ ����        //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/14 Created   equipment.js                                        //
// 2003/06/19 csv_flg ���ͤ� 0��2 �� 0��3(����¾)���ѹ�                     //
// 2005/07/05 isDigitDot()���������å�(�������б�)���ɲõ����ޥ�������ȿ��  //
//////////////////////////////////////////////////////////////////////////////

/* ����ʸ�����������ɤ��������å� */
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

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
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
    // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ǡ������� ���Ƥ�����å� */
function chk_equip_inst(obj){
    if(obj.init_siji_no.value.length!=5){
        alert("�ù��ؼ��ֹ�η���ϣ���Ǥ���");
        obj.init_siji_no.focus();
        obj.init_siji_no.select();
        return false;
    }
    if(!isDigit(obj.init_siji_no.value)){
        alert("�ù��ؼ��ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.init_siji_no.focus();
        obj.init_siji_no.select();
        return false;
    }
    return true;
    /****** �ʲ��Ϥ�äƤ�̵��
    if (obj.init_data_input.value == '��ǧ') {
        document.siji_form.init_data_input.click();
        return true;
    } else {
        return false;
    }
    **********/
}

/* ����ǡ������� ���Ƥ�����å� */
function chk_equipment_nippou(obj){
    if(obj.mac_no.value.length!=4){
        alert("�����������ֹ�η���ϣ���Ǥ���");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if(!isDigit(obj.mac_no.value)){
        alert("�����������ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if(obj.siji_no.value.length!=5){
        alert("�ù��ؼ��ֹ�η���ϣ���Ǥ���");
        obj.siji_no.focus();
        obj.siji_no.select();
        return false;
    }
    if(!isDigit(obj.siji_no.value)){
        alert("�ù��ؼ��ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.siji_no.focus();
        obj.siji_no.select();
        return false;
    }
    if(obj.parts_no.value.length!=9){
        alert("�����ֹ�η���ϣ���Ǥ���");
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }else{
        obj.parts_no.value = obj.parts_no.value.toUpperCase();
    }
    if(!obj.koutei.value.length) {
        alert("�����ֹ���󤬥֥�󥯤Ǥ���");
        obj.koutei.focus();
        obj.koutei.select();
        return false;
    }
    if(!isDigit(obj.koutei.value)) {
        alert("�����ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.koutei.focus();
        obj.koutei.select();
        return false;
    }
    if(obj.koutei.value < 1){
        alert("�����ֹ�ϣ��֤����˥������ȤǤ���");
        obj.koutei.focus();
        obj.koutei.select();
        return false;
    }
    if(!obj.plan_cnt.value.length) {
        alert("�����ײ�����󤬥֥�󥯤Ǥ���");
        obj.plan_cnt.focus();
        obj.plan_cnt.select();
        return false;
    }
    if(!isDigit(obj.plan_cnt.value)){
        alert("�����ײ���˿����ʳ��Υǡ���������ޤ���");
        obj.plan_cnt.focus();
        obj.plan_cnt.select();
        return false;
    }
    if(obj.plan_cnt.value<1){
        alert("�������Ϻ���Ǥ⣱�İʾ�Ǥ���");
        obj.plan_cnt.focus();
        obj.plan_cnt.select();
        return false;
    }
}

function chk_equip_plan_mnt(obj){
    if(obj.m_no.value.length!=4){
        alert("�����������ֹ�η���ϣ���Ǥ���");
        obj.m_no.focus();
        obj.m_no.select();
        return false;
    }
    if(!isDigit(obj.m_no.value)){
        alert("�����������ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.m_no.focus();
        obj.m_no.select();
        return false;
    }
    if(obj.s_no.value.length!=5){
        alert("��¤�ؼ���η���ϣ���Ǥ���");
        obj.s_no.focus();
        obj.s_no.select();
        return false;
    }
    if(!isDigit(obj.s_no.value)){
        alert("��¤�ؼ���˿����ʳ��Υǡ���������ޤ���");
        obj.s_no.focus();
        obj.s_no.select();
        return false;
    }
    if(obj.b_no.value.length!=9){
        alert("�����ֹ�η���ϣ���Ǥ���");
        obj.b_no.focus();
        obj.b_no.select();
        return false;
    }else{
        obj.b_no.value = obj.b_no.value.toUpperCase();
    }
    if(!isDigit(obj.k_no.value)){
        alert("�����ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.k_no.focus();
        obj.k_no.select();
        return false;
    }
    if(!isDigit(obj.k_no.value)){
        alert("�����ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.k_no.focus();
        obj.k_no.select();
        return false;
    }
    if(!isDigit(obj.p_no.value)){
        alert("�����ײ���˿����ʳ��Υǡ���������ޤ���");
        obj.p_no.focus();
        obj.p_no.select();
        return false;
    }
    if(obj.p_no.value<1){
        alert("�������Ϻ���Ǥ⣱�İʾ�Ǥ���");
        obj.p_no.focus();
        obj.p_no.select();
        return false;
    }
    if(!isDigit(obj.s_date.value)){
        alert("����ͽ�����˿����ʳ��Υǡ���������ޤ���");
        obj.s_date.focus();
        obj.s_date.select();
        return false;
    }
    if(obj.s_date.value<20020201 || obj.s_date.value>20201231){
        alert("����ͽ���������դ�̵���Ǥ���");
        obj.s_date.focus();
        obj.s_date.select();
        return false;
    }
    if(!isDigit(obj.e_date.value)){
        alert("��λͽ�����˿����ʳ��Υǡ���������ޤ���");
        obj.e_date.focus();
        obj.e_date.select();
        return false;
    }
    if(obj.e_date.value<20020201 || obj.e_date.value>20201231){
        alert("��λͽ���������դ�̵���Ǥ���");
        obj.e_date.focus();
        obj.e_date.select();
        return false;
    }
}

function chk_equip_mac_mst_mnt(obj) {
    if (obj.mac_no.value.length != 4) {
        alert("�����������ֹ�η���ϣ���Ǥ���");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if (!isDigit(obj.mac_no.value)) {
        alert("�����������ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    if (obj.mac_name.value.length == 0) {
        alert("����̾�Τ��֥�󥯤Ǥ���");
        obj.mac_name.focus();
        obj.mac_name.select();
        return false;
    }
    if (obj.maker_name.value.length==0) {
        alert("�᡼�����������֥�󥯤Ǥ���");
        obj.maker_name.focus();
        obj.maker_name.select();
        return false;
    }
    if (obj.maker.value.length == 0) {
        alert("�᡼�������֥�󥯤Ǥ���");
        obj.maker.focus();
        obj.maker.select();
        return false;
    }
    if (obj.factory.value < 1 || obj.factory.value > 6) {
        alert("�����ʬ�� 1��6 �Ǥ���");
        obj.factory.focus();
        obj.factory.select();
        return false;
    }
    if (obj.survey.value.toUpperCase() != "Y" && obj.survey.value.toUpperCase() != "N") {
        alert("ͭ����̵���� Y / N �Ǥ���");
        obj.survey.focus();
        obj.survey.select();
        return false;
    } else {
        obj.survey.value = obj.survey.value.toUpperCase();
    }
    if (obj.csv_flg.value < 0 || obj.csv_flg.value > 201) {
        alert("���󥿡��ե������� 0=�ʤ� 1=Netmoni 2=FWS1 3=FWS2 4=FWS3 ... 101=Net&FWS 201=����¾ �Ǥ���");
        obj.csv_flg.focus();
        obj.csv_flg.select();
        return false;
    }
    if (obj.sagyouku.value.length != 3) {
        alert("��ȶ襳���ɤϣ���Ǥ���");
        obj.sagyouku.focus();
        obj.sagyouku.select();
        return false;
    }
    if (!isDigit(obj.sagyouku.value)) {
        alert("��ȶ襳���ɤ˿����ʳ��Υǡ�����������ޤ���");
        obj.sagyouku.focus();
        obj.sagyouku.select();
        return false;
    }
    if (!isDigitDot(obj.denryoku.value)) {
        alert("�������Ϥ˿����ʳ��Υǡ�����������ޤ���");
        obj.denryoku.focus();
        obj.denryoku.select();
        return false;
    }
    if (!isDigitDot(obj.keisuu.value)) {
        alert("���Ϸ����˿����ʳ��Υǡ�����������ޤ���");
        obj.keisuu.focus();
        obj.keisuu.select();
        return false;
    }
}

function chk_del_equip_mac_mst(){
    var res = confirm("��������ǡ����ϸ����᤻�ޤ��󡣤�����Ǥ�����");
    return res;
}

function chk_end_inst(obj) {
    var mac_no   = obj.m_no.value;
    var name     = obj.m_name.value;
    var siji_no  = obj.s_no.value;
    var parts_no = obj.b_no.value;
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "��λ���ޤ����������Ǥ�����");
}

function chk_cut_form(obj) {
    var mac_no   = obj.m_no.value;
    var name     = obj.m_name.value;
    var siji_no  = obj.s_no.value;
    var parts_no = obj.b_no.value;
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "�����Ǥ��ޤ����������Ǥ�����");
}

function chk_break_del(mac_no, name, siji_no, parts_no) {
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "����������ޤ����������Ǥ�����");
}

function chk_break_restart(mac_no, name, siji_no, parts_no) {
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "��Ƴ����ޤ����������Ǥ�����");
}

function set_post_date(rec){
    document.getElementById('id_m_no').value = document.getElementById('id_m_no'+rec).value;
    document.getElementById('id_m_name').value = document.getElementById('id_m_name'+rec).value;
    document.getElementById('id_plan_no').value = document.getElementById('id_plan_no'+rec).value;
}
