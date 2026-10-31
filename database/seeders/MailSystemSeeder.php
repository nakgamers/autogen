<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LetterType;
use App\Models\LetterTemplate;
use Illuminate\Support\Facades\DB;

class MailSystemSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        LetterTemplate::truncate();
        LetterType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ====================================================================
        // 1. SURAT PEMBERITAHUAN KEWAJIBAN (SPKP)
        // ====================================================================
        $typeSpkp = LetterType::create([
            'code'          => 'SPKP',
            'name'          => 'Surat Pemberitahuan Kewajiban',
            'format_number' => '{sequence}/WOLSAM/{month}/{year}',
        ]);

        $templateSpkpHtml = <<<'HTML'
<!-- KOP SURAT -->
<table width="100%" style="border-collapse: collapse; margin-bottom: 25px;">
    <tr>
        <td width="70%" style="vertical-align: top;"><br><br><br>
            <strong>Jakarta, {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</strong><br><br>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Tujuan Surat -->
                    <div style="margin-bottom: 10px;">
                        Kepada Yth.<br>
                        <strong>{{ $bankAccount->customer_name ?? '[Nama Nasabah]' }}</strong><br>
                        {{ $bankAccount->billing_address ?? '[Alamat Tagih]' }}<br>
                        {{ $bankAccount->billing_city ?? '[Kota]' }} {{ $bankAccount->collateral_province ? ', ' . $bankAccount->collateral_province : '' }}
                    </div>
                </tr>
                <tr>
                    <td width="70" style="vertical-align: top;">Nomor</td>
                    <td width="10" style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">{{ $letter_number ?? '[Nomor Surat]' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Perihal</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;"><strong>Pemberitahuan Kewajiban {{ $isKonven ? 'Kredit' : 'Pembiayaan' }}</strong></td>
                </tr>
            </table>
        </td>
        <td width="30%" style="vertical-align: top; text-align: right; font-size: 8pt; color: #333; line-height: 1.3;">
            <div style="margin-bottom: 6px;">
                @if(isset($isPdf) && $isPdf)
                    <img src="{{ public_path('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @else
                    <img src="{{ asset('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @endif
            </div>
            <div style="text-align: left;">
                <br><br>
                <strong>Kantor Pusat</strong><br>
                Jl. Suryopranoto No.8<br>
                Jakarta Pusat 10130<br>
                Telp. (021) 80655555<br>
                Fax. (021) 80655500
            </div>
        </td>
    </tr>
</table>

<p style="text-align: justify; margin-bottom: 10px;">
    Sehubungan dengan fasilitas {{ $termFasilitas }} yang telah Saudara peroleh dari Bank Jakarta digolongkan {{ $termFasilitas }} bermasalah
    @if(is_array($allAccountNumbers) && count($allAccountNumbers) > 1)
        (Rekening No: <strong>{{ implode(', ', $allAccountNumbers) }}</strong>)
    @endif
    , dengan ini kami sampaikan hal-hal sebagai berikut :
</p>

<!-- Point 1 & Tabel Rincian -->
<ol style="margin-left: -20px; margin-bottom: 10px;">
    <li style="text-align: justify; margin-bottom: 15px;">
        Berdasarkan catatan administrasi per tanggal {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }} kewajiban Saudara sebesar <strong>Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</strong> dengan rincian sebagai berikut :
        
            <table border="1" style="width: auto; min-width: 50%; table-layout: auto; border-collapse: collapse; margin-top: 10px; margin-bottom: 6px; font-size: 8.5pt;">
    <thead>
        <tr style="background-color: #c0c0c0; font-weight: bold;">
            <th style="padding: 6px 10px; text-align: left; white-space: nowrap;">Rincian Kewajiban</th>
            @foreach($accountsList as $acc)
                <th style="padding: 6px 10px; text-align: center; white-space: nowrap;">Rekening<br><span style="font-family: monospace;">{{ $acc->account_number }}</span></th>
            @endforeach
            @if(count($accountsList) > 1)
                <th style="padding: 6px 10px; text-align: center; white-space: nowrap; background-color: #b0b0b0;">Total</th>
            @endif
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Pokok Kredit' : 'Pokok Pembiayaan' }}</td>
            @foreach($accountsList as $acc)
                <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->principal_arrears ?? 0, 2, ',', '.') }}</td>
            @endforeach
            @if(count($accountsList) > 1)
                <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalPokok, 2, ',', '.') }}</td>
            @endif
        </tr>
        <tr>
            <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Tunggakan Bunga' : 'Tunggakan Margin' }}</td>
            @foreach($accountsList as $acc)
                <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->interest_arrears ?? 0, 2, ',', '.') }}</td>
            @endforeach
            @if(count($accountsList) > 1)
                <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalBunga, 2, ',', '.') }}</td>
            @endif
        </tr>
        @if($totalDenda > 0)
    <tr>
        <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Denda Tunggakan' : 'Denda / Ta\'widh' }}</td>
        @foreach($accountsList as $acc)
            <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->penalty_fee ?? 0, 2, ',', '.') }}</td>
        @endforeach
        @if(count($accountsList) > 1)
            <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalDenda, 2, ',', '.') }}</td>
        @endif
    </tr>
@endif
        <tr style="font-weight: bold; background-color: #e0e0e0;">
            <td style="padding: 6px 10px; text-align: left; white-space: nowrap;">Total Kewajiban</td>
            @foreach($accountsList as $acc)
                <td style="padding: 6px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format(($acc->principal_arrears ?? 0) + ($acc->interest_arrears ?? 0) + ($acc->penalty_fee ?? 0), 2, ',', '.') }}</td>
            @endforeach
            @if(count($accountsList) > 1)
                <td style="padding: 6px 10px; text-align: right; font-weight: bold; white-space: nowrap; background-color: #d0d0d0;">Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</td>
            @endif
        </tr>
    </tbody>
</table>
        <span style="font-size: 9pt; font-style: italic; color: #444;">Catatan : Kewajiban belum termasuk biaya penyelesaian {{ $termFasilitas }}</span>
    </li>
    <li style="text-align: justify; margin-bottom: 15px;">
        Kami harap Saudara dapat menyelesaikan {{ $termFasilitas }} Saudara paling lambat pada tanggal <strong>{{ \Carbon\Carbon::parse($due_date ?? now()->addDays(14))->translatedFormat('d F Y') }}</strong> dengan menyetorkan dana ke rekening Nomor <strong>913.92.21702.7</strong> a.n. Setoran Angsuran Krdt Konsumtip NKUK di Bank Jakarta terdekat.
    </li>
</ol>

<p style="text-align: justify; margin-bottom: 20px;">
    Untuk informasi lebih lanjut mengenai penyelesaian {{ $termFasilitas }}, Saudara dapat menghubungi petugas Bank Jakarta Special Asset Management Group dengan <strong>Sdr. {{ $namaRM }}</strong> melalui nomor telepon <strong>{{ $phoneRM }}</strong>.
</p>

<p style="margin-bottom: 35px;">Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

<div>
    <strong>Bank Jakarta</strong><br>
    <strong>Special Asset Management Group</strong><br><br><br><br><br>
    <strong><u>M Yogie Suryo Swasono</u></strong><br>
    <strong>Group Head</strong>
</div>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typeSpkp->id,
            'title'          => 'Template Surat Pemberitahuan Kewajiban',
            'content'        => $templateSpkpHtml,
        ]);


        // ====================================================================
        // 2. SURAT PERINGATAN I (SP-1)
        // ====================================================================
        $typeSp1 = LetterType::create([
            'code'          => 'SP1',
            'name'          => 'Surat Peringatan I (Pertama)',
            'format_number' => '{sequence}/SP-1/{month}/{year}',
        ]);

        $templateSp1Html = <<<'HTML'
<!-- KOP SURAT -->
<table width="100%" style="border-collapse: collapse; margin-bottom: 10px;">
    <tr>
        <td width="70%" style="vertical-align: top;"><br><br><br>
            <strong>Jakarta, {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</strong><br><br>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Tujuan Surat -->
                    <div style="margin-bottom: 10px;">
                        Kepada Yth.<br>
                        <strong>{{ $bankAccount->customer_name ?? '[Nama Nasabah]' }}</strong><br>
                        {{ $bankAccount->billing_address ?? '[Alamat Tagih]' }}<br>
                        {{ $bankAccount->billing_city ?? '[Kota]' }} {{ $bankAccount->collateral_province ? ', ' . $bankAccount->collateral_province : '' }}
                    </div>
                </tr>
                <tr>
                    <td width="70" style="vertical-align: top;">Nomor</td>
                    <td width="10" style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">{{ $letter_number ?? '[Nomor Surat]' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Perihal</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;"><strong>SURAT PERINGATAN I</strong></td>
                </tr>
            </table>
        </td>
        <td width="30%" style="vertical-align: top; text-align: right; font-size: 8pt; color: #333; line-height: 1.3;">
            <div style="margin-bottom: 6px;">
                @if(isset($isPdf) && $isPdf)
                    <img src="{{ public_path('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @else
                    <img src="{{ asset('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @endif
            </div>
            <div style="text-align: left;">
                <br><br>
                <strong>Kantor Pusat</strong><br>
                Jl. Suryopranoto No.8<br>
                Jakarta Pusat 10130<br>
                Telp. (021) 80655555<br>
                Fax. (021) 80655500
            </div>
        </td>
    </tr>
</table>

<p style="text-align: justify; margin-bottom: 10px;">
    Dengan hormat,<br>
    Sehubungan dengan perjanjian {{ $termFasilitas }} beserta turutannya antara Bank Jakarta dengan <strong>{{ $bankAccount->customer_name ?? '[Nama Nasabah]' }}</strong> selaku Nasabah. Bahwa berdasarkan catatan administrasi kami saudara telah mengalami keterlambatan pembayaran kewajiban
    @if(is_array($allAccountNumbers) && count($allAccountNumbers) > 1)
        (Rekening No: <strong>{{ implode(', ', $allAccountNumbers) }}</strong>)
    @endif
    , sehingga dengan ini kami menyampaikan Surat Peringatan atas keterlambatan pembayaran kewajiban Saudara, dengan rincian sebagai berikut:
</p>

<!-- Poin Rincian -->
<ol style="margin-left: -20px; margin-bottom: 5px; text-align: justify;">
    <li style="margin-bottom: 5px;">
    Bahwa berdasarkan Perjanjian, kewajiban pembayaran terakhir Saudara jatuh tempo pada: <strong>{{ $previous_due_date ? \Carbon\Carbon::parse($previous_due_date)->locale('id')->isoFormat('D MMMM YYYY') : '-' }}</strong>.
    </li>
    <li style="margin-bottom: 5px;">
        Bahwa sampai dengan tanggal surat ini, Saudara telah terlambat melakukan pembayaran selama: <strong>{{ $days_overdue ?? '-' }} hari kalender</strong> terhitung sejak tanggal jatuh tempo tersebut di atas.
    </li>
    <li style="margin-bottom: 5px;">
        Berdasarkan catatan administrasi kami total kewajiban Saudara yang masih terutang per tanggal {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }} sebesar <strong>Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</strong> dengan rincian sebagai berikut:
        
            <table border="1" style="width: auto; min-width: 50%; table-layout: auto; border-collapse: collapse; margin-top: 10px; margin-bottom: 6px; font-size: 8.5pt;">
    <thead>
        <tr style="background-color: #c0c0c0; font-weight: bold;">
            <th style="padding: 6px 10px; text-align: left; white-space: nowrap;">Rincian Kewajiban</th>
            @foreach($accountsList as $acc)
                <th style="padding: 6px 10px; text-align: center; white-space: nowrap;">Rekening<br><span style="font-family: monospace;">{{ $acc->account_number }}</span></th>
            @endforeach
            @if(count($accountsList) > 1)
                <th style="padding: 6px 10px; text-align: center; white-space: nowrap; background-color: #b0b0b0;">Total</th>
            @endif
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Pokok Kredit' : 'Pokok Pembiayaan' }}</td>
            @foreach($accountsList as $acc)
                <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->principal_arrears ?? 0, 2, ',', '.') }}</td>
            @endforeach
            @if(count($accountsList) > 1)
                <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalPokok, 2, ',', '.') }}</td>
            @endif
        </tr>
        <tr>
            <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Tunggakan Bunga' : 'Tunggakan Margin' }}</td>
            @foreach($accountsList as $acc)
                <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->interest_arrears ?? 0, 2, ',', '.') }}</td>
            @endforeach
            @if(count($accountsList) > 1)
                <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalBunga, 2, ',', '.') }}</td>
            @endif
        </tr>
        @if($totalDenda > 0)
    <tr>
        <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Denda Tunggakan' : 'Denda / Ta\'widh' }}</td>
        @foreach($accountsList as $acc)
            <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->penalty_fee ?? 0, 2, ',', '.') }}</td>
        @endforeach
        @if(count($accountsList) > 1)
            <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalDenda, 2, ',', '.') }}</td>
        @endif
    </tr>
@endif
        <tr style="font-weight: bold; background-color: #e0e0e0;">
            <td style="padding: 6px 10px; text-align: left; white-space: nowrap;">Total Kewajiban</td>
            @foreach($accountsList as $acc)
                <td style="padding: 6px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format(($acc->principal_arrears ?? 0) + ($acc->interest_arrears ?? 0) + ($acc->penalty_fee ?? 0), 2, ',', '.') }}</td>
            @endforeach
            @if(count($accountsList) > 1)
                <td style="padding: 6px 10px; text-align: right; font-weight: bold; white-space: nowrap; background-color: #d0d0d0;">Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</td>
            @endif
        </tr>
    </tbody>
</table>
    </li>
    
    <li style="margin-bottom: 5px;">
        Untuk menghindari potensi timbulnya biaya-biaya tambahan dalam hal nasabah tidak melakukan pembayaran yang berakibat dilaksanakannya lelang atas agunan, antara lain:<br>
        a. Biaya Administrasi Pelaksanaan Lelang<br>
        b. Biaya Pemasaran Agunan Lelang.
    </li>
    
    <li style="margin-bottom: 5px;">
        Kami meminta Saudara melakukan pembayaran seluruh kewajiban tersebut di atas paling lambat pada tanggal <strong>{{ \Carbon\Carbon::parse($due_date ?? now()->addDays(14))->translatedFormat('d F Y') }}</strong>, dengan menyetor ke rekening setoran sementara nomor <strong>913.92.21702.7</strong> an. Setoran Angsuran Krdt Konsumtip NKUK di Bank Jakarta terdekat.
    </li>
    
    <li style="margin-bottom: 5px;">
        Dalam hal sampai dengan batas waktu yang telah ditentukan Saudara tetap tidak memenuhi kewajiban pembayaran, maka Bank Jakarta akan mengambil langkah-langkah sesuai ketentuan dalam Perjanjian dan ketentuan peraturan perundang-undangan di sektor jasa keuangan.
    </li>
</ol>

<p style="text-align: justify; margin-bottom: 5px;">
    Untuk penjelasan lebih lanjut atas penyelesaian kewajiban {{ $termFasilitas }}, Saudara dapat menghubungi Special Asset Management Group dengan <strong>Sdr. {{ $namaRM }}</strong> melalui nomor <strong>{{ $phoneRM }}</strong>.
</p>

<p style="margin-bottom: 5px;">
    Demikian Surat Peringatan ini kami sampaikan. Atas perhatian dan kerja sama Saudara, diucapkan terima kasih.
</p>

<div>
    <strong>BANK JAKARTA</strong><br>
    <strong>Special Asset Management Group</strong>
    <br><br><br><br><br>
    <strong><u>M Yogie Suryo Swasono</u></strong><br>
    <strong>Group Head</strong>
</div>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typeSp1->id,
            'title'          => 'Template Surat Peringatan I',
            'content'        => $templateSp1Html,
        ]);


        // ====================================================================
        // 3. SURAT PERINGATAN II (SP-2)
        // ====================================================================
        $typeSp2 = LetterType::create([
            'code'          => 'SP2',
            'name'          => 'Surat Peringatan II (Kedua)',
            'format_number' => '{sequence}/SP-2/{month}/{year}',
        ]);

        $templateSp2Html = <<<'HTML'
<!-- KOP SURAT -->
<table width="100%" style="border-collapse: collapse; margin-bottom: 10px;">
    <tr>
        <td width="70%" style="vertical-align: top;"><br><br><br>
            <strong>Jakarta, {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</strong><br><br>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Tujuan Surat -->
                    <div style="margin-bottom: 10px;">
                        Kepada Yth.<br>
                        <strong>{{ $bankAccount->customer_name ?? '[Nama Nasabah]' }}</strong><br>
                        {{ $bankAccount->billing_address ?? '[Alamat Tagih]' }}<br>
                        {{ $bankAccount->billing_city ?? '[Kota]' }} {{ $bankAccount->collateral_province ? ', ' . $bankAccount->collateral_province : '' }}
                    </div>
                </tr>
                <tr>
                    <td width="70" style="vertical-align: top;">Nomor</td>
                    <td width="10" style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">{{ $letter_number ?? '[Nomor Surat]' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Perihal</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;"><strong>SURAT PERINGATAN II</strong></td>
                </tr>
            </table>
        </td>
        <td width="30%" style="vertical-align: top; text-align: right; font-size: 8pt; color: #333; line-height: 1.3;">
            <div style="margin-bottom: 6px;">
                @if(isset($isPdf) && $isPdf)
                    <img src="{{ public_path('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @else
                    <img src="{{ asset('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @endif
            </div>
            <div style="text-align: left;">
                <br><br>
                <strong>Kantor Pusat</strong><br>
                Jl. Suryopranoto No.8<br>
                Jakarta Pusat 10130<br>
                Telp. (021) 80655555<br>
                Fax. (021) 80655500
            </div>
        </td>
    </tr>
</table>

<p style="text-align: justify; margin-bottom: 10px;">
    Dengan hormat,<br>
    Menunjuk surat Bank Jakarta Nomor <strong>{{ $previous_letter_number ?? '-' }}</strong> tanggal <strong>{{ isset($previous_letter_date) && $previous_letter_date ? \Carbon\Carbon::parse($previous_letter_date)->translatedFormat('d F Y') : '-' }}</strong> perihal Surat Peringatan I, dengan ini kami sampaikan hal-hal sebagai berikut:
</p>

<!-- Poin Rincian SP-2 -->
<ol style="margin-left: -20px; margin-bottom: 5px; text-align: justify;">
    <li style="margin-bottom: 5px;">
        Bahwa Saudara telah diberikan kesempatan untuk melakukan pembayaran sesuai jangka waktu pada Surat Peringatan I namun tidak ada realisasi pembayaran.
    </li>
    
    <li style="margin-bottom: 5px;">
        Berdasarkan catatan administrasi kami total kewajiban Saudara yang masih terutang per tanggal {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }} sebesar <strong>Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</strong> dengan rincian sebagai berikut:
        
        <table border="1" style="width: auto; min-width: 50%; table-layout: auto; border-collapse: collapse; margin-top: 10px; margin-bottom: 6px; font-size: 8.5pt;">
            <thead>
                <tr style="background-color: #c0c0c0; font-weight: bold;">
                    <th style="padding: 6px 10px; text-align: left; white-space: nowrap;">Rincian Kewajiban</th>
                    @foreach($accountsList as $acc)
                        <th style="padding: 6px 10px; text-align: center; white-space: nowrap;">Rekening<br><span style="font-family: monospace;">{{ $acc->account_number }}</span></th>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <th style="padding: 6px 10px; text-align: center; white-space: nowrap; background-color: #b0b0b0;">Total</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Pokok Kredit' : 'Pokok Pembiayaan' }}</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->principal_arrears ?? 0, 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalPokok, 2, ',', '.') }}</td>
                    @endif
                </tr>
                <tr>
                    <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Tunggakan Bunga' : 'Tunggakan Margin' }}</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->interest_arrears ?? 0, 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalBunga, 2, ',', '.') }}</td>
                    @endif
                </tr>
                @if($totalDenda > 0)
                <tr>
                    <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Denda Tunggakan' : 'Denda / Ta\'widh' }}</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->penalty_fee ?? 0, 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalDenda, 2, ',', '.') }}</td>
                    @endif
                </tr>
                @endif
                <tr style="font-weight: bold; background-color: #e0e0e0;">
                    <td style="padding: 6px 10px; text-align: left; white-space: nowrap;">Total Kewajiban</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 6px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format(($acc->principal_arrears ?? 0) + ($acc->interest_arrears ?? 0) + ($acc->penalty_fee ?? 0), 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 6px 10px; text-align: right; font-weight: bold; white-space: nowrap; background-color: #d0d0d0;">Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</td>
                    @endif
                </tr>
            </tbody>
        </table>
    </li>

    <li style="margin-bottom: 5px;">
        Kami meminta Saudara melakukan pembayaran seluruh kewajiban tersebut di atas paling lambat pada tanggal <strong>{{ \Carbon\Carbon::parse($due_date ?? now()->addDays(14))->translatedFormat('d F Y') }}</strong>, dengan menyetor ke rekening setoran sementara nomor <strong>913.92.21702.7</strong> an. Setoran Angsuran Krdt Konsumtip NKUK di Bank Jakarta terdekat.
    </li>

    <li style="margin-bottom: 5px;">
        Sampai dengan batas waktu yang telah ditentukan, jika Saudara tidak memenuhi pembayaran kewajiban diatas, maka Bank Jakarta akan mengambil langkah-langkah sesuai ketentuan dalam Perjanjian dan ketentuan peraturan perundang-undangan sektor jasa keuangan.
    </li>
</ol>

<p style="text-align: justify; margin-bottom: 5px;">
    Untuk penjelasan lebih lanjut atas penyelesaian kewajiban {{ $termFasilitas }}, Saudara dapat menghubungi Special Asset Management Group dengan <strong>Sdr. {{ $namaRM }}</strong> melalui nomor <strong>{{ $phoneRM }}</strong>.
</p>

<p style="margin-bottom: 5px;">
    Demikian Surat Peringatan ini kami sampaikan. Atas perhatian dan kerja sama Saudara, diucapkan terima kasih.
</p>

<div>
    <strong>BANK JAKARTA</strong><br>
    <strong>Special Asset Management Group</strong>
    <br><br><br><br><br>
    <strong><u>M Yogie Suryo Swasono</u></strong><br>
    <strong>Group Head</strong>
</div>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typeSp2->id,
            'title'          => 'Template Surat Peringatan II',
            'content'        => $templateSp2Html,
        ]);


        // ====================================================================
        // 4. SURAT PERINGATAN III (SP-3)
        // ====================================================================
        $typeSp3 = LetterType::create([
            'code'          => 'SP3',
            'name'          => 'Surat Peringatan III (Ketiga)',
            'format_number' => '{sequence}/SP-3/{month}/{year}',
        ]);

        $templateSp3Html = <<<'HTML'
<!-- KOP SURAT -->
<table width="100%" style="border-collapse: collapse; margin-bottom: 10px;">
    <tr>
        <td width="70%" style="vertical-align: top;"><br><br><br>
            <strong>Jakarta, {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</strong><br><br>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Tujuan Surat -->
                    <div style="margin-bottom: 10px;">
                        Kepada Yth.<br>
                        <strong>{{ $bankAccount->customer_name ?? '[Nama Nasabah]' }}</strong><br>
                        {{ $bankAccount->billing_address ?? '[Alamat Tagih]' }}<br>
                        {{ $bankAccount->billing_city ?? '[Kota]' }} {{ $bankAccount->collateral_province ? ', ' . $bankAccount->collateral_province : '' }}
                    </div>
                </tr>
                <tr>
                    <td width="70" style="vertical-align: top;">Nomor</td>
                    <td width="10" style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">{{ $letter_number ?? '[Nomor Surat]' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Perihal</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;"><strong>SURAT PERINGATAN III</strong></td>
                </tr>
            </table>
        </td>
        <td width="30%" style="vertical-align: top; text-align: right; font-size: 8pt; color: #333; line-height: 1.3;">
            <div style="margin-bottom: 6px;">
                @if(isset($isPdf) && $isPdf)
                    <img src="{{ public_path('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @else
                    <img src="{{ asset('images/logo-bank-jakarta.webp') }}" alt="Bank Jakarta Logo" style="height: 45px; width: auto; display: inline-block;">
                @endif
            </div>
            <div style="text-align: left;">
                <br><br>
                <strong>Kantor Pusat</strong><br>
                Jl. Suryopranoto No.8<br>
                Jakarta Pusat 10130<br>
                Telp. (021) 80655555<br>
                Fax. (021) 80655500
            </div>
        </td>
    </tr>
</table>

<p style="text-align: justify; margin-bottom: 10px;">
    Dengan hormat,<br>
    Menunjuk surat Bank Jakarta Nomor <strong>{{ $previous_letter_number ?? '-' }}</strong> tanggal <strong>{{ isset($previous_letter_date) && $previous_letter_date ? \Carbon\Carbon::parse($previous_letter_date)->translatedFormat('d F Y') : '-' }}</strong> perihal Surat Peringatan II dengan ini kami menyampaikan Surat Peringatan atas keterlambatan pembayaran kewajiban Saudara, dengan ini kami sampaikan hal-hal sebagai berikut:
</p>

<!-- Poin Rincian SP-3 -->
<ol style="margin-left: -20px; margin-bottom: 5px; text-align: justify;">
    <li style="margin-bottom: 5px;">
        Saudara telah diberikan kesempatan pembayaran sampai dengan tanggal <strong>{{ isset($previous_due_date) && $previous_due_date ? \Carbon\Carbon::parse($previous_due_date)->translatedFormat('d F Y') : '-' }}</strong> namun tidak ada realisasi pembayaran oleh Saudara.
    </li>
    
    <li style="margin-bottom: 5px;">
        Berdasarkan catatan administrasi kami total kewajiban Saudara yang masih terutang per tanggal {{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }} sebesar <strong>Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</strong> dengan rincian sebagai berikut:
        
        <table border="1" style="width: auto; min-width: 50%; table-layout: auto; border-collapse: collapse; margin-top: 10px; margin-bottom: 6px; font-size: 8.5pt;">
            <thead>
                <tr style="background-color: #c0c0c0; font-weight: bold;">
                    <th style="padding: 6px 10px; text-align: left; white-space: nowrap;">Rincian Kewajiban</th>
                    @foreach($accountsList as $acc)
                        <th style="padding: 6px 10px; text-align: center; white-space: nowrap;">Rekening<br><span style="font-family: monospace;">{{ $acc->account_number }}</span></th>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <th style="padding: 6px 10px; text-align: center; white-space: nowrap; background-color: #b0b0b0;">Total</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Pokok Kredit' : 'Pokok Pembiayaan' }}</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->principal_arrears ?? 0, 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalPokok, 2, ',', '.') }}</td>
                    @endif
                </tr>
                <tr>
                    <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Tunggakan Bunga' : 'Tunggakan Margin' }}</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->interest_arrears ?? 0, 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalBunga, 2, ',', '.') }}</td>
                    @endif
                </tr>
                @if($totalDenda > 0)
                <tr>
                    <td style="padding: 5px 10px; text-align: left; white-space: nowrap;">{{ $isKonven ? 'Denda Tunggakan' : 'Denda / Ta\'widh' }}</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 5px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format($acc->penalty_fee ?? 0, 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 5px 10px; text-align: right; font-weight: bold; white-space: nowrap;">Rp {{ number_format($totalDenda, 2, ',', '.') }}</td>
                    @endif
                </tr>
                @endif
                <tr style="font-weight: bold; background-color: #e0e0e0;">
                    <td style="padding: 6px 10px; text-align: left; white-space: nowrap;">Total Kewajiban</td>
                    @foreach($accountsList as $acc)
                        <td style="padding: 6px 10px; text-align: right; white-space: nowrap;">Rp {{ number_format(($acc->principal_arrears ?? 0) + ($acc->interest_arrears ?? 0) + ($acc->penalty_fee ?? 0), 2, ',', '.') }}</td>
                    @endforeach
                    @if(count($accountsList) > 1)
                        <td style="padding: 6px 10px; text-align: right; font-weight: bold; white-space: nowrap; background-color: #d0d0d0;">Rp {{ number_format($totalAccumulated, 2, ',', '.') }}</td>
                    @endif
                </tr>
            </tbody>
        </table>
    </li>

    <li style="margin-bottom: 5px;">
        Kami harap Saudara melakukan pembayaran seluruh kewajiban tersebut di atas paling lambat pada tanggal <strong>{{ \Carbon\Carbon::parse($due_date ?? now()->addDays(14))->translatedFormat('d F Y') }}</strong>, sesuai jangka waktu yang diatur dalam Perjanjian dengan menyetor ke rekening setoran sementara nomor <strong>913.92.21702.7</strong> an. Setoran Angsuran Krdt Konsumtip NKUK di Bank Jakarta terdekat.<br>
    </li>
    <li style="margin-bottom: 5px;">
        Dalam hal sampai dengan batas waktu yang telah ditentukan Saudara tetap tidak memenuhi kewajiban pembayaran, maka Bank Jakarta akan mengambil langkah-langkah sesuai ketentuan dalam Perjanjian dan ketentuan peraturan perundang-undangan di sektor jasa keuangan, antara lain:<br>
        a. Melakukan pemasangan atribut Bank Jakarta pada agunan.<br>
        b. Melakukan penilaian ulang agunan.<br>
        c. Melakukan lelang eksekusi Hak Tanggungan.<br>
        d. Melakukan pemasaran agunan.<br>
        Adapun biaya-biaya tambahan yang akan timbul dalam hal debitur/nasabah tidak melakukan pembayaran yang berakibat dilaksanakannya lelang atas agunan akan dibebankan kepada debitur/nasabah, antara lain:<br>
        - Biaya Administrasi Pelaksanaan Lelang<br>
        - Biaya Pemasaran Agunan Lelang.
    </li>
</ol>

<p style="text-align: justify; margin-bottom: 5px;">
    Untuk penjelasan lebih lanjut atas penyelesaian kewajiban {{ $termFasilitas }}, Saudara dapat menghubungi Special Asset Management Group dengan <strong>Sdr. {{ $namaRM }}</strong> melalui nomor <strong>{{ $phoneRM }}</strong>.
</p>

<p style="margin-bottom: 5px;">
    Demikian Surat Peringatan ini kami sampaikan. Atas perhatian dan kerja sama Saudara, diucapkan terima kasih.
</p>

<div>
    <strong>Bank Jakarta</strong><br>
    <strong>Special Asset Management Group</strong>
    <br><br><br><br><br>
    <strong><u>M Yogie Suryo Swasono</u></strong><br>
    <strong>Group Head</strong>
</div>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typeSp3->id,
            'title'          => 'Template Surat Peringatan III',
            'content'        => $templateSp3Html,
        ]);


        // ====================================================================
        // 5. FORMULIR PEMINJAMAN BUNDEL KREDIT / PEMBIAYAAN (FPBK)
        // ====================================================================
        $typePeminjamanBundel = LetterType::create([
            'code'          => 'FPBK',
            'name'          => 'Formulir Peminjaman Bundel Kredit/Pembiayaan',
            'format_number' => '{sequence}/WOLSAM/{month}/{year}',
        ]);

        $templatePeminjamanHtml = <<<'HTML'
<!-- KOP / HEADER SURAT -->

<div style="text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 15px; text-transform: uppercase;">
    Formulir Peminjaman Bundel Kredit/Pembiayaan
</div>

<table width="100%" style="border-collapse: collapse; margin-bottom: 20px;">
    <tr>
        <td width="60%" style="vertical-align: top;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                <tr>
                    <td width="120" style="padding: 3px 0;">No. Form</td>
                    <td width="10" style="padding: 3px 0;">:</td>
                    <td style="padding: 3px 0;">{{ $letter_number ?? '[Nomor Surat]' }}</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0;">Unit Kerja</td>
                    <td style="padding: 3px 0;">:</td>
                    <td style="padding: 3px 0;">Departemen Penyelesaian Kredit/Pembiayaan Hapus Buku</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0;">Tanggal Permohonan</td>
                    <td style="padding: 3px 0;">:</td>
                    <td style="padding: 3px 0;">{{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<p style="text-align: justify; margin-bottom: 15px; font-size: 10pt;">
    Sehubungan dengan adanya kebutuhan dokumen bundel kredit/pembiayaan atas nama debitur kami, maka dengan ini kami mohon untuk dapat melakukan peminjaman bundel kredit/pembiayaan dengan rincian sebagai berikut:
</p>

<!-- TABEL RINCIAN BUNDEL -->
<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt;">
    <thead>
        <tr style="background-color: #e0e0e0; font-weight: bold; text-align: center;">
            <th style="padding: 6px 4px; width: 5%;">No</th>
            <th style="padding: 6px 6px; width: 18%;">No Rekening</th>
            <th style="padding: 6px 6px; width: 22%;">Nama Debitur</th>
            <th style="padding: 6px 6px; width: 12%;">Segmen</th>
            <th style="padding: 6px 6px; width: 13%;">Periode HB</th>
            <th style="padding: 6px 6px; width: 14%;">Cabang</th>
            <th style="padding: 6px 6px; width: 16%;">Tujuan Peminjaman</th>
        </tr>
    </thead>
    <tbody>
    @if(isset($bundlesList) && count($bundlesList) > 0)
    @foreach($bundlesList as $index => $bundle)
        <tr>
            <td style="padding: 5px 4px; text-align: center; vertical-align: top;">{{ $index + 1 }}</td>
            <td style="padding: 5px 6px; text-align: center; vertical-align: top; font-family: monospace;">{{ $bundle['account_number'] ?? '-' }}</td>
            <td style="padding: 5px 6px; text-align: left; vertical-align: top;">{{ $bundle['customer_name'] ?? '-' }}</td>
            <td style="padding: 5px 6px; text-align: center; vertical-align: top;">{{ $bundle['segment'] ?? '-' }}</td>
            <td style="padding: 5px 6px; text-align: center; vertical-align: top;">{{ $bundle['hb_period'] ?? '-' }}</td>
            <td style="padding: 5px 6px; text-align: center; vertical-align: top;">{{ $bundle['branch'] ?? '-' }}</td>
            <td style="padding: 5px 6px; text-align: center; vertical-align: top;">{{ $bundle['purpose'] ?? '-' }}</td>
        </tr>
    @endforeach
@endif
    </tbody>
</table>

<p style="text-align: justify; margin-bottom: 25px; font-size: 10pt;">
    Demikian permohonan kami, atas bantuannya kami mengucapkan terima kasih.
</p>

<!-- TANDA TANGAN & LEMBAR SIRKULASI -->
<table width="100%" style="border-collapse: collapse; font-size: 9pt;">
    <tr>
    <td width="40%" style="vertical-align: top;">
            <strong>Pemohon</strong><br><br><br><br><br>
            <strong><u>{{ $namaPemohon ?? auth()->user()->name ?? '[Nama Pemohon]' }}</u></strong>
        </td>
        <td width="60%" style="vertical-align: top;">
            <table border="1" style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                <thead>
                    <tr style="background-color: #f0f0f0; text-align: center;">
                        <th colspan="4" style="padding: 4px;">Lembar Sirkulasi</th>
                    </tr>
                    <tr style="background-color: #f0f0f0; text-align: center;">
                        <th style="padding: 4px;">Tanggal</th>
                        <th style="padding: 4px;">TTD Pemohon</th>
                        <th style="padding: 4px;">TTD DLP</th>
                        <th style="padding: 4px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 12px 4px; text-align: center;"></td>
                        <td style="padding: 12px 4px; text-align: center;"></td>
                        <td style="padding: 12px 4px; text-align: center;"></td>
                        <td style="padding: 12px 4px; text-align: center;">Peminjaman</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 4px; text-align: center;"></td>
                        <td style="padding: 12px 4px; text-align: center;"></td>
                        <td style="padding: 12px 4px; text-align: center;"></td>
                        <td style="padding: 12px 4px; text-align: center;">Pengembalian</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typePeminjamanBundel->id,
            'title'          => 'Template Formulir Peminjaman Bundel Kredit/Pembiayaan',
            'content'        => $templatePeminjamanHtml,
        ]);


        // ====================================================================
        // 6. MEMO PERMINTAAN PENYERAHAN JAMINAN (PPJ)
        // ====================================================================
        $typePenyerahanJaminan = LetterType::create([
            'code'          => 'PPJ',
            'name'          => 'Memo Permintaan Penyerahan Jaminan dan Dokumen Pelunasan',
            'format_number' => '{sequence}/MMO/WOLSAM/{month}/{year}',
        ]);

        $templatePenyerahanHtml = <<<'HTML'
<div style="text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 15px; text-transform: uppercase;">
    MEMORANDUM<br>
    <span style="font-size: 10pt; font-weight: normal;">No. {{ $letter_number ?? '[Nomor Surat]' }}</span>
</div>

<table width="100%" style="border-collapse: collapse; margin-bottom: 20px; font-size: 10pt;">
    <tr>
        <td width="15%" style="vertical-align: top; padding: 2px 0;">Kepada Yth.</td>
        <td width="2%" style="vertical-align: top; padding: 2px 0;">:</td>
        <td width="83%" style="vertical-align: top; padding: 2px 0;">Strategy & Enabler Division Cq. Performance & Support Departement Head</td>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 2px 0;">Dari</td>
        <td style="vertical-align: top; padding: 2px 0;">:</td>
        <td style="vertical-align: top; padding: 2px 0;">{{ $departmentName ?? 'SAM 1 Write-Off Loan Resolution Department' }}</td>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 2px 0;">Perihal</td>
        <td style="vertical-align: top; padding: 2px 0;">:</td>
        <td style="vertical-align: top; padding: 2px 0;"><b>Permintaan Penyerahan Jaminan dan Dokumen Pelunasan Debitur Hapus Buku</b><br>an. {{ $customer_name ?? '[Nama Debitur]' }}</td>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 2px 0;">Tanggal</td>
        <td style="vertical-align: top; padding: 2px 0;">:</td>
        <td style="vertical-align: top; padding: 2px 0;">{{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</td>
    </tr>
</table>

<hr style="border: none; border-top: 1px solid #000; margin-bottom: 20px;">

<p style="text-align: justify; margin-bottom: 15px; font-size: 10pt;">
    Dengan Hormat,<br><br>
    Berkenaan dengan pelunasan debitur an. <b>{{ $customer_name ?? '[Nama Debitur]' }}</b> sesuai dengan Surat Persetujuan Penyelesaian Kredit No. {{ $sppk_number ?? '[No SPPK]' }} tanggal {{ $sppk_date ? \Carbon\Carbon::parse($sppk_date)->translatedFormat('d F Y') : '[Tanggal SPPK]' }} dengan ini kami mohon untuk diserahkan jaminan kredit debitur sebagai berikut:
</p>

<!-- TABEL JAMINAN -->
<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt;">
    <thead>
        <tr style="background-color: #e0e0e0; font-weight: bold; text-align: center;">
            <th style="padding: 6px 4px; width: 8%;">No</th>
            <th style="padding: 6px 6px; width: 25%;">Nama Debitur</th>
            <th style="padding: 6px 6px; width: 25%;">Nomor Rekening</th>
            <th style="padding: 6px 6px; width: 42%;">Rincian Agunan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 6px 4px; text-align: center; vertical-align: top;">1</td>
            <td style="padding: 6px 6px; text-align: left; vertical-align: top;">{{ $customer_name ?? '-' }}</td>
            <td style="padding: 6px 6px; text-align: center; vertical-align: top; font-family: monospace;">{{ $account_number ?? '-' }}</td>
            <td style="padding: 6px 6px; text-align: left; vertical-align: top;">{{ $collateral_details ?? '-' }}</td>
        </tr>
    </tbody>
</table>

<p style="text-align: justify; margin-bottom: 25px; font-size: 10pt;">
    Sehubungan dengan hal tersebut, kami mohon untuk dokumen tersebut di atas dan dokumen terkait pelunasan lainnya dapat diserahkan kepada debitur sesuai ketentuan paling lambat tanggal <b>{{ $deadline_date ? \Carbon\Carbon::parse($deadline_date)->translatedFormat('d F Y') : '[Batas Tanggal]' }}</b>.<br><br>
    Demikian kami sampaikan, atas perhatian dan kerjasama yang baik kami ucapkan terima kasih.
</p>

<!-- TANDA TANGAN -->
<table width="100%" style="border-collapse: collapse; font-size: 9pt; margin-top: 30px;">
    <tr>
        <td width="50%" style="vertical-align: top;">
            Write-Off Loan & Auction Division<br>
            {{ $departmentName ?? 'SAM 1 Write-Off Loan Resolution Department' }}<br><br><br><br><br>
            <strong><u>{{ $departmentHeadName ?? '' }}</u></strong><br>
            Department Head
        </td>
    </tr>
</table>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typePenyerahanJaminan->id,
            'title'          => 'Template Permintaan Penyerahan Jaminan dan Dokumen Pelunasan',
            'content'        => $templatePenyerahanHtml,
        ]);


        // ====================================================================
        // 7. MEMO BIAYA / KONFIRMASI BIAYA-BIAYA DEBITUR (MMO)
        // ====================================================================
        $typeMemoBiaya = LetterType::create([
            'code'          => 'MMO',
            'name'          => 'Memo Biaya / Konfirmasi Biaya-Biaya Debitur',
            'format_number' => '{sequence}/MMO/WOLSAM/{month}/{year}',
        ]);

        $templateMemoBiayaHtml = <<<'HTML'
        
        <div style="text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 15px; text-transform: uppercase;">
            MEMORANDUM<br>
            <span style="font-size: 10pt; font-weight: normal;">No. {{ $letter_number ?? '[Nomor Surat]' }}</span>
        </div>

        <hr style="border: none; border-top: 1px solid #000; margin-bottom: 20px;">

        <table width="100%" style="border-collapse: collapse; margin-bottom: 15px; font-size: 10pt;">
            <tr>
                <td width="15%" style="vertical-align: top; padding: 2px 0;">Kepada Yth</td>
                <td width="2%" style="vertical-align: top; padding: 2px 0;">:</td>
                <td width="83%" style="vertical-align: top; padding: 2px 0;">{{ $recipient_division ?? 'Strategy & Enabler Division Cq. Performance & Support Departement Head' }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 2px 0;">Dari</td>
                <td style="vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;">{{ $sender_department ?? 'SAM 1 Write-Off Loan Resolution Department' }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 2px 0;">Perihal</td>
                <td style="vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;"><b>{{ $subject_text ?? 'Konfirmasi Biaya-Biaya Debitur' }}</b></td>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 2px 0;">Tanggal</td>
                <td style="vertical-align: top; padding: 2px 0;">:</td>
                <td style="vertical-align: top; padding: 2px 0;">{{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
        

        <p style="text-align: justify; margin-bottom: 10px; font-size: 10pt;">
            Dengan hormat,<br><br>
            Berkenaan dengan pelunasan debitur hapus buku, bersama ini kami mohon informasi hal-hal sebagai berikut:
        </p>
        
        <ol style="margin-top: 5px; margin-bottom: 15px; padding-left: 20px; font-size: 10pt;">
            @if(isset($inquiry_points) && is_array($inquiry_points))
                @foreach ($inquiry_points as $point)
                    <li style="margin-bottom: 4px;">{{ $point }}</li>
                @endforeach
            @endif
        </ol>
        
        <p style="font-size: 10pt; margin-bottom: 8px;">Rincian debitur sebagai berikut:</p>
        
        <table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt;">
            <thead>
                <tr style="background-color: #e0e0e0; font-weight: bold; text-align: center;">
                    <th style="padding: 6px 4px; width: 6%;">No</th>
                    <th style="padding: 6px 6px; width: 26%;">Nama Debitur</th>
                    <th style="padding: 6px 6px; width: 20%;">Nomor Rekening</th>
                    <th style="padding: 6px 6px; width: 16%;">Periode Hapus Buku</th>
                    <th style="padding: 6px 6px; width: 12%;">Segmen</th>
                    <th style="padding: 6px 6px; width: 20%;">Cabang</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($debtorsList) && count($debtorsList) > 0)
                    @foreach($debtorsList as $index => $debtor)
                        <tr>
                            <td style="padding: 6px 4px; text-align: center; vertical-align: top;">{{ $index + 1 }}</td>
                            <td style="padding: 6px 6px; text-align: left; vertical-align: top;">{{ $debtor['customer_name'] ?? '-' }}</td>
                            <td style="padding: 6px 6px; text-align: center; vertical-align: top; font-family: monospace;">{{ $debtor['account_number'] ?? '-' }}</td>
                            <td style="padding: 6px 6px; text-align: center; vertical-align: top;">{{ $debtor['hb_period'] ?? '-' }}</td>
                            <td style="padding: 6px 6px; text-align: center; vertical-align: top;">{{ $debtor['segment'] ?? 'KPR' }}</td>
                            <td style="padding: 6px 6px; text-align: left; vertical-align: top;">{{ $debtor['branch'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        
        <p style="text-align: justify; margin-bottom: 20px; font-size: 10pt;">
            Mengingat pentingnya informasi tersebut, kami harapkan dapat kami terima paling lambat tanggal <strong>{{ isset($due_date) ? \Carbon\Carbon::parse($due_date)->translatedFormat('d F Y') : '[Batas Tanggal]' }}</strong>.<br><br>
            Demikian kami sampaikan, atas perhatian dan kerjasama yang baik kami ucapkan terima kasih.
        </p>
        
        <!-- TANDA TANGAN RATA KIRI -->
        <table width="100%" style="border-collapse: collapse; font-size: 9pt; margin-top: 30px;">
            <tr>
                <td width="50%" style="vertical-align: top;">
                    Write-Off Loan & Auction Division<br>
                    {{ $sender_department ?? 'SAM 1 Write-Off Loan Resolution Department' }}<br><br><br><br><br>
                    <strong><u>{{ $departmentHeadName ?? 'Yenni Nainggolan' }}</u></strong><br>
                    Department Head
                </td>
                <td width="50%"></td>
            </tr>
        </table>
        HTML;

        LetterTemplate::create([
            'letter_type_id' => $typeMemoBiaya->id,
            'title'          => 'Template Memo Biaya / Konfirmasi Biaya-Biaya Debitur',
            'content'        => $templateMemoBiayaHtml,
        ]);


        // ====================================================================
        // 8. MEMO PEMINDAHBUKUAN SETORAN KREDIT (PINBUK)
        // ====================================================================
        $typePinbuk = LetterType::create([
            'code'          => 'PINBUK',
            'name'          => 'Memo Pemindahbukuan Setoran Kredit',
            'format_number' => '{sequence}/MMO/WOLSAM/{month}/{year}',
        ]);

        $templatePinbukHtml = <<<'HTML'

<div style="text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 5px; text-transform: uppercase;">
    MEMORANDUM<br>
    <span style="font-size: 10pt; font-weight: normal;">No. {{ $letter_number ?? '[Nomor Surat]' }}</span>
</div>

<hr style="border: none; border-top: 1px solid #000; margin-bottom: 5px;">

<table width="100%" style="border-collapse: collapse; margin-bottom: 5px; font-size: 9pt;">
    <tr>
        <td width="15%" style="vertical-align: top; ">Kepada Yth</td>
        <td width="2%" style="vertical-align: top; ">:</td>
        <td width="83%" style="vertical-align: top; ">Strategy & Enabler Division Cq. Performance & Support Departement Head</td>
    </tr>
    <tr>
        <td style="vertical-align: top; ">Dari</td>
        <td style="vertical-align: top; ">:</td>
        <td style="vertical-align: top; ">{{ $sender_department ?? 'SAM 1 Write-Off Loan Resolution Department' }}</td>
    </tr>
    <tr>
        <td style="vertical-align: top; ">Perihal</td>
        <td style="vertical-align: top; ">:</td>
        <td style="vertical-align: top; "><b>Pemindah bukuan Setoran Kredit an. {{ $customer_name ?? '[Nama Debitur]' }}</b></td>
    </tr>
    <tr>
        <td style="vertical-align: top; ">Tanggal</td>
        <td style="vertical-align: top; ">:</td>
        <td style="vertical-align: top; ">{{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</td>
    </tr>
</table>

<p style="text-align: justify; margin-bottom: 5px; font-size: 9pt;">
    Dengan hormat,<br><br>
    Berkenaan dengan setoran debitur, kami harap Saudara dapat melakukan koordinasi dengan Grup Administrasi Kredit dan Pembiayaan untuk melakukan pemindah bukuan dengan rincian sebagai berikut:
</p>

<!-- TABEL VERIFIKASI SETORAN HAPUS BUKU -->
@php
    $bv = !empty($pinbuk_table) ? json_decode($pinbuk_table, true) : null;
    $bvCols = $bv['columns'] ?? [];
    $bvRows = $bv['body'] ?? [];
    $bvHeader = $bv['header']['raw_lines'] ?? [];
@endphp
 
<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 8pt; background-color: #fafafa;">
    <tr>
        <td style="padding: 8px;">
            @if(!empty($bvRows))
                {{-- HEADER: tetap monospace apa adanya --}}
                <pre style="font-family: 'Consolas', 'Courier New', monospace; margin: 0 0 4px 0; font-size: 8pt; white-space: pre; line-height: 1.25; color: #111;">{{ implode("\n", $bvHeader) }}</pre>
 
                {{-- BODY: tabel sungguhan, baris sorotan berlatar kuning --}}
                <table style="width: 100%; border-collapse: collapse; font-family: 'Consolas', 'Courier New', monospace; font-size: 8pt;">
                    <thead>
                        <tr>
                            @foreach($bvCols as $col)
                                <th style="border-bottom: 1px solid #000; padding: 1px 6px; text-align: center;">{{ $col['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bvRows as $row)
                            {{-- background di <td> (bukan <tr>) agar aman untuk render PDF --}}
                            @php $bg = !empty($row['highlight']) ? 'background-color: #ffff00;' : ''; @endphp
                            <tr>
                                @foreach($bvCols as $col)
                                    <td style="padding: 1px 6px; text-align: {{ $col['align'] }}; white-space: nowrap; {{ $bg }}">{{ $row[$col['key']] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <pre style="font-family: 'Consolas', 'Courier New', monospace; margin: 0; white-space: pre-wrap; line-height: 1.25; color: #111;">{{ $pinbuk_description ?? '-' }}</pre>
            @endif
        </td>
    </tr>
</table>

<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 8pt;">
    <tr>
        <td colspan="2" style="padding: 3px 5px; background-color: #f0f0f0;text-align: center;"><b>VERIFIKASI SETORAN HAPUS BUKU</b></td>
        <td colspan="2" style="padding: 3px 5px; background-color: #f0f0f0; text-align: center;"><b>STATUS LELANG</b></td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; width: 30%; background-color: #f5f5f5; font-weight: bold;">Nama Debitur</td>
        <td style="padding: 3px 5px; width: 35%;">{{ $customer_name ?? '-' }}</td>
        <td colspan="2" rowspan="2" style="padding: 6px 10px; vertical-align: middle; text-align: center;">
            {{ $auction_status ?? '-' }}
        </td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Nama Penyetor</td>
        <td style="padding: 3px 5px;">{{ $depositor_name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">No. Rekening Pendebetan</td>
        <td style="padding: 3px 5px; font-family: monospace;">{{ $debit_account ?? '-' }}</td>
        <td colspan="2" style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold; text-align: center;">NAMA VERIFIKATOR</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Nama Rekening</td>
        <td style="padding: 3px 5px;">{{ $debit_account_name ?? '-' }}</td>
        <td rowspan="8" style="padding: 3px 5px; width: 17.5%; vertical-align: bottom; text-align: center; height: 90px;">
            <!-- Kolom Kiri Tanda Tangan: Nama User yang Login / Verifikator -->
            <br><br>
            <b>{{ $verifier_name ?? Auth::user()->name }}</b>
        </td>
        <td rowspan ="8" style="padding: 3px 5px; width: 17.5%; vertical-align: bottom; text-align: center; height: 90px;">
            <!-- Kolom Kanan Tanda Tangan: Nama Pimpinan SAM Sesuai Pilihan -->
            <br><br>
            <b>{{ $departmentHeadName ?? '-' }}</b>
        </td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">No. Rekening Pinjaman</td>
        <td style="padding: 3px 5px; font-family: monospace;">{{ $loan_account ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Cabang/Capem</td>
        <td style="padding: 3px 5px;" >{{ $branch ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Periode HB</td>
        <td style="padding: 3px 5px;" >{{ $hb_period ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Jumlah Setoran</td>
        <td style="padding: 3px 5px; font-weight: bold;" colspan="3">Rp {{ isset($deposit_amount) ? number_format($deposit_amount, 0, ',', '.') : '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Tanggal Setoran</td>
        <td style="padding: 3px 5px;" >{{ isset($deposit_date) ? \Carbon\Carbon::parse($deposit_date)->translatedFormat('d F Y') : '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Tujuan Pendebetan</td>
        <td style="padding: 3px 5px;" >{{ $debit_purpose ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding: 3px 5px; background-color: #f5f5f5; font-weight: bold;">Mohon Dilakukan Update pada SLIK Sesuai Baki Debet Terakhir</td>
        <td style="padding: 3px 5px;" >{{ $slik_update_note ?? '-' }}</td>
    </tr>
</table>

<p style="text-align: justify; margin-bottom: 5px; font-size: 9pt;">
    Demikian untuk dilaksanakan, atas perhatian dan kerjasama yang baik kami ucapkan terima kasih.
</p>

<!-- TANDA TANGAN -->
<table width="100%" style="border-collapse: collapse; font-size: 9pt; margin-top: 5px;">
    <tr>
        <td width="50%" style="vertical-align: top;">
            Write-Off Loan & Auction Division<br>
            {{ $sender_department ?? 'SAM 1 Write-Off Loan Resolution Department' }}<br><br><br><br><br>
            <strong><u>{{ $departmentHeadName ?? 'Yenni Nainggolan' }}</u></strong><br>
            Department Head
        </td>
        <td width="50%"></td>
    </tr>
</table>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typePinbuk->id,
            'title'          => 'Template Memo Pemindahbukuan Setoran Kredit',
            'content'        => $templatePinbukHtml,
        ]);

        // ====================================================================
        // 9. MEMO BUKA BLOKIR, PEMINDAHBUKUAN, DAN BLOKIR REKENING TABUNGAN (PINBUK-BB)
        // ====================================================================
        $typePinbukBb = LetterType::create([
            'code'          => 'PINBUK-BB',
            'name'          => 'Memo Buka Blokir, Pemindahbukuan, dan Blokir Rekening Tabungan',
            'format_number' => '{sequence}/MMO/WOLSAM/{month}/{year}',
        ]);

        $templatePinbukBbHtml = <<<'HTML'
<div style="text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 5px; text-transform: uppercase;">
    MEMORANDUM<br>
    <span style="font-size: 10pt; font-weight: normal;">No. {{ $letter_number ?? '[Nomor Surat]' }}</span>
</div>

<hr style="border: none; border-top: 1px solid #000; margin-bottom: 5px;">

<table width="100%" style="border-collapse: collapse; margin-bottom: 5px; font-size: 9pt;">
    <tr>
        <td width="15%" style="vertical-align: top;">Kepada Yth</td>
        <td width="2%" style="vertical-align: top;">:</td>
        <td width="83%" style="vertical-align: top;">Strategy & Enabler Division Cq. Performance & Support Departement Head</td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Dari</td>
        <td style="vertical-align: top;">:</td>
        <td style="vertical-align: top;">{{ $sender_department ?? 'SAM 1 Write-Off Loan Resolution Department' }}</td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Perihal</td>
        <td style="vertical-align: top;">:</td>
        <td style="vertical-align: top;"><b>Buka Blokir, Pemindahbukuan, dan Blokir Rekening Tabungan Setoran Debitur an. {{ $customer_name ?? '[Nama Debitur]' }}</b></td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Tanggal</td>
        <td style="vertical-align: top;">:</td>
        <td style="vertical-align: top;">{{ \Carbon\Carbon::parse($letter_date ?? now())->translatedFormat('d F Y') }}</td>
    </tr>
</table>

<p style="text-align: justify; margin-bottom: 8px; font-size: 9pt;">
    Dengan hormat,<br><br>
    Sehubungan dengan komitmen dari debitur an. <b>{{ $customer_name ?? '[Nama Debitur]' }}</b> untuk dapat menyelesaikan tunggakan kredit dengan nomor rekening pinjaman <b>{{ $loan_account ?? '-' }}</b>, kami mohon bantuan Saudara untuk dapat melakukan hal-hal sebagai berikut:
</p>

<ol style="margin-top: 0px; margin-bottom: 10px; padding-left: 20px; font-size: 9pt; text-align: justify;">
    <li style="margin-bottom: 6px;">
        <b>Buka blokir dan Pemindahbukuan</b> dengan rincian sebagai berikut:
    </li>
</ol>

{{-- TABEL 1: BUKA BLOKIR & PEMINDAHBUKUAN --}}
@php
    $tb1 = !empty($table_buka_blokir) ? json_decode($table_buka_blokir, true) : null;
    $tb1Cols = $tb1['columns'] ?? [];
    $tb1Rows = $tb1['body'] ?? [];
@endphp

<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8pt;">
    <thead>
        <tr style="background-color: #e0e0e0; font-weight: bold; text-align: center;">
            <th style="padding: 5px 4px;">No</th>
            <th style="padding: 5px 4px;">Nama Debitur</th>
            <th style="padding: 5px 4px;">Rekening Pinjaman</th>
            <th style="padding: 5px 4px;">Tabungan Sumber</th>
            <th style="padding: 5px 4px;">Nominal Buka Blokir</th>
            <th style="padding: 5px 4px;">Pokok</th>
            <th style="padding: 5px 4px;">Bunga / Margin</th>
            <th style="padding: 5px 4px;">Denda</th>
            <th style="padding: 5px 4px;">Total</th>
            <th style="padding: 5px 4px;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($tb1Rows) && count($tb1Rows) > 0)
            @foreach($tb1Rows as $index => $row)
                <tr>
                    <td style="padding: 4px; text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                    <td style="padding: 4px; text-align: left; vertical-align: middle;">{{ $row['customer_name'] ?? '-' }}</td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-family: monospace;">{{ $row['loan_account'] ?? '-' }}</td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-family: monospace;">{{ $row['source_saving'] ?? '-' }}</td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle;">Rp {{ number_format($row['unblock_amount'] ?? 0, 0, ',', '.') }}</td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle;">Rp {{ number_format($row['principal'] ?? 0, 0, ',', '.') }}</td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle;">Rp {{ number_format($row['interest'] ?? 0, 0, ',', '.') }}</td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle;">Rp {{ number_format($row['penalty'] ?? 0, 0, ',', '.') }}</td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold;">Rp {{ number_format($row['total'] ?? 0, 0, ',', '.') }}</td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle;">{{ $row['description'] ?? '-' }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10" style="padding: 6px; text-align: center;">Tidak ada data rincian.</td>
            </tr>
        @endif
    </tbody>
</table>

<ol start="2" style="margin-top: 0px; margin-bottom: 10px; padding-left: 20px; font-size: 9pt; text-align: justify;">
    <li style="margin-bottom: 6px;">
        <b>Blokir Rekening Tabungan Debitur</b> dengan rincian sebagai berikut:
    </li>
</ol>

{{-- TABEL 2: BLOKIR REKENING TABUNGAN --}}
@php
    $tb2 = !empty($table_blokir_rekening) ? json_decode($table_blokir_rekening, true) : null;
    $tb2Rows = $tb2['body'] ?? [];
@endphp

<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8pt;">
    <thead>
        <tr style="background-color: #e0e0e0; font-weight: bold; text-align: center;">
            <th style="padding: 5px 4px;">No</th>
            <th style="padding: 5px 4px;">Nama Debitur</th>
            <th style="padding: 5px 4px;">Rekening Pinjaman</th>
            <th style="padding: 5px 4px;">Cabang / Capem</th>
            <th style="padding: 5px 4px;">Rekening Tabungan</th>
            <th style="padding: 5px 4px;">Nominal Blokir</th>
            <th style="padding: 5px 4px;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($tb2Rows) && count($tb2Rows) > 0)
            @foreach($tb2Rows as $index => $row)
                <tr>
                    <td style="padding: 4px; text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                    <td style="padding: 4px; text-align: left; vertical-align: middle;">{{ $row['customer_name'] ?? '-' }}</td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-family: monospace;">{{ $row['loan_account'] ?? '-' }}</td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle;">{{ $row['branch'] ?? '-' }}</td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-family: monospace;">{{ $row['saving_account'] ?? '-' }}</td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle;">Rp {{ number_format($row['block_amount'] ?? 0, 0, ',', '.') }}</td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle;">{{ $row['description'] ?? '-' }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="7" style="padding: 6px; text-align: center;">Tidak ada data rincian.</td>
            </tr>
        @endif
    </tbody>
</table>

<p style="text-align: justify; margin-bottom: 10px; font-size: 9pt;">
    3. Mohon untuk dilakukan penyesuaian data SLIK debitur pada pelaporan sesuai baki debet yang seharusnya.<br>
    Demikian untuk dilaksanakan, atas perhatian dan kerjasama yang baik kami ucapkan terima kasih.
</p>

<!-- TANDA TANGAN -->
<table width="100%" style="border-collapse: collapse; font-size: 9pt; margin-top: 15px;">
    <tr>
        <td width="50%" style="vertical-align: top;">
            Write-Off Loan & Auction Division<br>
            {{ $sender_department ?? 'SAM 1 Write-Off Loan Resolution Department' }}<br><br><br><br><br>
            <strong><u>{{ $departmentHeadName ?? 'Yenni Nainggolan' }}</u></strong><br>
            Department Head
        </td>
        <td width="50%"></td>
    </tr>
</table>
HTML;

        LetterTemplate::create([
            'letter_type_id' => $typePinbukBb->id,
            'title'          => 'Template Memo Buka Blokir, Pemindahbukuan, dan Blokir Rekening Tabungan',
            'content'        => $templatePinbukBbHtml,
        ]);
    }
    
}