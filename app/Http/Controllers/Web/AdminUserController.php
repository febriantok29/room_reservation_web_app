<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\User;
use App\Services\EmployeeIdGenerator;
use App\Support\WebMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    private const DEFAULT_PASSWORD = 'User@123';

    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $query = User::query()->with('division')->orderBy('first_name');

        if ($request->filled('q')) {
            $kw = trim((string) $request->input('q'));
            $query->where(function ($q) use ($kw) {
                $q->where('first_name', 'like', "%{$kw}%")
                  ->orWhere('last_name', 'like', "%{$kw}%")
                  ->orWhere('email', 'like', "%{$kw}%")
                  ->orWhere('employee_id', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }

        return view('admin.users.index', [
            'users'          => $query->paginate(10)->withQueryString(),
            'divisions'      => Division::query()->whereNull('deleted_at')->orderBy('name')->get(),
            'searchQuery'    => $request->input('q', ''),
            'divisionFilter' => $request->input('division_id', ''),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.users.create', [
            'divisions' => Division::query()->whereNull('deleted_at')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'first_name'    => 'required|string|max:50',
            'last_name'     => 'required|string|max:50',
            'email'         => 'required|email|max:100|unique:s_users,email',
            'division_id'   => 'nullable|string|exists:m_divisions,id',
            'is_admin'      => 'nullable|boolean',
            'date_of_birth' => 'nullable|date|before:today',
        ], [
            'email.unique' => 'Email sudah digunakan.',
        ]);

        $isAdmin  = $request->boolean('is_admin');
        $division = $validated['division_id'] ?? null
            ? Division::query()->whereNull('deleted_at')->find($validated['division_id'])
            : null;

        if (!$isAdmin && !$division) {
            return back()->withErrors(['division_id' => 'Karyawan non-admin wajib memiliki divisi.'])->withInput();
        }

        $employeeId = $division
            ? EmployeeIdGenerator::generate($division->code)
            : EmployeeIdGenerator::generateAdmin();

        User::query()->create([
            'employee_id'   => $employeeId,
            'division_id'   => $division?->id,
            'email'         => strtolower(trim($validated['email'])),
            'password'      => Hash::make(self::DEFAULT_PASSWORD),
            'first_name'    => Str::of($validated['first_name'])->squish()->toString(),
            'last_name'     => Str::of($validated['last_name'])->squish()->toString(),
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'is_admin'      => $isAdmin,
            'is_active'     => true,
            'created_by'    => $request->user()?->id,
        ]);

        return redirect()->route('admin.users')->with(
            'success',
            "Karyawan berhasil ditambahkan. No. Induk: {$employeeId} — password awal: " . self::DEFAULT_PASSWORD
        );
    }

    public function edit(Request $request, User $user): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.users.edit', [
            'user'      => $user,
            'divisions' => Division::query()->whereNull('deleted_at')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'first_name'    => 'required|string|max:50',
            'last_name'     => 'required|string|max:50',
            'email'         => ['required', 'email', 'max:100', Rule::unique('s_users', 'email')->ignore($user->id)],
            'division_id'   => 'nullable|string|exists:m_divisions,id',
            'is_admin'      => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
            'date_of_birth' => 'nullable|date|before:today',
        ], [
            'email.unique' => 'Email sudah digunakan.',
        ]);

        $isSelf = $user->id === $request->user()?->id;

        if ($isSelf && (!$request->boolean('is_active') || !$request->boolean('is_admin'))) {
            return back()->withErrors(['is_active' => WebMessages::USER_CANNOT_MODIFY_SELF])->withInput();
        }

        $user->first_name    = Str::of($validated['first_name'])->squish()->toString();
        $user->last_name     = Str::of($validated['last_name'])->squish()->toString();
        $user->email         = strtolower(trim($validated['email']));
        $user->division_id   = $validated['division_id'] ?? null;
        $user->date_of_birth = $validated['date_of_birth'] ?? null;
        $user->is_admin      = $request->boolean('is_admin');
        $user->is_active     = $request->boolean('is_active');
        $user->updated_by    = $request->user()?->id;
        $user->save();

        return redirect()->route('admin.users')->with('success', WebMessages::USER_UPDATED_SUCCESS);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $user->password   = Hash::make(self::DEFAULT_PASSWORD);
        $user->updated_by = $request->user()?->id;
        $user->save();

        return back()->with('success', 'Password ' . $user->full_name . ' direset ke: ' . self::DEFAULT_PASSWORD);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        if ($user->id === $request->user()?->id) {
            return back()->withErrors(['user' => WebMessages::USER_CANNOT_MODIFY_SELF]);
        }

        $user->deleted_by = $request->user()?->id;
        $user->save();
        $user->delete();

        return redirect()->route('admin.users')->with('success', WebMessages::USER_DELETED_SUCCESS);
    }
}
