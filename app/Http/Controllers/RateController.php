<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rate; // Assuming you have a Rate model

class RateController extends Controller
{
    public function show($bookId)
    {
        if (!empty($bookId)) {
            // Fetch ratings for the specified book
            $bookId = (int) $bookId; // Ensure bookId is an integer
            $hasil = Rate::where('book_id', '=', $bookId)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($hasil->isEmpty()) {
                return response()->json([
                    'result' => 'OK',
                    'book_id' => $bookId,
                    'message' => 'No ratings found for this book',
                ], 404);
            }

            return response()->json([
                'result' => 'OK',
                'book_id' => $bookId,
                'ratings' => $hasil->values()->all(),
            ]);
        } else {
            // keluarkan semua hasilnya
            $hasil = Rate::all();
            if ($hasil->isEmpty()) {
                return response()->json([
                    'result' => 'OK',
                    'message' => 'No ratings found',
                ], 404);
            }
            return response()->json([
                'result' => 'OK',
                'ratings' => $hasil->values()->all(),
            ]);
        }
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (empty($data['user_id'])) {
            return response()->json([
            'result' => 'ERROR',
            'message' => 'User must be logged in',
            ], 401);
        }
        if (empty($data['book_id'])) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Book ID is required',
            ], 400);
        }
        if (empty($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Rating must be between 1 and 5',
            ], 400);
        }

        $rate = new Rate();
        $rate->fill($data);
        $rate->save();

        return response()->json([
            'result' => 'OK',
            'message' => 'Rating created successfully',
            'rating' => $rate,
        ], 201);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $bookId = (int) $data['book_id'];

        $rate = Rate::where('book_id', $bookId)
            ->where('user_id', $data['user_id'])
            ->first();
        
        if (!$rate) {
            return response()->json([
                'result' => 'ERROR',
                'book_id' => $bookId,
                'message' => 'Rating not found',
            ], 404);
        }

        if (empty($data['user_id'])) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'User must be logged in',
            ], 401);
        }
        if (empty($data['book_id'])) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Book ID is required',
            ], 400);
        }
        if (empty($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Rating must be between 1 and 5',
            ], 400);
        }

        //set updated_at to now
        $data['updated_at'] = now();
        // Update the rating bagi yang berubah aja
        $rate->fill($data);
        $rate->save();

        return response()->json([
            'result' => 'OK',
            'message' => 'Rating updated successfully',
            'rating' => $rate,
        ]);
        
    }

    public function page (Request $request, $page) // url: /api/ratings/page
    {
        $page = (int) $request->input('page', $page);
        $limit = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $limit;

        $ratings = Rate::skip($offset)->take($limit)->get();

        if ($ratings->isEmpty()) {
            return response()->json([
                'result' => 'OK',
                'message' => 'No ratings found',
            ], 404);
        }

        return response()->json([
            'result' => 'OK',
            'ratings' => $ratings->values()->all(),
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function destroy($id)
    {
        $rate = Rate::find($id);
        if (!$rate) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Rating not found',
            ], 404);
        }

        $rate->delete();

        return response()->json([
            'result' => 'OK',
            'message' => 'Rating deleted successfully',
        ]);
    }

}
