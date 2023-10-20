<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function getGenres($category_id)
    {
        $genres = Genre::where('category_id', $category_id)->get();
        return response()->json($genres);
    }

    public function addGenre (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $exist_genre = Genre::where('category_id', $request->category_id)->where('name', $request->name)->get();
        if(count($exist_genre) > 0) {
            return response()->json([
                'error' => 'ジャンルはすでに存在します'
            ], 400);
        }
        $genre = new Genre;
        $genre->name = $request->name;
        $genre->category_id = $request->category_id;
        $genre->save();

        return response()->json([
            'message' => 'ジャンルが正常に作成されました。',
            'genre' => $genre
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

    public function updateGenre (Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['message' => 'ジャンルは正常に削除されました'], 404);
        }
        $exist_genre = Genre::where('category_id', $genre->category_id)->where('name', $request->name)->get();

        if(count($exist_genre) > 0) 
        {
            return response()->json([
                'error' => 'ジャンルはすでに存在します'
            ], 400);
        }

        $genre->name = $request->name;
        $genre->update();

        return response()->json(['message' => 'ジャンルが正常に更新されました'], 201);

    }
}
