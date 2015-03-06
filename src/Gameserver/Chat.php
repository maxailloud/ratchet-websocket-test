<?php

namespace Gameserver;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $connection)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($connection);

        echo "New connection! ({$connection->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        $numLeftConnection = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $message, $numLeftConnection, $numLeftConnection > 1 ? 's' : '');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($message);
            }
        }
    }

    public function onClose(ConnectionInterface $connection)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($connection);

        echo "Connection {$connection->resourceId} has disconnected\n";
        $numLeftConnection = count($this->clients);
        echo sprintf("%s connection%s left", $numLeftConnection, $numLeftConnection > 1 ? 's' : '');
    }

    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        echo "An error has occurred: {$exception->getMessage()}\n";

        $connection->close();
    }
}