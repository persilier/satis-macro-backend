<?php
namespace Satis2020\ServicePackage\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Metadata;

class ProxyConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            $meta = Metadata::query()
                ->where('name',Constants::PROXY)
                ->first();
            if ($meta==null){
                Metadata::query()->create(["name"=>Constants::PROXY,"data"=>json_encode(null)]);
            }

        }
}
