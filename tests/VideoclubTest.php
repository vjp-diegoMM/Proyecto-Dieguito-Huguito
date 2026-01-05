<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Dwes\ProyectoVideoclub\Videoclub;
use Dwes\ProyectoVideoclub\Cliente;
use Dwes\ProyectoVideoclub\Dvd;
use Dwes\ProyectoVideoclub\Bluray;
use Dwes\ProyectoVideoclub\Util\CupoSuperadoException;
use Dwes\ProyectoVideoclub\Util\SoporteYaAlquiladoException;
use Dwes\ProyectoVideoclub\Exception\ClienteNoExisteException;

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

    public function testProductoInexistenteNoRompe(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);
        $vc->incluirSocio('A', 'a', 'p');

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

        // Intenta devolver con cliente inexistente - ahora lanza excepción
        $this->expectException(ClienteNoExisteException::class);
        $vc->devolverSocioProducto(99, 1);
    }

    // ===== NUEVOS TESTS PARA ClienteNoExisteException =====

    public function testExcepcionClienteNoExisteAlAlquilar(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);

        $this->expectException(ClienteNoExisteException::class);
        $vc->alquilaSocioProducto(99, 1);
    }

    public function testExcepcionClienteNoExisteAlDevolver(): void
    {
        $vc = new Videoclub();
        $vc->incluirCintaVideo(null, 'P1', 1.0, 60);

        $this->expectException(ClienteNoExisteException::class);
        $vc->devolverSocioProducto(99, 1);
    }

    // ===== NUEVOS TESTS PARA DURACION EN DVD =====

    public function testDvdAlmacenaDuracion(): void
    {
        $dvd = new Dvd('Test DVD', 1, 5.0, ['es', 'en'], '16:9', 120, null);
        
        $this->assertEquals(120, $dvd->getDuracion());
    }

    public function testIncluirDvdConDuracion(): void
    {
        $vc = new Videoclub();
        $vc->incluirDvd(null, 'Inception', 3.5, ['es', 'en'], '16:9', 148);

        $this->assertCount(1, $vc->productos);
        $dvd = $vc->productos[0];
        $this->assertInstanceOf(Dvd::class, $dvd);
        $this->assertEquals(148, $dvd->getDuracion());
    }

    // ===== NUEVOS TESTS PARA BLURAY =====

    public function testBlurayHeredaDeSoporte(): void
    {
        $bluray = new Bluray('Test Bluray', 1, 15.0, 180, true, null);
        
        $this->assertInstanceOf(\Dwes\ProyectoVideoclub\Soporte::class, $bluray);
    }

    public function testBlurayAlmacenaTituloYDuracion(): void
    {
        $bluray = new Bluray('Avatar', 1, 12.99, 162, false, null);
        
        $this->assertEquals('Avatar', $bluray->getTitulo());
        $this->assertEquals(162, $bluray->getDuracion());
        $this->assertEquals(1, $bluray->getNumero());
        $this->assertEquals(12.99, $bluray->getPrecio());
    }

    public function testBlurayAlmacenaAttributoIs4k(): void
    {
        $bluray4k = new Bluray('4K Movie', 1, 18.99, 200, true, null);
        $blurayNormal = new Bluray('Normal Movie', 2, 15.99, 150, false, null);
        
        $this->assertTrue($bluray4k->isIs4k());
        $this->assertFalse($blurayNormal->isIs4k());
    }

    public function testIncluirBlurayEnVideoclub(): void
    {
        $vc = new Videoclub();
        $vc->incluirBluray(null, 'Dune', 16.5, 166, true);

        $this->assertCount(1, $vc->productos);
        $bluray = $vc->productos[0];
        $this->assertInstanceOf(Bluray::class, $bluray);
        $this->assertEquals('Dune', $bluray->getTitulo());
        $this->assertEquals(166, $bluray->getDuracion());
        $this->assertTrue($bluray->isIs4k());
    }

    public function testAlquilarYDevolverBluray(): void
    {
        $vc = new Videoclub();
        $vc->incluirBluray(null, 'Interstellar', 17.99, 169, true);
        $vc->incluirSocio('Carlos', 'carlos', 'pass');

        $vc->alquilaSocioProducto(1, 1);
        $this->assertTrue($vc->productos[0]->alquilado);
        $this->assertEquals(1, $vc->getNumProductosAlquilados());

        $vc->devolverSocioProducto(1, 1);
        $this->assertFalse($vc->productos[0]->alquilado);
        $this->assertEquals(0, $vc->getNumProductosAlquilados());
    }
}