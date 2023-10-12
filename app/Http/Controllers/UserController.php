<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Category;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
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
     * Get All User in My Team.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUser() {
        $users = User::where('group_id', auth()->user()->group_id)->get();
        return response()->json($users);
    }

    /**
     * Register new user into MyTeam
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
                        'group_id' => auth()->user()->group_id,
                        'role_id' => '1',                        
                        'common1_permission' => json_encode($request->common1_permission),
                        'mygroup_permission' => json_encode($request->mygroup_permission),
                    ]
                ));
        
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201); 
    }

    /**
     * Delete User from MyTeam.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request) {
        $user = User::findOrFail($request->id);
        if($user->group_id == auth()->user()->group_id) {
            $user->delete();
            return response()->json([
                'message' => 'User successfully deleted',
            ], 201); 
        } else {
            return response()->json([
                'error' => "This user is not in your team."
            ], 403);
        }
    } 

    /**
     * Delete Users by SuperAdmin
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

        if(auth()->user()->group_id != $user->group_id) {
            return response()->json([
                'error' => "This user is not in your team."
            ], 403);
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
        $updatedData['role_id'] = $request->role_id;
        $updatedData['common1_permission'] = json_encode($request->common1_permission);
        $updatedData['mygroup_permission'] = json_encode($request->mygroup_permission);

        $user->update($updatedData);

        return response()->json([
            'message' => 'User successfully Updated',
            'user' => $user
        ], 201); 
    }




    

}
