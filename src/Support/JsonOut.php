<?php

namespace RauyeMVC\Support;

use RauyeMVC\Config;

class JsonOut
{
    /**
     * @var \stdClass $obj
     */
    private $obj;

    public function add($name, $value)
    {
        if (!empty($value) or is_null($value)) {
            $this->obj->$name = $value;
        }
        return $this;
    }

    public function show($exit = true)
    {
        if ($exit) {
            die(json_encode($this->obj));
        }
        echo json_encode($this->obj);
        return $this;
    }

    public static function create()
    {
        $self = new static();
        $self->obj = new \stdClass();
        return $self;
    }

    public static function createError($msg = null)
    {
        $self = self::create();
        $self->add('success', false);
        $self->add('message', $msg ?? 'Error performing this operation');
        return $self;
    }

    public static function createSuccess($msg = null)
    {
        $self = self::create();
        $self->add('success', true);
        is_null($msg) or $self->add('message', $msg);
        return $self;
    }

    public static function createByException($exception, $msg = null)
    {
        if (Config::$DEBUG) {
            return self::createError(sprintf("%s\nDEBUG: %s", $msg ?? '', $exception->getMessage()));
        } else {
            return self::createError($msg ?? $msg->getMessage());
        }
    }
}