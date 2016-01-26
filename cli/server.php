<?php if (!extension_loaded('swoole')) die('Cannot load swoole extension!');
require_once '../common/inc.php';

class WebSocket extends Swoole\Protocol\WebSocket
{
    protected $message;

    protected $userId = [];

    /**
     * @param $serv
     * @param int $worker_id
     */
    public function onStart($serv, $worker_id = 0)
    {
        Swoole::$php->router(array($this, 'router'));
        parent::onStart($serv, $worker_id);
    }

    public function router()
    {
        var_dump($this->message);
    }

    /**
     * 上线
     * @param $client_id
     */
    public function onEnter($client_id)
    {
        $time = time();
        $this->userId[$client_id] = $time;
        $json = json_encode(["type" => "welcome", "id" => $time]);
        $this->send($client_id, $json);
    }

    /**
     * 下线
     * @param $client_id
     */
    public function onExit($client_id)
    {
        if (!isset($this->connections[$client_id])) unset($this->userId[$client_id]);
        $json = json_encode(['type' => 'closed', 'id' => $this->userId[$client_id]]);
        $this->broadcast($client_id, $json);
    }


    /**
     * 消息到达
     * @param $client_id
     * @param $ws
     */
    public function onMessage($client_id, $ws)
    {
        $message = json_decode($ws['message'], true);
        if (!json_last_error()) {
            switch ($message['type']) {
                case 'login':
                    break;
                // 更新用户
                case 'update':
                    //转给所有用户
                    $json = json_encode(
                        [
                            'type' => 'update',
                            'id' => $this->userId[$client_id],
                            'angle' => $message["angle"] + 0,
                            'momentum' => $message["momentum"] + 0,
                            'x' => $message["x"] + 0,
                            'y' => $message["y"] + 0,
                            'life' => 1,
                            'name' => isset($message['name']) ? $message['name'] : 'Guest.' . $this->userId[$client_id],
                            'authorized' => false,
                        ]
                    );
                    $this->broadcast($client_id, $json, false);
                    return;

                case 'message':
                    // 向大家说
                    $json = json_encode([
                        'type' => 'message',
                        'id' => $this->userId[$client_id],
                        'message' =>$message['message']
                    ]);
                    $this->broadcast($client_id, $json,false);
                    return;
            }
        }

    }

    /**
     * 广播
     * @param $client_id
     * @param $msg
     */
    public function broadcast($client_id, $msg, $ignoreSelf = true)
    {
        foreach ($this->connections as $clid => $info) {
            if ($ignoreSelf && $client_id == $clid)
                continue;
            $this->send($clid, $msg);

        }
    }
}

$AppSvr = new WebSocket();
$AppSvr->loadSetting(CONFIG_PATH . "/swoole.ini"); //加载配置文件
$AppSvr->setLogger(new \Swoole\Log\EchoLog(true)); //Logger

$server = Swoole\Network\Server::autoCreate('0.0.0.0', 9443, false);
$server->setProtocol($AppSvr);
//$server->daemonize(); //作为守护进程
$server->run([
    'worker_num' => 1,
    'max_request' => 5000,
    'heartbeat_check_interval' => 60,
]);
