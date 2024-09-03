<?php

namespace App\Enums;

enum Delivery: string
{
    case NOVA_POSHTA = 'nova_poshta';
    case UKR_POSHTA = 'ukr_poshta';
    case JUSTIN = 'justin';

    public static function fromSlug(string $slug): ?self
    {
        return match($slug) {
            'nova-poshta' => self::NOVA_POSHTA,
            'ukr-poshta' => self::UKR_POSHTA,
            'justin' => self::JUSTIN,
            default => null,
        };
    }

    public static function allSlugs(): array
    {
        return array_map(fn($case) => $case->slug(), self::cases());
    }

    public function slug(): string
    {
        return match($this) {
            self::NOVA_POSHTA => 'nova-poshta',
            self::UKR_POSHTA => 'ukr-poshta',
            self::JUSTIN => 'justin',
        };
    }
}
