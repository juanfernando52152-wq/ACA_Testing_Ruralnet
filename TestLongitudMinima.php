<?php
use PHPUnit\Framework\TestCase;
require 'Validador.php';

class TestLongitudMinima extends TestCase {
    public function testLongitudMinima() {
        $validador = new Validador();
        $this->assertFalse($validador->validarLongitud("12345"), "Fallo: se permitió guardar una clave corta.");
    }
}
