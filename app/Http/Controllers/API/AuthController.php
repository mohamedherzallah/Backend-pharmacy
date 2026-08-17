<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;
use App\Models\Pharmacy;


class AuthController extends Controller
{
    // سجل مستخدم (user أو pharmacy عبر role)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'phone' => 'nullable|string',
        ]);

        // SECURITY: role is intentionally NOT accepted from the request.
        // Previously this endpoint accepted role=admin from any anonymous
        // caller, letting anyone create an admin account. Public
        // self-registration can only ever create a plain 'user' account;
        // pharmacy accounts are created via the dedicated
        // /auth/pharmacy/register endpoint, and admin accounts must be
        // created some other way (seeder / trusted internal tooling).
        $user = User::create([
            'name' => $request->name,
            'email'=> $request->email,
            'phone'=> $request->phone,
            'role'=> 'user',
            'password' => Hash::make($request->password),
        ]);

        $otp = rand(100000, 999999);

        OtpCode::create([
            'user_id' => $user->id,
            'code' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ], 201);
    }

    public function registerPharmacy(Request $request)
    {
        // التحقق من بيانات المستخدم فقط
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'phone' => 'required|string|unique:users,phone',
        ]);

        // 1) إنشاء المستخدم بدور pharmacy
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'pharmacy',
            'password' => Hash::make($request->password),
        ]);

        // 2) إنشاء سجل صيدلية فارغ سيتم استكماله لاحقاً
        $pharmacy = Pharmacy::create([
            'user_id' => $user->id,
            'pharmacy_name' => $request->pharmacy_name,
            'address' => $request->address,
            'latitude' => null,
            'longitude' => null,
            'license_image' => null,
            'logo' => null,
            'is_approved' => false,
        ]);

        // 3) توليد التوكن
        $token = $user->createToken('pharmacy-token')->plainTextToken;

        return response()->json([
            'message' => 'Pharmacy registered successfully. Complete your profile.',
            'user' => new UserResource($user),
            'pharmacy' => $pharmacy,
            'token' => $token,
        ], 201);
    }


    // تسجيل دخول
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // احذف توكنات سابقة (اختياري)
        // $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }

    // تسجيل خروج
    public function logout(Request $request)
    {
        $user = $request->user();
        // احذف التوكن الحالي
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Reset link sent to your email',
            ]);
        }

        return response()->json([
            'message' => 'Failed to send reset link',
        ], 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully']);
        }

        return response()->json(['message' => 'Invalid token'], 400);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
            // حقل جديد_password_confirmation لتأكيد كلمة المرور
        ]);

        $user = $request->user(); // المستخدم المسجّل الدخول

        // التحقق من كلمة المرور الحالية
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'كلمة المرور الحالية غير صحيحة'
            ], 403);
        }

        // تحديث كلمة المرور الجديدة
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required',
        ]);

        $otp = OtpCode::where('user_id', $request->user_id)
            ->where('code', $request->otp_code)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json([
                'message' => 'الكود غير صحيح أو منتهي'
            ], 422);
        }

        $user = User::find($request->user_id);

        // تفعيل الحساب
        $user->update([
            'is_verified' => true // ← تحتاج هذا العمود فقط
        ]);

        // حذف كل الأكواد القديمة
        OtpCode::where('user_id', $user->id)->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'تم تفعيل الحساب',
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }
}
