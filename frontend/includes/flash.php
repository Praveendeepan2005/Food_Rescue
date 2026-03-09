<?php
/**
 * flash.php
 * Renders and clears any session flash messages.
 * Must be included AFTER session_start().
 */
if (!empty($_SESSION['flash'])):
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $isError = ($flash['type'] === 'error');
    $color = $isError ? '#D32F2F' : '#2E7D32';
    $bg = $isError ? '#FEE2E2' : '#DCFCE7';
    $icon = $isError ? '✕' : '✓';
    ?>
    <div style="
    background:<?= $bg ?>;
    border-left: 4px solid <?= $color ?>;
    color: <?= $color ?>;
    padding: 14px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    animation: fadeIn 0.3s ease;
">
        <span style="font-weight:700; font-size:1rem;">
            <?= $icon ?>
        </span>
        <span>
            <?= htmlspecialchars($flash['msg']) ?>
        </span>
    </div>
<?php endif; ?>