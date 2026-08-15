<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\pharmacy_medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyMedicineController extends Controller
{
    //
    public function index(Pharmacy $pharmacy)
    {
  //$categoris = Medicine::where('category_id',$medicine->category_id )->first();
        // الآن $pharmacy هو كائن Model فعلي

        $medicines = $pharmacy->medicines()->with('category')->get();

        //ما بلزمني عنا ويث بايفوت لانها موجودة في العلاقة بين الصيدلية والدواء، لذلك يمكننا الوصول إليها مباشرة من خلال العلاقة.
//كلهم غلط والحل بسيط فوق
      //  $categoris = $pharmacy->with('medicines.category')->get();
      //$categoris = $pharmacy->medicines()->with('category')->get();
       // $categoris = $pharmacy->medicines()->where('category_id',$medicine->category_id )->first();
        return view('Pharmacy.Medicines.index', compact('pharmacy', 'medicines'));
    }

    public function create(Pharmacy $pharmacy)
    {
        $medicines = Medicine::orderBy('name')->get();
        return view('pharmacy.medicines.add', compact('medicines','pharmacy'));
    }


    // إضافة دواء من القائمة للصيدلية
    public function store(Request $request,Pharmacy $pharmacy)
    {
        $request->validate([
            'medicine_id' => 'exists:medicines,id',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        pharmacy_medicine::updateOrCreate(
            [
                'pharmacy_id' =>  $pharmacy->id,
                'medicine_id' => $request->medicine_id,
            ],
            [
                'stock' => $request->stock,
                'price' => $request->price
            ]
        );

        return redirect()->route('pharmacy.medicines.index',$pharmacy->id)->with('success', 'Medicine added/updated successfully');
    }

    // إزالة دواء من الصيدلية

    public function destroy(Pharmacy $pharmacy, Medicine $medicine)
    {
        $pharmacy->medicines()->detach($medicine->id);

        return redirect()
            ->back()
            ->with('success', 'Medicine removed from pharmacy successfully');
    }

}
