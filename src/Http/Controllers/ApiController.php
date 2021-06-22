<?php

namespace Encore\FileSelector\Http\Controllers;

use App\Http\Controllers\Controller;
use Encore\FileSelector\RestApi\Helpers\ApiResponse;

class ApiController extends Controller
{
    use ApiResponse;

    /**
     * @return mixed
     */
    public function userInfo()
    {
        return \Admin::user();

    }
}
