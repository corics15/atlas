<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/tcpdf/tcpdf.php';

class Atlas_pdf
{
  public function render($html, $filename = 'document.pdf', $orientation = 'P')
  {
    $pdf = new TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('ATLAS');
    $pdf->SetAuthor('ATLAS');
    $pdf->SetTitle($filename);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);

    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output($filename, 'I');
  }
}