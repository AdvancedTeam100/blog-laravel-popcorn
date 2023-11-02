<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GenreController extends Controller
{
    public function getGenres($category_id)
    {
        $genres = Genre::where('category_id', $category_id)->get();
        return response()->json($genres);
    }

    private function getCurrent_Category ($id) {
        $category = Category::find($id);
        if(!$category) {
            return response()->json([
                'message' => 'そのカテゴリは存在しません。',
            ], 404);
        }

        if(auth()->user()->role_id != 1) {

            $allowed_categories = json_decode(auth()->user()->allowed_categories);

            $allowed = false;
            foreach ($allowed_categories as $key => $categories) {
                    if(in_array($id, $categories)) {
                        $allowed  = true; 
                        break;
                    }
            }

            if(auth()->user()->role_id == 2) {
                
                if(!$allowed && $category->group_id != '1' &&  $category->group_id != auth()->user()->group_id) {
                    return response()->json([
                        'message' => 'そのカテゴリに対する権限がありません。',
                    ], 400);
                }
            } else {
                if(!$allowed) {
                    return response()->json([
                        'message' => 'そのカテゴリに対する権限がありません。',
                    ], 400);
                }
            }
        }   

        $group = $category->group;
        $genres = $category->genres()->get();
        $temp = [];
        foreach ($genres as $key => $genre) {
            $blogs = Blog::where('genre_id', $genre->id)->get();
            $c = count($blogs);
            $genre['blog_count'] = $c;
        }

        $blogs = Blog::where('category_id', $category->id)->get();
        $category['blog_count'] = count($blogs);
        $category['genres'] = $genres;
        $category['parent_group']  = $group;

        return $category;

    }

    public function getCurrentCategory ($id)
    {
        $category = $this->getCurrent_Category($id);
        return response()->json($category);
    }

    public function addGenre(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:1,100',
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if(auth()->user()->role_id == 2) {
            $category = Category::find($request->category_id);
            if($category->group_id != auth()->user()->group_id){
                return response()->json([
                    'message' => 'このカテゴリにはアクセスできません',
                ], 400);
            }
        }

        $exist_genre = Genre::where('category_id', $request->category_id)->where('name', $request->name)->get();
        if (count($exist_genre) > 0) {
            return response()->json([
                'message' => 'ジャンルはすでに存在します',
            ], 400);
        }

        $parent_category  = Category::find($request->category_id);
        $parent_group = Category::find($parent_category->group_id);
        
        $genre = new Genre;
        $genre->name = $request->name;
        $genre->category_id = $request->category_id;
        $genre->group_id = $parent_category->group_id;
        $genre->user_id = $parent_group->user_id;
        $genre->save();

        $category = $this->getCurrent_Category($request->category_id);

        return response()->json([
            'message' => 'ジャンルが正常に作成されました。',
            'current_category' => $category,
        ], 201);

    }   

    public function deleteGenre($id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'ジャンルが見つかりません'], 404);
        }

        if(auth()->user()->role_id == 2) {
            $category = Category::find($genre->category_id);
            if($category->group_id != auth()->user()->group_id){
                return response()->json([
                    'message' => 'このカテゴリにはアクセスできません',
                ], 400);
            }
        }
        
        Blog::where('genre_id', $id)->delete();
        
        $genre->delete();

        $parent_category = $this->getCurrent_Category($genre->category_id);

        return response()->json([
            'message' => 'ジャンルは正常に削除されました',
            'current_category' => $parent_category
        ], 201);

    }

    public function updateGenre(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'ジャンルは正常に削除されました'], 404);
        }

        if(auth()->user()->role_id == 2) {
            $category = Category::find($request->category_id);
            if($category->group_id != auth()->user()->group_id){
                return response()->json([
                    'message' => 'このカテゴリにはアクセスできません',
                ], 400);
            }
        }

        $exist_genre = Genre::where('category_id', $genre->category_id)->where('name', $request->name)->get();

        if (count($exist_genre) > 0) {
            return response()->json([
                'message' => 'ジャンルはすでに存在します',
            ], 400);
        }

        $genre->name = $request->name;
        $genre->update();

        $parent_category = $this->getCurrent_Category($request->category_id);

        return response()->json([
            'message' => 'ジャンルが正常に更新されました',
            'current_category' => $parent_category
        ], 201);

    }
}
