<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ClothesController extends Controller
{
    protected $models = [
        'Skin' => \App\Models\Skin::class,
        'Top' => \App\Models\Top::class,
        'Shoes' => \App\Models\Shoes::class,
        'Hair' => \App\Models\Hair::class,
        'Bottom' => \App\Models\Bottom::class,
        'Accessory' => \App\Models\Accessory::class,
        'Jacket' => \App\Models\Jacket::class,
        'ClothesInSet' => \App\Models\ClothesInSet::class,
    ];

    protected function getModel($type)
    {
        if (!array_key_exists($type, $this->models)) {
            abort(404, 'Invalid resource type');
        }
        return $this->models[$type];
    }

    public function index($type)
    {
        $model = $this->getModel($type);
        return response()->json($model::all());
    }

    public function show($type, $id)
    {
        $model = $this->getModel($type);
        return response()->json($model::findOrFail($id));
    }

    public function store(Request $request, $type)
    {
        $model = $this->getModel($type);

        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:255',
            'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('Image')) {
            $file = $request->file('Image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $folder = strtolower($type);
            $path = base_path("public/images/{$folder}");
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $file->move($path, $filename);
            $validated['Image_URL'] = "images/{$folder}/{$filename}";
        }

        $item = $model::create($validated);
        return response()->json($item, 201);
    }

    public function update(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        $item = $model::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:255',
            'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('Image')) {
            // Delete old image
            $oldImage = base_path("public/" . $item->Image_URL);
            if ($item->Image_URL && file_exists($oldImage)) {
                unlink($oldImage);
            }

            // Upload new image
            $file = $request->file('Image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $folder = strtolower($type);
            $path = base_path("public/images/{$folder}");
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $file->move($path, $filename);
            $validated['Image_URL'] = "images/{$folder}/{$filename}";
        }

        $item->update($validated);
        return response()->json($item);
    }

    public function destroy($type, $id)
    {
        $model = $this->getModel($type);
        $item = $model::findOrFail($id);

        $imagePath = base_path("public/" . $item->Image_URL);
        if ($item->Image_URL && file_exists($imagePath)) {
            unlink($imagePath);
        }

        $item->delete();
        return response()->json(null, 204);
    }
}
