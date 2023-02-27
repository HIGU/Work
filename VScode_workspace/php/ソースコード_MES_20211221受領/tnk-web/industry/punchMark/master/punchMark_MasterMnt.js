//////////////////////////////////////////////////////////////////////////////
// �����������ޥ��������ƥʥ�   �Ȳ�����ƥʥ�                    //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/07/26 Created   punchMark_MasterMnt.js                              //
// 2007/10/18 sortItem()�ؿ����ɲ�  ����                                    //
// 2007/10/19 ̤������ɲäǷ��������ɡ������������ɤ����ϥ����å����ɲþ���//
// 2007/10/20 ����γ�ǧ��å�������shelf_no�����ϥ����å����ɲ�        ����//
// 2007/11/10 �����ե�����ɺ�� function ���ɲ�                        ����//
//////////////////////////////////////////////////////////////////////////////

/* ����ʸ�����������ɤ��������å� */
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

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
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

/* �ɲ����ϻ������ƥ����å��ؿ� */
function checkEdit(obj) {
    if (obj.punchMark_code.value.length == 0) {
        alert('��������ɤ����Ϥ���Ƥ��ޤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (obj.shelf_no.value.length == 0) {
        alert('ê�֤����Ϥ���Ƥ��ޤ���');
        obj.shelf_no.focus();
        obj.shelf_no.select();
        return false;
    }
    if (obj.mark.value.length == 0) {
        alert('������Ƥ����Ϥ���Ƥ��ޤ���');
        obj.mark.focus();
        obj.mark.select();
        return false;
    }
    if (!obj.shape_code.value.length) {
        alert('���������ɤ����򤵤�Ƥ��ޤ���');
        obj.shape_code.focus();
        return false;
    }
    if (!obj.size_code.value.length) {
        alert('�����������ɤ����򤵤�Ƥ��ޤ���');
        obj.size_code.focus();
        return false;
    }
    document.entry_form.entry.value = "�ɲ�";
    document.entry_form.submit();
    return true;
}
/* �ѹ����ϻ������ƥ����å��ؿ� */
function checkChange(obj) {
    if (obj.punchMark_code.value.length == 0) {
        alert('��������ɤ����Ϥ���Ƥ��ޤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (obj.shelf_no.value.length == 0) {
        alert('ê�֤����Ϥ���Ƥ��ޤ���');
        obj.shelf_no.focus();
        obj.shelf_no.select();
        return false;
    }
    if (obj.mark.value.length == 0) {
        alert('������Ƥ����Ϥ���Ƥ��ޤ���');
        obj.mark.focus();
        obj.mark.select();
        return false;
    }
    if (!obj.shape_code.value.length) {
        alert('���������ɤ����򤵤�Ƥ��ޤ���');
        obj.shape_code.focus();
        return false;
    }
    if (!obj.size_code.value.length) {
        alert('�����������ɤ����򤵤�Ƥ��ޤ���');
        obj.size_code.focus();
        return false;
    }
    document.entry_form.change.value = "�ѹ�";
    document.entry_form.submit();
    return true;
}
/* ����������ƥ����å��ؿ� */
function checkDelete(obj) {
    if (!obj.punchMark_code.value.length) {
        alert('��������ɤ����Ϥ���Ƥ��ޤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (!obj.shelf_no.value.length) {
        alert('ê�֤����Ϥ���Ƥ��ޤ���');
        obj.shelf_no.focus();
        obj.shelf_no_code.select();
        return false;
    }
    if (confirm("������ޤ���������Ǥ�����")) {
        document.entry_form.del.value = "���";
        document.entry_form.submit();
    }
    return true;
}

/***** ������ܤǤ��¤��ؤ� *****/
function sortItem(item)
{
    document.entry_form.targetSortItem.value = item;
    document.entry_form.submit();
}

/* �����ե�����ɤ��� */
function clearKeyValue(obj) {
    obj.punchMark_code.value = "";
    obj.shelf_no.value       = "";
    return true;
}

