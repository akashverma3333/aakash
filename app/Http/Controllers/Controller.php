<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Akash\Mylaravelpackage\Test;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $test;

    // Inject the Test class into the controller
    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    // A method to test the class functions
    public function show()
    {
        return response()->json([
            'test1' => $this->test->testing(),  // Returns "Hello 1234"
            'test2' => $this->test->testing1(), // Returns "hedflo"
            'test3' => $this->test->testing2(), // Returns "heldfgo"
        ]);
    }
}