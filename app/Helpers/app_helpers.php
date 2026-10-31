<?php

if (!function_exists('formatRupiah')) {
    /**
     * Format angka ke Rupiah. 
     * Otomatis menghilangkan ,00 jika angka bulat, dan menampilkan desimal jika ada pecahan.
     * 
     * @param float|int $angka
     * @return string
     */
    function formatRupiah($angka)
    {
        $angka = (float) ($angka ?? 0);
        
        // Cek apakah ada nilai di belakang koma
        $decimal = $angka - floor($angka);
        
        if ($decimal > 0) {
            // Jika ada pecahan (misal: 50.5), format dengan 2 digit di belakang koma
            return number_format($angka, 2, ',', '.');
        } else {
            // Jika bulat (misal: 50), format tanpa koma nol
            return number_format($angka, 0, ',', '.');
        }
    }
}