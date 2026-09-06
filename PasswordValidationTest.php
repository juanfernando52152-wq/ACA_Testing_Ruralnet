<?php
use PHPUnit\Framework\TestCase;
require 'Validador.php';

class PasswordValidationTest extends TestCase {
    public function testLongitudMinima() {
        $validador = new Validador();
        $this->assertFalse($validador->validarLongitud("12345"), "Fallo: todo porque dejo guardar una clave corta.");
    }
    public function testCoincidenciaPasswords() {
        $validador = new Validador();
        $this->assertFalse($validador->coincidenPasswords("Clave2026", "Clave2020"), "Fallo: se acepto y registro claves que no coinciden.");
    }
    public function testPasswordNuevaNoEsCedula() {
        $validador = new Validador();
        $this->assertFalse($validador->esSeguraContraCedula("10203040", "10203040"), "Fallo de seguridad: Aceptó nuevamente la cédula como clave.");
    }
}
?>
