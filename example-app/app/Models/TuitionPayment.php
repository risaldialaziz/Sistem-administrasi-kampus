<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TuitionPayment extends Model { use HasFactory; protected $fillable=['student_id','invoice_number','academic_year','term','amount','status','paid_at','verified_by']; protected $casts=['paid_at'=>'datetime','amount'=>'decimal:2']; public function student(){return $this->belongsTo(Student::class);} }
