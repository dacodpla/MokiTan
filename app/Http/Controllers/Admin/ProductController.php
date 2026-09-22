<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with('category')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['image_path'] = $this->handleImage($request);

        $product = Product::create($data);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Product created. Link recommended add-ons below.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('recommendations'),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        if ($file = $this->handleImage($request)) {
            $data['image_path'] = $file;
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:180',
            'brand' => 'required|string|max:80',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'mold_history' => 'nullable|string|max:2000',
            'is_featured' => 'sometimes|boolean',
            'image' => 'nullable|image|max:4096',
            'specifications' => 'nullable|array',
            'specifications.keys' => 'nullable|array',
            'specifications.keys.*' => 'nullable|string|max:60',
            'specifications.values' => 'nullable|array',
            'specifications.values.*' => 'nullable|string|max:200',
        ];

        $validated = $request->validate($rules);
        $validated['is_featured'] = (bool) $request->boolean('is_featured');
        $validated['specifications'] = $this->zipSpecs($request->input('specifications', []));

        return collect($validated)->except('image')->all();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /**
     * @param  array{keys?: array<int, ?string>, values?: array<int, ?string>}  $raw
     * @return array<string, string>|null
     */
    private function zipSpecs(array $raw): ?array
    {
        $keys = $raw['keys'] ?? [];
        $values = $raw['values'] ?? [];
        $out = [];
        foreach ($keys as $i => $k) {
            $k = trim((string) $k);
            $v = trim((string) ($values[$i] ?? ''));
            if ($k !== '' && $v !== '') {
                $out[$k] = $v;
            }
        }

        return $out === [] ? null : $out;
    }

    private function handleImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('products', 'public');

        return 'storage/'.$path;
    }
}
