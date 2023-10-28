<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Group;
use App\Models\Blog;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CategoryController extends Controller
{       

    public function getCategories ($group_id)
    {
        $categories = Category::where('group_id', $group_id)->get();
        return response()->json($categories);
    }

    private function getCurrent_group($id) {
        $group = Group::find($id);
        if(!$group) {
            return response()->json([
                'message' => 'そのグループは存在しません。',
            ], 404);
        }

        if(auth()->user()->role_id != 1) {

            $allowed_categories = json_decode(auth()->user()->allowed_categories);

            $allowed = false;
            foreach ($allowed_categories as $key => $categories) {
                if($key == $id) {
                    if(count($categories) > 0) {
                        $allowed  = true; 
                        break;
                    }
                }
            }

            if(auth()->user()->role_id == 2) {
                
                if(!$allowed && $id != '1' &&  $id != auth()->user()->group_id) {
                    return response()->json([
                        'message' => 'そのグループへのアクセス権がありません。',
                    ], 400);
                }
            } else {
                if(!$allowed) {
                    return response()->json([
                        'message' => 'そのグループへのアクセス権がありません。',
                    ], 400);
                }
            }
        }

        $categories = $group->categories()->get();
        $temp = [];
        foreach ($categories as $key => $category) {
            $blogs = Blog::where('category_id', $category->id)->get();
            $c = count($blogs);
            $category['blog_count'] = $c;
        }

        $blogs = Blog::where('group_id', $group->id)->get();
        $group['blog_count'] = count($blogs);
        $group['categories'] = $categories;

        return $group;
    }


    public function getCurrentGroup ($id) {
        
        $group = $this->getCurrent_group($id);
        return response()->json($group);
    }


    //super admin
    public function getAllGroup ()
    {
        $groups = Group::all();

        $all_categories = [];
        foreach ($groups as $key => $group) {
            $categories = $group->categories()->get();
            $temp = array(
                $group->id => $categories
            );

            $all_categories[$group->id] = $categories;
        }

        return response()->json([
            "groups" => $groups,
            "categories" => $all_categories
        ]);
    }


    public function addCategory (Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:1,100',
            'group_id' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        if(auth()->user()->role_id == 2 && $request->group_id != auth()->user()->group_id)  
        {
            return response()->json([
                'message' => 'あなたはこのグループに対する編集権限を持っていません。',
            ], 400);
        }
 
        $category = new Category;
        $category->name = $request->name;
        $category->group_id = $request->group_id;
        $category->save();

        $parent_group = $this->getCurrent_group($request->group_id);

        return response()->json([
            'message' => 'カテゴリーが正常に作成されました。',
            'current_group' => $parent_group
        ], 201);
    } 

    public function deleteCategory($id)
    {   
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'カテゴリが見つかりません'], 404);
        }

        if(auth()->user()->role_id == 2 && $request->group_id != auth()->user()->group_id)  
        {
            return response()->json([
                'message' => 'あなたはこのグループに対する編集権限を持っていません。',
            ], 400);
        }
    
        $category->delete();

        $parent_group = $this->getCurrent_group($category->group_id);

        return response()->json([
            'message' => 'カテゴリが正常に削除されました',
            'current_group' => $parent_group
        ], 201);
        // return response()->json(['message' => 'カテゴリが正常に削除されました'], 201);

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
            return response()->json(['message' => 'カテゴリが見つかりません'], 404);
        }

        if(auth()->user()->role_id == 2 && $request->group_id != auth()->user()->group_id)  
        {
            return response()->json([
                'message' => 'あなたはこのグループに対する編集権限を持っていません。',
            ], 400);
        }
 

        $category->name = $request->name;
        $category->update();

        $parent_group = $this->getCurrent_group($request->group_id);

        return response()->json([
            'message' => 'カテゴリが正常に更新されました',
            'current_group' => $parent_group
        ], 201);
    }
 }
