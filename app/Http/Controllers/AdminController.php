<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api');
    } 
    
    public function getLeaders() {
        $leaders = User::where('role_id',2)->orderBy('id', 'asc')->get();
        return response()->json($leaders);
    } 

    public function getLeaderById($id)
    {
        $leader = User::find($id);

        if (!$leader) {
            return response()->json(['message' => 'グループリーダーが存在しない'], 404);
        }

        if ($leader->role_id != '2') 
        {
            return response()->json(['message' => 'このIDのユーザーはグループリーダーではありません。'], 400);
        }

        return response()->json($leader);
    }

    /**
     * SuperAdmin による新しいチームリーダーの登録
     * 
     *  @return \Illuminate\Http\JsonResponse
     */
    public function addLeader(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users',
            'user_id' => 'required|string|between:2,100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
             return response()->json($validator->errors(), 400);
        }
        $group = Group::create([
            'name' => $request->user_id
        ]);

        $common1_permission = $request->common1_permission;
        $user = User::create(array_merge(
                    $validator->validated(),
                    [   'user_id' => $request->user_id,
                        'password' => bcrypt($request->password),
                        'name' => $request->name,
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
                        'group_id' => $group->id,
                        'role_id' => '2',                        
                        'common1_permission' => json_encode($common1_permission),
                    ]
                ));
        

        return response()->json([
            'message' => 'チームリーダーが登録されました',
            'user' => $user
        ], 201); 
    }

    /**
     * SuperAdminによるユーザーの削除
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLeader($id) {

        $leader = User::find($id);

        if (!$leader) {
            return response()->json(['message' => 'グループリーダーが存在しない'], 404);
        }

        $leader->delete();
        return response()->json([
            'message' => 'チームリーダーは正常に削除されました',
        ], 201); 
    } 

    /**
     * SuperAdmin によるユーザーの更新
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLeader(Request $request, $id) {

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

        $leader = User::find($id);

        if (!$leader) {
            return response()->json(['message' => 'グループリーダーが存在しない'], 404);
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
        $updatedData['common1_permission'] = json_encode($request->common1_permission);

        $user->update($updatedData);

        return response()->json([
            'message' => 'チームリーダーが正常に更新されました',
            'user' => $user
        ], 201); 
    }

}
