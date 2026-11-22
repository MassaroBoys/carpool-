<?php
/**
 * Общая инициализация: сессия, автозагрузка классов, вспомогательные функции.
 */
session_start();

spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

/**
 * Экранирует значение для безопасного вывода в HTML (защита от XSS).
 *
 * @param mixed $value исходное значение
 * @return string экранированная строка
 */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
