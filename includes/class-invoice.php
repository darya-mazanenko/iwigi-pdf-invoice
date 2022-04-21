<?php
if( !defined('ABSPATH') ){
    exit;
}

// documentanion https://www.radmin.com/tcpdf/doc/com-tecnick-tcpdf/TCPDF.html#methodCell
class CustomPdfGenerator extends TCPDF 
{
    public function Header() 
    {
        $image_file = DMPdfInvoices::$plugin_path . 'assets/images/logo.jpg';
        $this->SetFont('helvetica', 'B', 12);
        $this->Ln();
        $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln();
        $this->Cell(0, 15, 'Marcos Enterprise Ltd', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 10, 'Unit 71', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Cell(0, 10, 'Hillgrove Business Park', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Cell(0, 10, 'Nazeing Road', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Cell(0, 10, 'Waltham Abbey', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Cell(0, 10, 'EN9 2HB', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Ln();
        $this->Image($image_file, '', 10, 40, '', 'JPG', '', 'N', false, 300, 'R', false, false, 0, false, false, false);
        $this->Ln();
        $this->Cell(0, 20, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
 
    public function Footer() 
    {
        $this->SetY(-30);
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 10, 'Bank details:', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Cell(0, 10, 'Metro Bank', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Cell(0, 10, 'Sort code: 23-05-80', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
        $this->Cell(0, 10, 'Account no.: 16955922', 0, 1, 'L', 0, '', 0, false, 'C', 'M');
    }
 
    public function printTable($header, $data, $total_tax, $total_total, $shipping)
    {
        $this->SetFillColor(0, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B', 10);
 
        $w = array(10, 112, 20, 20, 20);
        
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; ++$i) {
            if($i == 1){
                $this->Cell($w[$i], 6, $header[$i], 1, 0, 'L', 1);
            }else{
                $this->Cell($w[$i], 6, $header[$i], 1, 0, 'C', 1);
            }
        }
        $this->Ln();
 
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
 
        // table data
        $fill = 0;
        $total = 0;
 
        foreach($data as $key => $row) {
            $title = $row['title'];

            $arr = explode(" ", $title);
            $l = count($arr);
            
            $str = "";
            $lines = 1;
            for( $i = 0;  $i < $l; $i++ ){
            
              if($i == 0 || $i % 4 != 0 ){
            	$str .= $arr[$i] . " ";
              }else{
              	$str .= "<br>";
                $lines++;
              }
            }
            
            $h = $lines*6; 
            
            $ind = $key + 1;
            $this->Cell($w[0], $h, $ind, 'LR', 0, 'T', $fill);
            
            if($lines > 1){
                $x = $this->GetX();
                $y = $this->GetY();
                
                $y = $y-3;
                
                $this->SetY($y, false);
                $this->writeHTMLCell($w[1], '', $x, $y, $str, 0, 0, 0, false, 'L', true);
                
                $y = $y+3;
                $this->SetY($y, false);
                
            } else {
                $this->writeHTMLCell($w[1], $h, '', '', $str, 0, 0, 0, false, 'L', true);
            }
            
            $this->Cell($w[2], $h, $row['uprice'], 'LR', 0, 'T', $fill);
            $this->Cell($w[3], $h, $row['qty'], 'LR', 0, 'T', $fill);
            $this->Cell($w[4], $h, $row['subtotal'], 'LR', 0, 'T', $fill);
            $this->Ln();
            $fill=!$fill;
            $total+=$row['subtotal'];
            
        }

        $this->Cell(array_sum($w), 0, '', 'T');
        
        $this->Ln();
        
        $totalHTML = "<table>";
        $totalHTML .= "<tbody>";
        
        $totalHTML .= "<tr>";
        $totalHTML .= "<td><b>Net Amount</b></td>";
        $totalHTML .= "<td>£$total</td>";
        $totalHTML .= "</tr>";
        
        if( $shipping > 0 ){
            $totalHTML .= "<tr>";
            $totalHTML .= "<td><b>Shipping</b></td>";
            $totalHTML .= "<td>£$shipping</td>";
            $totalHTML .= "</tr>";
        }
        
        $totalHTML .= "<tr>";
        $totalHTML .= "<td><b>VAT 20% </b></td>";
        $totalHTML .= "<td>£$total_tax</td>";
        $totalHTML .= "</tr>";
        
        $totalHTML .= "<tr>";
        $totalHTML .= "<td><b>Total Invoice</b></td>";
        $totalHTML .= "<td>£$total_total</td>";
        $totalHTML .= "</tr>";
        
        $totalHTML .= "<tr>";
        $totalHTML .= "<td><b>Balance Due</b></td>";
        $totalHTML .= "<td>£0.00</td>";
        $totalHTML .= "</tr>";
        
        $totalHTML .= "</tbody>";
        $totalHTML .= "</table>";
 
        $y = $this->getY();
        $left_column = '';
        // write the first column
        $this->writeHTMLCell(130, '', '', $y, $left_column, 0, 0, 0, true, 'L', true);
        // write the second column
        $this->writeHTMLCell(50, '', '', '', $totalHTML, 'B', 1, 0, true, 'R', true);
        $this->lastPage();
    }
    
    public function printNetTable($header, $total_item, $shipping, $total_tax, $total_total)
    {
        $this->SetFillColor(0, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B', 10);
 
        $w = array(60, 60, 60);
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w[$i], 6, $header[$i], 1, 0, 'R', 1);
        }
        $this->Ln();
 
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
 
        // table data
        $fill = 0;
           
        $net =  $total_item + $shipping;
           
        $this->Cell($w[0], 6, 'VAT @ 20%', 'LR', 0, 'R', $fill);
        $this->Cell($w[1], 6, $total_tax, 'LR', 0, 'R', $fill);
        $this->Cell($w[2], 6, $net, 'LR', 0, 'R', $fill);
        $this->Ln();
        
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln();
        
    }
}
