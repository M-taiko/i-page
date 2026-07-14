<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Organization $organization)
    {
        $this->authorize('view', $organization);

        $brands = $organization->brands()
            ->withCount(['channels', 'followers'])
            ->orderBy('name')
            ->get();

        return view('organizations.brands.index', compact('organization', 'brands'));
    }

    public function show(Organization $organization, Brand $brand)
    {
        $this->authorize('view', $organization);
        abort_unless($brand->organization_id === $organization->id, 404);

        $brand->loadCount('followers');
        $channels = $brand->channels()->withCount('users', 'posts')->get();
        $postsCount = \App\Models\Post::where('brand_id', $brand->id)->count();

        return view('organizations.brands.show', compact('organization', 'brand', 'channels', 'postsCount'));
    }

    public function create(Organization $organization)
    {
        $this->authorize('update', $organization);

        return view('organizations.brands.form', compact('organization'));
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $organization->brands()->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'colors' => array_filter([
                'primary' => $validated['primary_color'] ?? null,
                'secondary' => $validated['secondary_color'] ?? null,
            ]),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->redirectAfterSave($organization, 'Brand created successfully.');
    }

    public function edit(Organization $organization, Brand $brand)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $brand);

        return view('organizations.brands.form', compact('organization', 'brand'));
    }

    public function update(Request $request, Organization $organization, Brand $brand)
    {
        $this->authorize('update', $organization);
        $this->authorize('update', $brand);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'description' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $brand->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'colors' => array_filter([
                'primary' => $validated['primary_color'] ?? null,
                'secondary' => $validated['secondary_color'] ?? null,
            ]),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->redirectAfterSave($organization, 'Brand updated successfully.');
    }

    public function destroy(Organization $organization, Brand $brand)
    {
        $this->authorize('update', $organization);
        $this->authorize('delete', $brand);

        $brand->delete();

        return $this->redirectAfterSave($organization, 'Brand deleted successfully.');
    }

    /**
     * Super Admin returns to the admin org panel; Org Admin to org settings.
     */
    private function redirectAfterSave(Organization $organization, string $message)
    {
        $route = auth()->user()->hasRole('super_admin')
            ? route('admin.organizations.show', $organization)
            : route('organizations.brands.index', $organization);

        return redirect($route)->with('success', $message);
    }
}
