<?php
// Incluir configurações apenas se não estiverem já incluídas
if (!defined('SISTEMA_NOME')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-mobile-alt"></i>
                <h1><?php echo SISTEMA_NOME; ?></h1>
                <small class="version">v<?php echo SISTEMA_VERSAO; ?></small>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="../ordens/cadastro.php"><i class="fas fa-plus-circle"></i> Nova Ordem</a></li>
                    <li><a href="../ordens/lista.php"><i class="fas fa-clipboard-list"></i> Ordens</a></li>
                    <li><a href="../clientes/lista.php"><i class="fas fa-users"></i> Clientes</a></li>
                    <li><a href="../configuracoes.php" class="no-print"><i class="fas fa-cog"></i> Configurações</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<?php
// Exibir mensagens flash
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    ?>
    <div class="container">
        <div class="message <?php echo $message['type']; ?>">
            <p><i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message['text']; ?></p>
        </div>
    </div>
    <?php
}
?>

<style>
.version {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.7);
    margin-left: 5px;
}
</style>