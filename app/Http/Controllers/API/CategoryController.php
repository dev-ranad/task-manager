<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\Response;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    use Response;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = new Category;
        $query = $model::latest();

        $results = $query->get();
        $data = ['category' => $results];

        return $this->successResponse(
            $this->responseMessage('Category', 'index'),
            ['results' => $data]
        );
    }
}
