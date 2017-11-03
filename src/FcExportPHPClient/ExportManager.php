<?php

namespace FcExportPHPClient;

class ExportManager
{
    protected $host;

    protected $port;

    protected $socket;

    public function __construct($host = '127.0.0.1', $port = 1337)
    {
        $this->host = $host;

        $this->port = $port;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    }

    public function connect()
    {
        socket_connect($this->socket, $this->host, $this->port);
    }

    private function emitData($target, $method, $body)
    {
        $payload = $target . '.' . $method . '<=:=>' . $body;
        socket_write($this->socket, $payload, strlen($payload));
        return socket_read($this->socket, 4096);
    }

    public function export($exportConfig)
    {
        $content = $this->emitData('ExportManager', 'export', $exportConfig);
        return json_decode($content)->data;
    }

    public function close()
    {
        socket_close($this->socket);
    }
}
