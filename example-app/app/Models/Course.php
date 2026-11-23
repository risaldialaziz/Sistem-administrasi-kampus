<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Course extends Model { use HasFactory; protected $fillable=['study_program_id','code','name','credits','semester','is_active']; protected $casts=['is_active'=>'boolean']; public function studyProgram(){return $this->belongsTo(StudyProgram::class);} public function classes(){return $this->hasMany(CampusClass::class);} }
