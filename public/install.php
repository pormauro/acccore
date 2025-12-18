<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| FASE 0 INSTALLER – ACCOUNTING CORE
|--------------------------------------------------------------------------
| Este instalador:
| - Verifica entorno
| - Verifica extensiones
| - Verifica DB PostgreSQL
| - Verifica reglas contables (triggers)
| - Verifica auditoría
| NO instala software del sistema
| NO crea lógica de negocio
| NO crea datos productivos
*/

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

/* =======================
   CONFIG
======================= */

const REQUIRED_PHP_VERSION = '8.2.0';
const REQUIRED_EXTENSIONS = [
    'pdo_pgsql',
    'mbstring',
    'openssl',
    'json',
    'ctype',
    'xml'
];

const INSTALL_LOCK_ENV = 'INSTALL_LOCK';

/* =======================
   UI HELPERS
======================= */

function ok(string $msg): void {
    echo "✅ {$msg}<br>";
}

function fail(string $msg): void {
    echo "❌ {$msg}<br>";
}

function fatal(string $msg): void {
    fail($msg);
    echo "<h3 style='color:red'>INSTALACIÓN BLOQUEADA</h3>";
    exit;
}

echo "<h2>FASE 0 – Instalador del Sistema Contable Base</h2>";
echo "<hr>";

/* =======================
   LOAD ENV
======================= */

if (!file_exists(__DIR__ . '/../.env')) {
    fatal(".env no encontrado. Copiá .env.example y configurá la DB.");
}

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if (!empty($_ENV[INSTALL_LOCK_ENV]) && $_ENV[INSTALL_LOCK_ENV] === 'true') {
    fatal("Instalador bloqueado (INSTALL_LOCK=true).");
}

/* =======================
   PHP VERSION
======================= */

if (version_compare(PHP_VERSION, REQUIRED_PHP_VERSION, '>=')) {
    ok("PHP version " . PHP_VERSION);
} else {
    fatal("PHP >= " . REQUIRED_PHP_VERSION . " requerido");
}

/* =======================
   PHP EXTENSIONS
======================= */

foreach (REQUIRED_EXTENSIONS as $ext) {
    if (extension_loaded($ext)) {
        ok("Extensión PHP {$ext} cargada");
    } else {
        fatal("Falta extensión PHP obligatoria: {$ext}");
    }
}

/* =======================
   STORAGE PERMISSIONS
======================= */

$storage = __DIR__ . '/../storage';
$bootstrap = __DIR__ . '/../bootstrap/cache';

if (is_writable($storage)) {
    ok("storage/ es escribible");
} else {
    fatal("storage/ NO es escribible");
}

if (is_writable($bootstrap)) {
    ok("bootstrap/cache es escribible");
} else {
    fatal("bootstrap/cache NO es escribible");
}

/* =======================
   DATABASE CONNECTION
======================= */

try {
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s",
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'] ?? 5432,
        $_ENV['DB_DATABASE']
    );

    $pdo = new PDO(
        $dsn,
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    ok("Conexión a PostgreSQL OK");
} catch (Throwable $e) {
    fatal("No se pudo conectar a PostgreSQL");
}

/* =======================
   CHECK TABLES EXIST
======================= */

$requiredTables = [
    'companies',
    'users',
    'company_memberships',
    'accounts',
    'journal_entries',
    'journal_lines',
    'accounting_periods',
    'audit_log'
];

foreach ($requiredTables as $table) {
    $stmt = $pdo->prepare("
        SELECT EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = 'public'
            AND table_name = :table
        )
    ");
    $stmt->execute(['table' => $table]);
    if ($stmt->fetchColumn()) {
        ok("Tabla {$table} existe");
    } else {
        fatal("Tabla obligatoria faltante: {$table}");
    }
}

/* =======================
   CHECK AUDIT TRIGGER
======================= */

try {
    $pdo->beginTransaction();

    $testId = uniqid('test_', true);

    $pdo->exec("
        INSERT INTO companies (id, name, created_at)
        VALUES (gen_random_uuid(), 'TEST_AUDIT', now())
    ");

    $audit = $pdo->query("
        SELECT *
        FROM audit_log
        WHERE table_name = 'companies'
        ORDER BY created_at DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    $pdo->rollBack();

    if ($audit && $audit['action'] === 'INSERT') {
        ok("Auditoría automática funcionando");
    } else {
        fatal("audit_log no registra cambios");
    }
} catch (Throwable $e) {
    $pdo->rollBack();
    fatal("Error verificando auditoría");
}

/* =======================
   CHECK DELETE BLOCK
======================= */

try {
    $pdo->exec("DELETE FROM journal_entries");
    fatal("Se pudo borrar contabilidad (NO permitido)");
} catch (Throwable $e) {
    ok("Borrado de contabilidad correctamente bloqueado");
}

/* =======================
   FINAL
======================= */

echo "<hr>";
echo "<h3 style='color:green'>FASE 0 VALIDADA CORRECTAMENTE</h3>";
echo "<p>El sistema está listo para avanzar a FASE 1.</p>";
echo "<p><strong>IMPORTANTE:</strong> bloqueá o eliminá este archivo antes de producción.</p>";
