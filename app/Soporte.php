<?php

namespace Dwes\ProyectoVideoclub;

interface Resumible
{
    public function muestraResumen(): void;
}

abstract class Soporte implements Resumible
{
    private const IVA = 21;
    public bool $alquilado = false;

    // propiedades principales
    private string $titulo;
    private int $numero;
    private float $precio;
    private ?string $metacritic;

    public function __construct(string $titulo, int $numero, float $precio, ?string $metacritic = null)
    {
        $this->titulo = $titulo;
        $this->numero = $numero;
        $this->precio = $precio;
        $this->metacritic = $metacritic;
    }

    // getters básicos
    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    // metacritic getter / setter
    public function setMetacritic(?string $url): void
    {
        $this->metacritic = $url;
    }

    public function getMetacritic(): ?string
    {
        return $this->metacritic;
    }

    // método protegido que realiza la petición y parsea la puntuación de Metacritic
    // devuelve float con la puntuación (0-100) o null si no se obtiene
    protected function fetchMetacriticScore(): ?float
    {
        $url = $this->metacritic ?? '';
        if ($url === '') {
            return null;
        }

        // Asegurarse de que la URL empiece por http(s)
        if (!preg_match('#^https?://#i', $url)) {
            return null;
        }

        // usar cURL con user-agent para evitar bloqueos sencillos
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; VideoclubBot/1.0)');
        // permitir recibir respuesta incluso si sitio bloquea bots simples
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/html,application/xhtml+xml']);
        $html = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$html || $code !== 200) {
            return null;
        }

        // buscar spans con clase metascore_w o metascore_w large (estructura típica)
        if (preg_match('/<span[^>]*class=["\'][^"\']*metascore_w[^"\']*["\'][^>]*>\s*(\d{1,3})\s*<\/span>/i', $html, $m)) {
            return (float)$m[1];
        }

        // alternativa: buscar "metascore" seguido de número
        if (preg_match('/Metascore[^0-9]{0,20}(\d{1,3})/i', $html, $m2)) {
            return (float)$m2[1];
        }

        return null;
    }

    // método abstracto que deben implementar las subclases
    abstract public function getPuntuacion(): ?float;

    // Resumen básico (usa htmlspecialchars para evitar inyección al mostrar)
    public function muestraResumen(): void
    {
        $titulo = htmlspecialchars($this->titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        echo "<div>Soporte #{$this->numero}: {$titulo} — precio {$this->precio}€" . ($this->alquilado ? " (alquilado)" : "") . "</div>\n";
    }
}