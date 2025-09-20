<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\DateBasedFileStorage;

class fUploadController extends Controller
{
    /**
     * Handle standard file uploads
     */
    public function fUpload(Request $request): JsonResponse
    {
        // Validate the request
        $files = $request->file('files');

        // If it's a single file, wrap it in an array
        if ($files && !is_array($files)) {
            $files = [$files];
        }
        $userId = $request->input('userId');
        $orgId = $request->input('orgId');
        // Now validate - adjust based on what we actually have
        $validator = Validator::make(['files' => $files, 'userId' => $userId, 'orgId' => $orgId]  , [
            'files' => 'required|array|min:1',
            'files.*' => 'file|max:10240', // 10MB max per file
            'userId' => 'required|string',
            'orgId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadedFiles = [];
        $storage = new DateBasedFileStorage();
        $storageDirectory = $storage->createDateDirectory();

        try {
            foreach ($request->file('files') as $file) {

                // TODO: Process each file here
                // - Move to storage location
                // - Save to database
                // - Generate thumbnails, etc.

                $uploadedFiles[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    // 'path' => $stored_path,
                    // 'id' => $database_id,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'files' => $uploadedFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle chunked file uploads
     */
    public function uploadChunk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chunk' => 'required|file',
            'chunkIndex' => 'required|integer',
            'totalChunks' => 'required|integer',
            'uploadId' => 'required|string',
            'fileName' => 'required|string',
            'fileSize' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // TODO: Store chunk temporarily
            // - Save chunk to temp directory with uploadId
            // - Track progress in cache/database

            return response()->json([
                'success' => true,
                'message' => 'Chunk uploaded successfully',
                'chunkIndex' => $request->chunkIndex,
                'uploadId' => $request->uploadId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chunk upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize chunked upload
     */
    public function finalizeUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'uploadId' => 'required|string',
            'fileName' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // TODO: Combine chunks into final file
            // - Read all chunks for this uploadId
            // - Combine into single file
            // - Clean up temporary chunks
            // - Process final file (save, database entry, etc.)

            return response()->json([
                'success' => true,
                'message' => 'File assembled successfully',
                'uploadId' => $request->uploadId,
                'fileName' => $request->fileName
                // 'fileId' => $final_file_id,
                // 'path' => $final_file_path
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Finalization failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
