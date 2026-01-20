<?php

namespace App\Enums;

enum UserRole: int
{
    case VENDEDOR = 0;
    case ADMINISTRADOR = 1;
    case SUPERVISOR = 2;

    /**
     * Obtener el nombre legible del rol
     */
    public function label(): string
    {
        return match($this) {
            self::VENDEDOR => 'Vendedor',
            self::ADMINISTRADOR => 'Administrador',
            self::SUPERVISOR => 'Supervisor',
        };
    }

    /**
     * Verificar si el rol es vendedor
     */
    public function isVendedor(): bool
    {
        return $this === self::VENDEDOR;
    }

    /**
     * Verificar si el rol es administrador
     */
    public function isAdministrador(): bool
    {
        return $this === self::ADMINISTRADOR;
    }

    /**
     * Verificar si el rol es supervisor
     */
    public function isSupervisor(): bool
    {
        return $this === self::SUPERVISOR;
    }

    /**
     * Verificar si el rol tiene permisos de administrador o supervisor
     */
    public function isAdminOrSupervisor(): bool
    {
        return $this === self::ADMINISTRADOR || $this === self::SUPERVISOR;
    }

    /**
     * Obtener todos los roles como array para validaciones
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
