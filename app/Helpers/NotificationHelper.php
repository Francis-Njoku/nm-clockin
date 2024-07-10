<?php

namespace App\Helpers;
use App\Events\NotificationAllEvent;
use App\Events\NotificationEvent;

class NotificationHelper
{
    public static function single($message, $messageType, $userId)
    {
        event(new NotificationEvent($message, $messageType, $userId));

        //return "This is a utility function with param: " . $param;
    }

    public static function general($message, $messageType)
    {
        event(new NotificationAllEvent($message, $messageType));

        //return "This is a utility function with param: " . $param;
    }

}