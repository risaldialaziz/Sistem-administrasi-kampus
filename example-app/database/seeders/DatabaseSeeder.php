<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = \Illuminate\Support\Facades\Hash::make('password');
        foreach ([
            ['Super Admin','admin@siak.test','super_admin'],
            ['Admin Kampus','kampus@siak.test','admin_kampus'],
            ['Admin Akademik','akademik@siak.test','admin_akademik'],
            ['Admin Keuangan','keuangan@siak.test','admin_keuangan'],
            ['Dosen Pengampu','dosen@siak.test','dosen'],
            ['Mahasiswa','mahasiswa@siak.test','mahasiswa'],
        ] as [$name,$email,$role]) {
            \App\Models\User::updateOrCreate(['email'=>$email],['name'=>$name,'password'=>$password,'role'=>$role,'is_active'=>true,'email_verified_at'=>now()]);
        }
        $faculty=\App\Models\Faculty::updateOrCreate(['code'=>'FT'],['name'=>'Fakultas Teknologi','dean_name'=>'Prof. Dr. Ahmad','is_active'=>true]);
        $program=\App\Models\StudyProgram::updateOrCreate(['code'=>'TI'],['faculty_id'=>$faculty->id,'name'=>'Teknik Informatika','degree'=>'S1','is_active'=>true]);
        $course=\App\Models\Course::updateOrCreate(['code'=>'IF101'],['study_program_id'=>$program->id,'name'=>'Pemrograman Dasar','credits'=>3,'semester'=>1,'is_active'=>true]);
        $lecturer=\App\Models\Lecturer::updateOrCreate(['nidn'=>'0012345678'],['study_program_id'=>$program->id,'name'=>'Dr. Budi Santoso','email'=>'dosen@siak.test','status'=>'aktif']);
        $student=\App\Models\Student::updateOrCreate(['nim'=>'20260001'],['study_program_id'=>$program->id,'name'=>'Mahasiswa','email'=>'mahasiswa@siak.test','phone'=>'081234567890','gender'=>'L','admission_year'=>2026,'status'=>'aktif']);
        $class=\App\Models\CampusClass::updateOrCreate(['course_id'=>$course->id,'name'=>'IF101-A'],['lecturer_id'=>$lecturer->id,'academic_year'=>'2026/2027','term'=>'ganjil','room'=>'Lab 01','schedule'=>'Senin 08:00-10:30','capacity'=>40]);
        $krs=\App\Models\Krs::updateOrCreate(['student_id'=>$student->id,'class_id'=>$class->id],['academic_year'=>'2026/2027','term'=>'ganjil','status'=>'disetujui']);
        \App\Models\Grade::updateOrCreate(['krs_id'=>$krs->id],['score'=>88,'letter_grade'=>'A','grade_point'=>4]);
        \App\Models\TuitionPayment::updateOrCreate(['invoice_number'=>'UKT-2026-0001'],['student_id'=>$student->id,'academic_year'=>'2026/2027','term'=>'ganjil','amount'=>3500000,'status'=>'lunas','paid_at'=>now(),'verified_by'=>1]);
        \Illuminate\Support\Facades\DB::table('settings')->insertOrIgnore([['key'=>'campus_name','value'=>'Universitas SIAK','created_at'=>now(),'updated_at'=>now()],['key'=>'campus_address','value'=>'Jl. Pendidikan No. 1','created_at'=>now(),'updated_at'=>now()]]);
    }
}
