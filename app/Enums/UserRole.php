<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'مدير النظام',
            self::Manager => 'مدير المتجر',
            self::Staff => 'موظف',
        };
    }

    /** مستوى الصلاحية: الأعلى يملك صلاحيات الأدنى. */
    public function level(): int
    {
        return match ($this) {
            self::Admin => 3,
            self::Manager => 2,
            self::Staff => 1,
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
