<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ImageProxyController extends Controller
{
    //
    public function fetchImage(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid URL'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Gửi request đến Imgur
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0',  // Bypass 403
                'Referer' => 'https://imgur.com/'
            ])->get($url);

            if (!$response->successful()) {
                return response()->json(['error' => 'Cannot fetch image'], $response->status());
            }

            // Lấy nội dung ảnh
            $content = $response->body();
            $mimeType = $response->header('Content-Type', 'image/jpeg');  // Mặc định là JPEG
            // dd(response($content, 200)->header('Content-Type', $mimeType));
            return response($content, 200)->header('Content-Type', $mimeType);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function fetchImgurImage(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid URL'], Response::HTTP_BAD_REQUEST);
        }

        // Kiểm tra xem URL có phải là ảnh trực tiếp không
        if (!preg_match('/^https?:\/\/i\.imgur\.com\/.+\.(jpg|jpeg|png|gif)$/i', $url)) {
            return response()->json(['error' => 'URL is not a direct Imgur image'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Gửi request đến Imgur
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0',  // Bypass 403
                'Referer' => 'https://imgur.com/'
            ])->get($url);

            if (!$response->successful()) {
                return response()->json(['error' => 'Cannot fetch image'], $response->status());
            }

            // Trả về hình ảnh
            return response($response->body(), 200)->header('Content-Type', $response->header('Content-Type'));
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
