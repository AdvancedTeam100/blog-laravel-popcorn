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
        $blog = new Blog;
        $blog->title = $request->title;
        $blog->content = $request->content;
        
        if(auth()->user()->role_id == '1') {
            $blog->group_id = $request->group_id;
        } else {
            $blog->group_id = auth()->user()->role_id;
        }
        $blog->category_id = $request->category_id;
        $blog->genre_id = $request->genre_id;
    
        $validator = Validator::make($request->all(), [
            'pdf_files.*' => 'nullable|mimes:pdf',
            'images.*' => 'nullable|mimes:jpeg,png',
            'videos.*' => 'nullable|mimes:mp4,avi',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
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
    
        $blog->save();
    
        return response()->json([
            'message' => 'ブログが正常に保存されました',
        ], 201);
    }

        
    public function getAllBlogs() {
        $blogs = Blog::with('group', 'category', 'genre')->orderBy('group_id')->orderBy('category_id')->orderBy('genre_id')->get();
        
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
                    'blog' => $current
                );
            }
        }

        

        return json_encode($tree);
    }
}
