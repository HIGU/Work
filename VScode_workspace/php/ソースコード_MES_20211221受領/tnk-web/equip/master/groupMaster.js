//////////////////////////////////////////////////////////////////////////////
// �����������Υ��롼��(����)��ʬ �ޥ����� �Ȳ�����ƥʥ�               //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   groupMaster.js                                      //
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


function chk_groupMaster(obj) {
    if (obj.group_no.value.length == 0) {
        alert("�����ʬ(���롼�ץ�����)���֥�󥯤Ǥ���");
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    if (!isDigit(obj.group_no.value)) {
        alert("�����ʬ(���롼�ץ�����)�˿����ʳ��Υǡ���������ޤ���");
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    if ( (obj.group_no.value < 1) || (obj.group_no.value > 999) ) {
        alert("�����ʬ(���롼�ץ�����)�ϣ���999�ޤǤǤ���");
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    if (obj.group_name.value.length == 0) {
        alert("����̾(���롼��̾)���֥�󥯤Ǥ���");
        obj.group_name.focus();
        obj.group_name.select();
        return false;
    }
    if ( (obj.active.value.toUpperCase() != 'T') && (obj.active.value.toUpperCase() != 'F') ) {
        alert("ͭ����̵���������ͤ��۾�Ǥ��� ����ô���Ԥ�Ϣ���Ʋ�������");
        obj.active.focus();
        obj.active.select();
        return false;
    }
    return true;
}


