<?php

namespace App\Http\Controllers;

use App\Models\UsersWithExtraAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
//use Dotenv\Validator;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Helpers\ApiCode;


class AuthController extends Controller {

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    private function getLoginData($validated) {

        if (! $token = auth()->attempt($validated)) {
            return response()->json(['response_type' => 'warning_message', 'message' => 'no user by login', 'data' => 'no user by login']);
        }

        $userData = $this->createNewToken($token);

        $userDataObject = $userData->getData();

        $specialAccess = false;

        if ($userDataObject) {
            if (property_exists($userDataObject, 'user') && property_exists($userDataObject->user, 'id')) {
                $userId = $userDataObject->user->id;

                $UsersWithExtraAccess = new UsersWithExtraAccess();
                $isExtraUser = $UsersWithExtraAccess->select('*')->where('user_id', '=', $userId)->count();

                if (!empty($isExtraUser)) {
                    $specialAccess = true;
                }
            }
        }

        $userDataArray =  (array) $userDataObject;
        $userDataArray['special_access'] = $specialAccess;
        $userDataArray['user'] = (array) $userDataArray['user'];

        return RB::success(['data' => $userDataArray]);

//        return response()->json(['response_type' => 'ok', 'data' => $userDataObject]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();

        return $this->getLoginData($validated);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|between:2,100',
            'password' => 'required|string|confirmed|min:5',
        ]);

        if($validator->fails()){
            return ['response_type' => 'warning_message', 'message' => 'validation fail', 'data' => $validator->errors()];
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        $validated = $validator->validated();

        return $this->getLoginData($validated);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout(true);

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 2400,
            'user' => auth()->user()
        ]);
    }

}
