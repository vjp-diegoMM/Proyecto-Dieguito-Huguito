<?php

namespace Dwes\ProyectoVideoclub;
use Dwes\ProyectoVideoclub;

use Dwes\ProyectoVideoclub\Util\SoporteYaAlquiladoException;
use Dwes\ProyectoVideoclub\Util\CupoSuperadoException;
use Dwes\ProyectoVideoclub\Util\SoporteNoEncontradoException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

class Cliente
{
    private string $nombre;
    private int $numero;
    private string $usuario;
    private string $contrasena;
    private int $maxAlquileres;
    /** @var Soporte[] */
    private array $alquileres = [];

    public function __construct(string $nombre, int $numero, string $usuario, string $contrasena, int $maxAlquileres = 3)
    {
        $this->nombre = $nombre;
        $this->numero = $numero;
        $this->usuario = $usuario;
        $this->contrasena = $contrasena;
        $this->maxAlquileres = $maxAlquileres;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function getContrasena(): string
    {
        return $this->contrasena;
    }

    /**
     * Intenta alquilar un soporte.
     * Devuelve true si se completó, lanza excepción en errores.
     * @throws CupoSuperadoException
     * @throws SoporteYaAlquiladoException
     */
    public function alquilar(Soporte $s): bool
    {
        if (count($this->alquileres) >= $this->maxAlquileres) {
            throw new CupoSuperadoException("Cupo de alquileres superado (max {$this->maxAlquileres}).");
        }
        if ($s->alquilado) {
            throw new SoporteYaAlquiladoException("El soporte #{$s->getNumero()} ya está alquilado.");
        }
        // marcar y añadir
        $s->alquilado = true;
        $this->alquileres[] = $s;
        return true;
    }

    /**
     * Forzar registro de un alquiler (usado por Videoclub para asegurar consistencia).
     */
    public function setAlquiler(Soporte $s): void
    {
        // evitar duplicados
        foreach ($this->alquileres as $a) {
            if ($a->getNumero() === $s->getNumero()) {
                return;
            }
        }
        $s->alquilado = true;
        $this->alquileres[] = $s;
    }

    /**
     * Devuelve un soporte por número. Devuelve true si la devolución fue correcta.
     */
    public function devolver(int $numeroProducto): bool
    {
        foreach ($this->alquileres as $i => $a) {
            if ($a->getNumero() === $numeroProducto) {
                // marcar como no alquilado y quitar del array
                $a->alquilado = false;
                array_splice($this->alquileres, $i, 1);
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene array de alquileres (Soporte[]).
     * @return Soporte[]
     */
    public function getAlquileres(): array
    {
        return $this->alquileres;
    }
}