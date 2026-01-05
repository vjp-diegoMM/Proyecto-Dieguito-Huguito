<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Dwes\ProyectoVideoclub\Videoclub;
use Dwes\ProyectoVideoclub\Cliente;
use Dwes\ProyectoVideoclub\Util\CupoSuperadoException;
use Dwes\ProyectoVideoclub\Util\SoporteYaAlquiladoException;

final class VideoclubTest extends TestCase
{
    public function testIncluirProductosYSocios(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'Película A', 2.5, 100);
        $vc->incluirDvd(null, 'DVD B', 3.0, ['es','en'], '16:9');
        $vc->incluirJuego(null, 'Juego C', 4.0, 'PS5', 1, 4);

        $this->assertCount(3, $vc->productos);

        $vc->incluirSocio('Ana', 'ana', 'pass');
        $vc->incluirSocio('Luis', 'luis', 'pass', 2);

        $this->assertCount(2, $vc->socios);
        $this->assertEquals(2, $vc->numSocios);
    }

    public function testAlquilarUnSoloProductoYContadores(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'Película A', 2.5, 100);
        $vc->incluirSocio('Ana', 'ana', 'pass');

        // socio número 1, producto 1
        $vc->alquilaSocioProducto(1, 1);

        $this->assertTrue($vc->productos[0]->alquilado);
        $this->assertEquals(1, $vc->getNumProductosAlquilados());
        $this->assertEquals(1, $vc->getNumTotalAlquileres());

        $cliente = $vc->socios[0];
        $this->assertCount(1, $cliente->getAlquileres());
    }

    public function testAlquilarMultiplesProductos(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirDvd(null, 'P2', 1.5, ['es'], '4:3');
        $vc->incluirJuego(null, 'P3', 2.0, 'Switch', 1, 2);
        $vc->incluirSocio('Pepe', 'pepe', 'pass', 5);

        // alquilar productos 1 y 3
        $vc->alquilaSocioProducto(1, [1, 3]);

        $this->assertTrue($vc->productos[0]->alquilado);
        $this->assertTrue($vc->productos[2]->alquilado);

        $this->assertEquals(2, $vc->getNumProductosAlquilados());
        $this->assertEquals(2, $vc->getNumTotalAlquileres());

        $this->assertCount(2, $vc->socios[0]->getAlquileres());
    }

    public function testNoAlquilaSiProductoYaAlquilado(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirSocio('A', 'a', 'p');
        $vc->incluirSocio('B', 'b', 'p');

        // socio 1 alquila producto 1
        $vc->alquilaSocioProducto(1, 1);
        $this->assertTrue($vc->productos[0]->alquilado);
        $this->assertEquals(1, $vc->getNumProductosAlquilados());

        // socio 2 intenta alquilar el mismo producto -> no debe incrementar contadores ni cambiar alquileres
        $vc->alquilaSocioProducto(2, 1);
        $this->assertEquals(1, $vc->getNumProductosAlquilados());
        $this->assertEquals(1, $vc->getNumTotalAlquileres());

        // socio 2 no debe tener alquileres
        $this->assertCount(0, $vc->socios[1]->getAlquileres());
    }

    public function testDevolverSocioProducto(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirSocio('A', 'a', 'p');

        $vc->alquilaSocioProducto(1, 1);
        $this->assertTrue($vc->productos[0]->alquilado);
        $this->assertEquals(1, $vc->getNumProductosAlquilados());

        $vc->devolverSocioProducto(1, 1);

        $this->assertFalse($vc->productos[0]->alquilado);
        $this->assertEquals(0, $vc->getNumProductosAlquilados());
        $this->assertCount(0, $vc->socios[0]->getAlquileres());
    }

    public function testAlquilarConClienteOProductoInexistenteNoRompe(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirSocio('A', 'a', 'p');

        // cliente inexistente -> no excepción, no cambios
        $vc->alquilaSocioProducto(99, 1);
        $this->assertEquals(0, $vc->getNumProductosAlquilados());

        // producto inexistente -> no excepción, no cambios
        $vc->alquilaSocioProducto(1, 999);
        $this->assertEquals(0, $vc->getNumProductosAlquilados());
    }

    public function testExcepcionCupoSuperado(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirCintaVideo(null, 'P2', 1.0, 60);
        $vc->incluirCintaVideo(null, 'P3', 1.0, 60);
        $vc->incluirCintaVideo(null, 'P4', 1.0, 60);
        $vc->incluirSocio('Juan', 'juan', 'pass', 2);

        $vc->alquilaSocioProducto(1, 1);
        $vc->alquilaSocioProducto(1, 2);

        $this->assertEquals(2, $vc->getNumProductosAlquilados());

        $this->expectException(CupoSuperadoException::class);
        $vc->socios[0]->alquilar($vc->productos[2]);
    }

    public function testExcepcionSoporteYaAlquilado(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirSocio('Ana', 'ana', 'pass');
        $vc->incluirSocio('Luis', 'luis', 'pass');

        $vc->alquilaSocioProducto(1, 1);
        $this->assertTrue($vc->productos[0]->alquilado);

        $this->expectException(SoporteYaAlquiladoException::class);
        $vc->socios[1]->alquilar($vc->productos[0]);
    }

    public function testListarProductos(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'Película A', 2.5, 100);
        $vc->incluirDvd(null, 'DVD B', 3.0, ['es'], '16:9');
        
        // No lanza excepción
        $vc->listarProductos();
        $this->assertCount(2, $vc->productos);
    }

    public function testListarSocios(): void
    {
        $vc = new Videoclub();
        $vc->incluirSocio('Ana', 'ana', 'pass');
        $vc->incluirSocio('Luis', 'luis', 'pass');
        
        // No lanza excepción
        $vc->listarSocios();
        $this->assertCount(2, $vc->socios);
    }

    public function testDevolverSocioProductosMultiple(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirDvd(null, 'P2', 1.5, ['es'], '4:3');
        $vc->incluirJuego(null, 'P3', 2.0, 'Switch', 1, 2);
        $vc->incluirSocio('Pepe', 'pepe', 'pass', 5);

        $vc->alquilaSocioProducto(1, [1, 2, 3]);
        $this->assertEquals(3, $vc->getNumProductosAlquilados());
        $this->assertCount(3, $vc->socios[0]->getAlquileres());

        $vc->devolverSocioProductos(1, [1, 2, 3]);
        $this->assertEquals(0, $vc->getNumProductosAlquilados());
        $this->assertCount(0, $vc->socios[0]->getAlquileres());
    }

    public function testDevolverProductoInexistente(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirSocio('A', 'a', 'p');

        $vc->alquilaSocioProducto(1, 1);

        // Intenta devolver producto que no existe
        $vc->devolverSocioProducto(1, 999);
        
        // El producto original aún debe estar alquilado
        $this->assertTrue($vc->productos[0]->alquilado);
        $this->assertEquals(1, $vc->getNumProductosAlquilados());
    }

    public function testAlquilarConClienteInexistenteDevoluciones(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirSocio('A', 'a', 'p');

        // Intenta devolver con cliente inexistente
        $vc->devolverSocioProducto(99, 1);
        
        // No debe lanzar excepción, todo debe estar igual
        $this->assertEquals(0, $vc->getNumProductosAlquilados());
    }
}