//////////////////////////////////////////////////////////////////////////////
// �ԣΣ˵�����˥塼���� ���ϥե��������Υ����å��� JavaScript             //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/17 Created   authenticate.js                                     //
//////////////////////////////////////////////////////////////////////////////
/* ����ʸ�����������ɤ��������å� */
/* false=�����Ǥʤ�  ture=�����Ǥ��� */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            return false;
        }
    }
    return true;
}
/* ���Ͼ���Υ����å� & ���� */
function inpConf(obj) {
    if (!obj.userid.value.length) {
        alert("[�桼����ID]�������󤬶���Ǥ���������Ǥ��ޤ���");
        obj.userid.focus();
        return false;
    }
    if (!obj.passwd.value.length) {
        alert("[�ѥ����]�������󤬶���Ǥ���������Ǥ��ޤ���");
        obj.passwd.focus();
        return false;
    }
    return true;
}

function ini_focus(){
    document.login_form.userid.focus();
    document.login_form.userid.select();
}
function next_focus(){
    document.login_form.passwd.focus();
    document.login_form.passwd.select();
}
