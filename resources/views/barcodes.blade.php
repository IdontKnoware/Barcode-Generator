<!DOCTYPE html>
<html>
<head>
    <title>WorldSBK Store - Barcode Generator</title>
</head>
<body>
<style>
    h4 {
        font-family: 'Courier New', Courier, monospace;
    }
    a {
        font-family: 'Trebuchet MS';
    }
    .barcode {
        display: flex;
        flex-direction: column;
        width: 450px;
        align-content: center;
        justify-content: center;
        align-items: center;
    }
    .barcode img:first-child {
        width: 350px;
    }
    form {
        text-align: center;
        padding: 50px;
    }
    form button {
        font-size: 18px;
        padding: 25px;
    }
    form button:hover {
        cursor: pointer;
        scale: 1.01;
        transition: .111ms;
        font-weight: bold;
    }
</style>


@foreach ($barcodes as $k => $v)
   @php $numeros[] = $v['label'] @endphp
    <div class="barcode barcode--{{ $k }}" style="border: 1px solid lightslategrey; border-radius: 6px; margin:5px; padding: 15px;">
{{--        <img src={{ asset('cap_kawasaki.webp') }}>--}}
        <img id="barcode-{{$k}}" src="data:image/png;base64,{{ base64_encode($v['img']) }}" alt="{{ $v['label'] }}" style="width: 45%">
        <h4 style="margin: 0; text-align: center; font-size: 25px">{{ $v['label'] }}</h4>
        <a id="download-{{$k}}" download="{{$v['label']}}" style="display:none;"></a>
    </div>
    <br>
@endforeach
<hr>
<form action="{{ route('barcodes-csv') }}" method="POST">
    @csrf
    <input type="hidden" name="numeros" value="{{ json_encode($numeros) }}">
    <button type="submit">Descarregar barcodes com CSV</button>
</form>
<hr>
<button id="download-barcode-images">Descargar todas las imágenes</button>


</body>
</html>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>
<script>
    let images = [];
    let barcodes = document.querySelectorAll('[id^="barcode-"]');

    barcodes.forEach((barcode, index) => {
        html2canvas(barcode.parentElement).then(function(canvas) {
            barcode.src = canvas.toDataURL();

            images.push({ filename: 'Cap_Kawasaki_Experience_' + barcode.alt + '.jpg', data: barcode });
            let parent = barcode.parentNode;
            let link = document.createElement('a');

            link.href = barcode.src;
            link.download = 'Cap_Kawasaki_Experience_' + barcode.alt + '.jpg';
            link.text = 'Download image';

            parent.appendChild(link);
        });
    });
</script>

<script>
    let downloadAllButton = document.querySelector('#download-barcode-images');

    downloadAllButton.addEventListener('click', function() {
        // Crea un objeto FormData y agrega el arreglo de imágenes
        let formData = new FormData();
        formData.append('images', JSON.stringify(images));
        console.log(images[0]);

        // Envia la solicitud al backend
        fetch('/barcode-images-zip', {
            method: 'POST',
            body: formData
        })
            .then(function(response) {
                return response.blob();
            })
            .then(function(blob) {
                // Crea un enlace para descargar el archivo ZIP
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'barcode_imgs.zip';
                link.click();
            });
    });
</script>
