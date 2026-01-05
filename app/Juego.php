<?php

namespace Dwes\ProyectoVideoclub;

class Juego extends Soporte
{
    private string $consola;
    private int $minJugadores;
    private int $maxJugadores;

    public function __construct(string $titulo, int $numero, float $precio, string $consola, int $minJugadores, int $maxJugadores, ?string $metacritic = null)
    {
        parent::__construct($titulo, $numero, $precio, $metacritic);
        $this->consola = $consola;
        $this->minJugadores = $minJugadores;
        $this->maxJugadores = $maxJugadores;
    }

    public function getConsola(): string
    {
        return $this->consola;
    }

    public function getPuntuacion(): ?float
    {
        return $this->fetchMetacriticScore();
    }

    public function muestraResumen(): string
    {
        $titulo = htmlspecialchars($this->getTitulo(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $mensaje = "<div>Juego #{$this->getNumero()}: {$titulo} — {$this->consola} — {$this->getPrecio()}€" . ($this->alquilado ? " (alquilado)" : "") . "</div>\n";
        echo $mensaje;
        return $mensaje;
    }
}