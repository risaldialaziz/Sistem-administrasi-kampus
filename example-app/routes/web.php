<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:super_admin,admin_kampus,admin_akademik')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
        Route::resource('fakultas', \App\Http\Controllers\FacultyController::class)->parameters(['fakultas' => 'fakultas'])->except(['show']);
        Route::resource('program-studi', \App\Http\Controllers\StudyProgramController::class)->parameters(['program-studi' => 'programStudi'])->except(['show']);
        Route::resource('mahasiswa', \App\Http\Controllers\StudentController::class)->parameters(['mahasiswa' => 'mahasiswa']);
        Route::resource('dosen', \App\Http\Controllers\LecturerController::class)->parameters(['dosen' => 'dosen'])->except(['show']);
        Route::resource('mata-kuliah', \App\Http\Controllers\CourseController::class)->parameters(['mata-kuliah' => 'mataKuliah'])->except(['show']);
        Route::resource('jadwal', \App\Http\Controllers\ScheduleController::class)->parameters(['jadwal' => 'jadwal'])->except(['show']);
        Route::resource('tahun-akademik', \App\Http\Controllers\AcademicYearController::class)->parameters(['tahun-akademik' => 'tahunAkademik'])->except(['show']);
    });

    Route::middleware('role:super_admin,admin_akademik,dosen,mahasiswa')->group(function () {
        Route::resource('krs', \App\Http\Controllers\KrsController::class)->except(['edit', 'update', 'destroy']);
        Route::patch('krs/{krs}/status', [\App\Http\Controllers\KrsController::class, 'status'])->name('krs.status');
        Route::resource('nilai', \App\Http\Controllers\GradeController::class)->except(['show', 'create', 'edit']);
        Route::get('khs', [\App\Http\Controllers\GradeController::class, 'khs'])->name('khs.index');
    });

    Route::middleware('role:super_admin,admin_kampus,admin_keuangan')->group(function () {
        Route::resource('pembayaran', \App\Http\Controllers\PaymentController::class)->parameters(['pembayaran' => 'pembayaran'])->only(['index', 'store', 'destroy']);
        Route::patch('pembayaran/{pembayaran}/status', [\App\Http\Controllers\PaymentController::class, 'status'])->name('pembayaran.status');
        Route::get('laporan/keuangan', [\App\Http\Controllers\ReportController::class, 'finance'])->name('reports.finance');
    });

    Route::get('surat', [\App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::post('surat', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::patch('surat/{document}/status', [\App\Http\Controllers\DocumentController::class, 'status'])->name('documents.status');
    Route::delete('surat/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::resource('pengumuman', \App\Http\Controllers\AnnouncementController::class)->parameters(['pengumuman' => 'pengumuman'])->except(['show']);
    Route::get('notifikasi', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifikasi/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::get('laporan/akademik', [\App\Http\Controllers\ReportController::class, 'academic'])->name('reports.academic');
    Route::get('laporan/akademik/export', [\App\Http\Controllers\ReportController::class, 'exportAcademic'])->name('reports.academic.export');
    Route::get('audit-log', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit.index');
    Route::get('pengaturan', [\App\Http\Controllers\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('pengaturan', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
});
