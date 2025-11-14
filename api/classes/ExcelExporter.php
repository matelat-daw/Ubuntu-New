<?php
/**
 * Excel Exporter Helper Class
 * Handles Excel file generation for billing and reports
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExporter {
    
    private $spreadsheet;
    private $sheet;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }
    
    /**
     * Generate billing report for a date range
     * 
     * @param array $orders Array of orders
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return string File path
     */
    public function generateBillingReport($orders, $startDate, $endDate) {
        // Set document properties
        $this->spreadsheet->getProperties()
            ->setCreator('Business API')
            ->setTitle('Reporte de Facturación')
            ->setSubject('Facturación del ' . $startDate . ' al ' . $endDate)
            ->setDescription('Reporte automático de facturación');
        
        // Title
        $this->sheet->setCellValue('A1', 'REPORTE DE FACTURACIÓN');
        $this->sheet->mergeCells('A1:H1');
        $this->styleHeader('A1');
        
        // Date range
        $this->sheet->setCellValue('A2', 'Periodo: ' . $startDate . ' - ' . $endDate);
        $this->sheet->mergeCells('A2:H2');
        
        // Column headers
        $this->sheet->setCellValue('A4', 'ID Pedido');
        $this->sheet->setCellValue('B4', 'Fecha');
        $this->sheet->setCellValue('C4', 'Cliente');
        $this->sheet->setCellValue('D4', 'Email');
        $this->sheet->setCellValue('E4', 'Estado');
        $this->sheet->setCellValue('F4', 'Subtotal');
        $this->sheet->setCellValue('G4', 'IVA');
        $this->sheet->setCellValue('H4', 'Total');
        
        $this->styleColumnHeaders('A4:H4');
        
        // Data rows
        $row = 5;
        $totalSubtotal = 0;
        $totalIVA = 0;
        $totalAmount = 0;
        
        foreach ($orders as $order) {
            $this->sheet->setCellValue('A' . $row, $order['id']);
            $this->sheet->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($order['created_at'])));
            $this->sheet->setCellValue('C' . $row, $order['customer_name'] ?? 'N/A');
            $this->sheet->setCellValue('D' . $row, $order['customer_email'] ?? 'N/A');
            $this->sheet->setCellValue('E' . $row, $this->translateStatus($order['status']));
            $this->sheet->setCellValue('F' . $row, $order['subtotal']);
            $this->sheet->setCellValue('G' . $row, $order['tax']);
            $this->sheet->setCellValue('H' . $row, $order['total']);
            
            // Format currency
            $this->sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00 €');
            
            $totalSubtotal += $order['subtotal'];
            $totalIVA += $order['tax'];
            $totalAmount += $order['total'];
            
            $row++;
        }
        
        // Totals row
        $this->sheet->setCellValue('E' . $row, 'TOTALES:');
        $this->sheet->setCellValue('F' . $row, $totalSubtotal);
        $this->sheet->setCellValue('G' . $row, $totalIVA);
        $this->sheet->setCellValue('H' . $row, $totalAmount);
        
        $this->styleTotalsRow('A' . $row . ':H' . $row);
        $this->sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()
            ->setFormatCode('#,##0.00 €');
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Generate file
        return $this->save('facturacion_' . date('Y-m-d_His') . '.xlsx');
    }
    
    /**
     * Generate sales report by product
     * 
     * @param array $products Array of products with sales data
     * @return string File path
     */
    public function generateSalesReport($products) {
        // Set document properties
        $this->spreadsheet->getProperties()
            ->setCreator('Business API')
            ->setTitle('Reporte de Ventas por Producto')
            ->setDescription('Reporte de ventas detallado por producto');
        
        // Title
        $this->sheet->setCellValue('A1', 'REPORTE DE VENTAS POR PRODUCTO');
        $this->sheet->mergeCells('A1:F1');
        $this->styleHeader('A1');
        
        // Column headers
        $this->sheet->setCellValue('A3', 'ID');
        $this->sheet->setCellValue('B3', 'Producto');
        $this->sheet->setCellValue('C3', 'Unidades Vendidas');
        $this->sheet->setCellValue('D3', 'Precio Unitario');
        $this->sheet->setCellValue('E3', 'Total Ventas');
        $this->sheet->setCellValue('F3', 'Stock Actual');
        
        $this->styleColumnHeaders('A3:F3');
        
        // Data rows
        $row = 4;
        $totalUnits = 0;
        $totalSales = 0;
        
        foreach ($products as $product) {
            $this->sheet->setCellValue('A' . $row, $product['id']);
            $this->sheet->setCellValue('B' . $row, $product['name']);
            $this->sheet->setCellValue('C' . $row, $product['units_sold']);
            $this->sheet->setCellValue('D' . $row, $product['price']);
            $this->sheet->setCellValue('E' . $row, $product['total_sales']);
            $this->sheet->setCellValue('F' . $row, $product['stock']);
            
            // Format currency
            $this->sheet->getStyle('D' . $row . ':E' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00 €');
            
            $totalUnits += $product['units_sold'];
            $totalSales += $product['total_sales'];
            
            $row++;
        }
        
        // Totals row
        $this->sheet->setCellValue('B' . $row, 'TOTALES:');
        $this->sheet->setCellValue('C' . $row, $totalUnits);
        $this->sheet->setCellValue('E' . $row, $totalSales);
        
        $this->styleTotalsRow('A' . $row . ':F' . $row);
        $this->sheet->getStyle('E' . $row)->getNumberFormat()
            ->setFormatCode('#,##0.00 €');
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Generate file
        return $this->save('ventas_por_producto_' . date('Y-m-d_His') . '.xlsx');
    }
    
    /**
     * Style header cell
     */
    private function styleHeader($cell) {
        $this->sheet->getStyle($cell)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '667eea']
            ]
        ]);
        $this->sheet->getRowDimension(1)->setRowHeight(30);
    }
    
    /**
     * Style column headers
     */
    private function styleColumnHeaders($range) {
        $this->sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '764ba2']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }
    
    /**
     * Style totals row
     */
    private function styleTotalsRow($range) {
        $this->sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8E8E8']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }
    
    /**
     * Translate order status to Spanish
     */
    private function translateStatus($status) {
        $translations = [
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado'
        ];
        
        return $translations[$status] ?? $status;
    }
    
    /**
     * Save spreadsheet to file
     */
    private function save($filename) {
        $filePath = __DIR__ . '/../exports/' . $filename;
        
        // Create exports directory if it doesn't exist
        $exportDir = __DIR__ . '/../exports';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filePath);
        
        return $filePath;
    }
    
    /**
     * Download file
     */
    public static function download($filePath, $filename = null) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        if ($filename === null) {
            $filename = basename($filePath);
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');
        
        readfile($filePath);
        
        // Optional: delete file after download
        // unlink($filePath);
        
        return true;
    }
}
