<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $items = User::query()
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('email', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('items'));
    }

    public function create()
    {
        return view('users.form', ['item' => new User]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in($this->assignableRoles($request))],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active');

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->authorizeManagedUser($user);

        return view('users.form', ['item' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeManagedUser($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in($this->assignableRoles($request))],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($data['role'] === 'super_admin' && $user->role !== 'super_admin') {
            abort_unless($request->user()->role === 'super_admin', 403);
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorizeManagedUser($user);
        abort_if($user->id === auth()->id(), 403, 'Anda tidak dapat menghapus akun sendiri.');

        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    private function assignableRoles(Request $request): array
    {
        $roles = ['admin_kampus', 'admin_akademik', 'admin_keuangan', 'dosen', 'mahasiswa'];

        if ($request->user()->role === 'super_admin') {
            array_unshift($roles, 'super_admin');
        }

        return $roles;
    }

    private function authorizeManagedUser(User $user): void
    {
        abort_if($user->role === 'super_admin' && auth()->user()->role !== 'super_admin', 403);
    }
}
