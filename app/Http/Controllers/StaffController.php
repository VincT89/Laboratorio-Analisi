<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Verifica permessi base.
     */
    private function authorizeAdmin()
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Accesso negato. Solo l\'amministratore può gestire lo staff.');
    }

    public function index()
    {
        $this->authorizeAdmin();
        $users = User::with('roles')->orderBy('name')->paginate(15);
        return view('staff.index', compact('users'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $roles = Role::all();
        return view('staff.create', compact('roles'));
    }

    public function store(StoreStaffRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('staff.index')->with('success', 'Utente creato con successo.');
    }

    public function edit(User $staff)
    {
        $this->authorizeAdmin();

        // Evita che l'admin si tolga i permessi da solo per sbaglio
        if ($staff->id === auth()->id()) {
            return redirect()->route('staff.index')->with('error', 'Non puoi modificare il tuo stesso ruolo da questa interfaccia.');
        }

        $roles = Role::all();
        return view('staff.edit', compact('staff', 'roles'));
    }

    public function update(UpdateStaffRequest $request, User $staff)
    {
        if ($staff->id === auth()->id()) {
            return redirect()->route('staff.index')->with('error', 'Non puoi modificare te stesso.');
        }

        $staff->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $staff->update(['password' => Hash::make($request->password)]);
        }

        $staff->syncRoles([$request->role]);

        return redirect()->route('staff.index')->with('success', 'Utente aggiornato con successo.');
    }

    public function destroy(User $staff)
    {
        $this->authorizeAdmin();

        if ($staff->id === auth()->id()) {
            return redirect()->route('staff.index')->with('error', 'Non puoi eliminare il tuo account.');
        }

        $staff->update(['is_active' => false]);
        return redirect()->route('staff.index')->with('success', 'Utente disattivato correttamente.');
    }
}