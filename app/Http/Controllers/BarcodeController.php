<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;

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

    public function downloadEan13Barcodes(Request $request)
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
}
