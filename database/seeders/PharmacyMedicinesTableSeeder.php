<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyMedicinesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على جميع الصيدليات الموجودة
        $pharmacies = DB::table('pharmacies')->get();

        if ($pharmacies->isEmpty()) {
            $this->command->error('❌ لا توجد صيدليات في قاعدة البيانات!');
            $this->command->info('👉 يرجى إنشاء صيدليات أولاً.');
            return;
        }

        $this->command->info('📋 قائمة الصيدليات الموجودة:');
        foreach ($pharmacies as $pharmacy) {
            $this->command->info("   - {$pharmacy->pharmacy_name} (ID: {$pharmacy->id})");
        }

        // الحصول على جميع الأدوية
        $medicines = DB::table('medicines')->get();

        if ($medicines->isEmpty()) {
            $this->command->error('❌ لا توجد أدوية في قاعدة البيانات!');
            $this->command->info('👉 قم بتشغيل MedicinesTableSeeder أولاً.');
            return;
        }

        $this->command->info("📦 عدد الأدوية المتاحة: {$medicines->count()}");

        // أسعار مختلفة لكل صيدلية (تنافسية)
        $priceRanges = [
            'home' => [0.9, 1.1],      // أسعار منخفضة نسبياً
            'road pharmacy' => [0.95, 1.15], // أسعار متوسطة
            'mohamed' => [1.0, 1.2],    // أسعار مرتفعة نسبياً
            'mohamedherzallah' => [0.85, 1.05], // أسعار منخفضة
        ];

        $totalAdded = 0;
        $totalSkipped = 0;

        foreach ($pharmacies as $pharmacy) {
            $this->command->info("\n🏥 معالجة صيدلية: {$pharmacy->pharmacy_name}");

            // تحديد نطاق السعر بناءً على اسم الصيدلية
            $priceRange = [0.9, 1.2]; // النطاق الافتراضي

            $pharmacyNameLower = strtolower($pharmacy->pharmacy_name);
            foreach ($priceRanges as $key => $range) {
                if (str_contains($pharmacyNameLower, $key)) {
                    $priceRange = $range;
                    break;
                }
            }

            // تحديد عدد الأدوية التي ستضاف لهذه الصيدلية (80-100% من الأدوية)
            $medicinesCount = count($medicines);
            $medicinesToAdd = rand(ceil($medicinesCount * 0.7), $medicinesCount); // 70-100% من الأدوية

            $this->command->info("   📊 سيتم إضافة {$medicinesToAdd} دواء من أصل {$medicinesCount}");

            // خلط الأدوية لاختيار عشوائي
            $shuffledMedicines = $medicines->shuffle();
            $selectedMedicines = $shuffledMedicines->take($medicinesToAdd);

            $addedCount = 0;

            foreach ($selectedMedicines as $medicine) {
                // التحقق من عدم وجود تكرار
                $exists = DB::table('pharmacy_medicines')
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('medicine_id', $medicine->id)
                    ->exists();

                if ($exists) {
                    $this->command->info("   ⏭️  الدواء موجود مسبقاً: {$medicine->name}");
                    $totalSkipped++;
                    continue;
                }

                // حساب السعر: السعر الأساسي * عامل عشوائي
                $basePrice = $this->getBasePrice($medicine->name);
                $priceMultiplier = rand($priceRange[0] * 100, $priceRange[1] * 100) / 100;
                $price = round($basePrice * $priceMultiplier, 2);

                // المخزون: 10-200 قطعة
                $stock = rand(10, 200);

                DB::table('pharmacy_medicines')->insert([
                    'pharmacy_id' => $pharmacy->id,
                    'medicine_id' => $medicine->id,
                    'stock' => $stock,
                    'price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $addedCount++;
                $totalAdded++;
            }

            $this->command->info("   ✅ تم إضافة {$addedCount} دواء لصيدلية {$pharmacy->pharmacy_name}");
        }

        $this->command->info("\n" . str_repeat('=', 50));
        $this->command->info("📊 التقرير النهائي:");
        $this->command->info("   ✅ تم إضافة {$totalAdded} علاقة (دواء-صيدلية) جديدة");
        $this->command->info("   ⏭️  تم تخطي {$totalSkipped} علاقة (موجودة مسبقاً)");
        $this->command->info("   🏥 عدد الصيدليات: " . $pharmacies->count());
        $this->command->info("   💊 عدد الأدوية: " . $medicines->count());
        $this->command->info("   🔗 إجمالي العلاقات: " . DB::table('pharmacy_medicines')->count());
    }

    /**
     * الحصول على السعر الأساسي للدواء (قيمة افتراضية)
     */
    private function getBasePrice($medicineName)
    {
        $medicineNameLower = strtolower($medicineName);

        // تحديد السعر الأساسي حسب نوع الدواء
        if (str_contains($medicineNameLower, 'بنادول') || str_contains($medicineNameLower, 'أدول')) {
            return 12.00;
        } elseif (str_contains($medicineNameLower, 'بروفين') || str_contains($medicineNameLower, 'فولتارين')) {
            return 18.00;
        } elseif (str_contains($medicineNameLower, 'أموكسيسيلين') || str_contains($medicineNameLower, 'أزيثروميسين')) {
            return 25.00;
        } elseif (str_contains($medicineNameLower, 'فيتامين')) {
            return 30.00;
        } elseif (str_contains($medicineNameLower, 'أوميغا') || str_contains($medicineNameLower, 'زنك')) {
            return 45.00;
        } elseif (str_contains($medicineNameLower, 'لوبريسور') || str_contains($medicineNameLower, 'نورفاسك')) {
            return 35.00;
        } elseif (str_contains($medicineNameLower, 'ميتفورمين') || str_contains($medicineNameLower, 'دياميكرون')) {
            return 28.00;
        } elseif (str_contains($medicineNameLower, 'كلاريتين') || str_contains($medicineNameLower, 'زيرتيك')) {
            return 22.00;
        } elseif (str_contains($medicineNameLower, 'كريم')) {
            return 15.00;
        } elseif (str_contains($medicineNameLower, 'سبازموكان') || str_contains($medicineNameLower, 'بوسكوبان')) {
            return 20.00;
        } elseif (str_contains($medicineNameLower, 'موتليوم')) {
            return 18.00;
        } else {
            return 25.00; // السعر الافتراضي
        }
    }
}
