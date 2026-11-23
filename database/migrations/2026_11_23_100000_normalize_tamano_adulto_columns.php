<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeColumn('perfiles_cliente');
        $this->normalizeColumn('plantas');
    }

    public function down(): void
    {
        $this->restoreLegacyProfileName();
        $this->restoreLegacyPlantName();
    }

    private function restoreLegacyProfileName(): void
    {
        if (! Schema::hasTable('perfiles_cliente')) {
            return;
        }

        $columns = $this->columnNames('perfiles_cliente');

        if (in_array('tamano_adulto', $columns, true)
            && ! in_array('tamaño_adulto', $columns, true)) {
            Schema::table('perfiles_cliente', function ($table): void {
                $table->renameColumn('tamano_adulto', 'tamaño_adulto');
            });
        }
    }

    private function restoreLegacyPlantName(): void
    {
        if (! Schema::hasTable('plantas')) {
            return;
        }

        $columns = $this->columnNames('plantas');

        if (in_array('tamano_adulto', $columns, true)
            && ! in_array('tamaño_adulto', $columns, true)) {
            Schema::table('plantas', function ($table): void {
                $table->renameColumn('tamano_adulto', 'tamaño_adulto');
            });
        }
    }

    private function normalizeColumn(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = $this->columnNames($table);
        $canonical = 'tamano_adulto';
        $variants = array_values(array_filter($columns, function (string $column) use ($canonical): bool {
            if ($column === $canonical) {
                return true;
            }

            $lower = mb_strtolower($column, 'UTF-8');

            return str_contains($lower, 'tam')
                && str_contains($lower, 'adulto');
        }));

        if (in_array($canonical, $variants, true)) {
            $otherVariants = array_values(array_filter($variants, fn (string $column): bool => $column !== $canonical));

            if ($otherVariants !== []) {
                throw new RuntimeException(
                    "Ambigüedad en {$table}: existen {$canonical} y las variantes ".implode(', ', $otherVariants).
                    '; no se modificó el esquema.'
                );
            }

            return;
        }

        if (count($variants) === 0) {
            return;
        }

        if (count($variants) > 1) {
            throw new RuntimeException(
                "Ambigüedad en {$table}: existen varias variantes de tamaño adulto: ".implode(', ', $variants).
                '; no se modificó el esquema.'
            );
        }

        Schema::table($table, function ($schema) use ($variants, $canonical): void {
            $schema->renameColumn($variants[0], $canonical);
        });
    }

    private function columnNames(string $table): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA table_info('{$table}')");

            return array_map(fn ($row): string => (string) $row->name, $rows);
        }

        if ($driver === 'mysql') {
            $database = (string) DB::getDatabaseName();
            $rows = DB::select(
                'SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?',
                [$database, $table]
            );

            return array_map(fn ($row): string => (string) $row->column_name, $rows);
        }

        return Schema::getColumnListing($table);
    }
};
