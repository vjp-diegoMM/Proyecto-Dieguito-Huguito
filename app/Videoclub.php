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
            $this->registrarInfoSocio($socio);
        }
    }

    /**
     * Registra la información de un socio en el log
     */
    private function registrarInfoSocio(Cliente $socio): void
    {
        $info = [];
        if (method_exists($socio, 'getNumero')) {
            $info['numero'] = $socio->getNumero();
        }
        if (method_exists($socio, 'getNombre')) {
            $info['nombre'] = $socio->getNombre();
        }
        $this->logger->info('Socio', $info);
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
        $cliente = $this->obtenerClientePorNumero($numSocio);
        if (!$cliente) {
            return $this;
        }

        // validar y obtener soportes
        $soportes = $this->validarYObtenerSoportes($numerosProductos, $numSocio);
        if (empty($soportes)) {
            return $this;
        }

        // registrar alquileres
        $this->registrarAlquileres($cliente, $soportes, $numSocio);

        return $this;
    }

    /**
     * Obtiene un cliente por su número
     */
    private function obtenerClientePorNumero(int $numSocio): ?Cliente
    {
        foreach ($this->socios as $s) {
            if (method_exists($s, 'getNumero') && $s->getNumero() === $numSocio) {
                return $s;
            }
        }
        $this->logger->warning('Cliente no encontrado', ['numSocio' => $numSocio]);
        return null;
    }

    /**
     * Valida y obtiene todos los soportes para alquilar
     * @return array
     */
    private function validarYObtenerSoportes(array $numerosProductos, int $numSocio): array
    {
        $soportes = [];
        foreach ($numerosProductos as $numProd) {
            $soporte = $this->obtenerProductoPorNumero($numProd);
            if (!$soporte) {
                $this->logger->warning('Soporte no encontrado - no se realiza alquiler', ['numProd' => $numProd, 'numSocio' => $numSocio]);
                return [];
            }
            if (isset($soporte->alquilado) && $soporte->alquilado) {
                $this->logger->warning('Soporte ya está alquilado - no se realiza alquiler', ['numProd' => $numProd, 'numSocio' => $numSocio]);
                return [];
            }
            $soportes[] = $soporte;
        }
        return $soportes;
    }

    /**
     * Registra los alquileres en el cliente
     */
    private function registrarAlquileres(Cliente $cliente, array $soportes, int $numSocio): void
    {
        foreach ($soportes as $soporte) {
            $ok = $cliente->alquilar($soporte);
            if ($ok) {
                $cliente->setAlquiler($soporte);
                $this->numProductosAlquilados++;
                $this->numTotalAlquileres++;
                $this->logger->info('Soporte alquilado', ['soporte' => $soporte->getNumero(), 'cliente' => $numSocio]);
            } else {
                $this->logger->warning('No se pudo alquilar el soporte', ['soporte' => $soporte->getNumero(), 'cliente' => $numSocio]);
            }
        }
    }

    public function devolverSocioProducto(int $numSocio, int $numeroProducto)
    {
        return $this->devolverSocioProductos($numSocio, [$numeroProducto]);
    }

    public function devolverSocioProductos(int $numSocio, array $numerosProductos)
    {
        $cliente = $this->obtenerClientePorNumero($numSocio);
        if (!$cliente) {
            return $this;
        }

        foreach ($numerosProductos as $numProd) {
            $this->devolverProductoDelCliente($cliente, $numProd, $numSocio);
        }

        return $this;
    }

    /**
     * Obtiene un producto por su número
     */
    private function obtenerProductoPorNumero(int $numProd): ?Soporte
    {
        foreach ($this->productos as $p) {
            if ($p->getNumero() === $numProd) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Devuelve un producto individual del cliente
     */
    private function devolverProductoDelCliente(Cliente $cliente, int $numProd, int $numSocio): void
    {
        $soporte = $this->obtenerProductoPorNumero($numProd);
        if (!$soporte) {
            $this->logger->warning('Soporte no encontrado al devolver', ['numProd' => $numProd, 'numSocio' => $numSocio]);
            return;
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
}