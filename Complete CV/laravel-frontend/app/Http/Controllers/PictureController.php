<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PictureController extends Controller
{
    private $apiUrl = 'http://localhost:5001';
    
    public function index()
    {
        return view('picture-validator');
    }
    
    public function validateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'mode' => 'required|in:webcam,upload'
        ]);
        
        try {
            $response = Http::post($this->apiUrl . '/api/validate-image', [
                'image' => $request->image,
                'mode' => $request->mode
            ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'API request failed'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getResults()
    {
        try {
            $response = Http::get($this->apiUrl . '/api/get-results');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Silently fail for now
        }
        
        return response()->json(['success' => false, 'results' => []]);
    }
}