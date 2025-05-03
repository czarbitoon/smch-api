<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceCategory;
use App\Models\DeviceType;
use App\Models\DeviceSubcategory;

class DeviceCategoryController extends Controller
{
    public function getCategories(Request $request)
    {
        try {
            $query = DeviceCategory::query();

            if ($request->has('office_id')) {
                $officeId = $request->input('office_id');
                $query->whereHas('deviceTypes.subcategories.devices', function($q) use ($officeId) {
                    $q->where('office_id', $officeId);
                });
            }

            $categories = $query->get();
            return response()->json($categories);
        } catch (\Exception $e) {
            \Log::error('Error fetching device categories: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching categories: ' . $e->getMessage()], 500);
        }
    }

    public function getTypes($categoryId, Request $request)
    {
        try {
            $query = DeviceType::where('device_category_id', $categoryId);

            $user = $request->user();
            $isAdmin = $user && in_array($user->role, [2, 3]);

            if (!$isAdmin && $request->has('office_id')) {
                $officeId = $request->input('office_id');
                $query->whereHas('subcategories.devices', function($q) use ($officeId) {
                    $q->where('office_id', $officeId);
                });
            }

            $types = $query->get();
            return response()->json($types);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching types'], 500);
        }
    }

    public function getSubcategories($typeId, Request $request)
    {
        try {
            $query = DeviceSubcategory::where('device_type_id', $typeId);

            $user = $request->user();
            $isAdmin = $user && in_array($user->role, [2, 3]);

            if (!$isAdmin && $request->has('office_id')) {
                $officeId = $request->input('office_id');
                $query->whereHas('devices', function($q) use ($officeId) {
                    $q->where('office_id', $officeId);
                });
            }

            $subcategories = $query->get();
            return response()->json($subcategories);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching subcategories'], 500);
        }
    }

    public function getSubcategoriesByCategory($categoryId, Request $request)
    {
        try {
            $query = DeviceSubcategory::where('device_category_id', $categoryId);
            $user = $request->user();
            $isAdmin = $user && in_array($user->role, [2, 3]);
            if (!$isAdmin && $request->has('office_id')) {
                $officeId = $request->input('office_id');
                $query->whereHas('devices', function($q) use ($officeId) {
                    $q->where('office_id', $officeId);
                });
            }
            $subcategories = $query->get();
            return response()->json(['data' => ['subcategories' => $subcategories]]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching subcategories'], 500);
        }
    }

    public function createCategory(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category = DeviceCategory::create($request->all());
            return response()->json($category, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating category'], 500);
        }
    }

    public function createType(Request $request, $categoryId)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $type = new DeviceType($request->all());
            $type->device_category_id = $categoryId;
            $type->save();

            return response()->json($type, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating type'], 500);
        }
    }

    public function createSubcategory(Request $request, $typeId)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $subcategory = new DeviceSubcategory($request->all());
            $subcategory->device_type_id = $typeId;
            $subcategory->save();

            return response()->json($subcategory, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating subcategory'], 500);
        }
    }
}
