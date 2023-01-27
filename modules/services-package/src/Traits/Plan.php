<?php

namespace Satis2020\ServicePackage\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Metadata as MetadataModel;
use Satis2020\ServicePackage\Rules\HeaderValidationRules;
use Satis2020\ServicePackage\Rules\LayoutValidationRules;

trait Plan
{

    public function initialisation()
    {
        // tables to fill by default

        // Accounts
        $accountSeeder = new \Satis2020\ServicePackage\Database\Seeds\InitializeChannelsTableSeeder();
        $accountSeeder->run();

    }
}
