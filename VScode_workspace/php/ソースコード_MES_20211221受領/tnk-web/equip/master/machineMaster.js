//////////////////////////////////////////////////////////////////////////////
// �����������Υ��󥿡��ե������ޥ����� �Ȳ�����ƥʥ�                  //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2005-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/14 Created   interfaceMasterjs                                   //
// 2018/12/25 7�����﫤�SUS��ʬΥ�����١������ʬ��1��8���ѹ�       ��ë //
//////////////////////////////////////////////////////////////////////////////

/* ����ʸ�����������ɤ��������å� */
function isDigit(str){
    var len=str.length;
    var c;
    for(var i=0; i<len; i++) {
        c = str.charAt(i);
        if( ('0' > c) || (c > '9') ) return false;
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (var i=0; i<len; i++) {
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
    for (var i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
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
    if (obj.maker_name.value.length == 0) {
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
    if (obj.factory.value < 1 || obj.factory.value > 8) {
        alert("�����ʬ�� 1��8 �Ǥ���");
        obj.factory.focus();
        obj.factory.select();
        return false;
    }
    if ( (obj.survey.value.toUpperCase() != 'Y') && (obj.survey.value.toUpperCase() != 'N') ) {
        alert("ͭ����̵���������ͤ��۾�Ǥ��� ����ô���Ԥ�Ϣ���Ʋ�������");
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
    return true;
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

