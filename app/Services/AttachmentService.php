<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    public function storeMany(Model $model, array $files, string $directory = 'attachments'): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->store($model, $file, $directory);
            }
        }
    }

    public function store(Model $model, UploadedFile $file, string $directory = 'attachments'): Attachment
    {
        $path = $file->store($directory, 'public');

        return $model->attachments()->create([
            'uploaded_by' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function delete(Attachment $attachment): void
    {
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
    }
}
