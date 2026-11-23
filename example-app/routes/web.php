<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudyProgramController;
use App\Http\Controllers\UserController;
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
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware(['auth', 'active', 'audit.activity'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:super_admin,admin_kampus,admin_akademik')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('fakultas', FacultyController::class)->parameters(['fakultas' => 'fakultas'])->except(['show']);
        Route::resource('program-studi', StudyProgramController::class)->parameters(['program-studi' => 'programStudi'])->except(['show']);
        Route::resource('mahasiswa', StudentController::class)->parameters(['mahasiswa' => 'mahasiswa']);
        Route::resource('dosen', LecturerController::class)->parameters(['dosen' => 'dosen'])->except(['show']);
        Route::resource('mata-kuliah', CourseController::class)->parameters(['mata-kuliah' => 'mataKuliah'])->except(['show']);
        Route::resource('jadwal', ScheduleController::class)->parameters(['jadwal' => 'jadwal'])->except(['show']);
        Route::resource('tahun-akademik', AcademicYearController::class)->parameters(['tahun-akademik' => 'tahunAkademik'])->except(['show']);
    });

    Route::middleware('role:super_admin,admin_akademik,dosen,mahasiswa')->group(function () {
        Route::get('krs', [KrsController::class, 'index'])->name('krs.index');
        Route::get('krs/create', [KrsController::class, 'create'])->name('krs.create')->middleware('role:super_admin,admin_akademik,mahasiswa');
        Route::post('krs', [KrsController::class, 'store'])->name('krs.store')->middleware('role:super_admin,admin_akademik,mahasiswa');
        Route::get('krs/{krs}', [KrsController::class, 'show'])->name('krs.show');
        Route::patch('krs/{krs}/status', [KrsController::class, 'status'])->name('krs.status')->middleware('role:super_admin,admin_akademik');
        Route::get('nilai', [GradeController::class, 'index'])->name('nilai.index');
        Route::post('nilai', [GradeController::class, 'store'])->name('nilai.store')->middleware('role:super_admin,admin_akademik,dosen');
        Route::patch('nilai/{nilai}', [GradeController::class, 'update'])->name('nilai.update')->middleware('role:super_admin,admin_akademik,dosen');
        Route::delete('nilai/{nilai}', [GradeController::class, 'destroy'])->name('nilai.destroy')->middleware('role:super_admin,admin_akademik,dosen');
        Route::get('khs', [GradeController::class, 'khs'])->name('khs.index')->middleware('role:mahasiswa');
    });

    Route::get('pembayaran', [PaymentController::class, 'index'])
        ->name('pembayaran.index')
        ->middleware('role:super_admin,admin_kampus,admin_keuangan,mahasiswa');
    Route::middleware('role:super_admin,admin_kampus,admin_keuangan')->group(function () {
        Route::post('pembayaran', [PaymentController::class, 'store'])->name('pembayaran.store');
        Route::delete('pembayaran/{pembayaran}', [PaymentController::class, 'destroy'])->name('pembayaran.destroy');
        Route::patch('pembayaran/{pembayaran}/status', [PaymentController::class, 'status'])->name('pembayaran.status');
        Route::get('laporan/keuangan', [ReportController::class, 'finance'])->name('reports.finance');
    });

    Route::middleware('role:super_admin,admin_kampus,admin_akademik,mahasiswa')->group(function () {
        Route::get('surat', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('surat', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('surat/{document}/berkas', [DocumentController::class, 'download'])->name('documents.download');
    });
    Route::patch('surat/{document}/status', [DocumentController::class, 'status'])->name('documents.status')->middleware('role:super_admin,admin_kampus,admin_akademik');
    Route::delete('surat/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy')->middleware('role:super_admin,admin_kampus,admin_akademik');

    Route::get('pengumuman', [AnnouncementController::class, 'index'])->name('pengumuman.index');
    Route::middleware('role:super_admin,admin_kampus,admin_akademik')->group(function () {
        Route::get('pengumuman/create', [AnnouncementController::class, 'create'])->name('pengumuman.create');
        Route::post('pengumuman', [AnnouncementController::class, 'store'])->name('pengumuman.store');
        Route::get('pengumuman/{pengumuman}/edit', [AnnouncementController::class, 'edit'])->name('pengumuman.edit');
        Route::put('pengumuman/{pengumuman}', [AnnouncementController::class, 'update'])->name('pengumuman.update');
        Route::delete('pengumuman/{pengumuman}', [AnnouncementController::class, 'destroy'])->name('pengumuman.destroy');
    });
    Route::get('notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifikasi/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::middleware('role:super_admin,admin_kampus,admin_akademik,admin_keuangan')->group(function () {
        Route::get('laporan/akademik', [ReportController::class, 'academic'])->name('reports.academic');
        Route::get('laporan/akademik/export', [ReportController::class, 'exportAcademic'])->name('reports.academic.export');
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    });
    Route::middleware('role:super_admin,admin_kampus')->group(function () {
        Route::get('pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('settings.update');
    });
});
