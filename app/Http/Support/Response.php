<?php

namespace App\Http\Support;

use Validator;

class Response {
  private function responseJson($success, $statusCode, $message, $result) {
    $jsonData = [
      'status' => [
        'success' => $success,
        'code' => $statusCode,
        'message' => $message
      ],
      'result' => $result
    ];
    return response()->json($jsonData, 200);
  }

  private function message($success, $statusCode, $message) {
    return $this->responseJson($success, $statusCode, $message, null);
  }

  public function validate($req, $rule) {
    $validator = Validator::make($req->all(), $rule);
    if ($validator->fails()) return $this->errorField($validator->errors());
    return;
  }

  /*|
  | -------------------------
  | Succes Method
  | -------------------------
  |*/

  public function data($data) {
    return $this->responseJson(true, 200, 'OK', $data);
  }

  public function messageSuccess($message, $statusCode) {
    return $this->message(true, $statusCode, $message);
  }

  /*|
  | -------------------------
  | Error Method
  | -------------------------
  |*/

  public function serverError() {
    return $this->message(false, 500, 'Internal Server Error');
  }

  private function errorField($field) {
    return $this->responseJson(false, 400, 'error in input', $field);
  }

  public function messageError($message, $statusCode) {
    return $this->message(false, $statusCode, $message);
  }


}
