<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // قائمة الفئات المطلوبة
        $requiredCategories = [
            'مسكنات',
            'مضادات حيوية',
            'فيتامينات',
            'مكملات غذائية',
            'أدوية ضغط',
            'أدوية سكري',
            'مضادات هيستامين',
            'عناية بالبشرة',
        ];

        // الحصول على الفئات الموجودة حالياً
        $existingCategories = DB::table('categories')->pluck('name')->toArray();

        $this->command->info('📋 الفئات الموجودة حالياً: ' . implode(', ', $existingCategories));

        $addedCount = 0;

        // إضافة الفئات غير الموجودة
        foreach ($requiredCategories as $categoryName) {
            if (!in_array($categoryName, $existingCategories)) {
                DB::table('categories')->insert([
                    'name' => $categoryName,
                    'avatar' => 'categories/default.png',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $addedCount++;
                $this->command->info("✅ تم إضافة فئة: {$categoryName}");
            } else {
                $this->command->info("⏭️  الفئة موجودة مسبقاً: {$categoryName}");
            }
        }

        // عرض الفئات بعد الإضافة
        $allCategories = DB::table('categories')->pluck('name')->toArray();

        $this->command->info("\n📋 الفئات المتاحة الآن: " . implode(', ', $allCategories));
        $this->command->info("✅ تم إضافة {$addedCount} فئة جديدة بنجاح!");
    }
}
