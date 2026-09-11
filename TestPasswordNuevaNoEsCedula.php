<?php
use PHPUnit\Framework\TestCase;
require 'Validador.php';

class TestPasswordNuevaNoEsCedula extends TestCase {
    public function testPasswordNuevaNoEsCedula() {
        $validador = new Validador();
        $this->assertFalse($validador->esSeguraContraCedula("10203040", "10203040"), "Fallo: se aceptó la cédula como clave.");
    }
}
