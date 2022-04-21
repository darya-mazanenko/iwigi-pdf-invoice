<?php
if( !defined('ABSPATH') ) {
    exit;
}

class DMPdfInvoices
{
    /**
     * Plugin folder path
     */
    public static $plugin_basefile_path;
    
    public static $plugin_basefile;
    
    public static $plugin_url;
    
    public static $plugin_path;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->define_constants();
        $this->load();
    }
    
    private function define_constants(){
        //self::$dmpdf_path = plugin_dir_path( __FILE__ );
        
        self::$plugin_basefile_path = dirname( dirname( __FILE__ ) ) . '/dmpdf-invoices-for-woocommerce.php';
        self::$plugin_basefile      = plugin_basename( self::$plugin_basefile_path );
		self::$plugin_url           = plugin_dir_url( self::$plugin_basefile );
		self::$plugin_path          = trailingslashit( dirname( self::$plugin_basefile_path ) );
    }

    public function load()
    {
        add_action('plugins_loaded', array($this, 'attach_invoices'));
    }

    /**
     * Check if WooCommerce is active
     **/
    public function is_woocommerce_installed()
    {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return true;
        }
        return false;
    }
    
    public function include_classes()
    {
        require_once 'tcpdf/tcpdf.php';
        require_once 'class-invoice.php';
    }

    /**
     * Attaches the Generated Invoice to Complete Emails 
     */
    public function attach_invoices()
    {
        if( $this->is_woocommerce_installed() ){
            $this->include_classes();
            
            add_filter( 'woocommerce_email_attachments', array($this, 'dm_attach_pdf_to_emails'), 10, 4);
        }
    }

    public function dm_attach_pdf_to_emails($attachments, $email_id, $order, $email)
    {
        // need to add check if sending complete emails is active
        
        $email_ids = array( 'customer_completed_order' ); //customer_processing_order
        if ( in_array ( $email_id, $email_ids ) ) {
            
            $id = $order->get_id();
            
            //$vat     = get_post_meta( $id, '_vat', true );
            $total_total = $order->get_total();
            $shipping = $order->get_shipping_total();
            $shipping_tax = $order->get_shipping_tax();
            
            $fname = $order->get_billing_first_name();
            $lname = $order->get_billing_last_name();
            
            $company = $order->get_billing_company();
            $bill_addr1 = $order->get_billing_address_1();
            $bill_addr2 = $order->get_billing_address_2();
            $city = $order->get_billing_city();
            $state = $order->get_billing_state();
            $postcode = $order->get_billing_postcode();
            $country = $order->get_billing_country();

            //Generate Invoice
            
            $pdf = new CustomPdfGenerator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 12, '', true);
            
            // start a new page
            $pdf->AddPage();
            
            $orderItems = [];
            $total_item = 0;
            $total_tax = 0;
            //Title', 'Unit Price', 'Qty', 'Subtotal');
            $k = 0;
            
            foreach ( $order->get_items() as $item_id => $item ) {
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $subtotal = $item->get_subtotal();
                $tax = $item->get_subtotal_tax();
                
                $total_tax += $tax;
                $total_item += $subtotal;
                
                $uprice = $subtotal/$quantity;
                
                //$total = $item->get_total();
                
                $orderItems[$k]['title'] = $product_name;
                $orderItems[$k]['uprice'] = $uprice;
                $orderItems[$k]['qty'] = $quantity;
                $orderItems[$k]['subtotal'] = $subtotal;
                
                $k++;
            }
            
            $total_tax += $shipping_tax;
            
            // date and invoice no
            $pdf->Write(0, "\n\n\n\n", '', 0, 'C', true, 0, false, false, 0);
            $today = date("d/m/Y");
            
            $pdf->writeHTML("<h2>VAT INVOICE</h2>", true, false, false, false, 'L');
            $pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);
            
            // get current vertical position
            
            $left_column = "";
            if($company !== ''){
                $left_column .= $company;
                $left_column .= "<br>";
            }
            
            if( $fname !== ''){
                $left_column .= $fname;
                $left_column .= " ";
            }
            if($lname !== ''){
                $left_column .= $lname;
                $left_column .= "<br>";
            }
            
            if($bill_addr1 !== ''){
                $left_column .= $bill_addr1;
                $left_column .= "<br>";
            }
            if($bill_addr2 !== ''){
                $left_column .= $bill_addr2;
                $left_column .= "<br>";
            }
            if($city !== ''){
                $left_column .= $city;
                $left_column .= "<br>";
            }
            if($state !== ''){
                $left_column .= $state;
                $left_column .= "<br>";
            }
            if($postcode !== ''){
                $left_column .= $postcode;
                $left_column .= "<br>";
            }
            if($country !== ''){
                $left_column .= $country;
                $left_column .= "<br>";
            }
            $right_column = "";
            $right_column .= "<b>Number: </b>$id<br>";
            $right_column .= "<b>Billing Date: </b>$today<br>";
            $right_column .= "<b>Our VAT Number: </b>GB209787862<br>";
            
            $y = $pdf->getY();
            
            // write the first column
            $pdf->writeHTMLCell(80, '', '', $y, $left_column, 0, 0, 0, true, 'L', true);
            // write the second column
            $pdf->writeHTMLCell(100, '', '', '', $right_column, 0, 1, 0, true, 'R', true);
            
            // reset pointer to the last page
            $pdf->lastPage();
            
            $pdf->Write(0, "\n\n\n", '', 0, 'C', true, 0, false, false, 0);
            //$pdf->Write(0, "\n", '', 0, 'C', true, 0, false, false, 0);
            
            // invoice table starts here
            $header = array('#', 'Title', 'Unit Price', 'Qty', 'Subtotal');
            
            $pdf->printTable($header, $orderItems, $total_tax, $total_total, $shipping);
            $pdf->Ln();
            
            $header1 = array('Rate', 'Vat', 'Net');
            $pdf->printNetTable($header1, $total_item, $shipping, $total_tax, $total_total);
            $pdf->Ln();
            
            $pdf->Write(0, "\n\n\n", '', 0, 'C', true, 0, false, false, 0);
            
            $upload_dir = wp_upload_dir();
            $file = "$id.pdf";
            $pdf->Output($upload_dir['basedir'] . '/' .$file, 'F');
            
            $attachments[] = $upload_dir['basedir'] . '/' . "$file";
        }
        return $attachments;
    }
}