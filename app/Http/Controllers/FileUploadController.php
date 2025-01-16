<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validate the file
        $request->validate([
            'filepond' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        // Store the file
        $file = $request->file('filepond');
        $location = $request->input('location', 'uploads'); // Default to 'uploads' if no location is provided
        $filePath = $file->store($location, 'public');

        // Return the file path
        return response()->json([
            'filePath' => $filePath,
        ]);
    }
}
