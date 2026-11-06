<?php

namespace App\Services;

class ExcelExporter
{
    /**
     * Xuất dữ liệu ra file Excel (.xls) tương thích 100% Microsoft Excel, Google Sheets, WPS Office
     *
     * @param object $export Đối tượng có phương thức view()
     * @param string $filename Tên file tải về
     * @return \Illuminate\Http\Response
     */
    public static function download($export, string $filename)
    {
        $view = $export->view();
        $html = $view->render();

        $content = '<!DOCTYPE html>'
            . '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">'
            . '<head>'
            . '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'
            . '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Báo cáo</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->'
            . '<style>'
            . 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 13px; color: #1f2937; }'
            . 'table { border-collapse: collapse; width: 100%; margin-top: 10px; }'
            . 'th { background-color: #F47716; color: #FFFFFF; font-weight: bold; border: 1px solid #CBD5E1; padding: 10px 14px; text-align: left; }'
            . 'td { border: 1px solid #E2E8F0; padding: 8px 12px; vertical-align: middle; }'
            . '.table-title { font-size: 18px; font-weight: bold; text-align: center; color: #111827; }'
            . '.table-meta { font-size: 12px; font-style: italic; text-align: center; color: #6B7280; }'
            . '</style>'
            . '</head>'
            . '<body>'
            . $html
            . '</body>'
            . '</html>';

        if (!str_ends_with(strtolower($filename), '.xls')) {
            $filename = preg_replace('/\.xlsx?$/i', '', $filename) . '.xls';
        }

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ]);
    }
}
