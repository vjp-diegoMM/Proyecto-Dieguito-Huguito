<?php

namespace Dwes\ProyectoVideoclub;

class CintaVideo extends Soporte
{
    private int $duracion;

    public function __construct(string $titulo, int $numero, float $precio, int $duracion, ?string $metacritic = null)
    {
        parent::__construct($titulo, $numero, $precio, $metacritic);
        $this->duracion = $duracion;
    }

    public function getDuracion(): int
    {
        return $this->duracion;
    }

    public function getPuntuacion(): ?float
    {
        return $this->fetchMetacriticScore();
    }

    // opcional: override muestraResumen para incluir duracion
    public function muestraResumen(): string
    {
        $titulo = htmlspecialchars($this->getTitulo(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $mensaje = "<div>CintaVideo #{$this->getNumero()}: {$titulo} — {$this->duracion} min — {$this->getPrecio()}€" . ($this->alquilado ? " (alquilado)" : "") . "</div>\n";
        echo $mensaje;
        return $mensaje;
    }
}