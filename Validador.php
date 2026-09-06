<?php
class Validador {
    public function validarLongitud($password) { return strlen($password) >= 6; }
    public function coincidenPasswords($nueva, $confirmacion) { return $nueva === $confirmacion; }
    public function esSeguraContraCedula($password, $cedula) { return $password !== $cedula; }
}
?>