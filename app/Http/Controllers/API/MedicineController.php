<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicineResource;
use App\Models\Medicine;
use App\Models\Pharmacy;

use Illuminate\Http\Request;


class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // كل الأدوية (قد تحتاج pagination)
//    public function index(Request $request)
//    {
//        $query = Medicine::query();
//
//        // فلترة حسب الفئة
//        if ($request->has('category_id')) {
//            $query->where('category_id', $request->category_id);
//        }
//
//        // البحث الجزئي مع تجاهل الحالة
//        if ($request->has('q')) {
//            $query->search($request->q);
//        }
//
//        $medicines = $query->paginate(20);
//
//        return response()->json([
//            'status' => 'success',
//            'data' => MedicineResource::collection($medicines),
//            'pagination' => [
//                'total' => $medicines->total(),
//                'per_page' => $medicines->perPage(),
//                'current_page' => $medicines->currentPage(),
//                'last_page' => $medicines->lastPage(),
//            ]
//        ]);
//    }
    public function index(Request $request)
    {
        $query = Medicine::query();

        // فلترة حسب الفئة
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // البحث الجزئي
        if ($request->has('q')) {
            $query->search($request->q);
        }

        // جلب الأدوية مع الصيدليات
        $query->with(['pharmacies' => function($q) {
            $q->where('pharmacies.is_approved', true)
                ->where('pharmacy_medicines.stock', '>', 0)
                ->select('pharmacies.id', 'pharmacies.pharmacy_name as name', 'pharmacies.address')
                ->withPivot('price', 'stock');
        }]);

        $medicines = $query->paginate(20);

        // تنسيق البيانات مع إضافة أرخص صيدلية
        $formattedMedicines = $medicines->map(function($medicine) {
            $cheapestPharmacy = null;
            $cheapestPrice = null;
            $cheapestPharmacyId = null;


            if ($medicine->pharmacies && $medicine->pharmacies->count() > 0) {
                // إيجاد أرخص صيدلية
                $cheapestPharmacy = $medicine->pharmacies->sortBy('pivot.price')->first();
                $cheapestPrice = $cheapestPharmacy->pivot->price;
                $cheapestPharmacyId = $cheapestPharmacy->id; // 👈 أضف هذا
            }

            return [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'description' => $medicine->description,
                'image' => $medicine->image,
                'category' => $medicine->category ? [
                    'id' => $medicine->category->id,
                    'name' => $medicine->category->name
                ] : null,
                'pharmacies' => $medicine->pharmacies->map(function($pharmacy) {
                    return [
                        'id' => $pharmacy->id,
                        'name' => $pharmacy->name,
                        'price' => $pharmacy->pivot->price,
                        'stock' => $pharmacy->pivot->stock
                    ];
                }),
                'cheapest_price' => $cheapestPrice,
                'cheapest_pharmacy_name' => $cheapestPharmacy ? $cheapestPharmacy->name : null,
                'cheapest_pharmacy_id' => $cheapestPharmacyId, // 👈 أضف هذا
                'has_pharmacies' => $medicine->pharmacies->count() > 0
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedMedicines,
            'pagination' => [
                'total' => $medicines->total(),
                'per_page' => $medicines->perPage(),
                'current_page' => $medicines->currentPage(),
                'last_page' => $medicines->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $medicine = Medicine::with('category')->findOrFail($id);
        return new MedicineResource($medicine);
    }
    public function byPharmacy($pharmacyId)
    {
        $pharmacy = Pharmacy::findOrFail($pharmacyId);
        $medicines = $pharmacy->medicines()->paginate(20);
        return MedicineResource::collection($medicines);
    }

    /**
     * عرض الصيدليات التي يتوفر فيها دواء معين
     * GET /api/medicines/{id}/pharmacies
     */
    public function getPharmacies($id)
    {
        $medicine = Medicine::findOrFail($id);

        $pharmacies = $medicine->pharmacies()
            ->where('pharmacies.is_approved', true)
            ->where('pharmacy_medicines.stock', '>', 0)
            ->select(
                'pharmacies.id',
                'pharmacies.pharmacy_name as name',
                'pharmacies.address',
                'pharmacies.logo'
            )
            ->withPivot('price', 'stock')
            ->get()
            ->map(function($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'address' => $pharmacy->address,
                    'logo' => $pharmacy->logo,
                    'price' => $pharmacy->pivot->price,
                    'stock' => $pharmacy->pivot->stock,
                    'is_available' => $pharmacy->pivot->stock > 0
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $pharmacies
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
