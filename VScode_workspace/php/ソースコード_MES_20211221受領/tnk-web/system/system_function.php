<?php
//////////////////////////////////////////////////////////////////////////////
//                                                                          //
// �����ƥ���� �� �ե��󥯥å���� �ե�����                                //
// Copyright (C) 2001 Kazuhiro Kobayashi all rights reserved.               //
//                                           2002/03/23                     //
// �ѹ�����                                                                 //
// 2002/03/23                                                               //
//////////////////////////////////////////////////////////////////////////////


require("../function.php");		// ������define.php pgsql.php �� require()���Ƥ��롣

define("HIUURI","/home/www/html/daily/W#HIUURI.TXT");

// ���ǡ����Υƥ����Ȥ�ե�����ɤ��Ȥ�ʬ���ƥ쥳���ɿ����֤���
function hiuuri_get_field(&$f01,&$f02,&$f03,&$f04,&$f05,&$f06,&$f07,&$f08,&$f09,&$f10,&$f11,&$f12,&$f13,&$f14,&$f15,&$f16,&$f17,&$f18,&$f19){
	if($fp = fopen(HIUURI,'r')){
		$c = 0;
		while(!feof($fp)){
			$data = fgets($fp,200);
			$f01[$c] = substr($data,0,1);		// ������
			$f02[$c] = substr($data,1,8);		// ������
			$f03[$c] = substr($data,9,8);		// �׾���
			$f04[$c] = substr($data,17,9);	// assyno
			$f05[$c] = substr($data,26,9);	// ���ʥ�����
			$f06[$c] = substr($data,35,8);	// �ײ��ֹ�
			$f07[$c] = substr($data,43,7);	// ��¤�ֹ�
			$f08[$c] = substr($data,50,7);	// ��ʸ�ֹ�
			$f09[$c] = substr($data,57,7);	// ȯ���ֹ�
			$f10[$c] = substr($data,64,2);	// ���˾��
			$f11[$c] = substr($data,66,5);	// ��Ω��λ�ֹ�
			$f12[$c] = substr($data,71,6);	// ��ɼ�ֹ�
			$f13[$c] = substr($data,77,6);	// ����
			$f14[$c] = substr($data,83,9);	// ñ�� �������/100
			$f15[$c] = substr($data,92,3);	// �ú�Ψ
			$f16[$c] = substr($data,95,1);	// datatype
			$f17[$c] = substr($data,96,5);	// ������
			$f18[$c] = substr($data,101,15);	// ����
			$f19[$c] = substr($data,116,1);	// �����ʬ
			$c += 1;
		}
		fclose($fp);
		return ($c-1);
	}else
		return FALSE;
}

