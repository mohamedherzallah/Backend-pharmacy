<?php

namespace App\Http\Controllers;

use App\Http\Requests\PharmacyRequest;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PharmacyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
//    public function index(Pharmacy $pharmacy)
//    {
//        $total_medicines = $pharmacy->medicines()->withPivot('price', 'stock')->count();
//        $pharmacies = Pharmacy::with('user')->latest()->paginate(10);
//        return view('Pharmacy.pharmacies', compact('pharmacies','total_medicines'));
//    }
    public function index()
    {
        $pharmacies = Pharmacy::with('user')
            ->withCount('medicines')   // ⭐ السطر المهم
            ->latest()
            ->paginate(10);

        return view('Pharmacy.pharmacies', compact('pharmacies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all(); // For selecting owner
        return view('Pharmacy.add-pharmacy', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PharmacyRequest $request)
    {
        // 1️⃣ Validation واحد فقط
        $request->validated();

        // 2️⃣ إنشاء المستخدم
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3️⃣ تجهيز بيانات الصيدلية
        $data = [
            'pharmacy_name' => $request->pharmacy_name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'user_id' => $user->id,
            'is_approved' => $request->has('is_approved')
        ];

        // 4️⃣ الصور
        if ($request->hasFile('license_image')) {
            $data['license_image'] = $request->file('license_image')->store('licenses', 'public');
        }

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // 5️⃣ حفظ الصيدلية
        Pharmacy::create($data);

        return redirect()->route('pharmacies.index')
            ->with('success', 'Pharmacy created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pharmacy = Pharmacy::with('user')->findOrFail($id);
        return view('dashboard.pharmacies.show', compact('pharmacy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $users = User::all();

        return view('pharmacy.edit-pharmacy', compact('pharmacy', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // 1. البحث عن الصيدلية
        $pharmacy = Pharmacy::findOrFail($id);

        // 2. التحقق من صحة البيانات
        $validated = $request->validate([
            'pharmacy_name'  => 'string|max:255',
            'address'        => 'nullable|string',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'is_approved'    => 'nullable|boolean',
            'license_image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);


        // 3. معالجة الصور بشكل منفصل (أفضل للأمان)
        $updateData = $request->only([
            'pharmacy_name',
            'address',
            'latitude',
            'longitude'
        ]);
        $updateData['is_approved'] = $request->has('is_approved') ? 1 : 0;
        // 4. تحديث صورة الرخصة مع حذف القديمة
        if ($request->hasFile('license_image')) {
            // حذف الصورة القديمة إذا وجدت
            if ($pharmacy->license_image) {
                Storage::disk('public')->delete($pharmacy->license_image);
            }

            $updateData['license_image'] = $request->file('license_image')
                ->store('licenses', 'public');
        }

        // 5. تحديث اللوجو مع حذف القديم
        if ($request->hasFile('logo')) {
            // حذف اللوجو القديم إذا وجد
            if ($pharmacy->logo) {
                Storage::disk('public')->delete($pharmacy->logo);
            }

            $updateData['logo'] = $request->file('logo')
                ->store('logos', 'public');
        }

        // 6. التحديث في قاعدة البيانات
        $pharmacy->update($updateData);

        // 7. الإعادة مع رسالة النجاح
        return redirect()->route('pharmacies.index')
            ->with('success', 'Pharmacy updated successfully');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->delete();

        return redirect()->route('pharmacies.index')
            ->with('success', 'Pharmacy deleted successfully');
    }

    /**
     * Approve pharmacy (custom admin action)
     */
    public function pending()
    {
        // استخدام paginate() لجلب البيانات مع تفعيل ترقيم الصفحات
        $pendingPharmacies = Pharmacy::with('user')
            ->where('is_approved', false)
            ->latest()
            ->paginate(10); // يمكنك تغيير الرقم 10 لعدد العناصر التي تريد عرضها في كل صفحة

        return view('Pharmacy.pending', compact('pendingPharmacies'));
    }

    /**
     * دالة الموافقة (approve)
     */
    public function approve($id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'تم الموافقة على الصيدلية بنجاح.');
    }

    /**
     * دالة الرفض (reject)
     */
    public function reject($id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        // بما أنك تستخدم onDelete('cascade') في الميجريشن، حذف الصيدلية يحذف كل ما يتعلق بها.
        $pharmacy->delete();

        return redirect()->route('pharmacies.pending')->with('success', 'تم رفض وحذف الصيدلية بنجاح.');
    }


}
