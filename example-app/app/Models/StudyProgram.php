<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class StudyProgram extends Model { use HasFactory; protected $fillable=['faculty_id','code','name','degree','is_active']; protected $casts=['is_active'=>'boolean']; public function faculty(){return $this->belongsTo(Faculty::class);} public function students(){return $this->hasMany(Student::class);} public function courses(){return $this->hasMany(Course::class);} }
