<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Group;
use App\Models\Category;
use App\Models\Genre;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PostController extends Controller
{
    public function createBlog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:2,100',
            'genre_id' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        
        $genre_id = $request->genre_id;
        $genre = Genre::find($genre_id);
        if(!$genre) {
            return response()->json([
                'message' => "正しいジャンルを選択してください。"
            ], 400);
        }
        $category = $genre->category;

        if(auth()->user()->role_id != 1)
        {
            if($category->group_id != auth()->user()->group_id) 
            {
                return response()->json([
                    'message' => "正しいジャンルを選択してください。"
                ], 400);
            } 
        }

        $blog = new Blog;
        $blog->title = $request->title;
        $blog->content = $request->content;
        $blog->group_id = $category->group_id;
        $blog->category_id = $category->id;
        $blog->genre_id = $request->genre_id;

        //アップロードを開始する
        $validator = Validator::make($request->all(), [
            'pdf_files.*' => 'nullable|mimes:pdf',
            'images.*' => 'nullable|mimes:jpeg,png',
            'videos.*' => 'nullable|mimes:mp4,avi',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        } 
    
        $pdfFileNames = [];
        if ($request->hasFile('pdf_files')) {
            $pdf_files = $request->file('pdf_files');
    
            foreach ($pdf_files as $index => $file) {
                $fileName = time() . $index . '.' . $file->extension();
                $pdfFileNames[] = $fileName;
                $file->move(public_path('upload/pdf/'), $fileName);
            }
            $blog->pdf = json_encode($pdfFileNames);
        }
    
        $imageFileNames = [];
        if ($request->hasFile('images')) {
            $image_files = $request->file('images');
    
            foreach ($image_files as $index => $file) {
                $fileName = time() . $index . '.' . $file->extension();
                $imageFileNames[] = $fileName;
                $file->move(public_path('upload/images/'), $fileName);
            }
            $blog->images = json_encode($imageFileNames);
        }
    
        $videoFileNames = [];
        if ($request->hasFile('videos')) {
            $video_files = $request->file('videos');
    
            foreach ($video_files as $index => $file) {
                $fileName = time() . $index . '.' . $file->extension();
                $videoFileNames[] = $fileName;
                $file->move(public_path('upload/videos/'), $fileName);
            }
            $blog->videos = json_encode($videoFileNames);
        }
        //アップロードを終了する
    
        $blog->save();
    
        return response()->json([
            'message' => 'ブログが正常に保存されました',
            'blog' => $blog
        ], 201);
    }


    public function updateBlog (Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:2,100',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'ブログが見つかりません'], 404);
        }

        $genre_id = $blog->genre_id;
        $genre = Genre::find($genre_id);
        if(!$genre) {
            return response()->json([
                'message' => "正しいブログを選択してください。"
            ], 400);
        }
        $category = $genre->category;

        if(auth()->user()->role_id != 1)
        {
            if($category->group_id != auth()->user()->group_id) 
            {
                return response()->json([
                    'message' => "正しいブログを選択してください。"
                ], 400);
            } 
        }

        $blog->title = $request->title;
        $blog->content = $request->content;
        
        //アップロードを開始する
        $validator = Validator::make($request->all(), [
            'pdf_files.*' => 'nullable|mimes:pdf',
            'images.*' => 'nullable|mimes:jpeg,png',
            'videos.*' => 'nullable|mimes:mp4,avi',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        } 
    
        $pdfFileNames = [];
        if ($request->hasFile('pdf_files')) {
            $pdf_files = $request->file('pdf_files');
    
            foreach ($pdf_files as $index => $file) {
                $fileName = time() . $index . '.' . $file->extension();
                $pdfFileNames[] = $fileName;
                $file->move(public_path('upload/pdf/'), $fileName);
            }
            $blog->pdf = json_encode($pdfFileNames);
        }
    
        $imageFileNames = [];
        if ($request->hasFile('images')) {
            $image_files = $request->file('images');
    
            foreach ($image_files as $index => $file) {
                $fileName = time() . $index . '.' . $file->extension();
                $imageFileNames[] = $fileName;
                $file->move(public_path('upload/images/'), $fileName);
            }
            $blog->images = json_encode($imageFileNames);
        }
    
        $videoFileNames = [];
        if ($request->hasFile('videos')) {
            $video_files = $request->file('videos');
    
            foreach ($video_files as $index => $file) {
                $fileName = time() . $index . '.' . $file->extension();
                $videoFileNames[] = $fileName;
                $file->move(public_path('upload/videos/'), $fileName);
            }
            $blog->videos = json_encode($videoFileNames);
        }
        //アップロードを終了する

        $blog->update();
        return response()->json([
            'message' => 'ブログが正常に更新されました',
            'blog' => $blog
        ], 201);
    }
    //create, update, delete, get

    public function deleteBlog ($id) 
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'ブログが見つかりません'], 404);
        }

        $genre_id = $blog->genre_id;
        $genre = Genre::find($genre_id);
        if(!$genre) {
            return response()->json([
                'message' => "正しいブログを選択してください。"
            ], 400);
        }
        $category = $genre->category;

        if(auth()->user()->role_id != 1)
        {
            if($category->group_id != auth()->user()->group_id) 
            {
                return response()->json([
                    'message' => "正しいブログを選択してください。"
                ], 400);
            } 
        }

        $blog->delete();
        return response()->json(['message' => 'ブログは正常に削除されました'], 201);
    }

    public function show($id) 
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'ブログが見つかりません'], 404);
        }

        //No SuperAdmin
        if(auth()->user()->role_id != 1) 
        {

            if(!in_array($blog->category_id,  json_decode(auth()->user()->common1_permission)) && auth()->user()->group_id != $blog->group_id) 
            {
                return response()->json([
                    'message' => "ブログにアクセスできません"
                ], 406);  
            } 

            if(auth()->user()->role_id < 2 && !in_array($blog->category_id, json_decode(auth()->user()->mygroup_permission))) {
                return response()->json([
                    'message' => "ブログにアクセスできません"
                ], 406);  
            }
        }

        return response()->json($blog);
    }

        
    public function getAllBlogs() {

        $blogs = Blog::with('group', 'category', 'genre')->orderBy('group_id')->orderBy('category_id')->orderBy('genre_id')->get();
        // dd('d');

        // if(auth()->user()->role_id == 2) {
        //     $blogs = Blog::with('group', 'category', 'genre')->where('group_id', )->orderBy('group_id')->orderBy('category_id')->orderBy('genre_id')->get();
        // }
        
        $tree = array();

        foreach ($blogs as $current) {
            $group_id = $current["group_id"];
            $category_id = $current["category_id"];
            $genre_id = $current["genre_id"];
            $blog_id = $current["id"];
            $current['pdf'] = json_decode($current['pdf']);
            if (!isset($tree[$group_id])) {
                $tree[$group_id] = array(
                    "gourp_id" => $group_id,
                    "group_name" => Group::where('id', $group_id)->get()->first() != null ? Group::where('id', $group_id)->get()->first()->name : ''
                );
            }
            if (!isset($tree[$group_id][$category_id])) {
                $tree[$group_id][$category_id] = array(
                    "category_id" => $category_id,
                    "category_name" => Category::where('id', $category_id)->get()->first() != null ? Category::where('id', $category_id)->get()->first()->name : ''
                );
            }
            if (!isset($tree[$group_id][$category_id][$genre_id])) {
                $tree[$group_id][$category_id][$genre_id] = array(
                    "genre_id" => $genre_id, 
                    "genre_name" => Genre::where('id', $genre_id)->get()->first() != null ? Genre::where('id', $genre_id)->get()->first()->name : ''
                );
            }
            if (!isset($tree[$group_id][$category_id][$genre_id][$blog_id])) {
                $tree[$group_id][$category_id][$genre_id][$blog_id] = array(
                    "blog_id" => $blog_id,
                    "blog_title" => $current->title,
                    // 'blog' => $current
                );
            }
        }

        

        return json_encode($tree);
    }
    
    public function getBlogs ($genre_id) 
    {
        // $blogs = Blog::all();
        $blogs = Blog::where('genre_id', $genre_id)->get();

        return response()->json($blogs);
    }
}
