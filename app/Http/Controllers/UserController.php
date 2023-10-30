<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Category;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 私のチームのすべてのユーザーを取得します。
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers() 
    {
        if(auth()->user()->role_id == 1) {
            $users = User::with('group')->where('role_id', '!=', 1)->get();
        } else {
            $users = User::with('group')->where('group_id', auth()->user()->group_id)->where('role_id', 3)->get();
        }
        return response()->json($users);
    }

    public function getGroupUsers() 
    {
        $users = User::where('group_id', auth()->user()->group_id)->get();
        return response()->json($users);
    }

    public function getUserById($id) 
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'ユーザーが存在しません'], 404);
        }

        if(auth()->user()->role_id  == '3') {
            return response()->json(['message' => 'このユーザーにはアクセスできません'], 400);
        } else if(auth()->user()->role_id  == '3') {
            if($user->group_id != auth()->user()->group_id){
                return response()->json(['message' => 'このユーザーにはアクセスできません'], 400);
            }
        }
        

        return response()->json($user);
    }

    /**
     * 新しいユーザーを MyTeam に登録する
     * 
     *  @return \Illuminate\Http\JsonResponse
     */
    public function addUser(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|max:100|unique:users',
            'user_id' => 'required|string|unique:users',
            'password' => 'required|string',
            'avatar' => 'nullable|mimes:jpeg,png',
            'birthday' => 'date_format:Y-m-d',
        ], [
            'user_id.unique' => 'このユーザー ID はすでに取得されています。',
            'email.unique' => 'このメールはすでに受信されています。',
            'avatar.mimes' => 'アバターは JPEG または PNG 画像である必要があります。',
            'birthday.date_format' => '誕生日は YYYY-MM-DD 形式である必要があります。'
        ]);
        
        if ($validator->fails()) {
            return response()->json(["message" =>  $validator->errors()], 400);
        }
        
        if(auth()->user()->role_id == 1 && !$request->group_id) {
            return response()->json(['message' => "グループを正確に選択してください。"], 400);
        }
        
        $avatarFileName = '';   
        if($request->file('avatar')) {
            $avatarFileName = time().'.'.$request->file('avatar')->extension();
            $request->file('avatar')->move(public_path('upload/images'), $avatarFileName);
        }
        $user = User::create(array_merge(
                    $validator->validated(),
                    [   'user_id' => $request->user_id,
                        'parent_id' => auth()->user()->id,
                        'password' => bcrypt($request->password),
                        'read_name' => $request->read_name ?: '',
                        'status' => $request->status ?: '',
                        'birthday' => $request->birthday ?: '1990-01-01',
                        'phone_number' => $request->phone_number ?: '',
                        'memo' => $request->memo ?: '',
                        'phone_device' => $request->phone_device ?: '1',
                        'ninetieth_life' => $request->ninetieth_life ?: '0',
                        'work_life' => $request->work_life ?: '0',
                        'die_life' => $request->die_life ?: '0',
                        'healthy_life' => $request->healthy_life ?: '0',
                        'average_life' => $request->average_life ?: '0',
                        'group_id' => auth()->user()->role_id == 1 ? $request->group_id : auth()->user()->group_id,
                        'role_id' => '3',
                        'avatar' => $avatarFileName,         
                        'allowed_categories' => $request->allowed_categories ?: '',               
                    ]
                ));

        return response()->json([
            'message' => 'ユーザーが正常に登録されました',
            'user' => $user
        ], 201); 
    }

    /**
     * MyTeam からユーザーを削除します。
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser($id) 
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'ユーザーが存在しません'], 404);
        }

        if(auth()->user()->role_id != 1 && $user->group_id != auth()->user()->group_id) 
        {
            return response()->json([
                'message' => "このユーザーはあなたのグループに属していません。"
            ], 400);
        }

        $user->delete();


        if(auth()->user()->role_id == 1) {
            $users = User::with('group')->where('role_id', '!=', 1)->get();
        } else {
            $users = User::with('group')->where('group_id', auth()->user()->group_id)->where('role_id', 3)->get();
        }

        return response()->json([
            'message' => 'ユーザーは正常に削除されました',
            'users' => $users
        ], 201); 

    } 

    /**     
     * SuperAdminによるユーザーの削除
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'user_id' => 'required|string',
            'avatar' => 'nullable|mimes:jpeg,png',
            'birthday' => 'date_format:Y-m-d',
        ], [
            'user_id.unique' => 'このユーザー ID はすでに取得されています。',
            'email.unique' => 'このメールはすでに受信されています。',
            'avatar.mimes' => 'アバターは JPEG または PNG 画像である必要があります。',
            'birthday.date_format' => '誕生日は YYYY-MM-DD 形式である必要があります。'
        ]);
        
        
        if ($validator->fails()) {
            return response()->json( ["message" =>  $validator->errors()], 400);
        }

        //exist
        $user = User::find($id);
        if (!$user) 
        {
            return response()->json(['message' => 'ユーザーが存在しません'], 404);
        }

        if(auth()->user()->role_id != 1 && auth()->user()->group_id != $user->group_id) 
        {
            return response()->json([
                'message' => "このユーザーはあなたのグループに属していません。"
            ], 400);
        }

        if(auth()->user()->role_id == 1 && !$request->group_id) {
                return response()->json(['message' => "グループを正確に選択してください。"], 400);
        }

        $avatarFileName = '';   
        if($request->file('avatar')) {
            $avatarFileName = time().'.'.$request->file('avatar')->extension();
            $request->file('avatar')->move(public_path('upload/images'), $avatarFileName);
        }

        // $updatedData = $request->all();
        
        $user['user_id'] = $request->user_id;
        $user['parent_id'] = auth()->user()->id;
        // $user['password'] = bcrypt($request->password);
        $user['read_name'] = $request->read_name ?: '';
        $user['status'] = $request->status ?: '1';
        $user['birthday'] = $request->birthday ?: '1992-02-27';
        $user['phone_number'] = $request->phone_number ?: '';
        $user['memo'] = $request->memo ?: '';
        $user['phone_device'] = $request->phone_device ?: '1';
        $user['ninetieth_life'] = $request->ninetieth_life ?: '0';
        $user['work_life'] = $request->work_life ?: '0';
        $user['die_life'] = $request->die_life ?: '0';
        $user['healthy_life'] = $request->healthy_life ?: '0';
        $user['average_life'] = $request->average_life ?: '0';
        if($avatarFileName != '') {
            $user['avatar'] = $avatarFileName;
        }
        if(auth()->user()->role_id == 1 && $request->role_id) {
            $user['role_id'] = $request->role_id ?: '';
        } 
        if(auth()->user()->role_id == 1 && $request->group_id) {
            $user['group_id'] = $request->group_id ?: '';
         }
         $user['allowed_categories'] = $request->allowed_categories ?: '';

        // $user->update($updatedData);
        $user->update();

        return response()->json([   
            'message' => 'ユーザーは正常に更新されました',
            'user' => $user
        ], 201); 
    }

    // public function getAllCategories() {
    //     $groups = Group::all();
    //     foreach ($groups as $key => $value) {
            
    //     }
    // }

    public function getCategoriesForUser() {
        $common_group = Group::find(1);
        $common_group_categories = $common_group->categories()->get() ? $common_group->categories()->get() : [];

        $mygroup_categories = [];
        $role = auth()->user()->role_id;
        if($role == 2) {
            $mygroup = Group::find(auth()->user()->group_id);
            $mygroup_categories = $mygroup ? $mygroup->categories()->get() : [];
        }   

        if($role == 1) {
            $mygroup_categories = Category::all();
        }

        return response()->json([
            'common_group_categories' => $common_group_categories,
            'mygroup_categories' => $mygroup_categories
        ]);
    }

    public function getAllGroups () {
        $groups = Group::all();
        
        return response()->json($groups);
    }


    
}



