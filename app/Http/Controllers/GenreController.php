<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Blog;
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

    public function getAllGenres1()
    {
        $groups = Group::all();
        $temp = array();
        foreach ($groups as $key => $group) {
            $group_temp = array(
                "id" => $group->id,
                "name" => $group->name,
                "categories" => []
            );
            // $temp['categories' =z]
            $categories = $group->categories()->get();

            foreach ($categories as $key1 => $category) {

                $category_temp = array(
                    "id" => $category->id,
                    "name" => $category->name,
                    "genres" => []
                );

                $genres = $category->genres()->get();
                foreach ($genres as $key2 => $genre) {
                    $genre_temp = array(
                        "id" => $genre->id,
                        "name" => $genre->name,
                        "blog" => []
                    );
                    $category_temp['genres'][] = $genre_temp;
                }
                $group_temp["categories"][] = $category_temp;
            }
            $temp[] = $group_temp;
        }

        return response()->json($temp);

    }

    public function getAllGenres()
    {
        $groups = Group::all();

        if(auth()->user()->role_id == 2) {
            $group = Group::find(auth()->user()->group_id);
        
            $categories = $group->categories()->get();
            $temp1 = [];
            foreach ($categories as $key => $category) {
                $temp1[] = $category->id;
            }
        }
 
        //common group, group1
        $common_permission = json_decode(auth()->user()->common1_permission);
        $group_permission = json_decode(auth()->user()->mygroup_permission);

        $category_permissions = [];
        $category_permissions['1'] = $common_permission;
        $category_permissions[auth()->user()->group_id] = $group_permission;
        if(auth()->user()->role_id == 2) {
            $category_permissions[auth()->user()->group_id] = $temp1;
        } 
        // return response()->json($category_permissions);
        $temp = array();
        foreach ($groups as $key => $group) {

            if (auth()->user()->role_id == 1 || isset($category_permissions[$group->id])) {
                $g_blogs = Blog::where('group_id', $group->id)->get();

                $group_temp = array(
                    "id" => $group->id,
                    "name" => $group->name,
                    "categories" => [],
                    "count" => count($g_blogs)
                );
                $categories = $group->categories()->get();
                foreach ($categories as $key1 => $category) {

                    if (auth()->user()->role_id == 1 || in_array($category->id, $category_permissions[$group->id])) {
                        $s_blogs = Blog::where('category_id', $category->id)->get();

                        $category_temp = array(
                            "id" => $category->id,
                            "name" => $category->name,
                            "genres" => [],
                            "count" => count($s_blogs)
                        );

                        $genres = $category->genres()->get();
                        foreach ($genres as $key2 => $genre) {
                            $genre_blogs = Blog::where('genre_id', $genre->id)->get();

                            $genre_temp = array(
                                "id" => $genre->id,
                                "name" => $genre->name,
                                "blog" => [],
                                "count" => count($genre_blogs)
                            );
                            $category_temp['genres'][] = $genre_temp;
                        }
                        $group_temp["categories"][] = $category_temp;
                    }

                }

                $temp[] = $group_temp;
            }

        }

        return response()->json($temp);

    }

    public function addGenre(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:1,100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $exist_genre = Genre::where('category_id', $request->category_id)->where('name', $request->name)->get();
        if (count($exist_genre) > 0) {
            return response()->json([
                'message' => 'ジャンルはすでに存在します',
            ], 400);
        }
        $genre = new Genre;
        $genre->name = $request->name;
        $genre->category_id = $request->category_id;
        $genre->save();

        return response()->json([
            'message' => 'ジャンルが正常に作成されました。',
            'genre' => $genre,
        ], 201);

    }   

    public function deleteGenre($id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'ジャンルが見つかりません'], 404);
        }

        $genre->delete();
        return response()->json(['message' => 'ジャンルは正常に削除されました'], 201);
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
        $exist_genre = Genre::where('category_id', $genre->category_id)->where('name', $request->name)->get();

        if (count($exist_genre) > 0) {
            return response()->json([
                'message' => 'ジャンルはすでに存在します',
            ], 400);
        }

        $genre->name = $request->name;
        $genre->update();

        return response()->json(['message' => 'ジャンルが正常に更新されました'], 201);

    }
}
