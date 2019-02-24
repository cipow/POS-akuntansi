<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Profil extends Controller {

  private $user;

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function index() {
    return $this->response->data($this->user);
  }
}
