<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class CampusClass extends Model { use HasFactory; protected $table='classes'; protected $fillable=['course_id','lecturer_id','name','academic_year','term','room','schedule','capacity']; public function course(){return $this->belongsTo(Course::class);} public function lecturer(){return $this->belongsTo(Lecturer::class);} public function krs(){return $this->hasMany(Krs::class,'class_id');} }
