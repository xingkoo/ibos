<?php

class Message extends ICModel
{
    const ONE_ON_ONE_CHAT = 1;
    const MULTIPLAYER_CHAT = 2;
    const SYSTEM_NOTIFY = 3;

    protected $_reversibleType = array();

    public function init()
    {
        $this->_reversibleType = array(self::ONE_ON_ONE_CHAT, self::MULTIPLAYER_CHAT);
        parent::init();
    }
}
