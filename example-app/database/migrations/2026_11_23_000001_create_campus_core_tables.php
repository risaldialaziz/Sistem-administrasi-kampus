<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('mahasiswa')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::create('faculties', function (Blueprint $table) {
            $table->id(); $table->string('code', 20)->unique(); $table->string('name');
            $table->string('dean_name')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id(); $table->foreignId('faculty_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique(); $table->string('name'); $table->string('degree', 30);
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('nim', 30)->unique(); $table->string('name'); $table->string('email')->unique();
            $table->string('phone', 30)->nullable(); $table->enum('gender', ['L', 'P'])->nullable();
            $table->date('birth_date')->nullable(); $table->text('address')->nullable();
            $table->year('admission_year'); $table->enum('status', ['aktif', 'cuti', 'lulus', 'dikeluarkan'])->default('aktif');
            $table->timestamps();
        });
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('study_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nidn', 30)->unique(); $table->string('name'); $table->string('email')->unique();
            $table->string('phone', 30)->nullable(); $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif'); $table->timestamps();
        });
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); $table->foreignId('study_program_id')->constrained()->restrictOnDelete();
            $table->string('code', 20)->unique(); $table->string('name'); $table->unsignedTinyInteger('credits')->default(3);
            $table->unsignedTinyInteger('semester'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id(); $table->string('name', 20)->unique(); $table->enum('term', ['ganjil', 'genap']);
            $table->boolean('is_active')->default(false); $table->timestamps();
        });
        Schema::create('classes', function (Blueprint $table) {
            $table->id(); $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 50); $table->string('academic_year', 20); $table->enum('term', ['ganjil', 'genap']);
            $table->string('room', 50)->nullable(); $table->string('schedule')->nullable();
            $table->unsignedSmallInteger('capacity')->default(40); $table->timestamps();
        });
        Schema::create('krs', function (Blueprint $table) {
            $table->id(); $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('academic_year', 20); $table->enum('term', ['ganjil', 'genap']);
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak', 'dibatalkan'])->default('draft');
            $table->timestamps(); $table->unique(['student_id', 'class_id']);
        });
        Schema::create('grades', function (Blueprint $table) {
            $table->id(); $table->foreignId('krs_id')->constrained('krs')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable(); $table->string('letter_grade', 2)->nullable();
            $table->decimal('grade_point', 3, 2)->nullable(); $table->timestamps();
        });
        Schema::create('tuition_payments', function (Blueprint $table) {
            $table->id(); $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique(); $table->string('academic_year', 20);
            $table->enum('term', ['ganjil', 'genap']); $table->decimal('amount', 15, 2);
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'dibatalkan'])->default('belum_bayar');
            $table->timestamp('paid_at')->nullable(); $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type'); $table->string('number')->nullable(); $table->string('title');
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'diproses', 'siap', 'diterbitkan', 'ditolak'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('announcements', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->text('content'); $table->enum('audience', ['semua', 'mahasiswa', 'dosen', 'staf'])->default('semua');
            $table->timestamp('published_at')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('title');
            $table->text('message'); $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); $table->string('key')->unique(); $table->text('value')->nullable(); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); $table->string('subject_type')->nullable(); $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable(); $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable(); $table->text('user_agent')->nullable(); $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        foreach (['audit_logs','settings','notifications','announcements','documents','tuition_payments','grades','krs','classes','academic_years','courses','lecturers','students','study_programs','faculties'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'is_active']));
    }
};
