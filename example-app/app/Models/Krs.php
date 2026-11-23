<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Krs extends Model { use HasFactory; protected $table='krs'; protected $fillable=['student_id','class_id','academic_year','term','status']; public function student(){return $this->belongsTo(Student::class);} public function campusClass(){return $this->belongsTo(CampusClass::class,'class_id');} public function grade(){return $this->hasOne(Grade::class);} }
