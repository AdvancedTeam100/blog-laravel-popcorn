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

    /**
     * すべてのチームリーダーを取得
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAdmin() {
        $users = User::where('role_id',2)->orderBy('id', 'asc')->get();
        return response()->json($users);
    }

    /**
     * SuperAdmin による新しいチームリーダーの登録
     * 
     *  @return \Illuminate\Http\JsonResponse
     */
    public function registerAdmin(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'user_id' => 'required|string|between:2,100',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
             return response()->json($validator->errors(), 400);
        }

        $group = Group::create([
            'name' => $request->user_id
        ]);

        $categories = Category::all();
        $category_ids = [];
        foreach ($categories as $key => $category) {
            $category_ids[] = $category->id;
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
                        'group_id' => $group->id,
                        'role_id' => '2',                        
                        'common1_permission' => json_encode($category_ids),
                        'common2_permission' => json_encode($request->common2_permission),
                        'mygroup_permission' => json_encode($category_ids),
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
    public function deleteAdmin(Request $request) {
        $user = User::findOrFail($request->id);
        $user->delete();

        return response()->json([
            'message' => 'チームリーダーは正常に削除されました',
        ], 201); 
    } 

    /**
     * SuperAdmin によるユーザーの更新
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAdmin(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'user_id' => 'required|string|between:2,100',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
             return response()->json($validator->errors(), 400);
        }

        $user = User::findOrFail($id);
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
        // $updatedData['group_id'] = $request->group_id;
        // $updatedData['role_id'] = $request->role_id;
        // $updatedData['common1_permission'] = json_encode($request->common1_permission);
        $updatedData['common2_permission'] = json_encode($request->common2_permission);
        // $updatedData['mygroup_permission'] = json_encode($request->mygroup_permission);

        $user->update($updatedData);

        return response()->json([
            'message' => 'チームリーダーが正常に更新されました',
            'user' => $user
        ], 201); 
    }

        /**
     * チームリーダーごとにすべてのユーザーを取得
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUser() {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * SuperAdminによる新規ユーザーの登録
     * 
     *  @return \Illuminate\Http\JsonResponse
     */
    public function registerUser(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'user_id' => 'required|string|between:2,100',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
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
                        'group_id' => $request->group_id,
                        'role_id' => $request->role_id,                        
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
     * スーパー管理者によるユーザーの削除
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request) {
        $user = User::findOrFail($request->id);
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
    public function updateUser(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'user_id' => 'required|string|between:2,100',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
             return response()->json($validator->errors(), 400);
        }

        $user = User::findOrFail($id);
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
        $updatedData['group_id'] = $request->group_id;
        $updatedData['role_id'] = $request->role_id;
        $updatedData['common1_permission'] = json_encode($request->common1_permission);
        $updatedData['mygroup_permission'] = json_encode($request->mygroup_permission);

        $user->update($updatedData);


        return response()->json([
            'message' => 'ユーザーは正常に更新されました',
            'user' => $user
        ], 201); 
    }
}
