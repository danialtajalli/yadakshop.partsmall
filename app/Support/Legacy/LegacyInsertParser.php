<?php

namespace App\Support\Legacy;

class LegacyInsertParser
{
    public function __construct(
        private readonly string $sql,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function rows(string $table): array
    {
        $rows = [];
        $pattern = '/INSERT INTO `'.preg_quote($table, '/').'` \(([^)]+)\) VALUES\s*/i';

        if (! preg_match_all($pattern, $this->sql, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        foreach ($matches[0] as $index => $match) {
            $columns = $this->parseColumnList($matches[1][$index][0]);
            $valuesStart = $match[1] + strlen($match[0]);
            $valuesBlock = $this->extractValuesBlock($valuesStart);

            foreach ($this->parseValueTuples($valuesBlock) as $tuple) {
                $values = $this->parseTupleValues($tuple);

                if (count($values) !== count($columns)) {
                    continue;
                }

                $rows[] = array_combine($columns, $values);
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function parseColumnList(string $list): array
    {
        return array_map(
            static fn (string $column): string => trim($column, " `\t\n\r"),
            explode(',', $list),
        );
    }

    private function extractValuesBlock(int $offset): string
    {
        $length = strlen($this->sql);
        $depth = 0;
        $inString = false;
        $escape = false;
        $block = '';

        for ($i = $offset; $i < $length; $i++) {
            $char = $this->sql[$i];

            if ($inString) {
                $block .= $char;

                if ($escape) {
                    $escape = false;

                    continue;
                }

                if ($char === '\\') {
                    $escape = true;

                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;
                $block .= $char;

                continue;
            }

            if ($char === '(') {
                $depth++;
                $block .= $char;

                continue;
            }

            if ($char === ')') {
                $depth--;
                $block .= $char;

                if ($depth === 0) {
                    $next = $this->sql[$i + 1] ?? '';

                    if ($next === ';' || $next === '') {
                        break;
                    }

                    if ($next === ',') {
                        $block .= ',';
                        $i++;
                    }
                }

                continue;
            }

            if ($depth === 0 && $char === ';') {
                break;
            }

            if ($depth > 0 || ! ctype_space($char)) {
                $block .= $char;
            }
        }

        return $block;
    }

    /**
     * @return list<string>
     */
    private function parseValueTuples(string $block): array
    {
        $tuples = [];
        $length = strlen($block);
        $depth = 0;
        $inString = false;
        $escape = false;
        $start = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $block[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;

                    continue;
                }

                if ($char === '\\') {
                    $escape = true;

                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;

                continue;
            }

            if ($char === '(') {
                if ($depth === 0) {
                    $start = $i;
                }

                $depth++;

                continue;
            }

            if ($char === ')') {
                $depth--;

                if ($depth === 0 && $start !== null) {
                    $tuples[] = substr($block, $start, $i - $start + 1);
                    $start = null;
                }
            }
        }

        return $tuples;
    }

    /**
     * @return list<mixed>
     */
    private function parseTupleValues(string $tuple): array
    {
        $inner = trim($tuple, '()');
        $values = [];
        $length = strlen($inner);
        $buffer = '';
        $inString = false;
        $escape = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $inner[$i];

            if ($inString) {
                if ($escape) {
                    $buffer .= $char;
                    $escape = false;

                    continue;
                }

                if ($char === '\\') {
                    $escape = true;

                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                    $values[] = $this->decodeString($buffer);
                    $buffer = '';

                    continue;
                }

                $buffer .= $char;

                continue;
            }

            if ($char === "'") {
                $inString = true;
                $buffer = '';

                continue;
            }

            if ($char === ',') {
                if ($buffer !== '') {
                    $values[] = $this->decodeScalar(trim($buffer));
                    $buffer = '';
                }

                continue;
            }

            $buffer .= $char;
        }

        if ($buffer !== '') {
            $values[] = $this->decodeScalar(trim($buffer));
        }

        return $values;
    }

    private function decodeString(string $value): string
    {
        return str_replace(
            ["\\'", '\\"', '\\r', '\\n', '\\\\'],
            ["'", '"', "\r", "\n", '\\'],
            $value,
        );
    }

    private function decodeScalar(string $value): mixed
    {
        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }
}
