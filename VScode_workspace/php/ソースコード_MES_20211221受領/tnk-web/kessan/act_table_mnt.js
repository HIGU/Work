//////////////////////////////////////////////////////////////////////////
//  �������祳���ɡ�����Ψ���ݼ�  JavaScript ���ϥ����å�               //
//  2002/09/17   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  �ѹ�����                                                            //
//  2002/09/17 ��������                                                 //
//////////////////////////////////////////////////////////////////////////

/* ����ʸ�����������ɤ��������å� */
function isDigit(str){
	var len=str.length;
	var c;
	for(i=0;i<len;i++){
		c=str.charAt(i);
		if("0">c||c>"9")
			return true;
    	}
	return false;
}
function act_id_chk(obj){
	if(!obj.act_id.value.length){
		alert("[���祳����]�������󤬶���Ǥ���");
		obj.act_id.focus();
		obj.act_id.select();
		return false;
	}
	if(isDigit(obj.act_id.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.act_id.focus();
		obj.act_id.select();
		return false;
	}
	return true;
}

function act_add_chk(obj){
	if(!obj.act_id.value.length){
		alert("[���祳����]�������󤬶���Ǥ���");
		obj.act_id.focus();
		return false;
	}
	if(!obj.act_name.value.length){
		alert("[����̾]�������󤬶���Ǥ���");
		obj.act_name.focus();
		return false;
	}
	if(!obj.s_name.value.length){
		alert("[û��̾]�������󤬶���Ǥ���");
		obj.s_name.focus();
		return false;
	}
	return true;
}

///////////// �������祳���ɤΤ� ��Ͽ���ѹ� ���ϥ����å� ////////////////////
function act_chk_name(obj){
	if(!obj.act_name.value.length){
		alert("[����̾]�������󤬶���Ǥ���");
		obj.act_name.focus();
		return false;
	}
	if(!obj.s_name.value.length){
		alert("[û��̾]�������󤬶���Ǥ���");
		obj.s_name.focus();
		return false;
	}
	return true;
}



/* �������祳���ɡ�����Ψ����Ͽ���ѹ� ���ϥ����å� */
function act_chk(obj){
	if(!obj.act_name.value.length){
		alert("[����̾]�������󤬶���Ǥ���");
		obj.act_name.focus();
		return false;
	}
	if(!obj.s_name.value.length){
		alert("[û��̾]�������󤬶���Ǥ���");
		obj.s_name.focus();
		return false;
	}
////////////////////////////////////////////////////////
	if(isDigit(obj.c_exp.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.c_exp.focus();
		obj.c_exp.select();
		return false;
	}else if(obj.c_exp.value == "")
		alloc1 = 0;
	else
		alloc1 = parseInt(obj.c_exp.value,10);
	if(isDigit(obj.l_exp.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.l_exp.focus();
		obj.l_exp.select();
		return false;
	}else if(obj.l_exp.value == "")
		alloc2 = 0;
	else
		alloc2 = parseInt(obj.l_exp.value,10);
	if(isDigit(obj.s_g_exp.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.s_g_exp.focus();
		obj.s_g_exp.select();
		return false;
	}else if(obj.s_g_exp.value == "")
		alloc3 = 0;
	else
		alloc3 = parseInt(obj.s_g_exp.value,10);
	if(isDigit(obj.shoukan.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.shoukan.focus();
		obj.shoukan.select();
		return false;
	}else if(obj.shoukan.value == "")
		alloc4 = 0;
	else
		alloc4 = parseInt(obj.shoukan.value,10);
	alloc  = alloc1 + alloc2 + alloc3 + alloc4;
	if(alloc != 100){
		alert("��ʬ��ι������Ψ��100�ˤ��Ʋ������� ���� ���="+alloc);
		obj.c_exp.focus();
		return false;
	}
//////////////////////////////////////////////////////////////////////////
	if(isDigit(obj.c_assy.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.c_assy.focus();
		obj.c_assy.select();
		return false;
	}else if(obj.c_assy.value == "")
		alloc1 = 0;
	else
		alloc1 = parseInt(obj.c_assy.value,10);
	if(isDigit(obj.s_toku.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.s_toku.focus();
		obj.s_toku.select();
		return false;
	}else if(obj.s_toku.value == "")
		alloc2 = 0;
	else
		alloc2 = parseInt(obj.s_toku.value,10);
	if(isDigit(obj.s_1_nc.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.s_1_nc.focus();
		obj.s_1_nc.select();
		return false;
	}else if(obj.s_1_nc.value == "")
		alloc3 = 0;
	else
		alloc3 = parseInt(obj.s_1_nc.value,10);
	if(isDigit(obj.s_1_6.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.s_1_6.focus();
		obj.s_1_6.select();
		return false;
	}else if(obj.s_1_6.value == "")
		alloc4 = 0;
	else
		alloc4 = parseInt(obj.s_1_6.value,10);
	if(isDigit(obj.s_4_nc.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.s_4_nc.focus();
		obj.s_4_nc.select();
		return false;
	}else if(obj.s_4_nc.value == "")
		alloc5 = 0;
	else
		alloc5 = parseInt(obj.s_4_nc.value,10);
	if(isDigit(obj.s_5_pf.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.s_5_pf.focus();
		obj.s_5_pf.select();
		return false;
	}else if(obj.s_5_pf.value == "")
		alloc6 = 0;
	else
		alloc6 = parseInt(obj.s_5_pf.value,10);
	if(isDigit(obj.s_5_2.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.s_5_2.focus();
		obj.s_5_2.select();
		return false;
	}else if(obj.s_5_2.value == "")
		alloc7 = 0;
	else
		alloc7 = parseInt(obj.s_5_2.value,10);
	if(isDigit(obj.shape.value)){
		alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
		obj.shape.focus();
		obj.shape.select();
		return false;
	}else if(obj.shape.value == "")
		alloc8 = 0;
	else
		alloc8 = parseInt(obj.shape.value,10);

	alloc  = alloc1 + alloc2 + alloc3 + alloc4 + alloc5 + alloc6 + alloc7 + alloc8;
	if(alloc != 100 && alloc != 0){
		alert("���ץ� ���롼�פι������Ψ�� 100 �� 0 �ˤʤ�褦�ˤ��Ʋ������� ���� ���="+alloc);
		obj.c_assy.focus();
		obj.c_assy.select();
		return false;
	}
//////////////////////////////////////////////////////////////////////////
	return true;
}
