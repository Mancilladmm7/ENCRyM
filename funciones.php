<?php
function tienePermiso($permiso) {
    if (!isset($_SESSION['permisos'])) return false;
    return in_array($permiso, $_SESSION['permisos']);
}