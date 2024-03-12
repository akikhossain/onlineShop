<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;

class TempImagesController extends Controller
{
    public function create(Request $request)
    {
        $image = $request->file('image');

        if (!empty($image)) {
            $ext = $image->getClientOriginalExtension();
            $newName = time() . "." . $ext;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            // Move the uploaded file to the specified directory
            $image->storeAs('/temp', $newName, 'public');

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'message' => 'Image Uploaded Successfully'
            ]);
        }
    }
}
