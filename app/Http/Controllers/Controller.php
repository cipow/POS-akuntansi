<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Res;

class Controller extends BaseController
{
    protected $response;

    public function __construct() {
      $this->response = new Res();
    }
}
