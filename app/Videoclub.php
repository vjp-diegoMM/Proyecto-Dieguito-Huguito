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
    private ?string $direccion;

    public function __construct(?string $direccion = null)
    {
        $this->direccion = $direccion;
        // preparar directorio de logs
        $logsDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0777, true);
        }
        $logFile = $logsDir . '/videoclub.log';
        $this->logger = new Logger('VideoclubLogger');
        $this->logger->pushHandler(new StreamHandler($logFile, Level::Debug));
        $this->logger->info('Videoclub inicializado', ['direccion' => $direccion]);
    }

    public function getNumProductosAlquilados(): int
    {
        return $this->numProductosAlquilados;
    }

    public function getNumTotalAlquileres(): int
    {
        return $this->numTotalAlquileres;
    }

    public function incluirCintaVideo( $metacriticUrl, string $titulo, float $precio, int $duracion)
    {
        $numero = count($this->productos) + 1;
        $cintaVideo = new CintaVideo($titulo, $numero, $precio, $duracion, $metacriticUrl);
        $this->productos[] = $cintaVideo;

        $this->logger->info('Incluida cinta de video', [
            'titulo' => $titulo,
            'numero' => $numero,
            'precio' => $precio,
            'duracion' => $duracion,
            'metacritic' => $metacriticUrl
        ]);
    }

    public function incluirJuego( $metacriticUrl, string $titulo, float $precio, string $consola, int $minNumeroJugadores, int $maxNumeroJugadores)
    {
        $numero = count($this->productos) + 1;
        $juego = new Juego($titulo, $numero, $precio, $consola, $minNumeroJugadores, $maxNumeroJugadores, $metacriticUrl);
        $this->productos[] = $juego;

        $this->logger->info('Incluido juego', [
            'titulo' => $titulo,
            'numero' => $numero,
            'precio' => $precio,
            'consola' => $consola,
            'metacritic' => $metacriticUrl
        ]);
    }

    public function incluirDvd( $metacriticUrl, string $titulo, float $precio, $idiomas, $formatoPantalla)
    {
        $numero = count($this->productos) + 1;
        $dvd = new Dvd($titulo, $numero, $precio, $idiomas, $formatoPantalla, $metacriticUrl);
        $this->productos[] = $dvd;

        $this->logger->info('Incluido DVD', [
            'titulo' => $titulo,
            'numero' => $numero,
            'precio' => $precio,
            'idiomas' => $idiomas,
            'metacritic' => $metacriticUrl
        ]);
    }

    public function incluirSocio(string $nombre, string $user, string $password, int $maxAlquileresConcurrentes = 3): void
    {
        $this->numSocios++;
        $this->socios[] = new Cliente($nombre, $this->numSocios, $user, $password, $maxAlquileresConcurrentes);

        $this->logger->info('Incluido socio', [
            'nombre' => $nombre,
            'numSocio' => $this->numSocios,
            'usuario' => $user,
            'maxAlquileres' => $maxAlquileresConcurrentes
        ]);
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
            $info = [
                'numero' => method_exists($socio, 'getNumero') ? $socio->getNumero() : null,
                'nombre' => method_exists($socio, 'getNombre') ? $socio->getNombre() : null
            ];
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

        // buscar cliente
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
                // registrar el alquiler también en getAlquileres() del cliente (setAlquiler evita duplicados)
                $cliente->setAlquiler($soporte);

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