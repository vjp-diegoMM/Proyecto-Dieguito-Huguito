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
    private array $soportesAlquilados = [];
    private int $numSoportesAlquilados = 0;
    private string $usuario;
    private string $contrasena;
    private $alquileres = [];

    private Logger $logger;

    public function __construct(
        private string $nombre,
        private int $numero,
        string $user,
        string $contrasena,
        private int $maxAlquilerConcurrente = 3
    ) {
        $this->usuario = $user;
        $this->contrasena = $contrasena;

        
        $logFile = dirname(__DIR__) . '/logs/videoclub.log';
        $this->logger = new Logger('VideoclubLogger');
        $this->logger->pushHandler(new StreamHandler($logFile, Level::Debug));
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

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setUsuario(string $usuario): void
    {
        $this->usuario = $usuario;
    }

    public function setContrasena(string $contrasena): void
    {
        $this->contrasena = $contrasena;
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
            $this->logger->warning('Intento de alquiler: el soporte ya está alquilado', ['soporte' => $s->getNumero(), 'cliente' => $this->numero]);
            throw new SoporteYaAlquiladoException('El soporte ya está alquilado');
        }
        if ($this->numSoportesAlquilados >= $this->maxAlquilerConcurrente) {
            $this->logger->warning('Cupo superado al intentar alquilar', ['cliente' => $this->numero, 'max' => $this->maxAlquilerConcurrente]);
            throw new CupoSuperadoException('Se ha superado el cupo de alquileres del cliente');
        }

        $this->soportesAlquilados[] = $s;
        $this->numSoportesAlquilados++;

        $s->alquilado = true;

        // sustituido echo por log info
        $this->logger->info("Alquilado soporte a: {$this->nombre}", ['cliente' => $this->numero, 'soporte' => $s->getNumero()]);

        $s->muestraResumen();

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
                $this->logger->info("Devolución de soporte {$numSoporte} por cliente {$this->numero}");
                return $this;
            }
        }
        $this->logger->warning('Devolución fallida: el soporte no está alquilado por este cliente', ['cliente' => $this->numero, 'soporte' => $numSoporte]);
        throw new SoporteNoEncontradoException('El soporte no está alquilado por este cliente');
    }

    public function listarAlquileres(): void
    {
        // sustituido echo por log info
        $this->logger->info("Cliente: {$this->nombre}", ['cliente' => $this->numero]);
        $this->logger->info("Alquileres: {$this->numSoportesAlquilados}", ['cliente' => $this->numero]);

        foreach ($this->soportesAlquilados as $al) {
            $al->muestraResumen();
        }
    }

    public function muestraResumen(): void
    {
        echo "{$this->nombre} ({$this->numero}) - Usuario: {$this->usuario} - Alquileres: {$this->numSoportesAlquilados} <br>";
    }
}