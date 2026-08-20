<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * عرض جميع عناوين المستخدم
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    /**
     * إضافة عنوان جديد
     */
    public function store(AddressRequest $request)
    {
        $validator = $request->validated();

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // إذا كان العنوان جديداً سيكون افتراضي، نزيل العلامة من العناوين الأخرى
        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validator);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة العنوان بنجاح',
            'data' => $address
        ], 201);
    }

    /**
     * عرض عنوان محدد
     */
    public function show($id)
    {
        $user = Auth::user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'العنوان غير موجود'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    /**
     * تحديث عنوان
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'العنوان غير موجود'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:50',
            'area' => 'required|string|max:100',
            'street' => 'required|string|max:100',
            'building_number' => 'nullable|string|max:20',
            'floor' => 'nullable|string|max:20',
            'apartment' => 'nullable|string|max:20',
            'additional_info' => 'nullable|string|max:255',
            'is_default' => 'boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // إذا تم تعيين العنوان كافتراضي، نزيل العلامة من الآخرين
        if ($request->is_default && !$address->is_default) {
            $user->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث العنوان بنجاح',
            'data' => $address
        ]);
    }

    /**
     * حذف عنوان
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'العنوان غير موجود'
            ], 404);
        }

        // إذا كان العنوان افتراضياً، نجعل أول عنوان آخر افتراضياً
        if ($address->is_default) {
            $otherAddress = $user->addresses()
                ->where('id', '!=', $id)
                ->first();

            if ($otherAddress) {
                $otherAddress->update(['is_default' => true]);
            }
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العنوان بنجاح'
        ]);
    }
}
