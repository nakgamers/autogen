<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat - {{ $letter->letter_number }}</title>
    <style>
        /* 1. Atur Ukuran Kertas ke Letter dan Perkecil Margin */
        @page {
            size: letter; 
            margin: 10mm 15mm 10mm 15mm; /* Atas, Kanan, Bawah, Kiri */
        }

        /* 2. Perkecil Font dan Line Height agar lebih padat */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt; /* Lebih kecil dari sebelumnya (10pt) */
            line-height: 1.3; /* Jarak antar baris dirapatkan */
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }

        /* 3. Sesuaikan ukuran kanvas preview Web dengan ukuran Letter */
        .letter-body {
            background-color: #ffffff;
            width: 215.9mm; /* Lebar kertas Letter */
            min-height: 279.4mm; /* Tinggi kertas Letter */
            margin: 0 auto;
            padding: 15mm 20mm;
            box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Styling Bilah Tombol Preview (Tetap Sama) */
        .no-print-bar {
            background-color: #1f2937;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-family: sans-serif;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .no-print-bar .title { color: #ffffff; font-size: 14px; font-weight: 600; }
        .btn-group { display: flex; gap: 10px; }
        .btn {
            display: inline-block; padding: 8px 16px; font-size: 13px;
            font-weight: 600; text-decoration: none; border-radius: 6px;
            cursor: pointer; border: none;
        }
        .btn-download { background-color: #2563eb; color: #ffffff; }
        .btn-print { background-color: #059669; color: #ffffff; }
        .btn-back { background-color: #4b5563; color: #ffffff; }

        /* Mencegah tabel dan list terpotong di tengah halaman */
        table, tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
        }

        @media print {
            body { background-color: #ffffff; }
            .letter-body {
                width: 100%;
                min-height: 100%;
                padding: 0;
                box-shadow: none;
            }
            .no-print-bar {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            @page {
                size: letter;
                margin: 10mm 15mm 10mm 15mm;
            }
        }
    </style>
</head>
<body>

    @if(!isset($isPdf))
    <div class="no-print-bar">
        <span class="title">Preview Surat: {{ $letter->letter_number }}</span>
        <div class="btn-group">
            <a href="{{ route('letters.create') }}" class="btn btn-back">← Kembali</a>
            <button onclick="window.print()" class="btn btn-print">Cetak Langsung</button>
            <a href="{{ route('letters.export-pdf', $letter->id) }}" class="btn btn-download">Download PDF</a>
        </div>
    </div>
    @endif

    <div class="letter-body content">
        {!! $content !!}
    </div>

</body>
</html>