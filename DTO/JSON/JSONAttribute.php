<?php

namespace DTO\JSON;

use Attribute;

#[Attribute]
class JSONAttribute
{
    /**
     * @param string      $field
     * @param string|null $type
     */
    public function __construct(
        public string $field,
        public ?string $type = null,
    ) {
    
    }
}