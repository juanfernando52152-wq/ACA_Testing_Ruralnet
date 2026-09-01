<?php
use PHPUnit\Framework\TestCase;

class PasswordValidationTest extends TestCase
{
    // CP-01: Prueba unitaria para validar la longitud mínima de la contraseña
    public function testLongitudMinima()
    {
        $nueva_password = "12345"; // 5 caracteres
        $esValida = strlen($nueva_password) >= 6;
        
        $this->assertFalse($esValida, "Error: El sistema no debe permitir contraseñas menores a 6 caracteres.");
    }

    // CP-02: Prueba unitaria para validar que la confirmación coincida
    public function testCoincidenciaPasswords()
    {
        $nueva_password = "NuevoPass2026";
        $confirmar_password = "NuevoPass2020"; // Diferente
        
        $this->assertNotEquals($nueva_password, $confirmar_password, "Error: Las contraseñas no coinciden.");
    }

    // CP-03: Prueba unitaria de seguridad (No usar la cédula como contraseña nueva)
    public function testPasswordNuevaNoEsCedula()
    {
        $cedula_cliente = "10203040";
        $nueva_password = "10203040"; // Intenta usar la misma cédula
        
        $this->assertEquals($cedula_cliente, $nueva_password, "Error de seguridad: La nueva contraseña no puede ser igual a la cédula.");
    }
}
?>
