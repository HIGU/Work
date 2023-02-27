<?php
/* ------------------------------------------------
 * ̾��			��mu_Date
 * �С������	��Ver.0.4
 * ����			���������饹�饤�֥��
 * ư��Ķ�		��PHP4�ʹ�
 * ������		��2003/03/25
 * ������		��2004/03/01
 * ������		��D.asano
 * ���		��Copyright (c) 2004 YDS. All rights reserved.
 * �饤����	������BSD
 * ���۸�		��http://www.mula-net.com/mulib/
 *
 * Copyright (c) 2004 YDS. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above 
 *    copyright notice, this list of conditions and the following 
 *    disclaimer in the documentation and/or other materials 
 *    provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS 
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE 
 * REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class mu_Date
{
	/* ���դ�ե����ޥåȤ˹�碌���֤� */
	function toString($p_date = "", $p_format = "Ymd")
	{
		if ($p_date=='') 	return mu_Date::getDate($p_format,time());
		if (($a_ = mu_Date::_parseValue($p_date,'free',1))===false)	return false;
		$format	= ($p_format != '') ? $p_format : @$a_['FORMAT'];
		$time	= mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m'],@$a_['d'],@$a_['Y']);
		return mu_Date::getDate($format,$time);
	}
	/* ���դβû� */
	function addDay($p_date = "", $p_add = 0, $p_format = "")
	{
		$a_			= array();
		if (($a_ = mu_Date::_parseValue($p_date,'date datetime',1))===false)	return false;
		$format	= ($p_format != '') ? $p_format : @$a_['FORMAT'];
		$time	= mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m'],@$a_['d']+$p_add,@$a_['Y']);
		return mu_Date::getDate($format,$time);
	}
	/* ���βû� */
	function addWeek($p_date = "", $p_add = 0, $p_format = "")
	{
		if (($a_ = mu_Date::_parseValue($p_date,'date datetime',1))===false)	return false;
		$format	= ($p_format != '') ? $p_format : @$a_['FORMAT'];
		$time	= mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m'],@$a_['d']+($p_add*7),@$a_['Y']);
		return mu_Date::getDate($format,$time);
	}
	/* ��βû� */
	function addMonth($p_date = "", $p_add = 0, $p_format = "")
	{
		if (($a_ = mu_Date::_parseValue($p_date,'date month',1))===false)	return false;
		$format	= $p_format;
		if ($format=="")	$format	= @$a_['FORMAT'];
		$time	= mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m']+$p_add,@$a_['d'],@$a_['Y']);
		return mu_Date::getDate($format,$time);
	}
	/* ǯ�βû� */
	function addYear($p_date = "", $p_add = 0, $p_format = "")
	{
		if (($a_ = mu_Date::_parseValue($p_date,'date month',1))===false)	return false;
		$format	= ($p_format != '') ? $p_format : @$a_['FORMAT'];
		if (!isset($a_['m']))	$a_['m']	= 1;
		if (!isset($a_['d']))	$a_['d']	= 1;
		$time	= mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m'],@$a_['d'],@$a_['Y']+$p_add);
		return mu_Date::getDate($format,$time);
	}
	/* ���ա�����������������å� */
	function chkDate($p_date = '',$p_type = 'date')
	{
		if (($a_ = mu_Date::_parseValue($p_date,$p_type,0))===false)	return false;
		if (!mu_Date::_chkDateArray($a_))	return false;
		return true;
	}
	/* �����饹���ͤ� date �ؿ� */
	function getDate($p_format = '', $p_tstamp = "")
	{
		if ($p_tstamp=='')	$p_tstamp	= time();
		$format	= ($p_format != '') ? $p_format : 'Ymd';
		return date(mu_Date::_replaceFormat($format,$p_tstamp), $p_tstamp);
	}
	/* Unix�����ॹ����פμ��� */
	function getTimestamp($p_date = "")
	{
		if (($a_ = mu_Date::_parseValue($p_date,'date datetime',1))===false)	return false;
		if (!strlen(@$a_['FORMAT']))	return false;
		return mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m'],@$a_['d'],@$a_['Y']);
	}
	/* ���Ĥ����դκ�ʬ����������� */
	function getIntervalDay($p_date1 = "",$p_date2 = "")
	{
		if (($a_1 = mu_Date::_parseValue($p_date1,'date datetime',1))===false)	return false;
		if (($a_2 = mu_Date::_parseValue($p_date2,'date datetime',1))===false)	return false;
		$time1	= mktime(0,0,0,@$a_1['m'],@$a_1['d'],@$a_1['Y']);
		$time2	= mktime(0,0,0,@$a_2['m'],@$a_2['d'],@$a_2['Y']);
		return floor(($time2 - $time1) / 60 / 60 / 24);
	}
	/* ���Ĥ����դκ�ʬ������ʷ�� */
	function getIntervalMonth($p_date1 = "",$p_date2 = "")
	{
		// ���ʲ���ñ�̤��ڤ�ΤƤ��Ʒ׻����Ƥ��롣
		if (($a_1 = mu_Date::_parseValue($p_date1,'date datetime',1))===false)	return false;
		if (($a_2 = mu_Date::_parseValue($p_date2,'date datetime',1))===false)	return false;
		$result	= ((@$a_2['Y'])*12+@$a_2['m']) - ((@$a_1['Y'])*12+@$a_1['m']);
		return $result;
	}
	/* �������η��٤���� */
	function getMonth($p_date = "",$p_unit = '20',$p_format = 'Ym')
	{
		if ($p_unit != '20' && $p_unit != '30')				return false;
		if (($time = mu_Date::getTimestamp($p_date))==="")	return false;
		$yy	= date('Y',$time);
		$mn	= date('n',$time);
		$dj	= date('j',$time);
		if ($p_unit == '20' && $dj > 20) $mn++;
		return mu_Date::getDate($p_format,mktime(0,0,0,$mn,1,$yy));
	}
	/* ����ǯ��κǽ��������� */
	function getFirstDate($p_month = "",$p_unit = '20',$p_format = 'Ymd')
	{
		if (($a_ = mu_Date::_parseValue($p_month,'month',1))===false)	return false;
		switch ($p_unit) {
			case 20	:	$res	= mu_Date::getDate($p_format,mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m']-1,21,@$a_['Y']));	break;
			case 30 :	$res	= mu_Date::getDate($p_format,mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m'],1,@$a_['Y']));		break;
			default :	return false;
		}
		return $res;
	}
	/* ����ǯ��κǸ��������� */
	function getLastDate($p_month = "",$p_unit = '20',$p_format = 'Ymd')
	{
		if (($a_ = mu_Date::_parseValue($p_month,'month',1))===false)	return false;
		if ($a_['FORMAT']=='')	return false;
		switch ($p_unit) {
			case 20 :	$res	= mu_Date::getDate($p_format,mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m'],20,@$a_['Y']));		break;
			case 30	:	$res	= mu_Date::getDate($p_format,mktime(@$a_['H'],@$a_['i'],@$a_['s'],@$a_['m']+1,0,@$a_['Y']));	break;
			default :	return false;
		}
		return $res;
	}
	function _replaceFormat($p_str = "",$p_time = "")
	{
		/* �ե����ޥå����Ƥ����ܸ��Ѥ��ִ� */
		$a_cnv	= array(
			'A'		=> array('AM'=>'����','PM'=>'���'),
			'D'		=> array('Sun'=>'��','Mon'=>'��','Tue'=>'��','Wed'=>'��','Thu'=>'��','Fri'=>'��','Sat'=>'��'),
			'l'		=> array('Sunday'=>'������','Monday'=>'������','Tuesday'=>'������','Wednesday'=>'������',
							'Thursday'=>'������','Friday'=>'������','Saturday'=>'������')
		);
		$str	= $p_str;
		reset ($a_cnv);
		while (list($part)=each($a_cnv)) {
			$token	= "\\$part";
			$a_wk	= explode($token,$str);
			reset ($a_wk);
			while (list($wc,$wd)=each($a_wk)) {
				$a_wk[$wc]	= str_replace($part, $a_cnv[$part][date($part,$p_time)], $wd);
			}
			$str	= implode($token,$a_wk);
		}
		return $str;
	}
	/* ����ʸ�����ʬ�Ϥ�������֤� */
	function _parseValue($p_date = "", $p_type = 'date', $p_correct = 0)
	{
		$a_def	= array('Y'=>date("Y"),'m'=>date("m"),'d'=>1,'H'=>0,'i'=>'0','s'=>'0');
		$a_res	= array('FORMAT'=>'');
		$a_chk	= array('year'=>0,'month'=>0,'date'=>0,'datetime'=>0);
		$a_type	= explode(' ',$p_type);
		reset ($a_type);
		while (list($tc,$td)=each($a_type)) {
			switch ($td) {
				case 'year' :		$a_chk['year']		= 1;	break;
				case 'month' :		$a_chk['month']		= 1;	break;
				case 'date' : 		$a_chk['date']		= 1;	break;
				case 'datetime' :	$a_chk['datetime']	= 1;	break;
				case 'free' :		$a_chk	= array('year'=>0,'month'=>1,'date'=>1,'datetime'=>1);	break;
			}
		}
		if ($a_chk['datetime']) {
			/* Database �� Timestamp */
			if (preg_match('/^(\d{4})([-.\/])(\d{1,2})([-.\/])(\d{1,2})\s(\d{1,2}):(\d{1,2}):(\d{1,2})[-+]{0,1}\d{0,2}$/', $p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'Y'.$a_regs[2].'m'.$a_regs[4].'d H:i:s',
					'Y'=>$a_regs[1],'m'=>$a_regs[3],'d'=>$a_regs[5],'H'=>$a_regs[6],'i'=>$a_regs[7],'s'=>$a_regs[8]);
			}
			/* Y-m-d H:i:s */
			if (preg_match('/^(\d{4})([-.\/])(\d{1,2})([-.\/])(\d{1,2})\s(\d{1,2}):(\d{1,2}):(\d{1,2})$/', $p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'Y'.$a_regs[2].'m'.$a_regs[4].'d H:i:s',
					'Y'=>$a_regs[1],'m'=>$a_regs[3],'d'=>$a_regs[5],'H'=>$a_regs[6],'i'=>$a_regs[7],'s'=>$a_regs[8]);
			}
		} 
		if ($a_chk['date'] || $p_correct==1) {
			/* Ymd */
			if (preg_match('/^(\d{4})(\d{2})(\d{2})$/',$p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'Ymd','Y'=>$a_regs[1],'m'=>$a_regs[2],'d'=>$a_regs[3]);
			}
			/* Y-m-d */
			if (preg_match('/^(\d{4})([-.\/])(\d{1,2})([-.\/])(\d{1,2})$/',$p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'Y'.$a_regs[2].'m'.$a_regs[4].'d','Y'=>$a_regs[1],'m'=>$a_regs[3],'d'=>$a_regs[5]);
			}
		}
		if ($a_chk['month'] || $p_correct==1) {
			/* Y-m */
			if (preg_match('/^(\d{4})([-.\/])(\d{1,2})$/',$p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'Y'.$a_regs[2].'m','Y'=>$a_regs[1],'m'=>$a_regs[3]);
			}
			/* Ym */
			if (preg_match('/^(\d{4})(\d{2})$/',$p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'Ym','Y'=>$a_regs[1],'m'=>$a_regs[2]);
			}
		}
		if ($p_correct==1) {
			/* m-d */
			if (preg_match('/^[-.\/]{0,1}(\d{1,2})([-.\/])(\d{1,2})$/',$p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'m'.$a_regs[2].'d','m'=>$a_regs[1],'d'=>$a_regs[3]);
			}
			/* md */
			if (preg_match('/^(\d{1,2})(\d{2})$/',$p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'md','m'=>$a_regs[1],'d'=>$a_regs[2]);
			}
			/* d */
			if (preg_match('/^[-.\/]{0,1}(\d{1,2})$/',$p_date,$a_regs)) {
				$a_res	= array('FORMAT'=>'d','d'=>$a_regs[1]);
			}
		}
		if ($a_res['FORMAT']=='')	return false;
		if ($p_correct == 1)	$a_res	= array_merge($a_def,$a_res);
		if (mu_Date::_chkDateArray($a_res)===false)	return false;
		return $a_res;
	}
	/* ���󲽤������դ������������å� */
	function _chkDateArray($pa_ = array())
	{
		if (!count(@$pa_))	return false;
		$format	= @$pa_['FORMAT'];
		if (preg_match('/[Ymd]/',$format)) {
			$yy	= strlen(@$pa_['Y']) ? @$pa_['Y'] : '2000';
			$mm	= strlen(@$pa_['m']) ? @$pa_['m'] : '1';
			$dd	= strlen(@$pa_['d']) ? @$pa_['d'] : '1';
			if (!checkdate($mm,$dd,$yy))	return false;
		}
		if (preg_match('/[His]/',$format)) {
			$hh	= strlen(@$pa_['H']) ? @$pa_['H'] : '0';
			$ii	= strlen(@$pa_['i']) ? @$pa_['i'] : '0';
			$ss	= strlen(@$pa_['s']) ? @$pa_['s'] : '0';
			if ($hh < 0 || $hh > 23)		return false;
			if ($ii < 0 || $ii > 59)		return false;
			if ($ss < 0 || $ss > 59)		return false;
		}
		return true;
	}
}	/* end of class [mu_Date] */
?>
