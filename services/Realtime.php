<?php
namespace SLSupplyHub;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class RealtimeService implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;
    protected $subscriptions;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        $this->subscriptions = [];
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connections) {
            if (($key = array_search($conn, $connections)) !== false) {
                unset($this->userConnections[$userId][$key]);
                if (empty($this->userConnections[$userId])) {
                    unset($this->userConnections[$userId]);
                }
                break;
            }
        }
        
        // Remove subscriptions
        foreach ($this->subscriptions as $channel => $subscribers) {
            if (($key = array_search($conn, $subscribers)) !== false) {
                unset($this->subscriptions[$channel][$key]);
                if (empty($this->subscriptions[$channel])) {
                    unset($this->subscriptions[$channel]);
                }
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            if (!$data || !isset($data['action'])) {
                return;
            }
            
            switch ($data['action']) {
                case 'auth':
                    $this->handleAuthentication($from, $data);
                    break;
                    
                case 'subscribe':
                    $this->handleSubscription($from, $data);
                    break;
                    
                case 'unsubscribe':
                    $this->handleUnsubscription($from, $data);
                    break;
            }
            
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            $from->send(json_encode([
                'error' => 'Invalid message format'
            ]));
        }
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    protected function handleAuthentication($conn, $data) {
        if (!isset($data['user_id']) || !isset($data['token'])) {
            return;
        }
        
        // Verify token (implement your token verification logic)
        if ($this->verifyToken($data['user_id'], $data['token'])) {
            $userId = $data['user_id'];
            if (!isset($this->userConnections[$userId])) {
                $this->userConnections[$userId] = [];
            }
            $this->userConnections[$userId][] = $conn;
            
            $conn->send(json_encode([
                'type' => 'auth',
                'status' => 'success'
            ]));
        } else {
            $conn->send(json_encode([
                'type' => 'auth',
                'status' => 'error',
                'message' => 'Invalid authentication'
            ]));
        }
    }
    
    protected function handleSubscription($conn, $data) {
        if (!isset($data['channel'])) {
            return;
        }
        
        $channel = $data['channel'];
        if (!isset($this->subscriptions[$channel])) {
            $this->subscriptions[$channel] = [];
        }
        
        if (!in_array($conn, $this->subscriptions[$channel])) {
            $this->subscriptions[$channel][] = $conn;
        }
        
        $conn->send(json_encode([
            'type' => 'subscription',
            'status' => 'success',
            'channel' => $channel
        ]));
    }
    
    protected function handleUnsubscription($conn, $data) {
        if (!isset($data['channel'])) {
            return;
        }
        
        $channel = $data['channel'];
        if (isset($this->subscriptions[$channel])) {
            if (($key = array_search($conn, $this->subscriptions[$channel])) !== false) {
                unset($this->subscriptions[$channel][$key]);
                if (empty($this->subscriptions[$channel])) {
                    unset($this->subscriptions[$channel]);
                }
            }
        }
        
        $conn->send(json_encode([
            'type' => 'unsubscription',
            'status' => 'success',
            'channel' => $channel
        ]));
    }
    
    public function broadcast($channel, $event, $data) {
        if (!isset($this->subscriptions[$channel])) {
            return;
        }
        
        $message = json_encode([
            'channel' => $channel,
            'event' => $event,
            'data' => $data
        ]);
        
        foreach ($this->subscriptions[$channel] as $client) {
            $client->send($message);
        }
    }
    
    public function notifyUser($userId, $event, $data) {
        if (!isset($this->userConnections[$userId])) {
            return;
        }
        
        $message = json_encode([
            'event' => $event,
            'data' => $data
        ]);
        
        foreach ($this->userConnections[$userId] as $conn) {
            $conn->send($message);
        }
    }
    
    public function notifyUsers($userIds, $event, $data) {
        foreach ($userIds as $userId) {
            $this->notifyUser($userId, $event, $data);
        }
    }
    
    protected function verifyToken($userId, $token) {
        // Implement your token verification logic here
        // This should match your authentication system
        try {
            $session = new Session();
            return $session->verifyToken($userId, $token);
        } catch (\Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            return false;
        }
    }
}