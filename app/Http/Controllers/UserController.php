<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Users;
use GuzzleHttp\Client;

class UserController extends Controller
{
	public function __construct()
	{
	}

    public function all()
    {
        return response()->json(Users::all());
    }

    public function get($id)
    {
        return response()->json(Users::find($id));
    }

    public function create(Request $request)
    {
		$this->validate($request, [
			'firstname'=>'required',
			'lastname'=>'required',
			'email' => 'required|email|unique:users',
			'password' => 'required|min:6'
		]);

        $activation_code=base64_encode(str_random(40));
        $user = Users::create($request->all());
        $user=Users::findOrFail($user->id);
        $user->password=Hash::make($request->input('password'));
        $user->activation_code=$activation_code;
        $user->save();
		$mail=$request->input('email');

        $client = new Client();
        $response = $client->request('GET', env('SERVICE_ADDRESS_CONTENT').'/mails/activation/'.$mail.'/'.$activation_code, ['headers' => ['Authorization'=>'Bearer '.env('SERVICE_AUTHKEY')]]);

        return response()->json($user, 201);
    }

	public function authenticate(Request $request)
	{
		$this->validate($request, [
			'email' => 'required',
			'password' => 'required'
		]);
		$user = Users::where('email', $request->input('email'))->first();
		if(!$user->where('activation_code', '')){
			return response()->json(['status' => 'ActivationRequired'], 401);
		}elseif(Hash::check($request->input('password'), $user->password)){
			$apikey = base64_encode(str_random(40));
			Users::where('email', $request->input('email'))->update(['api_key' => "$apikey"]);;
			return response()->json(['status' => 'success', 'api_key' => $apikey], 200);
		}else{
			return response()->json(['status' => 'fail'], 401);
		}
	}

	public function testToken($id)
	{
		if(env("SERVICE_AUTHKEY")==$id){
			return response()->json('', 200);
		}else{
			$user = Users::where('api_key', $id)->first();
			if($user!=null){
				return response()->json($user, 200);
			}else{
				return response()->json(['status' => 'fail'], 401);
			}
		}
	}

	public function activate($id)
	{
		$user = Users::where('activation_code', $id)->first();
		if($user!=null){
			$user->activation_code='';
			$user->save();
	        return response()->json($user, 200);
		}else{
			return response()->json(['status' => 'fail'], 401);
		}
	}
}