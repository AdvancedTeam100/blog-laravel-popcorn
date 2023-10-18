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
        $users = User::where('group_id', auth()->user()->group_id)->get();
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
            return response()->json(['error' => 'ユーザーが存在しません'], 404);
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
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'user_id' => 'required|string|between:2,100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails())
        {
             return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    [   'user_id' => $request->user_id,
                        'password' => bcrypt($request->password),
                        'read_name' => $request->read_name,
                        'status' => $request->status,
                        'birthday' => $request->birthday,
                        'phone_number' => $request->phone_number,
                        'memo' => $request->memo,
                        'phone_device' => $request->phone_device,
                        'ninetieth_life' => $request->ninetieth_life,
                        'work_life' => $request->work_life,
                        'die_life' => $request->die_life,
                        'healthy_life' => $request->healthy_life,
                        'average_life' => $request->average_life,
                        'group_id' => auth()->user()->role_id == 1 ? $request->group_id : auth()->user()->group_id,
                        'role_id' => '3',                        
                        'common1_permission' => json_encode($request->common1_permission),
                        'mygroup_permission' => json_encode($request->mygroup_permission),
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
            return response()->json(['error' => 'ユーザーが存在しません'], 404);
        }

        if(auth()->user()->role_id != 1 && $user->group_id != auth()->user()->group_id) 
        {
            return response()->json([
                'error' => "このユーザーはあなたのグループに属していません。"
            ], 406);
        }

        $user->delete();
        return response()->json([
            'message' => 'ユーザーは正常に削除されました',
        ], 201); 

    } 

    /**
     * SuperAdminによるユーザーの削除
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users')->ignore($id),
            ],
            'user_id' => [
                'required',
                'string',
                'between:2,100',
                Rule::unique('users')->ignore($id),
            ],
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //exist
        $user = User::find($id);
        if (!$user) 
        {
            return response()->json(['error' => 'ユーザーが存在しません'], 404);
        }

        if(auth()->user()->role_id != 1 && auth()->user()->group_id != $user->group_id) 
        {
            return response()->json([
                'error' => "このユーザーはあなたのグループに属していません。"
            ], 406);
        }

        $updatedData = $request->all();
        $updatedData['user_id'] = $request->user_id;
        $updatedData['password'] = bcrypt($request->password);
        $updatedData['read_name'] = $request->read_name;
        $updatedData['status'] = $request->status;
        $updatedData['birthday'] = $request->birthday;
        $updatedData['phone_number'] = $request->phone_number;
        $updatedData['memo'] = $request->memo;
        $updatedData['phone_device'] = $request->phone_device;
        $updatedData['ninetieth_life'] = $request->ninetieth_life;
        $updatedData['work_life'] = $request->work_life;
        $updatedData['die_life'] = $request->die_life;
        $updatedData['healthy_life'] = $request->healthy_life;
        $updatedData['average_life'] = $request->average_life;
        $updatedData['role_id'] = auth()->user()->role_id == 1 ? $request->role_id : '3';
        $updatedData['group_id'] = auth()->user()->role_id == 1 ? $request->group_id : auth()->user()->group_id;
        $updatedData['common1_permission'] = json_encode($request->common1_permission);
        $updatedData['mygroup_permission'] = json_encode($request->mygroup_permission);

        $user->update($updatedData);

        return response()->json([
            'message' => 'ユーザーは正常に更新されました',
            'user' => $user
        ], 201); 
    }
}
