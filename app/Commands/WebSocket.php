<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Libraries\Chat;

class WebSocket extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'websocket:start';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Start the WebSocket server';

    public function run(array $params)
    {
        $wsServer = new WsServer(
			new Chat()
		);

        $server = IoServer::factory(new HttpServer($wsServer), env('WS_PORT'));

    	$wsServer->enableKeepAlive($server->loop, 30);
	
		$server->run();

        CLI::write('WebSocket server started on port 8080', 'green');
    }
}
