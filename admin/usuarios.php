<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/branding.php';

if (!isLoggedIn() || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$mensaje = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol = $_POST['rol'] ?? 'cliente';
        $visible = isset($_POST['visible']) ? 1 : 0;
        
        if ($nombre && $email && $password) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (nombre, email, telefono, password, rol, activo, visible) 
                        VALUES (?, ?, ?, ?, ?, 1, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$nombre, $email, $telefono, $hash, $rol, $visible])) {
                    $mensaje = "Usuario creado exitosamente.";
                } else {
                    $error = "Error al crear el usuario.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "El email ya está registrado.";
                } else {
                    $error = "Error: " . $e->getMessage();
                }
            }
        } else {
            $error = "Complete todos los campos obligatorios.";
        }
    }
    
    if ($accion === 'editar') {
        $id = $_POST['id'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rol = $_POST['rol'] ?? 'cliente';
        $visible = isset($_POST['visible']) ? 1 : 0;
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if ($id && $nombre && $email) {
            try {
                $sql = "UPDATE usuarios 
                        SET nombre = ?, email = ?, telefono = ?, rol = ?, visible = ?, activo = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$nombre, $email, $telefono, $rol, $visible, $activo, $id])) {
                    $mensaje = "Usuario actualizado exitosamente.";
                } else {
                    $error = "Error al actualizar el usuario.";
                }
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
    
    if ($accion === 'eliminar') {
        $id = $_POST['id'] ?? 0;
        if ($id && $id != $_SESSION['usuario']['id']) {
            $sql = "DELETE FROM usuarios WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$id])) {
                $mensaje = "Usuario eliminado exitosamente.";
            } else {
                $error = "Error al eliminar el usuario.";
            }
        } else {
            $error = "No puedes eliminarte a ti mismo.";
        }
    }
}

// Obtener todos los usuarios
$sql = "SELECT * FROM usuarios ORDER BY rol, nombre";
$usuarios = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Turnos Ya</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        <?php if (PORTADA_URL): ?>
        body::before {
            background-image: url('../img/portada-1767467044.jpg');
        }
        <?php endif; ?>
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .formulario {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 5px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-edit {
            background: #4CAF50;
            color: white;
        }
        
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-admin {
            background: #ff6b6b;
            color: white;
        }
        
        .badge-gerente {
            background: #4ecdc4;
            color: white;
        }
        
        .badge-cliente {
            background: #95e1d3;
            color: #333;
        }
        
        .estado-activo {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .estado-inactivo {
            color: #f44336;
            font-weight: bold;
        }
        
        .acciones {
            display: flex;
            gap: 5px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">← Volver al Dashboard</a>
        
        <h1>Gestión de Usuarios</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Formulario de creación -->
        <div class="formulario">
            <h2>Crear Nuevo Usuario</h2>
            <form method="post">
                <input type="hidden" name="accion" value="crear">
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" 
                           placeholder="Ej: Juan Pérez" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" 
                           placeholder="Ej: gerente@turnos-ya.com" required>
                    <div class="help-text">Este será el usuario para iniciar sesión</div>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" 
                           placeholder="Ej: +54 9 11 1234-5678">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol del Usuario *</label>
                    <select id="rol" name="rol" required>
                        <option value="cliente">Cliente - Puede reservar turnos</option>
                        <option value="gerente">Gerente/Colaborador - Gestiona turnos</option>
                        <option value="admin">Administrador - Control total</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="visible" value="1" checked>
                        <strong>Visible al público</strong>
                    </label>
                    <div class="help-text">
                        Solo para gerentes: marcar si atiende al público
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Crear Usuario</button>
            </form>
        </div>
        
        <!-- Lista de usuarios -->
        <h2>Usuarios Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Visible</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nombre']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['telefono'] ?? '-') ?></td>
                    <td>
                        <span class="badge badge-<?= $user['rol'] ?>">
                            <?= strtoupper($user['rol']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="<?= $user['activo'] ? 'estado-activo' : 'estado-inactivo' ?>">
                            <?= $user['activo'] ? 'ACTIVO' : 'INACTIVO' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['rol'] === 'gerente'): ?>
                            <?= $user['visible'] ? '✓ Sí' : '✗ No' ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="acciones">
                        <button onclick="editarUsuario(<?= htmlspecialchars(json_encode($user)) ?>)" 
                                class="btn btn-edit">Editar</button>
                        
                        <?php if ($user['id'] != $_SESSION['usuario']['id']): ?>
                        <form method="post" style="display:inline;" 
                              onsubmit="return confirm('¿Eliminar este usuario?')">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Modal de edición -->
    <div id="modalEditar" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="background:white; max-width:600px; margin:50px auto; padding:30px; border-radius:10px;">
            <h2>Editar Usuario</h2>
            <form method="post" id="formEditar">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_nombre">Nombre Completo *</label>
                    <input type="text" id="edit_nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email *</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_telefono">Teléfono</label>
                    <input type="tel" id="edit_telefono" name="telefono">
                </div>
                
                <div class="form-group">
                    <label for="edit_rol">Rol *</label>
                    <select id="edit_rol" name="rol" required>
                        <option value="cliente">Cliente</option>
                        <option value="gerente">Gerente/Colaborador</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="activo" id="edit_activo" value="1">
                        Usuario Activo
                    </label>
                    <label>
                        <input type="checkbox" name="visible" id="edit_visible" value="1">
                        Visible (solo gerentes)
                    </label>
                </div>
                
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <button type="button" onclick="cerrarModal()" class="btn">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editarUsuario(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_nombre').value = user.nombre;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_telefono').value = user.telefono || '';
            document.getElementById('edit_rol').value = user.rol;
            document.getElementById('edit_activo').checked = user.activo == 1;
            document.getElementById('edit_visible').checked = user.visible == 1;
            document.getElementById('modalEditar').style.display = 'block';
        }
        
        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('modalEditar').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    </script>
</body>
</html>
