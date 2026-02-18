<?php

namespace App\Http\Controllers;

use App\Contracts\HasApiResponse;
use App\Contracts\HasChangeableProfile;

abstract class Controller
{
    use HasApiResponse;
}
