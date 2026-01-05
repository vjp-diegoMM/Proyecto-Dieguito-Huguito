<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Dwes\ProyectoVideoclub\Cliente;
use Dwes\ProyectoVideoclub\CintaVideo;
use Dwes\ProyectoVideoclub\Dvd;
use Dwes\ProyectoVideoclub\Juego;
use Dwes\ProyectoVideoclub\Util\CupoSuperadoException;
use Dwes\ProyectoVideoclub\Util\SoporteYaAlquiladoException;

final class ClienteTest extends TestCase
{
    public function testAlquilarSuccess(): void
    {
        $cliente = new Cliente('Ana', 1, 'ana', 'pass', 3);
        $soporte = new CintaVideo('Pelicula', 101, 2.5, 90);
        $this->assertTrue($cliente->alquilar($soporte));
        $this->assertTrue($soporte->alquilado);
        $this->assertCount(1, $cliente->getAlquileres());
    }

    public function testDevolverUnmarksAndRemoves(): void
    {
        $cliente = new Cliente('Ana', 1, 'ana', 'pass', 3);
        $soporte = new CintaVideo('Pelicula', 101, 2.5, 90);
        $cliente->alquilar($soporte);
        $this->assertTrue($cliente->devolver(101));
        $this->assertFalse($soporte->alquilado);
        $this->assertCount(0, $cliente->getAlquileres());
    }

    /**
     * @dataProvider cupoProvider
     */
    public function testCupoSuperadoProvider(int $max): void
    {
        $cliente = new Cliente('Pepe', 2, 'pepe', 'pass', $max);
        // alquilar hasta el máximo
        for ($i = 1; $i <= $max; $i++) {
            $s = new CintaVideo("P{$i}", 200 + $i, 1.5, 50);
            $this->assertTrue($cliente->alquilar($s));
        }
        // intentar alquilar uno más debe lanzar CupoSuperadoException
        $this->expectException(CupoSuperadoException::class);
        $extra = new CintaVideo('Extra', 999, 1.5, 50);
        $cliente->alquilar($extra);
    }

    public function cupoProvider(): array
    {
        return [
            [1],
            [2],
            [3],
        ];
    }

    public function testAlquilarSoporteYaAlquiladoThrows(): void
    {
        $cliente = new Cliente('Luis', 3, 'luis', 'pass', 3);
        $soporte = new CintaVideo('Pelicula', 111, 2.0, 80);
        $soporte->alquilado = true; // ya marcado como alquilado
        $this->expectException(SoporteYaAlquiladoException::class);
        $cliente->alquilar($soporte);
    }

    public function testSetAlquilerMarksAndAvoidsDuplicate(): void
    {
        $cliente = new Cliente('Marta', 4, 'marta', 'pass', 3);
        $soporte = new CintaVideo('Juego', 303, 3.0, 120);
        $cliente->setAlquiler($soporte);
        $this->assertTrue($soporte->alquilado);
        $this->assertCount(1, $cliente->getAlquileres());
        // llamar otra vez no duplica
        $cliente->setAlquiler($soporte);
        $this->assertCount(1, $cliente->getAlquileres());
    }

    public function testDevolverFalseWhenNotFound(): void
    {
        $cliente = new Cliente('Nadie', 5, 'nadie', 'pass', 3);
        $this->assertFalse($cliente->devolver(12345));
    }
}