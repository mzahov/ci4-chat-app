<?php 
declare(strict_types=1);

namespace App\Libraries;

use SplObjectStorage;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use App\Entities\ChatMessage;
use App\Models\ChatMessageModel;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use CodeIgniter\Shield\Authentication\JWTManager;

class Chat implements MessageComponentInterface {
    protected SplObjectStorage $clients;
    protected array $onlineUsers = [];

    public function __construct() {
        $this->clients = new SplObjectStorage;
        db_connect();
    }

    public function onOpen(ConnectionInterface $conn) {
        $uriQueryParam = $conn->httpRequest->getUri()->getQuery();
        parse_str($uriQueryParam, $queryParamsArray);

        if (!isset($queryParamsArray['token']) || empty($queryParamsArray['token'])) {
            $conn->send(json_encode(['error' => 'Missing access token']));
            $conn->close();
            return;
        }

        $token = $queryParamsArray['token'];

        /** @var JWTManager $manager */
        $manager = service('jwtmanager');

        $userToken = $manager->parse($token);

        if ($userToken->exp <= time()) {
            $conn->send(json_encode(['error' => 'Expired access token']));
            $conn->close();
            return;
        }

        $conn->user = $userToken;
        $this->clients->attach($conn);

        $this->onlineUsers[$userToken->sub] = [
            'username' => $userToken->username,
        ];

        $this->broadcastOnlineUsers();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        match ($data['command']) {
            'create' => $this->createRoom($from, $data),
            'message' => $this->sendMessage($from, $data['message'], $data['room']),
            'file' => $this->sendFile($from, $data['file'], $data['file_type'], $data['room']),
            'ping' => $this->sendPong($from),
            default => $from->send(json_encode(['error' => 'Invalid command'])),
        };
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->user)) {
            unset($this->onlineUsers[$conn->user->sub]);
            $this->broadcastOnlineUsers();
        }
        
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function sendMessage(ConnectionInterface $from, $message, $room) {
        $chatMessageModel = new ChatMessageModel();
        $currentTime = Time::now();

        $data = [
            'type' => 'message',
            'message' => $message,
            'author' => $from->user->username,
            'time' => $currentTime->getTimestamp(),
            'room' => $room,
        ];

        foreach ($this->clients as $client) {
            if ($client !== $from) {
                $client->send(json_encode($data));
            }
        }

        $chat = new ChatMessage([
            'room_id' => $room,
            'user_id' => $from->user->sub,
            'author' => $from->user->username,
            'message' => $message,
        ]);

        $chatMessageModel->save($chat);
    }

    private function broadcastOnlineUsers() {
        $onlineUsersList = array_values($this->onlineUsers);

        foreach ($this->clients as $client) {
            $client->send(json_encode(['type' => 'online_users', 'data' => $onlineUsersList]));
        }
    }

    private function sendPong(ConnectionInterface $conn) {
        $conn->send(json_encode(['type' => 'pong']));
    }

    private function createRoom(ConnectionInterface $from, array|object $data) {
        $chatDetails = [
            'type' => 'create',
            'id' => $data['room'],
            'name' => $data['name'],
            'avatar' => $data['avatar']
        ];

        foreach($this->clients as $client) {
            if ($from !== $client) {
                foreach($data['users'] as $user) {
                    if ($user === $client->user->sub) {
                        $client->send(json_encode($chatDetails));
                    }
                }
            }            
        }
    }

    private function sendFile(ConnectionInterface $from, $file, $file_type, $room) {
        $currentTime = Time::now();

        $data = [
            'type' => 'file',
            'file' => $file,
            'file_type' => $file_type,
            'author' => $from->user->username,
            'time' => $currentTime->getTimestamp(),
            'room' => $room,
        ];

        foreach ($this->clients as $client) {
            if ($client !== $from) {
                $client->send(json_encode($data));
            }
        }
    }
}
