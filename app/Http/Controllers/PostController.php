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
    private function getCurrent_Genre ($id) {
        $genre = Genre::find($id);
        if(!$genre) {
            return response()->json([
                'message' => 'そのジャンルは存在しません。',
            ], 404);
        }

        if(auth()->user()->role_id != 1) {

            $allowed_categories = json_decode(auth()->user()->allowed_categories);

            $allowed = false;
            foreach ($allowed_categories as $key => $categories) {
                    if(in_array($genre->category_id, $categories)) {
                        $allowed  = true; 
                        break;
                    }
            }

            $category = Category::find($genre->category_id);

            if(auth()->user()->role_id == 2) {
                
                if(!$allowed && $category->group_id != '1' &&  $category->group_id != auth()->user()->group_id) {
                    return response()->json([
                        'message' => 'そのジャンルに対する許可がありません。',
                    ], 400);
                }
            } else {
                if(!$allowed) {
                    return response()->json([
                        'message' => 'そのジャンルに対する許可がありません。',
                    ], 400);
                }
            }
        }   
        $category = $genre->category;
        $group = $category->group;
        $blogs = $genre->blogs()->get();

        $genre['blog_count'] = count($blogs);
        $genre['blogs'] = $blogs;
        $genre['parent_category'] = $category;
        $genre['parent_group'] = $group;

        return $genre;
    }

    public function getCurrentGenre($id) 
    {
        $genre = $this->getCurrent_Genre($id);
        return response()->json($genre);
    }


    public function createBlog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:1,100',
            'genre_id' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        
        $genre = Genre::find($request->genre_id);
        if(!$genre) {
            return response()->json([
                'message' => "正しいジャンルを選択してください。"
            ], 400);
        }


        $category = $genre->category;   

        if(auth()->user()->role_id != 1)
        {
            if(auth()->user()->role_id == 2) {

                if($category->group_id != auth()->user()->group_id){
                    return response()->json([
                        'message' => 'このカテゴリにはアクセスできません',
                    ], 400);
                }
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
            'message' => 'ブログが正常に保存されました'
        ], 201);
    }

    public function updateBlog (Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:1,100',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'ブログが見つかりません'], 404);
        }

        $genre = Genre::find($blog->genre_id);
        if(!$genre) {
            return response()->json([
                'message' => "正しいブログを選択してください。"
            ], 400);
        }
        $category = $genre->category;

        if(auth()->user()->role_id != 1)
        {
            if(auth()->user()->role_id == 2) {

                if($category->group_id != auth()->user()->group_id){
                    return response()->json([
                        'message' => 'このカテゴリにはアクセスできません',
                    ], 400);
                }
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

        $genre = Genre::find($blog->genre_id);
        if(!$genre) {
            return response()->json([
                'message' => "正しいブログを選択してください。"
            ], 400);
        }
        $category = $genre->category;

        if(auth()->user()->role_id != 1)
        {
            if(auth()->user()->role_id == 2) {

                if($category->group_id != auth()->user()->group_id){
                    return response()->json([
                        'message' => 'このカテゴリにはアクセスできません',
                    ], 400);
                }
            }
        }

        $blog->delete();

        $parent_genre = $this->getCurrent_Genre($genre->id);
        return response()->json([
            'message' => 'ブログは正常に削除されました',
            'current_genre' => $parent_genre
    ], 201);
    }



    public function show($id) 
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'ブログが見つかりません'], 404);
        }

        $genre = Genre::find($blog->genre_id);
        if(!$genre) {
            return response()->json([
                'message' => 'そのジャンルは存在しません。',
            ], 404);
        }

        if(auth()->user()->role_id != 1) {

            $allowed_categories = json_decode(auth()->user()->allowed_categories);

            $allowed = false;
            foreach ($allowed_categories as $key => $categories) {
                    if(in_array($genre->category_id, $categories)) {
                        $allowed  = true; 
                        break;
                    }
            }
            
            $category = Category::find($genre->category_id);

            if(auth()->user()->role_id == 2) {
                
                if(!$allowed && $category->group_id != '1' &&  $category->group_id != auth()->user()->group_id) {
                    return response()->json([
                        'message' => 'このブログは見れません',
                    ], 400);
                }
            } else {
                if(!$allowed) {
                    return response()->json([
                        'message' => 'このブログは見れません',
                    ], 400);
                }
            }
        }  

        $parent_genre = $blog->genre;
        $parent_category = $parent_genre->category;
        $parent_group = $parent_category->group;


        return response()->json($blog);
    }

    public function getBlogs ($genre_id) 
    {
        // $blogs = Blog::all();
        $blogs = Blog::where('genre_id', $genre_id)->get();

        return response()->json($blogs);
    }

    private function getAllowedCategories() 
    {
        $allowed_categories = json_decode(auth()->user()->allowed_categories);
        $temp = [];
        foreach ($allowed_categories as $key => $categories) {
            foreach ($categories as $key => $category_id) {
                $temp[] = $category_id;
            }
        }

        if(auth()->user()->role_id == 2) {
            $group = Group::find(auth()->user()->group_id);
            $categories = $group->categories()->get();
            foreach ($categories as $key => $category) {
                $temp[] = $category->id;
            }

            $c_group1 = Group::find('1');
            $c1_categories = $c_group1->categories()->get();
            foreach ($c1_categories as $key => $category) {
                $temp[] = $category->id;
            }
        }

        return $temp;
    }

    public function getNewBlogs () 
    {        
        if(auth()->user()->role_id == 1) {
            $blogs = Blog::orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            return response()->json($blogs);
        } else {
            $allowed_categories = $this->getAllowedCategories();
            $blogs = Blog::whereIn('category_id', $allowed_categories)->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        }
        return response()->json($blogs);
    }

    public function searchResultBlogs(Request $request) {
        
        $title = $request->title;
        $content = $request->content;

        if(auth()->user()->role_id == 1) {
            $blogs = Blog::orderBy('created_at', 'desc')
            ->where('title', 'LIKE', '%' . $title . '%')
            ->where('content', 'LIKE', '%' . $content . '%')
            ->get();
        } else {
            $allowed_categories = $this->getAllowedCategories();
            $blogs = Blog::where('category_id', $allowed_categories)->orderBy('created_at', 'desc')
            ->where('title', 'LIKE', '%' . $title . '%')
            ->where('content', 'LIKE', '%' . $content . '%')
            ->get();
        }       
        return response()->json($blogs);
    }   
}
