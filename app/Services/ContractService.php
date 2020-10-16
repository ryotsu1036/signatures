<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use App\Repositories\ContractRepository;

class ContractService
{
    protected $directory;
    protected $contractRepository;

    public function __construct(ContractRepository $contractRepository)
    {
        $this->directory = Str::random(32);
        $this->contractRepository = $contractRepository;
    }

    public function create($data)
    {
        $collection = collect($data);
        $image = [];

        // 將 base64 格式轉成圖檔
        $base64Image = $collection->only(['signature_image']);
        $image = $this->base64ToImage($base64Image);

        // 增加簽名圖檔至文件中
        $document = $this->addSignatureImageToDocument($image['path']);
        $data = $collection->merge(['signature_image' => $image['url'], 'document' => $document]);

        // 儲存至 DB
        return $this->contractRepository->create($data);
    }

    public function addSignatureImageToDocument($signatureImage)
    {
        // 初始化 Fpdi()
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);

        // 取得當前 PDF 文件總頁數
        $originalDocumentPath = Storage::path('contracts/originals/【經絡儀與精油檢測】特種個人資料提供同意書.pdf');
        $pageCount = $pdf->setSourceFile($originalDocumentPath);
        for ($pageNo=1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);

            $width = $pdf->getTemplateSize($templateId)['width']; // 取得頁面的寬度
            $height = $pdf->getTemplateSize($templateId)['height']; // 取得頁面的高度
            $orientation = $pdf->getTemplateSize($templateId)['orientation']; // 取得頁面的方向，預設為直向 "P"、橫向 "L"
            $pdf->AddPage($orientation, [$width, $height]);
            $pdf->useTemplate($templateId, ['adjustPageSize' => false]);
        }

        // 增加簽名圖檔
        $pdf->Image($signatureImage, 140, 246, 30, '', 'PNG');

        // 顯示當前時間
        $pdf->SetXY(141, 258);
        $pdf->SetFont('', 'I', 8);
        $pdf->SetTextColor(24, 103, 192);
        $pdf->Write(0, Carbon::now()->format('Y/m/d H:i:s'), '', 0, 'L', true, 0, false, false, 0);

        // 中華民國 :: 年
        $pdf->SetXY(80, 268);
        $pdf->SetFont('', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Write(0, Carbon::now()->format('Y') - 1911, '', 0, 'L', true, 0, false, false, 0);

        // 中華民國 :: 月
        $pdf->SetXY(125, 268);
        $pdf->SetFont('', 'B', 14);
        $pdf->Write(0, Carbon::now()->format('m'), '', 0, 'L', true, 0, false, false, 0);

        // 中華民國 :: 日
        $pdf->SetXY(170, 268);
        $pdf->SetFont('', 'B', 14);
        $pdf->Write(0, Carbon::now()->format('d'), '', 0, 'L', true, 0, false, false, 0);

        $filePath = sprintf('contracts/%s/【經絡儀與精油檢測】特種個人資料提供同意書.pdf', $this->directory);

        /**
         * I：將文件內聯發送到瀏覽器（默認）。如果可用，請使用該插件。當在生成PDF的鏈接上選擇“另存為”選項時，將使用按名稱提供的名稱。
         * D：發送到瀏覽器並強制使用名稱指定的名稱下載文件。
         * F：使用名稱給定的名稱保存到本地服務器文件。
         * S：以字符串形式返回文檔（忽略名稱）。
         * FI：相當於 F + I 選項
         * FD：相當於 F + D 選項
         * E：以 base64 mime 多部分電子郵件附件的形式返回文檔（RFC 2045）
         */
        $pdf->Output(Storage::path($filePath), 'F');

        return Storage::url($filePath);
    }

    public function base64ToImage($base64Image)
    {
        $data = substr($base64Image, strpos($base64Image, ',') + 1);
        $data = base64_decode($data);

        $filePath = sprintf('contracts/%s/signature.png', $this->directory);
        Storage::put($filePath, $data);

        return [
            'url' => Storage::url($filePath),
            'path' => Storage::path($filePath)
        ];
    }
}
