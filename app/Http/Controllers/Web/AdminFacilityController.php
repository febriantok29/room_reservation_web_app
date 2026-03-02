<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Support\WebMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminFacilityController extends Controller
{
    private function ensureAdminAccess(Request $request): void
    {
        abort_unless($request->user()?->canApprove(), 403);
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess($request);

        $query = Facility::query()->orderBy('name');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        }

        return view('admin.facilities.index', [
            'facilities' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.facilities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = Str::of((string) $validated['name'])->squish()->title()->toString();
        $slug = Str::slug($name, '_');

        if ($slug === '') {
            return back()->withErrors(['name' => WebMessages::FACILITY_INVALID_NAME])->withInput();
        }

        $facility = Facility::query()->where('slug', $slug)->first();

        if ($facility) {
            return back()->withErrors(['name' => WebMessages::FACILITY_DUPLICATE_NAME])->withInput();
        }

        Facility::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => $name,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.facilities')->with('success', WebMessages::FACILITY_CREATED_SUCCESS);
    }

    public function edit(Request $request, Facility $facility): View
    {
        $this->ensureAdminAccess($request);

        return view('admin.facilities.edit', [
            'facility' => $facility,
        ]);
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = Str::of((string) $validated['name'])->squish()->title()->toString();
        $slug = Str::slug($name, '_');

        if ($slug === '') {
            return back()->withErrors(['name' => WebMessages::FACILITY_INVALID_NAME])->withInput();
        }

        $duplicate = Facility::query()
            ->where('slug', $slug)
            ->where('id', '!=', $facility->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['name' => WebMessages::FACILITY_DUPLICATE_NAME])->withInput();
        }

        $facility->name = $name;
        $facility->slug = $slug;
        $facility->save();

        return redirect()->route('admin.facilities')->with('success', WebMessages::FACILITY_UPDATED_SUCCESS);
    }

    public function destroy(Request $request, Facility $facility): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $facility->delete();

        return redirect()->route('admin.facilities')->with('success', WebMessages::FACILITY_DELETED_SUCCESS);
    }
}
