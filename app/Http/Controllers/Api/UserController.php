<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserBasicResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Mail\ResetPassword;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Enum\UserAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /***
     * Generate Identity
     * @param No params
     * @return unique Identity
     */

    private function getID($name)
    {
        // Query the database to find the user by email
        $user = User::where('name', $name)->first();

        // Check if the user exists
        if ($user) {
            // Return the user ID
            return $user->id; //response()->json(['id' => $user->id]);
        } else {
            // Return a not found response
            return response()->json(['message' => 'User not exist'], 404);
        }
    }
    
    
    /***
     * Generate Identity
     * @param No params
     * @return unique Identity
     */
    private function generateIdentity()
    {
        $randomNumber = random_int(10000000000000, 99999999999999);
        if (User::where('identity', '=', $randomNumber)->exists()) {
            return $this->generateIdentity();
         }
         else{
            return $randomNumber;
         }
    }

      /***
     * Generate Identity
     * @param No params
     * @return unique Identity
     */
    private function generateUser()
    {
        $randomNumber = random_int(100000, 999999);
        if (User::where('name', '=', $randomNumber)->exists()) {
            return $this->generateIdentity();
         }
         else{
            return $randomNumber;
         }
    }
    private function isValidTimezoneId($usertimezone) {
        try{
            new \DateTimeZone($usertimezone);
            return true;
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Wrong Timezone, please try again'
            ], 500);
        }
        return true;
    }
    private function getTimeZone($getZome)
    {
        $usertimezone="Africa/Lagos"; 

        date_default_timezone_set($usertimezone); 

        //new date and time
        $ndate= new datetime();
        //split into date and time seperate
        $nndate =$ndate->format("Y-m-d");
        $nntime= $ndate->format("H:i:S");
        //here you can test it
        echo $nndate.'<br/>';
        echo $nntime;

    }
    /***
     * Create User
     * @param Request $request
     * @return User
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'firstName' => '',
                    'lastName' => '',
                    'phone' => '',
                    'department_id' => '',
                    'joined' => 'required',
                    'hasManager' => '',
                    'gmt' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $this->isValidTimezoneId($request->gmt);

            if($request->manager_id)
            {
                $user = User::create([
                    'name' => $this->generateUser(),
                    'email' => $request->email,
                    'firstName' => $request->firstname,
                    'lastName' => $request->lastname,
                    'phone' => $request->phone,
                    'department_id' => $request->department_id,
                    'identity' => $this->generateIdentity(),
                    'isStaff' => 1,
                    'gmt' => $request->gmt,
                    'status' => 'approved',
                    'hasManager' => $request->hasManager,
                    'joined' => $request->joined,
                    'manager_id' => $this->getID($request->manager_id),
                    'password' => Hash::make($request->password)
                ]);
            }
            else{
                $user = User::create([
                    'name' => $this->generateUser(),
                    'email' => $request->email,
                    'firstName' => $request->firstname,
                    'lastName' => $request->lastname,
                    'phone' => $request->phone,
                    'identity' => $this->generateIdentity(),
                    'isStaff' => 1,
                    'gmt' => $request->gmt,
                    'status' => 'approved',
                    'department_id' => $request->department_id,
                    'hasManager' => $request->hasManager,
                    'joined' => $request->joined,
                    'password' => Hash::make($request->password)
                ]);
            }


            $userGroup = UserGroup::create([
                'user_id' => $user->id,
                'group_id' => '1'
            ]);

            // Send email to new user
            event(new Registered($user));

            //$accessToken = $user->createToken('access_token', [UserATokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            //$refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                //'token' => $user->createToken("API TOKEN")->plainTextToken,
                //'token' => $accessToken->plainTextToken,
                //'refresh_token' => $refreshToken->plainTextToken,
                //'group_id' => $group_id,
                //'gmt' => Auth::user()->gmt
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        /*
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $token =  Auth::guard('api')->attempt($credentials); //
        $token2 = Auth::attempt($credentials);
        $refreshToken = JWTAuth::refresh($token2);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user()->gmt;
        return response()->json([
            'user' => Auth::user()->created_at,
            'authorization' => [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'type' => 'bearer',
            ]
        ]);
        */
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
                
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $group = UserGroup::where('user_id', Auth::id())->get();

        $manager = User::where('manager_id', Auth::id())->exists();

        foreach($group as $groups)
        {
            $group_id = $groups->group_id;
        }
        return response()->json([
            'token' => $token,
            'refresh_token' => $this->createRefreshToken($token),
            'group_id' => $group_id,
            'isManager' => $manager
        ]);

    }

    public function refresh()
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }
            $newToken = JWTAuth::refresh($token);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }

        return response()->json(['token' => $newToken]);
    }

    private function createRefreshToken($token)
    {
        // Here you can store the refresh token in the database or another secure storage
        // For simplicity, we'll return the same token as the refresh token
        return $token;
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResource
    {
        auth()->user()->tokens()->delete();

        return response()->success([], 'logged out', 200);
    }

    /**
     * @return JsonResponse
     */
    public function signout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $exception) {
            return response()->json(['error' => 'Failed to logout, please try again.'], 500);
        }
    }
 
    /**
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['success' => false, 'message' => $validator->errors()], 422);
        }

        $verify = User::where('email', $request->all()['email'])->exists();

        if ($verify) {
            $verify2 = DB::table('password_resets')->where([
                ['email', $request->all()['email']]
            ]);

            if ($verify2->exists()) {
                $verify2->delete();
            }

            $token =  random_int(100000, 999999);
            $password_reset = DB::table('password_resets')->insert([
                'email' => $request->all()['email'],
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            if ($password_reset) {
                Mail::to($request->all()['email'])->send(new ResetPassword($token));

                return new JsonResponse(
                    [
                        'success' => true,
                        'message' => "Please check your email for a 6 digit pin"
                    ],
                    200
                );
            } else {
                return new JsonResponse(
                    [
                        'success' => false,
                        'message' => "This email does not exist"
                    ],
                    400
                );
            }
        }
    }

    /**
     * @param VerifyPin
     * @return JsonResponse
     * @throws ValidationException
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'token' => ['required'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['success' => false, 'message' => $validator->errors()], 422);
        }

        $check = DB::table('password_resets')->where([
            ['email', $request->all()['email']],
            ['token', $request->all()['token']],
        ]);

        if ($check->exists()) {
            $difference = Carbon::now()->diffInSeconds($check->first()->created_at);
            if ($difference > 3600) {
                return new JsonResponse(['success' => false, 'message' => "Token Expired"], 400);
            }
            $delete = DB::table('password_resets')->where([
                ['email', $request->all()['email']],
                ['token', $request->all()['token']],
            ])->delete();

            $user = User::where('email', $request->email);
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            $token = $user->first()->createToken('myapptoken')->plainTextToken;

            return new JsonResponse(
                [
                    'success' => true,
                    'message' => "Your password has been reset",
                    'token' => $token
                ],
                200
            );

            /*
            return new JsonResponse(
                [
                    'success' => true,
                    'message' => "You can now reset your password"
                ],
                200
            );*/
        } else {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => "Invalid token"
                ],
                401
            );
        }
    }


    /**
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/auth/list-users",
     *     summary="Get list of users",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Users not found"
     *     )
     * )
     */
    public function listUsers(Request $request)
    {
        /*
        $user = $request->user();
        if ($user->isAdmin == false) {
            return abort(403, 'Unauthorized action.');
        }*/
        return UserResource::collection(User::paginate(10));
    }

    public function listUserBasic(Request $request)
    {
        /*
        $user = $request->user();
        if ($user->isAdmin == false) {
            return abort(403, 'Unauthorized action.');
        }*/
        return UserBasicResource::collection(User::paginate(10));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {       // If the check passes, return the leave resource
        return new UserResource($user);
    }

    /**
     * Display the user profile.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function profile()
     {
        //$query = User::where('id', Auth::id())->get();
        return new UserResource(User::where('id', Auth::user()->id)->first());
     }

     /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function adminCreateUser()
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'firstName' => '',
                    'lastName' => '',
                    'phone' => '',
                    'department_id' => '',
                    'joined' => 'required',
                    'hasManager' => '',
                    'gmt' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $this->isValidTimezoneId($request->gmt);

            if($request->manager_id)
            {
                $user = User::create([
                    'name' => $this->generateUser(),
                    'email' => $request->email,
                    'firstName' => $request->firstname,
                    'lastName' => $request->lastname,
                    'phone' => $request->phone,
                    'department_id' => $request->department_id,
                    'identity' => $this->generateIdentity(),
                    'isStaff' => 1,
                    'gmt' => $request->gmt,
                    'status' => 'approved',
                    'hasManager' => $request->hasManager,
                    'joined' => $request->joined,
                    'manager_id' => $this->getID($request->manager_id),
                    'password' => Hash::make($request->password)
                ]);
            }
            else{
                $user = User::create([
                    'name' => $this->generateUser(),
                    'email' => $request->email,
                    'firstName' => $request->firstname,
                    'lastName' => $request->lastname,
                    'phone' => $request->phone,
                    'identity' => $this->generateIdentity(),
                    'isStaff' => 1,
                    'gmt' => $request->gmt,
                    'status' => 'approved',
                    'department_id' => $request->department_id,
                    'hasManager' => $request->hasManager,
                    'joined' => $request->joined,
                    'password' => Hash::make($request->password)
                ]);
            }


            $userGroup = UserGroup::create([
                'user_id' => $user->id,
                'group_id' => '1'
            ]);

            // Send email to new user
            event(new Registered($user));

            //$accessToken = $user->createToken('access_token', [UserATokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            //$refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update User
     * @param Request $request, int  $id
     * @return User
     */
    public function adminUpdateUser(Request $request, $id)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'firstName' => '',
                    'lastName' => '',
                    'phone' => '',
                    'department_id' => '',
                    'joined' => 'required',
                    'hasManager' => '',
                    'gmt' => 'required',
                    'status' => 'required',
                    'email' => 'required|email|unique:users,email,' . $id,
                    'password' => ''
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::findOrFail($id);

            $user->update([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'joined' => $request->joined,
                'hasManager' => $request->hasManager,
                'gmt' => $request->gmt,
                'email' => $request->email,
                'status' => 'required',
                'password' => $request->password ? Hash::make($request->password) : $user->password
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function adminPatchUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $user->update($request->only([
                'firstName',
                'lastName',
                'phone',
                'department_id',
                'joined',
                'hasManager',
                'gmt',
                'email',
                'status',
                'password'
            ]));

            if ($request->password) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'User partially updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function adminDeleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
