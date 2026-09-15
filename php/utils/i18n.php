<?php

function inicializar_i18n(): string
{
    static $idioma_cargado = null;

    if ($idioma_cargado !== null) {
        return $idioma_cargado;
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $idioma = 'es';

    if (!empty($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'], true)) {
        $idioma = $_GET['lang'];
        $_SESSION['classia_lang'] = $idioma;
        if (!headers_sent()) {
            @setcookie('classia_lang', $idioma, time() + (365 * 24 * 60 * 60), '/', '', false, true);
        }
    } elseif (!empty($_SESSION['classia_lang']) && in_array($_SESSION['classia_lang'], ['es', 'en'], true)) {
        $idioma = $_SESSION['classia_lang'];
    } elseif (!empty($_COOKIE['classia_lang']) && in_array($_COOKIE['classia_lang'], ['es', 'en'], true)) {
        $idioma = $_COOKIE['classia_lang'];
        $_SESSION['classia_lang'] = $idioma;
    }

    $idioma_cargado = $idioma;
    return $idioma_cargado;
}

function __t(string $key, ?string $fallback = null): string
{
    static $translations = null;
    static $current_lang = null;

    $lang = inicializar_i18n();

    if ($translations === null || $current_lang !== $lang) {
        $filePath = __DIR__ . '/../lang/' . $lang . '.php';
        if (file_exists($filePath)) {
            $translations = require $filePath;
        } else {
            $translations = [];
        }
        $current_lang = $lang;
    }

    return $translations[$key] ?? $fallback ?? $key;
}

function obtener_idioma_actual(): string
{
    return inicializar_i18n();
}
