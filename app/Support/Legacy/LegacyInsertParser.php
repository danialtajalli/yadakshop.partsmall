<?php

namespace App\Support\Legacy;

class LegacyInsertParser
{
    /** @var array<string, list<array<string, mixed>>> */
    private array $cache = [];

    public function __construct(private readonly string $sql) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function rows(string $table): array
    {
        if (isset($this->cache[$table])) {
            return $this->cache[$table];
        }

        $rows = [];
        $search = 'INSERT INTO `'.$table.'`';
        $offset = 0;

        while (($start = stripos($this->sql, $search, $offset)) !== false) {
            $valuesPos = stripos($this->sql, 'VALUES', $start);
            if ($valuesPos === false) {
                break;
            }

            $columnsStart = strpos($this->sql, '(', $start);
            $columnsEnd = strpos($this->sql, ')', $columnsStart);
            $columns = array_map(
                fn (string $column): string => trim($column, " `\t\n\r"),
                explode(',', substr($this->sql, $columnsStart + 1, $columnsEnd - $columnsStart - 1))
            );

            $dataStart = strpos($this->sql, '(', $valuesPos);
            $dataEnd = $this->findClosingSemicolon($dataStart);

            foreach ($this->parseValueGroups(substr($this->sql, $dataStart, $dataEnd - $dataStart)) as $values) {
                if (count($values) !== count($columns)) {
                    continue;
                }

                $rows[] = array_combine($columns, $values);
            }

            $offset = $dataEnd + 1;
        }

        return $this->cache[$table] = $rows;
    }

    private function findClosingSemicolon(int $start): int
    {
        $length = strlen($this->sql);
        $index = $start;
        $inString = false;

        while ($index < $length) {
            $char = $this->sql[$index];

            if ($inString) {
                if ($char === '\\' && $index + 1 < $length) {
                    $index += 2;
                    continue;
                }

                if ($char === "'") {
                    if ($index + 1 < $length && $this->sql[$index + 1] === "'") {
                        $index += 2;
                        continue;
                    }

                    $inString = false;
                }

                $index++;
                continue;
            }

            if ($char === "'") {
                $inString = true;
                $index++;
                continue;
            }

            if ($char === ';') {
                return $index;
            }

            $index++;
        }

        return $length;
    }

    /**
     * @return list<list<mixed>>
     */
    private function parseValueGroups(string $valuesSql): array
    {
        $groups = [];
        $length = strlen($valuesSql);
        $index = 0;

        while ($index < $length) {
            while ($index < $length && in_array($valuesSql[$index], [' ', ',', "\n", "\r", "\t"], true)) {
                $index++;
            }

            if ($index >= $length || $valuesSql[$index] !== '(') {
                break;
            }

            $index++;
            $values = [];
            $current = '';
            $inString = false;
            $depth = 0;

            while ($index < $length) {
                $char = $valuesSql[$index];

                if ($inString) {
                    if ($char === '\\' && $index + 1 < $length) {
                        $current .= $valuesSql[$index + 1];
                        $index += 2;
                        continue;
                    }

                    if ($char === "'") {
                        if ($index + 1 < $length && $valuesSql[$index + 1] === "'") {
                            $current .= "'";
                            $index += 2;
                            continue;
                        }

                        $inString = false;
                        $index++;
                        continue;
                    }

                    $current .= $char;
                    $index++;
                    continue;
                }

                if ($char === "'") {
                    $inString = true;
                    $current = '';
                    $index++;
                    continue;
                }

                if ($char === '(') {
                    $depth++;
                    $current .= $char;
                    $index++;
                    continue;
                }

                if ($char === ')') {
                    if ($depth === 0) {
                        $values[] = $this->castValue(trim($current));
                        $groups[] = $values;
                        $index++;
                        break;
                    }

                    $depth--;
                    $current .= $char;
                    $index++;
                    continue;
                }

                if ($char === ',' && $depth === 0) {
                    $values[] = $this->castValue(trim($current));
                    $current = '';
                    $index++;
                    continue;
                }

                $current .= $char;
                $index++;
            }
        }

        return $groups;
    }

    private function castValue(string $value): mixed
    {
        if ($value === '' || strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }
}
