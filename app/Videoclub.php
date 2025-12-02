<?php

namespace Dwes\ProyectoVideoclub;

use Dwes\ProyectoVideoclub\Util\ClienteNoEncontradoException;
use Dwes\ProyectoVideoclub\Util\SoporteNoEncontradoException;
use Dwes\ProyectoVideoclub\Util\VideoclubException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

class Videoclub
{
    public array $productos = [];
    public array $socios = [];

    public int $numProductosAlquilados = 0;
    public int $numTotalAlquileres = 0;
    public int $numSocios = 0;

    private Logger $logger;

    public function __construct()
    {
        $logFile = dirname(__DIR__) . '/logs/videoclub.log';
        $this->logger = new Logger('VideoclubLogger');
        $this->logger->pushHandler(new StreamHandler($logFile, Level::Debug));
    }

    public function getNumProductosAlquilados(): int
    {
        return $this->numProductosAlquilados;
    }

    public function getNumTotalAlquileres(): int
    {
        return $this->numTotalAlquileres;
    }

    public function incluirCintaVideo($titulo, $precio, $duracion)
    {
        $numero = count($this->productos) + 1;
        $cintaVideo = new CintaVideo($titulo, $numero, $precio, $duracion);
        $this->productos[] = $cintaVideo;

        $this->logger->info('Incluida cinta de video', ['titulo' => $titulo, 'numero' => $numero, 'precio' => $precio, 'duracion' => $duracion]);
    }

    public function incluirJuego($titulo, $precio, $consola, $minNumeroJugadores, $maxNumeroJugadores)
    {
        $numero = count($this->productos) + 1;
        $juego = new Juego($titulo, $numero, $precio, $consola, $minNumeroJugadores, $maxNumeroJugadores);
        $this->productos[] = $juego;

        $this->logger->info('Incluido juego', ['titulo' => $titulo, 'numero' => $numero, 'precio' => $precio, 'consola' => $consola]);
    }

    public function incluirDvd($titulo, $precio, $idiomas, $formatoPantalla)
    {
        $numero = count($this->productos) + 1;
        $dvd = new Dvd($titulo, $numero, $precio, $idiomas, $formatoPantalla);
        $this->productos[] = $dvd;

        $this->logger->info('Incluido DVD', ['titulo' => $titulo, 'numero' => $numero, 'precio' => $precio, 'idiomas' => $idiomas]);
    }

    public function incluirSocio(string $nombre, string $user, string $password, int $maxAlquileresConcurrentes = 3): void
    {
        $this->numSocios++;
        $this->socios[] = new Cliente($nombre, $this->numSocios, $user, $password, $maxAlquileresConcurrentes);

        $this->logger->info('Incluido socio', ['nombre' => $nombre, 'numSocio' => $this->numSocios, 'usuario' => $user, 'maxAlquileres' => $maxAlquileresConcurrentes]);
    }

    public function listarProductos(): void
    {
        $this->logger->info('Listar productos', ['total' => count($this->productos)]);
        foreach ($this->productos as $p) {
            $p->muestraResumen();
        }
    }

    public function listarSocios(): void
    {
        $this->logger->info('Listar socios', ['total' => count($this->socios)]);
        foreach ($this->socios as $socio) {
            $info = [];
            if (method_exists($socio, 'getNumero')) {
                $info['numero'] = $socio->getNumero();
            }
            if (method_exists($socio, 'getNombre')) {
                $info['nombre'] = $socio->getNombre();
            } else {
                $info['nombre'] = 'Nombre no disponible';
            }
            $this->logger->info('Socio', $info);
        }
    }

    public function alquilaSocioProducto(int $numSocio, $numerosProductos)
    {
        if (is_int($numerosProductos)) {
            $numerosProductos = [$numerosProductos];
        } elseif (!is_array($numerosProductos)) {
            $this->logger->warning('Parámetros inválidos para alquiler', ['param' => $numerosProductos, 'numSocio' => $numSocio]);
            return $this;
        }

        $cliente = null;
        foreach ($this->socios as $s) {
            if (method_exists($s, 'getNumero') && $s->getNumero() === $numSocio) {
                $cliente = $s;
                break;
            }
        }
        if (!$cliente) {
            $this->logger->warning('Cliente no encontrado', ['numSocio' => $numSocio]);
            return $this;
        }
        $soportesAAlquilar = [];
        foreach ($numerosProductos as $numProd) {
            $encontrado = null;
            foreach ($this->productos as $p) {
                if ($p->getNumero() === $numProd) {
                    $encontrado = $p;
                    break;
                }
            }
            if (!$encontrado) {
                $this->logger->warning('Soporte no encontrado - no se realiza alquiler', ['numProd' => $numProd, 'numSocio' => $numSocio]);
                return $this;
            }
            if (isset($encontrado->alquilado) && $encontrado->alquilado) {
                $this->logger->warning('Soporte ya está alquilado - no se realiza alquiler', ['numProd' => $numProd, 'numSocio' => $numSocio]);
                return $this;
            }
            $soportesAAlquilar[] = $encontrado;
        }

        foreach ($soportesAAlquilar as $soporte) {
            $ok = $cliente->alquilar($soporte);
            if ($ok) {
                $this->numProductosAlquilados++;
                $this->numTotalAlquileres++;
                $this->logger->info('Soporte alquilado', ['soporte' => $soporte->getNumero(), 'cliente' => $numSocio]);
            } else {
                $this->logger->warning('No se pudo alquilar el soporte', ['soporte' => $soporte->getNumero(), 'cliente' => $numSocio]);
            }
        }

        return $this;
    }

    public function devolverSocioProducto(int $numSocio, int $numeroProducto)
    {
        return $this->devolverSocioProductos($numSocio, [$numeroProducto]);
    }

    public function devolverSocioProductos(int $numSocio, array $numerosProductos)
    {
        $cliente = null;
        foreach ($this->socios as $s) {
            if (method_exists($s, 'getNumero') && $s->getNumero() === $numSocio) {
                $cliente = $s;
                break;
            }
        }
        if (!$cliente) {
            $this->logger->warning('Cliente no encontrado al devolver', ['numSocio' => $numSocio]);
            return $this;
        }

        foreach ($numerosProductos as $numProd) {
            $soporte = null;
            foreach ($this->productos as $p) {
                if ($p->getNumero() === $numProd) {
                    $soporte = $p;
                    break;
                }
            }
            if (!$soporte) {
                $this->logger->warning('Soporte no encontrado al devolver', ['numProd' => $numProd, 'numSocio' => $numSocio]);
                continue;
            }

            $devuelto = $cliente->devolver($numProd);
            if ($devuelto) {
                $soporte->alquilado = false;
                if ($this->numProductosAlquilados > 0) {
                    $this->numProductosAlquilados--;
                }
                $this->logger->info('Soporte devuelto', ['soporte' => $numProd, 'cliente' => $numSocio]);
            } else {
                $this->logger->warning('No se pudo devolver el soporte', ['soporte' => $numProd, 'cliente' => $numSocio]);
            }
        }

        return $this;
    }
}