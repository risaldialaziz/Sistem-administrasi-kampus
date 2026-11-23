<?php

namespace Tests\Feature;

use App\Models\CampusClass;
use App\Models\Course;
use App\Models\Document;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Krs;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\TuitionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AcademicAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_only_view_their_own_krs(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $ownStudent = $this->createStudent($user, '20260001', 'Mahasiswa Saya');
        $otherStudent = $this->createStudent(User::factory()->create(['role' => 'mahasiswa']), '20260002', 'Mahasiswa Lain');
        $class = $this->createClass();
        $ownKrs = $this->createKrs($ownStudent, $class);
        $otherKrs = $this->createKrs($otherStudent, $class);
        Grade::create(['krs_id' => $ownKrs->id, 'score' => 90, 'letter_grade' => 'A', 'grade_point' => 4]);
        Grade::create(['krs_id' => $otherKrs->id, 'score' => 60, 'letter_grade' => 'C+', 'grade_point' => 2.3]);

        $this->actingAs($user)
            ->get(route('krs.index'))
            ->assertOk()
            ->assertSee('Mahasiswa Saya')
            ->assertDontSee('Mahasiswa Lain');

        $this->get(route('nilai.index'))
            ->assertOk()
            ->assertSee('Mahasiswa Saya')
            ->assertDontSee('Mahasiswa Lain');

        $this->get(route('krs.show', $otherKrs))->assertNotFound();
        $this->get(route('krs.show', $ownKrs))->assertOk();
    }

    public function test_student_cannot_forge_krs_owner_or_approval_status(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $ownStudent = $this->createStudent($user, '20260011', 'Mahasiswa Saya');
        $otherStudent = $this->createStudent(User::factory()->create(['role' => 'mahasiswa']), '20260012', 'Mahasiswa Lain');
        $class = $this->createClass();

        $this->actingAs($user)
            ->post(route('krs.store'), [
                'student_id' => $otherStudent->id,
                'class_id' => $class->id,
                'academic_year' => $class->academic_year,
                'term' => $class->term,
                'status' => 'disetujui',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('krs', ['student_id' => $otherStudent->id]);

        $this->post(route('krs.store'), [
            'student_id' => $otherStudent->id,
            'class_id' => $class->id,
            'academic_year' => $class->academic_year,
            'term' => $class->term,
        ])->assertRedirect(route('krs.index'));

        $this->assertDatabaseHas('krs', [
            'student_id' => $ownStudent->id,
            'class_id' => $class->id,
            'status' => 'diajukan',
        ]);
        $this->assertDatabaseMissing('krs', ['student_id' => $otherStudent->id]);
    }

    public function test_student_cannot_write_grades(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = $this->createStudent($user, '20260021', 'Mahasiswa');
        $krs = $this->createKrs($student, $this->createClass());

        $this->actingAs($user)
            ->post(route('nilai.store'), ['krs_id' => $krs->id, 'score' => 90])
            ->assertForbidden();

        $this->assertDatabaseMissing('grades', ['krs_id' => $krs->id]);
    }

    public function test_lecturer_cannot_change_a_grade_for_another_lecturers_class(): void
    {
        $user = User::factory()->create(['role' => 'dosen']);
        $student = $this->createStudent(User::factory()->create(['role' => 'mahasiswa']), '20260031', 'Mahasiswa');
        $class = $this->createClass();
        $krs = $this->createKrs($student, $class);
        $grade = Grade::create(['krs_id' => $krs->id, 'score' => 70, 'letter_grade' => 'B', 'grade_point' => 3]);
        Lecturer::create([
            'user_id' => $user->id,
            'study_program_id' => $class->course->study_program_id,
            'nidn' => '9000000001',
            'name' => 'Dosen Lain',
            'email' => 'lecturer@example.test',
            'status' => 'aktif',
        ]);

        $this->actingAs($user)
            ->patch(route('nilai.update', $grade), ['score' => 99])
            ->assertNotFound();

        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'score' => 70]);
    }

    public function test_student_only_sees_their_own_payments_and_documents(): void
    {
        Storage::fake('local');
        Storage::put('documents/own.pdf', 'own document');
        Storage::put('documents/other.pdf', 'other document');

        $user = User::factory()->create(['role' => 'mahasiswa']);
        $ownStudent = $this->createStudent($user, '20260041', 'Mahasiswa Saya');
        $otherStudent = $this->createStudent(User::factory()->create(['role' => 'mahasiswa']), '20260042', 'Mahasiswa Lain');
        TuitionPayment::create([
            'student_id' => $ownStudent->id,
            'invoice_number' => 'INV-OWN',
            'academic_year' => '2026/2027',
            'term' => 'ganjil',
            'amount' => 1000,
            'status' => 'belum_bayar',
        ]);
        TuitionPayment::create([
            'student_id' => $otherStudent->id,
            'invoice_number' => 'INV-OTHER',
            'academic_year' => '2026/2027',
            'term' => 'ganjil',
            'amount' => 2000,
            'status' => 'belum_bayar',
        ]);
        $ownDocument = Document::create([
            'student_id' => $ownStudent->id,
            'type' => 'Surat Aktif',
            'title' => 'Dokumen Saya',
            'file_path' => 'documents/own.pdf',
            'created_by' => $user->id,
        ]);
        $otherDocument = Document::create([
            'student_id' => $otherStudent->id,
            'type' => 'Surat Aktif',
            'title' => 'Dokumen Lain',
            'file_path' => 'documents/other.pdf',
            'created_by' => $otherStudent->user_id,
        ]);

        $this->actingAs($user)
            ->get(route('pembayaran.index'))
            ->assertOk()
            ->assertSee('INV-OWN')
            ->assertDontSee('INV-OTHER');

        $this->get(route('documents.index'))
            ->assertOk()
            ->assertSee('Dokumen Saya')
            ->assertDontSee('Dokumen Lain');
        $this->get(route('documents.download', $ownDocument))->assertOk();
        $this->get(route('documents.download', $otherDocument))->assertNotFound();
    }

    public function test_academic_admin_cannot_promote_a_user_to_super_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin_akademik']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Escalated User',
                'email' => 'escalated@example.test',
                'password' => 'password123',
                'role' => 'super_admin',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'escalated@example.test']);
    }

    public function test_inactive_account_is_signed_out_before_accessing_the_app(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_successful_mutations_are_recorded_in_the_audit_log(): void
    {
        $user = User::factory()->create(['role' => 'admin_kampus']);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Admin',
                'email' => $user->email,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'profile.update',
        ]);
    }

    private function createStudent(User $user, string $nim, string $name): Student
    {
        $program = StudyProgram::firstOrCreate(
            ['code' => 'TI'],
            ['faculty_id' => Faculty::firstOrCreate(['code' => 'FT'], ['name' => 'Fakultas Teknik'])->id, 'name' => 'Informatika', 'degree' => 'S1']
        );

        return Student::create([
            'user_id' => $user->id,
            'study_program_id' => $program->id,
            'nim' => $nim,
            'name' => $name,
            'email' => $nim.'@example.test',
            'admission_year' => 2026,
            'status' => 'aktif',
        ]);
    }

    private function createClass(): CampusClass
    {
        $program = StudyProgram::firstOrCreate(
            ['code' => 'TI'],
            ['faculty_id' => Faculty::firstOrCreate(['code' => 'FT'], ['name' => 'Fakultas Teknik'])->id, 'name' => 'Informatika', 'degree' => 'S1']
        );
        $course = Course::create([
            'study_program_id' => $program->id,
            'code' => 'IF'.fake()->unique()->numerify('###'),
            'name' => 'Pemrograman',
            'credits' => 3,
            'semester' => 1,
        ]);

        return CampusClass::create([
            'course_id' => $course->id,
            'name' => 'Kelas A '.$course->code,
            'academic_year' => '2026/2027',
            'term' => 'ganjil',
            'capacity' => 40,
        ]);
    }

    private function createKrs(Student $student, CampusClass $class): Krs
    {
        return Krs::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year' => '2026/2027',
            'term' => 'ganjil',
            'status' => 'disetujui',
        ]);
    }
}
