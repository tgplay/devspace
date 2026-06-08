<?php

if (! function_exists('fmt_dt')) {
    /**
     * Formata um timestamp UTC vindo do banco para o fuso horário da aplicação.
     * Usa ' UTC' explícito para que strtotime() não interprete como horário local.
     */
    function fmt_dt(string $utc, string $format = 'd/m/Y H:i'): string
    {
        return date($format, strtotime($utc . ' UTC'));
    }
}

if (! function_exists('pg_bool')) {
    /**
     * Converte valor booleano do PostgreSQL para bool PHP.
     * PDO retorna 't'/'f' para colunas BOOLEAN do PostgreSQL.
     */
    function pg_bool(mixed $val): bool
    {
        if (is_bool($val)) return $val;
        if (is_int($val))  return $val !== 0;
        return in_array(strtolower((string) $val), ['t', 'true', '1', 'on', 'yes'], true);
    }
}
