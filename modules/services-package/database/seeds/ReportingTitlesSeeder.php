<?php
namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Metadata;

class ReportingTitlesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reportsTypes = Constants::reportTypes();

        foreach ($reportsTypes as $type){
            $name = $type['value'];
            $data = ["title"=>$type['label'],"description"=>$type['label']];
            $meta = Metadata::query()
                ->where('name',$name)
                ->first();
            if ($meta==null){
                Metadata::query()->create([
                    'id' => (string)Str::uuid(),
                    'name' => $name,
                    'data' => json_encode($data)
                ]);
            }

        }
    }
}
