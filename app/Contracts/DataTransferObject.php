<?php

namespace App\Contracts;

use ReflectionClass;

abstract class DataTransferObject
{
    public static function from(array $data): static
    {
        $dto = new static();

        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        return $dto;
    }

    public static function toArray(self $dto): array
    {
        $reflection = new ReflectionClass($dto);
        $properties = $reflection->getProperties();

        $data = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $dto->$name ?? null;

            if ($value instanceof self) {
                $data[$name] = static::toArray($value);
            } elseif (is_array($value)) {
                $data[$name] = array_map(function ($item) {
                    return $item instanceof self ? static::toArray($item) : $item;
                }, $value);
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    public static function toJson(self $dto, int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode(static::toArray($dto), $options);
    }
}
