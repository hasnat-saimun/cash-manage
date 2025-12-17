<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pdf:test-bengali', function () {
    $this->info('Generating Bengali sample PDF using mPDF...');
    try {
        if (!class_exists('Mpdf\\Mpdf')) {
            $this->error('mPDF not installed. Run: composer require mpdf/mpdf');
            return 1;
        }

        $fontDir = public_path('fonts');
        $reg = $fontDir.DIRECTORY_SEPARATOR.'NotoSansBengali-Regular.ttf';
        $bold = $fontDir.DIRECTORY_SEPARATOR.'NotoSansBengali-Bold.ttf';

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 12,
            'margin_right' => 12,
            'margin_bottom' => 12,
            'margin_left' => 12,
        ];

        if (is_file($reg) && is_file($bold)) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            $config['fontDir'] = array_merge($fontDirs, [$fontDir]);
            $config['fontdata'] = $fontData + [
                'notosansbengali' => [
                    'R' => 'NotoSansBengali-Regular.ttf',
                    'B' => 'NotoSansBengali-Bold.ttf',
                ],
            ];
            $config['default_font'] = 'notosansbengali';
        }

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        if (property_exists($mpdf, 'useOTL')) { $mpdf->useOTL = 0xFF; }

        $sample = 'বাংলা পাঠ্য পরীক্ষা — সংখ্যা ১২৩৪৫, তারিখ ২০২৫-১২-১৭';
          $html = '<!DOCTYPE html><html lang="bn"><head><meta charset="utf-8"><style>body{font-family: notosansbengali; font-size:14px;}</style></head><body>'
              . '<h3>বাংলা ফন্ট পরীক্ষা</h3><p>' . $sample . '</p>'
              . '<p><strong>English fallback:</strong> Bengali text above should be properly shaped.</p>'
              . '</body></html>';

        $mpdf->WriteHTML($html);
        $outPath = storage_path('app/test-bengali.pdf');
        file_put_contents($outPath, $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN));
        $this->info('PDF written to: ' . $outPath);
        return 0;
    } catch (\Throwable $e) {
        $this->error('Failed to generate PDF: ' . $e->getMessage());
        return 1;
    }
})->purpose('Generate a sample PDF with Bengali text to verify font rendering');
