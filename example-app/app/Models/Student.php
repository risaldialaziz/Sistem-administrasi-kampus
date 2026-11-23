<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Student extends Model { use HasFactory; protected $fillable=['user_id','study_program_id','nim','name','email','phone','gender','birth_date','address','admission_year','status']; protected $casts=['birth_date'=>'date']; public function studyProgram(){return $this->belongsTo(StudyProgram::class);} public function user(){return $this->belongsTo(User::class);} public function krs(){return $this->hasMany(Krs::class);} public function payments(){return $this->hasMany(TuitionPayment::class);} public function documents(){return $this->hasMany(Document::class);} }
