use App\Http\Controllers\BankAccountController;

Route::get('/import-status/{batchId}', [BankAccountController::class, 'importStatus']);