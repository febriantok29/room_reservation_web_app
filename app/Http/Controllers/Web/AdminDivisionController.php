<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Services\DivisionIdGenerator;
use App\Support\WebMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDivisionController extends Controller
{
    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $query = Division::query()->withCount('users')->whereNull('deleted_at')->orderBy('id');

        if ($request->filled('q')) {
            $kw = trim((string) $request->input('q'));
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('code', 'like', "%{$kw}%");
            });
        }

        return view('admin.divisions.index', [
            'divisions'   => $query->get(),
            'searchQuery' => $request->input('q', ''),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.divisions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
        ]);

        $name = Str::of((string) $validated['name'])->squish()->toString();
        $code = strtoupper(trim((string) $validated['code']));

        if (Division::query()->whereNull('deleted_at')->where('code', $code)->exists()) {
            return back()->withErrors(['code' => 'Kode divisi sudah digunakan.'])->withInput();
        }
        if (Division::query()->whereNull('deleted_at')->where('name', $name)->exists()) {
            return back()->withErrors(['name' => 'Nama divisi sudah digunakan.'])->withInput();
        }

        Division::query()->create([
            'id'          => DivisionIdGenerator::generate(),
            'name'        => $name,
            'code'        => $code,
            'description' => $validated['description'] ?? null,
            'created_by'  => $request->user()?->id,
        ]);

        return redirect()->route('admin.divisions')->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function edit(Request $request, Division $division): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
        ]);

        $name = Str::of((string) $validated['name'])->squish()->toString();
        $code = strtoupper(trim((string) $validated['code']));

        if (Division::query()->whereNull('deleted_at')->where('code', $code)->where('id', '!=', $division->id)->exists()) {
            return back()->withErrors(['code' => 'Kode divisi sudah digunakan.'])->withInput();
        }
        if (Division::query()->whereNull('deleted_at')->where('name', $name)->where('id', '!=', $division->id)->exists()) {
            return back()->withErrors(['name' => 'Nama divisi sudah digunakan.'])->withInput();
        }

        $division->name        = $name;
        $division->code        = $code;
        $division->description = $validated['description'] ?? null;
        $division->updated_by  = $request->user()?->id;
        $division->save();

        return redirect()->route('admin.divisions')->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Request $request, Division $division): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $division->deleted_by = $request->user()?->id;
        $division->save();
        $division->delete();

        return redirect()->route('admin.divisions')->with('success', 'Divisi berhasil dihapus.');
    }
}
