<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use ZipArchive;

class BarcodeController extends Controller
{
    public function makeEan13Barcodes(Request $request, $times = 0)
    {
        $barcodeGenerator = new BarcodeGeneratorPNG();
        $barcodeGenerator->useImagick();
        $countryPrefix = 840;

        for ($i = 0; $i < $times; $i++) {
            $randomizeEan13 = $countryPrefix . rand(100000000, 999999999);
            $barcodes[$i]['label'] = $randomizeEan13;
            $barcodes[$i]['img'] = $barcodeGenerator->getBarcode($randomizeEan13, $barcodeGenerator::TYPE_EAN_13);
        }

        return view('barcodes', [
            'barcodes' => $barcodes
        ]);
    }

    public function downloadEan13BarcodesCsv(Request $request)
    {
        // Recuperar los barcodes del formulario
        $numeros = json_decode($request->input('numeros'), true);

        // Verificar si los datos se decodifican correctamente
        if ($numeros === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Error en la decodificación de JSON'], 400);
        }

        // Generar CSV
        $csv = '';
        $csv .= 'Barcodes' . "\n";
        foreach ($numeros as $numero) {
            $csv .= $numero . "\n";
        }

        // Descargar CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="barcodes.csv"',
        ];
        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, 'barcodes.csv', $headers);
    }

    public function downloadBarcodes(Request $request)
    {
        $numeros = $request->input('numeros');
        $barcodes = [];
        foreach ($numeros as $numero) {
            $barcode = $this->generateBarcode($numero);
            $barcodes[] = $barcode;
        }
        $zipName = 'barcodes.zip';
        $zip = new ZipArchive;
        if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
            foreach ($barcodes as $barcode) {
                $zip->addFromString($barcode['label'] . '.jpg', base64_decode($barcode['img']));
            }
            $zip->close();
            return response()->download($zipName)->deleteFileAfterSend(true);
        } else {
            return response('Error: no se pudo crear el archivo zip', 500);
        }
    }

    public function downloadHtml2CanvasGeneratedImagesAsZip(Request $request)
    {
        dd($request);
        // Obtener los números y las imágenes del formulario
        $numeros = json_decode($request->input('numeros'));
        $images = $request->input('images');

        // Crear un nuevo archivo ZIP
        $zip = new \ZipArchive();
        $zipname = 'barcodes.zip';
        $zip->open($zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Agregar cada imagen al archivo ZIP
        foreach ($images as $index => $imageData) {
            dd($imageData);
            $filename = 'barcode-' . $numeros[$index] . '.jpg';
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
            $zip->addFromString($filename, $imageData);
        }

        // Cerrar el archivo ZIP y descargar
        $zip->close();
        return response()->download($zipname);
    }
}
