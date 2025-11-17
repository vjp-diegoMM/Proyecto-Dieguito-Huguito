<?php

namespace Dwes\ProyectoVideoclub;

use Dwes\ProyectoVideoclub\Util\ClienteNoEncontradoException;
use Dwes\ProyectoVideoclub\Util\SoporteNoEncontradoException;
use Dwes\ProyectoVideoclub\Util\VideoclubException;

class Videoclub
{
    public array $productos = [];
    public array $socios = [];

    public int $numProductosAlquilados = 0;
    public int $numTotalAlquileres = 0;
    public int $numSocios = 0;

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
    }

    public function incluirJuego($titulo, $precio, $consola, $minNumeroJugadores, $maxNumeroJugadores)
    {
        $numero = count($this->productos) + 1;
        $juego = new Juego($titulo, $numero, $precio, $consola, $minNumeroJugadores, $maxNumeroJugadores);
        $this->productos[] = $juego;
    }

    public function incluirDvd($titulo, $precio, $idiomas, $formatoPantalla)
    {
        $numero = count($this->productos) + 1;
        $dvd = new Dvd($titulo, $numero, $precio, $idiomas, $formatoPantalla);
        $this->productos[] = $dvd;
    }

    public function incluirSocio(string $nombre, string $user, string $password, int $maxAlquileresConcurrentes = 3): void
    {
        $this->numSocios++;
        $this->socios[] = new Cliente($nombre, $this->numSocios, $user, $password, $maxAlquileresConcurrentes);
    }

    public function listarProductos(): void
    {
        foreach ($this->productos as $p) {
            $p->muestraResumen();
        }
    }

    public function listarSocios(): void
    {
        foreach ($this->socios as $socio) {
            echo "<br>";
            if (method_exists($socio, 'getNumero')) {
                echo "Socio #" . $socio->getNumero() . ": ";
            }
            if (method_exists($socio, 'getNombre')) {
                echo $socio->getNombre();
            } else {
                echo "Nombre no disponible";
            }
        }
    }

    public function alquilaSocioProducto(int $numSocio, $numerosProductos)
    {
        if (is_int($numerosProductos)) {
            $numerosProductos = [$numerosProductos];
        } elseif (!is_array($numerosProductos)) {
            echo "Parámetros inválidos para alquiler.<br>";
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
            echo "Cliente no encontrado.<br>";
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
                echo "Soporte #$numProd no encontrado. No se realiza ningún alquiler.<br>";
                return $this;
            }
            if (isset($encontrado->alquilado) && $encontrado->alquilado) {
                echo "Soporte #$numProd ya está alquilado. No se realiza ningún alquiler.<br>";
                return $this;
            }
            $soportesAAlquilar[] = $encontrado;
        }

        foreach ($soportesAAlquilar as $soporte) {
            $ok = $cliente->alquilar($soporte);
            if ($ok) {
                $this->numProductosAlquilados++;
                $this->numTotalAlquileres++;
            } else {
                echo "No se pudo alquilar el soporte " . $soporte->getNumero() . " al cliente " . $cliente->getNombre() . "<br>";
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
            echo "Cliente no encontrado.<br>";
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
                echo "Soporte #$numProd no encontrado.<br>";
                continue;
            }

            $devuelto = $cliente->devolver($numProd);
            if ($devuelto) {
                $soporte->alquilado = false;
                if ($this->numProductosAlquilados > 0) {
                    $this->numProductosAlquilados--;
                }
            } else {
                echo "No se pudo devolver el soporte #$numProd para el socio $numSocio<br>";
            }
        }

        return $this;
    }
}
