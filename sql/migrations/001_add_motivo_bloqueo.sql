-- Migration 001: Agregar columna motivo_bloqueo a usuarios
-- Fecha: 2026-09-24
-- Descripción: Agrega la columna motivo_bloqueo (TEXT, NULL) a la tabla usuarios
--              para almacenar el motivo cuando un administrador bloquea una cuenta.
-- Compatible con: PostgreSQL (Supabase) y MySQL 8.0+

-- PostgreSQL / Supabase
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS motivo_bloqueo TEXT;

-- MySQL (no soporta IF NOT EXISTS en ALTER TABLE antes de 8.0.28 según configuración)
-- Ejecutar solo si la columna no existe:
-- ALTER TABLE usuarios ADD COLUMN motivo_bloqueo TEXT NULL;
