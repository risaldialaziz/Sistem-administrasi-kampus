<?php
namespace App\Http\Controllers;
use App\Models\AuditLog;
use Illuminate\Http\Request;
class AuditLogController extends Controller{public function index(Request $r){$items=AuditLog::with('user')->when($r->search,fn($q)=>$q->where('action','like','%'.$r->search.'%'))->latest()->paginate(15)->withQueryString();return view('audit.index',compact('items'));}}
