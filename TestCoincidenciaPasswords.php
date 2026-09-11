<?php
use PHPUnit\Framework\TestCase;
require 'Validador.php';

class TestCoincidenciaPasswords extends TestCase {
    public function testCoincidenciaPasswords() {
        $validador = new Validador();
        $this->assertFalse($validador->coincidenPasswords("Clave2026", "Clave2020"), "Fallo: se aceptaron claves que no coinciden.");
    }
}
