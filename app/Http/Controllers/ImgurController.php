<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ImgurController extends Controller
{
    //
    public function uploadImage(Request $request)
    {
        // Validate incoming image
        $request->validate([
            'image' => 'required|image|max:2048',  // Max 2MB
        ]);

        // Get the uploaded image
        $image = $request->file('image');

        // Get the image content as base64
        $imageData = base64_encode(file_get_contents($image));

        // Imgur API details
        $clientId = '1b49d0360c1da88';  // Replace with your Imgur Client ID
        $url = 'https://api.imgur.com/3/upload';  // Imgur upload endpoint

        // Initialize Guzzle client
        $client = new Client();

        // Send the image to Imgur API using POST method
        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Client-ID ' . $clientId,  // Make sure Client-ID is correct
                ],
                'form_params' => [
                    'image' => $imageData,  // The base64-encoded image
                    'type' => 'base64',  // We are sending the image as base64
                ]
            ]);

            // Decode the response from Imgur
            $responseData = json_decode($response->getBody(), true);

            if ($responseData['success']) {
                // Image uploaded successfully, return the public link
                return response()->json([
                    'message' => 'Image uploaded successfully!',
                    'link' => $responseData['data']['link'],  // Image URL
                ]);
            } else {
                return response()->json(['error' => 'Image upload failed.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'API error: ' . $e->getMessage()], 400);
        }
    }
}
