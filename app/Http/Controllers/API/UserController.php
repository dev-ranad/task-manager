<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\Response;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use Response;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::latest();

        $results = $query->get();
        $data = ['user' => $results];

        return $this->successResponse(
            $this->responseMessage('Task', 'index'),
            ['results' => $data]
        );
    }
}
