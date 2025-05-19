<?php

namespace App\Http\Traits;

use App\Models\Attachment;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Image;

trait Attachmentable
{
    // MODEL FUNCTIONS----------------------
    public function getFileAttribute()
    {
        return $this->url ? explode('/', $this->url)[count(explode('/', $this->url)) - 1] : '';
    }

    public function getContentTypeAttribute()
    {
        return $this->file ? explode('.', $this->file)[1] : '';
    }

    public function getUserAttribute()
    {
        return $this->user_id ? User::find($this->user_id) : 'Undefind User';
    }
    /**
     * Get all the project's attachments Only Trashed.
     */
    public function attachmentsOnlyTrashed()
    {
        return $this->morphMany(Attachment::class, 'attachmentable')->onlyTrashed();
    }

    public function getUrlAttribute($value)
    {
        $storage_path = config('app.env') === 'production' ? 'public/storage/' : 'storage/';
        $path = $storage_path . "upload/" . strtolower($this->state). '/';
        return $value ? url($path . $value) : null;
    }

    public function uploadedUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // TRAIT FUNCTIONS----------------------
    public function checkAttachment(Request $request, $resource_id = null)
    {
        $files = [];
        foreach ($request->allFiles() as $inputName => $file) {
            $this->checkAndGetFile($request, $inputName, $resource_id);
            if (!is_array($file)) {
                $files[] = [
                    'label' => $inputName,
                    'file' => $file,
                ];
            } else {
                foreach ($file as $attach) {
                    $files[] = [
                        'label' => $inputName,
                        'file' => $attach,
                    ];
                }
            }
        }
        return $files;
    }

    public function checkAndGetFile($request, $label = null, $resource_id = null)
    {
        $attachment = Attachment::where("attachmentable_id", $resource_id)
            ->where("label", $label)
            ->first();
        if ($attachment) {
            $request->merge(['deleteAttachmentIds' => array_merge($request->deleteAttachmentIds ?? [], [$attachment->id])]);
        }
    }

    /*-----IMAGE UPLOAD-----*/
    public function upload($file, $path, $base64 = false, $storePath = false)
    {
        $code = date('ymdhis') . '-' . rand(1111, 9999);
        /*-------FILE/IMAGE UPLOAD-------*/
        if (!is_null($file) && !$base64 && $file instanceof UploadedFile) {
            $fileName = $code . "." . $file->getClientOriginalExtension();
            $path = $file->storeAs('upload/' . $path, $fileName, 'public');
            // $path = Storage::putFileAs('upload/' . $path, $file, $fileName);
            return  $storePath ? $path : $fileName;
        }

        /*-------base64 IMAGE UPLOAD-------*/
        if (!empty($file) && $base64) {
            $image     = str_replace('data:image/jpeg;base64,', '', $file);
            $image     = str_replace(' ', '+', $image);
            $fileName = $code . '.jpeg';
            $imageName = 'upload/' . $path . '/' . $fileName;
            Storage::put($imageName, base64_decode($image));
            return $storePath ? $imageName : $fileName;
        }
    }

    public function handleAttachment($request, $data, $state, $path, $files = [])
    {
        if (count($files) === 1) {
            $this->addSingleAttachment($request, $data, $state, $path, $files[0]);
        } else {
            $this->addMultipleAttachment(
                $request,
                $data,
                $state,
                $path,
                $files
            );
        }
    }

    public function addSingleAttachment($request, $data, $state, $path, $file)
    {
        if (!empty($file['file'])) {
            $attachmentData = [
                'user_id' => $data->user_id,
                'url' => $this->upload($file['file'], $path),
                'state' => $state,
                'label' => $file['label'],
            ];
            $data->attachments()->create($attachmentData);
        }
    }

    public function addMultipleAttachment($request, $data, $state, $path, $files)
    {
        $attachments = [];
        foreach ($files as $file) {
            if (!is_array($file['file'])) {
                $attachments[] = [
                    'user_id' => $data->user_id,
                    'url' => $this->upload($file['file'], $path),
                    'state' => $state,
                    'label' => $file['label'],
                ];
            } else {
                foreach ($file['file'] as $element) {
                    $attachments[] = [
                        'user_id' => $data->user_id,
                        'url' => $this->upload($element, $path),
                        'state' => $state,
                        'label' => $file['label'],
                    ];
                }
            }
        }
        if (!empty($attachments)) {
            $data->attachments()->createMany($attachments);
        }
    }

    public function deleteOldAttachment(array $deleteAttachmentIds)
    {
        $attachments = Attachment::whereIn('id', $deleteAttachmentIds)->get();

        foreach ($attachments as $attachment) {
            if ($this->deleteFile($attachment->url)) {
                $attachment->forceDelete();
            }
        }
    }

    private function deleteFile($fileUrl)
    {
        $filePath = explode('storage/', $fileUrl)[1];

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
            return true;
        }

        return false;
    }
    /**
     * @param $data
     * @return void
     */
    public function deleteAttachment($data): void
    {
        foreach ($data->attachments as $attachment) {
            if ($this->deleteFile($attachment->url)) {
                $attachment->forceDelete();
            }
        }
    }
}
