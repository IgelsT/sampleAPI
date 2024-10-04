<?php

declare(strict_types=1);

namespace Controllers;

use DTO\TestDTO;
use DTO\TestDTOData;

class TestController
{
    function index()
    {
        $jsonStr = '{
            "action": "login",
            "data": {
              "device_uid": "deviceUID12345678",
              "username": "admin",
              "pass": "md5pass1234567890",
              "rowsInt": [
                    {"id": 1, "name": "first" },
                    {"id": 2, "name": "seecond" }
              ]              
            },
            "rows": [
                {"id": 1, "name": "first" },
                {"id": 2, "name": "seecond" }
            ],
            "rowsIndexes": [100,101,102],
            "isActive": true,
            "startIdx": 17,
            "price": 45.78
          }';

        $data = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);
        $dto = new TestDTO($data);
        print_r($dto);

        $dtoData = new TestDTOData($data['data']);
        print_r($dtoData);
        // return ['dataFull' => $data, 'data' => $dtoData];
        return ['OK'];
    }
}
