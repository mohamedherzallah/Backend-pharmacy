<?php
// app/Http/Controllers/API/FavoriteController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * عرض جميع مفضلات المستخدم
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مسجل الدخول'
                ], 401);
            }

            // جلب المفضلات مع معلومات الدواء
            $favorites = $user->favorites()
                ->with(['medicine.category']) // أزلنا medicine.pharmacy لأنها غير موجودة
                ->orderBy('created_at', 'desc')
                ->get();

            // تنسيق البيانات
            $formattedFavorites = $favorites->map(function ($favorite) {
                // الحصول على أول صيدلية للدواء (إذا وجدت)
                $firstPharmacy = $favorite->medicine->pharmacies->first();

                return [
                    'id' => $favorite->id,
                    'medicine' => [
                        'id' => $favorite->medicine->id,
                        'name' => $favorite->medicine->name,
                        'description' => $favorite->medicine->description,
                        'price' => $firstPharmacy ? $firstPharmacy->pivot->price : null,
                        'image' => $favorite->medicine->image,
                        'category' => $favorite->medicine->category->name ?? null,
                        'pharmacy' => $firstPharmacy ? $firstPharmacy->pharmacy_name : null
                    ],
                    'added_at' => $favorite->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $favorites->count(),
                'data' => $formattedFavorites
            ]);

        } catch (\Exception $e) {
            Log::error('Error in favorites index: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل المفضلة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إضافة دواء للمفضلة
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'medicine_id' => 'required|exists:medicines,id'
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مسجل الدخول'
                ], 401);
            }

            $medicineId = $request->medicine_id;

            // التحقق إذا كان الدواء موجود بالفعل في المفضلة
            $existingFavorite = $user->favorites()
                ->where('medicine_id', $medicineId)
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'الدواء موجود بالفعل في المفضلة'
                ], 400);
            }

            // التحقق من وجود الدواء
            $medicine = Medicine::find($medicineId);
            if (!$medicine) {
                return response()->json([
                    'success' => false,
                    'message' => 'الدواء غير موجود'
                ], 404);
            }

            // إضافة للمفضلة
            $favorite = $user->favorites()->create([
                'medicine_id' => $medicineId
            ]);

            // الحصول على أول صيدلية للدواء
            $firstPharmacy = $medicine->pharmacies->first();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الدواء إلى المفضلة',
                'data' => [
                    'id' => $favorite->id,
                    'medicine' => [
                        'id' => $medicine->id,
                        'name' => $medicine->name,
                        'price' => $firstPharmacy ? $firstPharmacy->pivot->price : null
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error in favorites store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إضافة المفضلة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إزالة دواء من المفضلة
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مسجل الدخول'
                ], 401);
            }

            // البحث باستخدام medicine_id
            $favorite = $user->favorites()
                ->where('medicine_id', $id)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'الدواء غير موجود في المفضلة'
                ], 404);
            }

            $medicineName = $favorite->medicine->name ?? 'الدواء';
            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => "تم إزالة '$medicineName' من المفضلة"
            ]);

        } catch (\Exception $e) {
            Log::error('Error in favorites destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إزالة المفضلة'
            ], 500);
        }
    }

    /**
     * التحقق إذا كان دواء في المفضلة
     */
    public function check($medicineId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مسجل الدخول'
                ], 401);
            }

            $isFavorite = $user->favorites()
                ->where('medicine_id', $medicineId)
                ->exists();

            return response()->json([
                'success' => true,
                'is_favorite' => $isFavorite
            ]);

        } catch (\Exception $e) {
            Log::error('Error in favorites check: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في التحقق من المفضلة'
            ], 500);
        }
    }
}
