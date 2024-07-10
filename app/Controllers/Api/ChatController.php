<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Entities\ChatRoom;
use App\Models\ChatRoomModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\RESTful\ResourcePresenter;

class ChatController extends ResourcePresenter
{
    use ResponseTrait;
    protected $modelName = 'App\Models\ChatRoomModel';

    public function index()
    {
        $data = ['users' => (new UserModel())->getUsers(user_id())];

        return view('chat', $data);
    }

    public function getUserRooms() : ResponseInterface
    {
        if ( $rooms = $this->model->getUserChatRooms(user_id())) {

            $dataRooms = [];
            foreach($rooms as $room) {
                $dataRooms[] = [
                    "id" => $room->id,
                    "name" => $room->name,
                    'avatar' => $room->image,
                    "message" => $room->message,
                    "created_at" => $room->created_at?->getTimestamp(),
                    "author" => $room->author,
                    "type" => $room->message === '' && $room->file_type ? $room->file_type : 'message'
                ];
            }

            return $this->respond($dataRooms);
        }

        return $this->respond($rooms);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     */
    public function show($id = null) : ResponseInterface
    {
        if (!$id) {
            return $this->failForbidden('Please dont forget to include ID.');
        }

        $roomDetails = $this->model->getRoom($id);

        if (!$roomDetails) {
            return $this->failNotFound("Nothing found for room $id.");
        }

        return $this->respond($roomDetails);
    }

    public function getMessages() : ResponseInterface
    {
        if (! $this->request->is('ajax')) {
            return $this->failForbidden('Method Not Allowed');
        }

        $rules = [
            'roomId' => 'required|is_natural_no_zero',
            'limit' => 'required|is_natural_no_zero',
            'offset' => 'required|integer'
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        $roomMessages = $this->model->getMessages($validData['roomId'], (int) $validData['limit'], (int) $validData['offset']);

        if (!$roomMessages) {
            return $this->failNotFound("The room " . $validData['roomId'] . " doesn't have messages.");
        }

        return $this->respond($roomMessages);
    }

    /**
     * Return a new resource object, with default properties.
     */
    public function new() : ResponseInterface
    {
        return $this->respond($this->model->orderBy('id', 'desc')->first());
    }

    /**
     * Create a new resource object, from "posted" parameters.
     */
    public function create(): ResponseInterface
    {
        $rules = [
            'name' => 'required|alpha_num_space',
            'description' => 'required|alpha_num_space',
            'users.*' => 'required|is_natural_no_zero',
            'image' => 'is_image[image]|max_size[image,2048]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        /** @var UploadedFile $uploadedAvatar */
        $uploadedAvatar = $this->request->getFile('image');
        
        if ($uploadedAvatar && $uploadedAvatar->isValid()) {
            $chatRoomData = new ChatRoom($validData);
            $chatRoomData->created_by = user_id();

            $chatRoomData->image = 'avatar.' . $uploadedAvatar->getExtension();
        
            $roomId = (new ChatRoomModel)->addChatRoom($chatRoomData, $validData['users']);

            $uploadedAvatar->store('room_' .$roomId . '/avatar', 'avatar.' . $uploadedAvatar->getExtension());

            return $this->respond(['success' => true, 'data' => ['roomId' => $roomId, 'name' => $validData['name'], 'avatar' => $chatRoomData->image, 'users' => $validData['users']]]);
        }

        return $this->respond(['success' => false]);
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     */
    public function edit($id = null): ResponseInterface
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     */
    public function update($id = null): ResponseInterface
    {
        //
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     */
    public function delete($id = null): ResponseInterface
    {
        //
    }
}