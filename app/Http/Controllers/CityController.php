<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        return response()->json(City::all());
    }

    public function posts(Request $request, $id)
    {
        $city = City::findOrFail($id);

        $posts = $city->posts()
            ->with(['user', 'category'])
            ->when(
                $request->category_id,
                fn($q) =>
                $q->where('category_id', $request->category_id)
            )
            ->get();

        return response()->json($posts);
    }
}
