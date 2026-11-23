<?php
namespace App\Http\Controllers;
use App\Models\CampusNotification;
class NotificationController extends Controller{
 public function index(){return view('notifikasi.index',['items'=>CampusNotification::where('user_id',auth()->id())->latest()->paginate(10)]);}
 public function read(CampusNotification $notification){abort_unless($notification->user_id===auth()->id(),403);$notification->update(['read_at'=>now()]);return back();}
}
