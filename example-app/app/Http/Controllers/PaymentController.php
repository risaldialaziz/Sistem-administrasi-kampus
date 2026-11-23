<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TuitionPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $r)
    {
        $query = TuitionPayment::with('student')->when($r->status, fn ($q) => $q->where('status', $r->status));
        if ($r->user()->role === 'mahasiswa') {
            $student = Student::where('user_id', $r->user()->id)->firstOrFail();
            $query->where('student_id', $student->id);
            $students = collect();
        } else {
            $students = Student::where('status', 'aktif')->orderBy('name')->get();
        }
        $items = $query->latest()->paginate(10)->withQueryString();

        return view('pembayaran.index', compact('items', 'students'));
    }

    public function store(Request $r)
    {
        $d = $r->validate(['student_id' => 'required|exists:students,id', 'invoice_number' => 'required|max:100|unique:tuition_payments,invoice_number', 'academic_year' => 'required|max:20', 'term' => 'required|in:ganjil,genap', 'amount' => 'required|numeric|min:0', 'status' => 'required|in:belum_bayar,menunggu_verifikasi,lunas,dibatalkan']);
        if ($d['status'] === 'lunas') {
            $d['paid_at'] = now();
            $d['verified_by'] = auth()->id();
        }TuitionPayment::create($d);

        return back()->with('success', 'Tagihan pembayaran berhasil dibuat.');
    }

    public function status(Request $r, TuitionPayment $pembayaran)
    {
        $d = $r->validate(['status' => 'required|in:belum_bayar,menunggu_verifikasi,lunas,dibatalkan']);
        $d['paid_at'] = $d['status'] === 'lunas' ? now() : null;
        $d['verified_by'] = $d['status'] === 'lunas' ? auth()->id() : null;
        $pembayaran->update($d);

        return back()->with('success', 'Status pembayaran diperbarui.');
    }

    public function destroy(TuitionPayment $pembayaran)
    {
        $pembayaran->delete();

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }
}
