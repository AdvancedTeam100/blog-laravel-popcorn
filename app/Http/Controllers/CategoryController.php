<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function getCategories ($group_id)
    {
        $categories = Category::where('group_id', $group_id)->get();
        return response()->json($categories);
    }

    public function addCategory (Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $category = new Category;
        $category->name = $request->name;
        $category->group_id = auth()->user()->role_id == 1 ? $request->group_id : auth()->user()->group_id;
        $category->save();

        return response()->json([
            'message' => 'キャティゴリが正常に作成されました。',
            'category' => $category
        ], 201);
    } 

    public function deleteCategory($id)
    {   
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'カテゴリが見つかりません'], 404);
        }
    
        $category->delete();
        return response()->json(['message' => 'カテゴリが正常に削除されました'], 201);

    }

    public function updateCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'カテゴリが見つかりません'], 404);
        }
        $category->name = $request->name;
        $category->update();

        return response()->json(
            [
                'message' => 'カテゴリが正常に更新されました',
                'category' => $category
            ], 201);

    }
 }
