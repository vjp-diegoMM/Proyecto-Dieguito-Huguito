<?php

namespace Dwes\ProyectoVideoclub;

class Dvd extends Soporte
{
    private $idiomas;
    private $formatoPantalla;
    private int $duracion;

    public function __construct(string $titulo, int $numero, float $precio, $idiomas, $formatoPantalla, int $duracion = 0, ?string $metacritic = null)
    {
        parent::__construct($titulo, $numero, $precio, $metacritic);
        $this->idiomas = $idiomas;
        $this->formatoPantalla = $formatoPantalla;
        $this->duracion = $duracion;
    }

    public function getIdiomas()
    {
        return $this->idiomas;
    }

    public function getDuracion(): int
    {
        return $this->duracion;
    }

    public function getPuntuacion(): ?float
    {
        return $this->fetchMetacriticScore();
    }

    public function muestraResumen(): string
    {
        $titulo = htmlspecialchars($this->getTitulo(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $idiomas = is_array($this->idiomas) ? implode(',', $this->idiomas) : (string)$this->idiomas;
        $duracionStr = $this->duracion > 0 ? " — {$this->duracion} min" : "";
        $mensaje = "<div>DVD #{$this->getNumero()}: {$titulo} — {$idiomas}{$duracionStr} — {$this->getPrecio()}€" . ($this->alquilado ? " (alquilado)" : "") . "</div>\n";
        echo $mensaje;
        return $mensaje;
    }
}