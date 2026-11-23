<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Grade extends Model { use HasFactory; protected $fillable=['krs_id','score','letter_grade','grade_point']; protected $casts=['score'=>'decimal:2','grade_point'=>'decimal:2']; public function krs(){return $this->belongsTo(Krs::class);} }
