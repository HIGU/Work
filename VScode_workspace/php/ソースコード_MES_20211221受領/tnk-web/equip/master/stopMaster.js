//////////////////////////////////////////////////////////////////////////////
// ��������������ߤ����(���ȥå�) �ޥ����� �Ȳ�����ƥʥ�             //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   stopMaster.js                                       //
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


function chk_stopMaster(obj) {
    if (obj.mac_no.value.length == 0) {
        alert("�����ֹ椬���򤵤�Ƥ��ޤ���");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if (!isDigit(obj.mac_no.value)) {
        alert("�����ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if ( (obj.mac_no.value < 1000) || (obj.mac_no.value > 9999) ) {
        alert("�����ֹ��1000��9999�ޤǤǤ���");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if (obj.parts_no.value.length == 0) {
        alert("����(����)�ֹ椬�֥�󥯤Ǥ���");
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.parts_no.value.length != 9) {
        alert("����(����)�ֹ�ϣ���Ǥ���");
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    } else {
        obj.parts_no.value = obj.parts_no.value.toUpperCase();
    }
    if (obj.stop.value.length == 0) {
        alert("��ߤ�Ƚ�Ǥ������(��)���֥�󥯤Ǥ���");
        obj.stop.focus();
        obj.stop.select();
        return false;
    }
    if (!isDigit(obj.stop.value)) {
        alert("��ߤ�Ƚ�Ǥ������(��)�˿����ʳ��Υǡ���������ޤ���");
        obj.stop.focus();
        obj.stop.select();
        return false;
    }
    if ( (obj.stop.value < 1) || (obj.stop.value > 9999) ) {
        alert("��ߤ�Ƚ�Ǥ������(��)�ϣ������������ޤǤǤ���");
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

