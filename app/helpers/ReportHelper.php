<?php

namespace App\Helpers;

class ReportHelper
{
    /**
     * Formata um valor para moeda brasileira (R$ 1.234,56).
     *
     * @param float|string|null $value
     * @return string
     */
    public static function formatCurrency($value): string
    {
        if ($value === null || $value === '') {
            return 'R$ 0,00';
        }
        
        // Converte para float tratando possíveis formatos (ex: 1.234,56 ou 1234.56)
        if (is_string($value) && strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value); // Remove separador de milhar
            $value = str_replace(',', '.', $value); // Converte vírgula decimal em ponto
        }
        
        return 'R$ ' . number_format((float)$value, 2, ',', '.');
    }

    /**
     * Formata uma data para o padrão brasileiro (dd/mm/aaaa).
     *
     * @param string|null $dateString
     * @return string
     */
    public static function formatDate($dateString): string
    {
        if (empty($dateString) || $dateString === '0000-00-00') {
            return '-';
        }
        try {
            $date = new \DateTime($dateString);
            return $date->format('d/m/Y');
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Formata data e hora para o padrão brasileiro (dd/mm/aaaa HH:MM).
     *
     * @param string|null $date
     * @return string
     */
    public static function formatDateTime($date): string
    {
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        return date('d/m/Y H:i', strtotime($date));
    }

    /**
     * Formata CPF ou CNPJ.
     *
     * @param string|null $value
     * @return string
     */
    public static function formatCpfCnpj($value): string
    {
        $cnpj_cpf = preg_replace("/\D/", '', $value ?? '');

        if (strlen($cnpj_cpf) === 11) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cnpj_cpf);
        }

        if (strlen($cnpj_cpf) === 14) {
            return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cnpj_cpf);
        }

        return $value ?? '';
    }
}