<?php
/**
 * Plugin Name: Attach Invoice to Woo Email on Complete Order
 * Plugin URI: 
 * Description: Generate and attach an invoice on order Complete
 * Version: 1.0.0
 * Author: Darya Mazanenka
 * Author URI: https://daryamazanenko.com
 * License: GPLv2 or later
 * Text Domain: nottrue
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists('DMPdfInvoices') ){
    
    require_once 'includes/class-dmpdf-invoices.php';
    
    $dmPdfInvoices = new DMPdfInvoices();
}