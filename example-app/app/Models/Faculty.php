<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Faculty extends Model { use HasFactory; protected $fillable=['code','name','dean_name','is_active']; protected $casts=['is_active'=>'boolean']; public function studyPrograms(){return $this->hasMany(StudyProgram::class);} }
