<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Menampilkan daftar seluruh akun pengguna.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            "search" => "nullable|string|max:255",
        ]);

        $users = User::query()
            ->when($filters["search"] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where("name", "like", "%{$search}%")
                        ->orWhere("email", "like", "%{$search}%");
                });
            })
            ->orderBy("name")
            ->paginate(10)
            ->withQueryString();

        return view("admin.user.index", compact('users'));
    }

        /**
     * Menampilkan formulir pendaftaran anggota baru.
     */
    public function create()
    {
        return view("admin.user.create");
    }

    /**
     * Menyimpan data anggota baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|max:255|unique:users,email",
            "password" => "required|string|min:8"
        ]);

        User::create($validated);

        return redirect()->route("admin.user.index")
            ->with("success", "Anggota berhasil ditambahkan.");
    }

    /**
     * Menampilkan formulir untuk mengedit data anggota.
     */
    public function edit(User $user)
    {
        return view("admin.user.edit", compact("user"));
    }

    /**
     * Memperbarui data anggota.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|max:255|unique:users,email," . $user->id,
            "password" => "nullable|string|min:8",
        ]);

        if (empty($validated["password"])) {
            unset($validated["password"]);
        }

        $user->update($validated);

        return redirect()->route("admin.user.index")
            ->with("success", "Data anggota berhasil diperbarui.");
    }

    /**
     * Menghapus anggota yang tidak memiliki riwayat peminjaman.
     */
    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return redirect()->route("admin.user.index")
                ->with("error", "Akun yang sedang digunakan tidak dapat dihapus.");
        }

        if ($user->loans()->exists()) {
            return redirect()->route("admin.user.index")
                ->with("error", "Anggota tidak dapat dihapus karena memiliki riwayat peminjaman.");
        }

        $user->delete();

        return redirect()->route("admin.user.index")
            ->with("success", "Anggota berhasil dihapus.");
    }
}
