<?php
class SGNotificationCenter
{
    private static $instance = null;
    private $observers = array();

    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function addObserver($name, $object, $method, $unique = false)
    {
        if ($unique)
        {
            $this->observers[$name] = array('object'=>$object, 'method'=>$method);
        }
        else
        {
            $this->observers[$name][] = array('object'=>$object, 'method'=>$method);
        }
    }

    public function postNotification($name, $userInfo = array())
    {
        if (isset($this->observers[$name]))
        {
            $observers = $this->observers[$name];
            foreach ($observers as $observer)
            {
                $this->executeHandler($observer, $userInfo);
            }
        }
    }

    public function postAskNotification($name, $userInfo = array())
    {
        if (isset($this->observers[$name]))
        {
            return $this->executeHandler($this->observers[$name], $userInfo);
        }

        return null;
    }

    public function postDoNotification($name, $userInfo = array())
    {
        if (isset($this->observers[$name]))
        {
            $this->executeHandler($this->observers[$name], $userInfo);
        }
    }

    private function executeHandler($observer, $userInfo)
    {
        $method = $observer['method'];
        $object = $observer['object'];
        
        if (method_exists($object, $method))
        {
            return $object->$method($userInfo);
        }
    }

    public function removeAllObservers()
    {
        $this->observers = array();
    }

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    private function __wakeup()
    {

    }
}