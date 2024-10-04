<?php

namespace DTO;

use DTO\JSON\Unmarshal;
use DTO\JSON\JSONAttribute;

class TestDTO
{
    var string $action;

    #[JSONAttribute(field: 'data')]
    var TestDTOData $data;

    #[JSONAttribute(field: 'rows', type: TestDTORows::class)]
    var array $rows;

    #[JSONAttribute(field: 'rowsIndexes')]
    var array $rowsIdx;

    var bool $isActive;

    var int $startIdx;

    var float $price;

    var int $endIdx;

    public function __construct(?array $data = null) {
        Unmarshal::unmarshalArray($data, $this);
    }
}

class TestDTOData
{
    var string $device_uid;
    var string $username;
    var string $pass;
    #[JSONAttribute(field: 'rowsInt', type: TestDTORows::class)]
    var array $rows;

    public function __construct(?array $data = null) {
        Unmarshal::unmarshalArray($data, $this);
    }
}

class TestDTORows
{
    var int $id;
    var string $name;
}