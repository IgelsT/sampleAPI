<?php

namespace DTO\JSON;

/*
    String
    Integer
    Float
    Boolean
    Array
    Object
*/

class Unmarshal
{
    public static function unmarshalArray(?array $data, $classInstance)
    {
        if($data == null || count($data) == 0) return $classInstance;
        
        $refClass = new \ReflectionClass($classInstance);
        $props = $refClass->getProperties();

        foreach ($props as $prop) {
            $classPropertyName = $jsonPropertyName = $prop->getName();
            $classPropertyType = $classPropertySubType = $prop->getType()->getName();
            $attributes = $prop->getAttributes(JSONAttribute::class);

            foreach ($attributes as $attr) {
                $jsonAttribute = $attr->newInstance();
                if ($jsonAttribute instanceof JSONAttribute) {
                    if ($jsonAttribute->field != null) $jsonPropertyName = $jsonAttribute->field;
                    if ($jsonAttribute->type != null) $classPropertySubType = $jsonAttribute->type;
                }
            }

            if (isset($data[$jsonPropertyName])) {
                if (
                    $classPropertyType == 'string' || $classPropertyType == 'float'
                    || $classPropertyType == 'bool' || $classPropertyType == 'int'
                ) {
                    $classInstance->$classPropertyName = $data[$jsonPropertyName];
                } else if ($classPropertyType == 'array') {
                    if ($classPropertySubType == 'array') {
                        $classInstance->$classPropertyName = $data[$jsonPropertyName];
                    } else {
                        foreach ($data[$jsonPropertyName] as $row) {
                            $classSubInstance = new $classPropertySubType();        
                            $classInstance->$classPropertyName[] = self::unmarshalArray($row, $classSubInstance);
                        }
                    }
                } else {
                    $classSubInstance = new $classPropertySubType();
                    $classInstance->$classPropertyName = self::unmarshalArray($data[$jsonPropertyName], $classSubInstance);
                }
            }
        }

        return $classInstance;
    }
}
