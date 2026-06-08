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
