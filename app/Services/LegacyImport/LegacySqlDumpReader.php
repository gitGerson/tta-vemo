<?php

namespace App\Services\LegacyImport;

use RuntimeException;

class LegacySqlDumpReader
{
    /**
     * @return array{tables: array<string, array<int, string>>, rows: array<string, list<array{legacy_id: int|string|null, attributes: array<string, mixed>}>>, skipped_statements: array<string, int>}
     */
    public function read(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('SQL dump file cannot be read.');
        }

        $contents = file_get_contents($path);

        if (! is_string($contents) || ! preg_match('/\b(?:MySQL|MariaDB) dump\b/i', $contents)) {
            throw new RuntimeException('File does not look like a MySQL/MariaDB dump.');
        }

        $tables = $this->parseCreateTableColumns($contents);
        $rows = [];

        foreach ($this->insertStatements($contents) as $statement) {
            if (! preg_match('/INSERT\s+INTO\s+`([^`]+)`\s+VALUES\s*(.+)$/is', $statement, $matches)) {
                continue;
            }

            $table = $matches[1];
            $columns = $tables[$table] ?? null;

            if ($columns === null) {
                continue;
            }

            foreach ($this->parseTuples($matches[2]) as $values) {
                $attributes = [];

                foreach ($columns as $index => $column) {
                    $attributes[$column] = $values[$index] ?? null;
                }

                $rows[$table][] = [
                    'legacy_id' => $attributes['id'] ?? null,
                    'attributes' => $attributes,
                ];
            }
        }

        return [
            'tables' => $tables,
            'rows' => $rows,
            'skipped_statements' => [
                'drop_table' => preg_match_all('/^\s*DROP\s+TABLE\b/mi', $contents),
                'alter_table' => preg_match_all('/^\s*ALTER\s+TABLE\b/mi', $contents),
                'lock_tables' => preg_match_all('/^\s*LOCK\s+TABLES\b/mi', $contents),
                'foreign_key_checks' => preg_match_all('/FOREIGN_KEY_CHECKS/i', $contents),
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function parseCreateTableColumns(string $contents): array
    {
        preg_match_all('/CREATE\s+TABLE\s+`([^`]+)`\s*\((.*?)\)\s+ENGINE=/is', $contents, $matches, PREG_SET_ORDER);

        $tables = [];

        foreach ($matches as $match) {
            preg_match_all('/^\s*`([^`]+)`\s+/m', $match[2], $columnMatches);
            $tables[$match[1]] = $columnMatches[1];
        }

        return $tables;
    }

    /**
     * @return list<string>
     */
    private function insertStatements(string $contents): array
    {
        preg_match_all('/INSERT\s+INTO\s+`[^`]+`\s+VALUES\s+.*?;/is', $contents, $matches);

        return $matches[0];
    }

    /**
     * @return list<list<mixed>>
     */
    private function parseTuples(string $valuesSql): array
    {
        $valuesSql = rtrim(trim($valuesSql), ';');
        $tuples = [];
        $currentTuple = [];
        $currentValue = '';
        $inString = false;
        $escaping = false;
        $insideTuple = false;
        $length = strlen($valuesSql);

        for ($index = 0; $index < $length; $index++) {
            $character = $valuesSql[$index];

            if ($inString) {
                if ($escaping) {
                    $currentValue .= $this->unescapeCharacter($character);
                    $escaping = false;

                    continue;
                }

                if ($character === '\\') {
                    $escaping = true;

                    continue;
                }

                if ($character === "'") {
                    $inString = false;

                    continue;
                }

                $currentValue .= $character;

                continue;
            }

            if ($character === "'") {
                $inString = true;

                continue;
            }

            if ($character === '(' && ! $insideTuple) {
                $insideTuple = true;
                $currentTuple = [];
                $currentValue = '';

                continue;
            }

            if (! $insideTuple) {
                continue;
            }

            if ($character === ',') {
                $currentTuple[] = $this->normalizeValue($currentValue);
                $currentValue = '';

                continue;
            }

            if ($character === ')') {
                $currentTuple[] = $this->normalizeValue($currentValue);
                $tuples[] = $currentTuple;
                $insideTuple = false;
                $currentValue = '';

                continue;
            }

            $currentValue .= $character;
        }

        return $tuples;
    }

    private function normalizeValue(string $value): mixed
    {
        $value = trim($value);

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }

    private function unescapeCharacter(string $character): string
    {
        return match ($character) {
            '0' => "\0",
            'b' => "\b",
            'n' => "\n",
            'r' => "\r",
            't' => "\t",
            'Z' => chr(26),
            default => $character,
        };
    }
}
