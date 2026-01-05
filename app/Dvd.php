<?php

namespace Dwes\ProyectoVideoclub;

class Dvd extends Soporte
{
    private $idiomas;
    private $formatoPantalla;

    public function __construct(string $titulo, int $numero, float $precio, $idiomas, $formatoPantalla, ?string $metacritic = null)
    {
        parent::__construct($titulo, $numero, $precio, $metacritic);
        $this->idiomas = $idiomas;
        $this->formatoPantalla = $formatoPantalla;
    }

    public function getIdiomas()
    {
        return $this->idiomas;
    }

    public function getPuntuacion(): ?float
    {
        return $this->fetchMetacriticScore();
    }

    public function muestraResumen(): void
    {
        $titulo = htmlspecialchars($this->getTitulo(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $idiomas = is_array($this->idiomas) ? implode(',', $this->idiomas) : (string)$this->idiomas;
        echo "<div>DVD #{$this->getNumero()}: {$titulo} — {$idiomas} — {$this->getPrecio()}€" . ($this->alquilado ? " (alquilado)" : "") . "</div>\n";
    }
}
?>