<?php

declare(strict_types=1);

namespace DTO;

class BaseDTO
{

    public function __construct(array $params = [])
    {
        // $seflFields = get_object_vars($this);
        $seflFields = get_class_vars($this::class);
        foreach ($seflFields as $key => $value) {
            if (array_key_exists($key, $params))
                $this->{$key} = $params[$key];
        }
    }
}
