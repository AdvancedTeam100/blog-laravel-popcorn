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

        // return response()->json([ "message" => json_decode($request->allowed_categories)]);

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

        $group = Group::create([
            'name' => $request->name,
            'user_id' => ''
        ]);

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
                        'name' => $request->name,
                        'read_name' => $request->read_name ?: '',
                        'status' => $request->status ?: '',
                        'birthday' => $request->birthday ?: '1991-03-30',
                        'phone_number' => $request->phone_number ?: '',
                        'memo' => $request->memo ?: '',
                        'phone_device' => $request->phone_device ?: '1',
                        'ninetieth_life' => $request->ninetieth_life ?: '0' ,
                        'work_life' => $request->work_life ?: '0' ,
                        'die_life' => $request->die_life ?: '0',
                        'healthy_life' => $request->healthy_life ?: '0',
                        'avatar' => $avatarFileName,                        
                        'average_life' => $request->average_life ?: '0' ,
                        'group_id' => $group->id ?:'',
                        'role_id' => '2',                        
                        'allowed_categories' => $request->allowed_categories ?: '',
                    ]
                ));
        $group->user_id = $user->id;
        $group->update();

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


        $leader = User::find($id);

        if (!$leader) {
            return response()->json(['message' => 'グループリーダーが存在しない'], 404);
        }

        $avatarFileName = '';   
        if($request->file('avatar')) {
            $avatarFileName = time().'.'.$request->file('avatar')->extension();
            $request->file('avatar')->move(public_path('upload/images'), $avatarFileName);
        }   

        $leader['user_id'] = $request->user_id;
        $leader['parent_id'] = auth()->user()->id;
        $leader['name'] = $request->name? : '';
        // $leader['password'] = bcrypt($request->password);
        $leader['read_name'] = $request->read_name ?: '';
        $leader['status'] = $request->status ?: '';
        $leader['birthday'] = $request->birthday ?: '1992-06-24';
        $leader['phone_number'] = $request->phone_number ?: '';
        $leader['memo'] = $request->memo ?:'';
        $leader['phone_device'] = $request->phone_device ?: '1';
        $leader['ninetieth_life'] = $request->ninetieth_life ?: '0';
        $leader['work_life'] = $request->work_life ?: '0' ;
        $leader['die_life'] = $request->die_life ?: '0';
        $leader['healthy_life'] = $request->healthy_life ?: '0';
        $leader['average_life'] = $request->average_life ?: '0';
        $leader['allowed_categories'] = $request->allowed_categories ?: '';

        if($avatarFileName != '') {
            $leader['avatar'] = $avatarFileName;
        }
        // $leader->update($updatedData);
        $leader->update();

        return response()->json([
            'message' => 'チームリーダーが正常に更新されました'
        ], 201); 
    }

}
