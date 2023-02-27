<?php
require('japanese.php');

$pdf=new PDF_Japanese();
$pdf->AddSJISFont();
$pdf->Open();
$pdf->AddPage();
$pdf->SetFont('SJIS', '', 18);
$pdf->Write(8, 'FPDFの日本語対応サンプルです。');
$pdf->Output();
?>
