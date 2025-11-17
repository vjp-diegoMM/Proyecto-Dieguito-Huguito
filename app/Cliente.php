<?php

namespace Dwes\ProyectoVideoclub;
use Dwes\ProyectoVideoclub;

use Dwes\ProyectoVideoclub\Util\SoporteYaAlquiladoException;
use Dwes\ProyectoVideoclub\Util\CupoSuperadoException;
use Dwes\ProyectoVideoclub\Util\SoporteNoEncontradoException;

class Cliente
{
    private array $soportesAlquilados = [];
    private int $numSoportesAlquilados = 0;
    private string $usuario;
    private string $contrasena;
    private $alquileres = [];

    public function __construct(
        private string $nombre,
        private int $numero,
        string $user,
        string $contrasena,
        private int $maxAlquilerConcurrente = 3
    ) {
        $this->usuario = $user;
        $this->contrasena = $contrasena;
    }

    public function setAlquiler($alquiler)
    {
        $this->alquileres[] = $alquiler;
    }

    public function getAlquileres() : array
    {
        return $this->alquileres;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function getContrasena(): string
    {
        return $this->contrasena;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): void
    {
        $this->numero = $numero;
    }

    public function tieneAlquilado(Soporte $s): bool
    {
        foreach ($this->soportesAlquilados as $al) {
            if ($al->getNumero() === $s->getNumero()) {
                return true;
            }
        }
        return false;
    }

    public function alquilar(Soporte $s)
    {
        if ($s->alquilado) {
            throw new SoporteYaAlquiladoException('El soporte ya está alquilado');
        }
        if ($this->numSoportesAlquilados >= $this->maxAlquilerConcurrente) {
            throw new CupoSuperadoException('Se ha superado el cupo de alquileres del cliente');
        }

        $this->soportesAlquilados[] = $s;
        $this->numSoportesAlquilados++;

        $s->alquilado = true;

        echo "<br>Alquilado soporte a: " . $this->nombre . "<br><br>";
        $s->muestraResumen();
        echo "<br>";

        return $this;
    }

    public function devolver(int $numSoporte): self
    {
        foreach ($this->soportesAlquilados as $index => $soporte) {
            if ($soporte->getNumero() === $numSoporte) {
                $soporte->alquilado = false;

                unset($this->soportesAlquilados[$index]);
                $this->soportesAlquilados = array_values($this->soportesAlquilados);
                $this->numSoportesAlquilados--;
                return $this;
            }
        }
        throw new SoporteNoEncontradoException('El soporte no está alquilado por este cliente');
    }

    public function listarAlquileres(): void
    {
        echo "Cliente: {$this->nombre} <br>";
        echo "Alquileres: {$this->numSoportesAlquilados} <br>";
        foreach ($this->soportesAlquilados as $al) {
            $al->muestraResumen();
        }
    }

    public function muestraResumen(): void
    {
        echo "{$this->nombre} ({$this->numero}) - Usuario: {$this->usuario} - Alquileres: {$this->numSoportesAlquilados} <br>";
    }
}
