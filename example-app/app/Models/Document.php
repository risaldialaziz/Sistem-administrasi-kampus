<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Document extends Model { use HasFactory; protected $fillable=['student_id','type','number','title','file_path','status','created_by']; public function student(){return $this->belongsTo(Student::class);} }
