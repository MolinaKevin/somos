<?php

namespace App\Enums;

enum SealState: int
{
    case NONE = 0;
    case PARTIAL = 1;
    case FULL = 2;

    /**
     * Devuelve el texto asociado al estado.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE => 'none',
            self::PARTIAL => 'partial',
            self::FULL => 'full',
        };
    }

    /**
     * Devuelve un mapa de estados con su representación de texto.
     *
     * @return array
     */
    public static function states(): array
    {
        return [
            'none' => self::NONE->value,
            'partial' => self::PARTIAL->value,
            'full' => self::FULL->value,
        ];
    }

    /**
     * Normaliza un arreglo de seals con sus estados.
     *
     * @param array $seals
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function normalize(array $seals): array
    {
        return array_map(function ($seal) {
            if (is_string($seal['state'])) {
                $states = self::states();
                $seal['state'] = $states[$seal['state']] 
                    ?? throw new \InvalidArgumentException('Invalid seal state: ' . $seal['state']);
            }
            return $seal;
        }, $seals);
    }
}

