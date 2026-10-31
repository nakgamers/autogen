<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Data Bank Account / Rekening Nasabah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <!-- Petunjuk Format Header Excel -->
                <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 text-sm">
                    <p class="font-bold mb-2">Petunjuk & Ketentuan Format File Excel / CSV:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Pastikan baris pertama (header) menggunakan format kolom persis seperti berikut:</li>
                    </ul>
                    <div class="mt-2 p-3 bg-white rounded border border-blue-200 font-mono text-xs overflow-x-auto text-gray-800 whitespace-nowrap">
                        NO REKENING | NAMA NASABAH | NOA SAAT INI | SEGMEN | RM PENGELOLA | CABANG | KC CABANG | BANK | TUNGGAKAN POKOK | TUNGGAKAN BUNGA | DENDA | ALAMAT TAGIH | KOTA ALAMAT TAGIH | AGUNAN FA | RINCIAN AGUNAN | KELURAHAN AGUNAN | KECAMATAN AGUNAN | KOTA AGUNAN | PROVINSI AGUNAN | KETERANGAN
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs mt-3">
                        <li>Sistem menggunakan <strong>NO REKENING</strong> sebagai kunci unik. Jika nomor sudah ada, data akan <strong>diperbarui (update)</strong>. Jika belum ada, akan <strong>ditambahkan (insert)</strong>.</li>
                        <li>Proses ini mendukung hingga <strong>50.000+ baris data</strong> yang akan dieksekusi di latar belakang.</li>
                    </ul>
                </div>

                <!-- Bagian Form Upload -->
                <div id="upload_section">
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel / CSV (.xlsx, .xls, .csv)</label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls, .csv" required 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md p-2">
                            <span id="file_error" class="text-red-500 text-xs mt-1 hidden"></span>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="submit" id="btn_submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium text-sm transition flex items-center">
                                <svg id="spinner" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Mulai Upload & Proses</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bagian Progress Bar (Disembunyikan secara default) -->
                <div id="progress_section" class="hidden mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Status Pemrosesan Data</h3>
                    
                    <!-- Progress Bar Element -->
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                        <div id="progress_bar" class="bg-indigo-600 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                    <p id="progress_text" class="text-sm text-gray-600 font-medium mb-6">Menginisiasi proses... 0%</p>

                    <!-- Statistik Card -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <p class="text-xs text-blue-600 font-bold uppercase">Total Baris</p>
                            <p id="stat_total" class="text-2xl font-bold text-blue-800 mt-1">0</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                            <p class="text-xs text-green-600 font-bold uppercase">Berhasil (Insert/Update)</p>
                            <p id="stat_success" class="text-2xl font-bold text-green-800 mt-1">0</p>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <p class="text-xs text-red-600 font-bold uppercase">Dilewati / Error</p>
                            <p id="stat_skipped" class="text-2xl font-bold text-red-800 mt-1">0</p>
                        </div>
                    </div>

                    <!-- Log Error / Data Dilewati -->
                    <div id="error_log_container" class="hidden">
                        <p class="font-bold text-red-600 text-sm mb-2">Daftar Data yang Dilewati (Error Validasi):</p>
                        <div class="bg-red-50 border border-red-200 rounded-md p-3 max-h-40 overflow-y-auto">
                            <ul id="error_list" class="list-disc list-inside text-xs text-red-700 space-y-1">
                                <!-- List error akan dimuat di sini -->
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" id="btn_reset" class="hidden px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 font-medium text-sm transition">
                            Upload File Baru
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let pollInterval;

            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                
                let formData = new FormData(this);
                let $btn =$('#btn_submit');
                let $spinner =$('#spinner');
                let $errorSpan =$('#file_error');
                
                // Reset UI
                $errorSpan.addClass('hidden').text('');
                $btn.prop('disabled', true).addClass('opacity-70');$spinner.removeClass('hidden');

                $.ajax({
                    url: "{{ route('bank-accounts.import-ajax') }}", // Route baru untuk handle AJAX
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Sembunyikan form, tampilkan progress
                        $('#upload_section').slideUp();
                        $('#progress_section').slideDown();
                        
                        // Mulai polling untuk cek progress berdasarkan ID Batch/Cache yang di-return
                        startPolling(response.batch_id);
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).removeClass('opacity-70');$spinner.addClass('hidden');
                        
                        let errorMsg = "Terjadi kesalahan sistem.";
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $errorSpan.removeClass('hidden').text(errorMsg);
                    }
                });
            });

            function startPolling(batchId) {
                pollInterval = setInterval(function() {
                    $.ajax({
                        url: "/api/import-status/" + batchId,
                        type: "GET",
                        success: function(res) {
                            // Update UI Progress Bar
                            let percent = res.progress;
                            $('#progress_bar').css('width', percent + '%');
                            $('#progress_text').text(`Memproses... ${percent}% (${res.processed} dari ${res.total} data)`);
                            
                            // Update Stats
                            $('#stat_total').text(res.total);
                            $('#stat_success').text(res.success);
                            $('#stat_skipped').text(res.failed);

                            // Jika selesai
                            if (res.is_finished) {
                                clearInterval(pollInterval);
                                $('#progress_bar').removeClass('bg-indigo-600').addClass('bg-green-500');
                                $('#progress_text').text('Pemrosesan Selesai!').addClass('text-green-600');
                                $('#btn_reset').removeClass('hidden');

                                // Jika ada error/data yang dilewati
                                if(res.errors && res.errors.length > 0) {
                                    $('#error_log_container').removeClass('hidden');
                                    let errorHtml = '';
                                    res.errors.forEach(err => {
                                        errorHtml += `<li>Baris ${err.row}: ${err.message}</li>`;
                                    });
                                    $('#error_list').html(errorHtml);
                                }
                            }
                        }
                    });
                }, 2000); // Polling setiap 2 detik
            }

            // Tombol Reset untuk upload ulang
            $('#btn_reset').on('click', function() {
                $('#progress_section').slideUp();
                $('#upload_section').slideDown();
                $('#importForm')[0].reset();
                $('#btn_submit').prop('disabled', false).removeClass('opacity-70');
                $('#spinner').addClass('hidden');
                
                // Reset statistik
                $('#progress_bar').css('width', '0%').removeClass('bg-green-500').addClass('bg-indigo-600');
                $('#progress_text').text('Menginisiasi proses... 0%').removeClass('text-green-600');
                $('#stat_total, #stat_success, #stat_skipped').text('0');
                $('#error_log_container').addClass('hidden');
                $('#error_list').empty();
            });
        });
    </script>
</x-app-layout>