<?php

namespace Dwes\ProyectoVideoclub;

/**
 * Clase Bluray que representa un soporte Bluray
 */
class Bluray extends Soporte
{
    private int $duracion;
    private bool $is4k;

    public function __construct(
        string $titulo,
        int $numero,
        float $precio,
        int $duracion,
        bool $is4k,
        ?string $metacriticUrl = null
    ) {
        parent::__construct($titulo, $numero, $precio, $metacriticUrl);
        $this->duracion = $duracion;
        $this->is4k = $is4k;
    }

    public function getDuracion(): int
    {
        return $this->duracion;
    }

    public function isIs4k(): bool
    {
        return $this->is4k;
    }

    public function getPuntuacion(): ?float
    {
        return $this->fetchMetacriticScore();
    }

    public function muestraResumen(): string
    {
        $tipo4k = $this->is4k ? '4K' : 'HD';
        $mensaje = "<div>Bluray #{$this->numero}: {$this->titulo} — {$this->duracion} min ({$tipo4k}) — {$this->precio}€</div>";
        echo $mensaje;
        return $mensaje;
    }
}
