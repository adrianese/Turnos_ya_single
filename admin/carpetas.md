admin/
├── index.php                ← Dashboard o inicio
├── configuracion.php        ← Configuración general del negocio
├── horarios.php             ← Días y horarios de atención
├── gerentes.php             ← Gestión de gerentes
├── turnos.php               ← Turnos asignados, cancelados
├── plantilla.php            ← 📌 PLANTILLA BASE (la que vamos a hacer ahora)
├── css/
│   └── admin.css            ← Estilos específicos del panel
├── inc/
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php


<?php
$pageTitle = "Título de esta sección";
include 'plantilla.php';
?>
Y dentro de plantilla.php, puedes cambiar el contenido de la sección <section class="content"> según la página (como hicimos arriba con un <p> temporal).
