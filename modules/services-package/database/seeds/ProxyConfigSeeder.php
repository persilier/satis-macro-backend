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
        $proxyConfig = Constants::proxyConfig();

        foreach ($proxyConfig as $config){
            $name = $config['name'];
            $data = [
                $config['name']=>$config['value'],
            ];
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
