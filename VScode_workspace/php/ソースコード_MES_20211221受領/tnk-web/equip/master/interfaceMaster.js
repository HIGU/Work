//////////////////////////////////////////////////////////////////////////////
// �����������Υ��󥿡��ե������ޥ����� �Ȳ�����ƥʥ�                  //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/14 Created   interfaceMasterjs                                   //
// 2005/08/03 interface �� JavaScript ��ͽ���(NN7.1)�ʤΤ� inter ���ѹ�    //
//////////////////////////////////////////////////////////////////////////////

/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
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


function chk_interfaceMaster(obj) {
    if (!isDigit(obj.inter.value)) {
        alert("���󥿡��ե������ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.inter.focus();
        obj.inter.select();
        return false;
    }
    if ( (obj.inter.value < 1) || (obj.inter.value > 9999) ) {
        alert("���󥿡��ե������ֹ�ϣ���9999�ޤǤǤ���");
        obj.inter.focus();
        obj.inter.select();
        return false;
    }
    if (obj.host.value.length == 0) {
        alert("HOST̾���֥�󥯤Ǥ���");
        obj.host.focus();
        obj.host.select();
        return false;
    }
    if (obj.ip_address.value.length == 0) {
        alert("IP���ɥ쥹���֥�󥯤Ǥ���");
        obj.ip_address.focus();
        obj.ip_address.select();
        return false;
    }
    if (obj.ftp_user.value.length == 0) {
        alert("FTP�Υ桼����̾���֥�󥯤Ǥ���");
        obj.ftp_user.focus();
        obj.ftp_user.select();
        return false;
    }
    if (obj.ftp_pass.value.length == 0) {
        alert("FTP�Υѥ���ɤ��֥�󥯤Ǥ���");
        obj.ftp_pass.focus();
        obj.ftp_pass.select();
        return false;
    }
    if ( (obj.ftp_active.value.toUpperCase() != 'T') && (obj.ftp_active.value.toUpperCase() != 'F') ) {
        alert("ͭ����̵���������ͤ��۾�Ǥ��� ����ô���Ԥ�Ϣ���Ʋ�������");
        obj.ftp_active.focus();
        obj.ftp_active.select();
        return false;
    }
    return true;
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

