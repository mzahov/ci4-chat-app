<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Entities\ChatMessage;
use App\Models\RoomFileModel;
use App\Models\ChatMessageModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Files\UploadedFile;

class FileController extends Controller
{
    use ResponseTrait;
    
    public function uploadRoomFile()
    {
        $rules = [
            'room_id' => 'required|is_natural_no_zero',
            'file' => 'max_size[file,10240]' // 10MB
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        /** @var UploadedFile $file */
        $file = $this->request->getFile('file');

        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $fileMime = explode('/', $file->getMimeType());

            $mime = count($fileMime) > 0 ? $fileMime[0] : $file->getMimeType();

            $type = match($mime) {
                'image' => 'image',
                'video' => 'video',
                default => 'file'
            };
            
            $path = 'room_' . $validData['room_id'] . '/' . $type;

            $file->store($path, $newName);
            
            // Save message for newly insrted file
            $chatMessageModel = new ChatMessageModel();

            $chat = new ChatMessage([
                'room_id' => $validData['room_id'],
                'user_id' => user_id(),
                'author' => auth()->user()->username,
                'message' => '',
            ]);
    
            $chatMessageModel->save($chat);
            $messageId = $chatMessageModel->insertID;

            // Save files in database
            $fileModel = new RoomFileModel();

            $data = [
                'room_id' => $validData['room_id'],
                'message_id' => $messageId,
                'file_name' => $newName,
                'file_original_name' => $file->getClientName(),
                'file_type' => $type,
                'file_size' => $file->getSizeByUnit('mb'),
            ];
            $fileModel->save($data);

            return $this->respond(['success' => true, 'file_type' => $type, 'file_name' => $newName, 'file_original_name' => $file->getClientName()]);
        }

        return $this->respond(['success' => false, 'error' => $file->getErrorString()]);
    }

    public function showFile(int|string $id, string $file, string $type) {
        if (!auth()->loggedIn()) {
            return $this->failUnauthorized();
        }

        $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'room_' . $id . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePath)) {
            return $this->response->download($filePath, null);
        }

        return $this->failNotFound("File not found");
    }
}