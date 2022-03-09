<?php

namespace Dentro\Patcher;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;

class PatcherRepository extends DatabaseMigrationRepository
{
    public function getRan(): array
    {
        return $this->table()
            ->orderBy('batch', 'asc')
            ->orderBy('patch', 'asc')
            ->pluck('patch')->all();
    }

    public function getMigrations($steps): array
    {
        $query = $this->table()->where('batch', '>=', '1');

        return $query->orderBy('batch', 'desc')
            ->orderBy('patch', 'desc')
            ->take($steps)->get()->all();
    }

    public function getLast(): array
    {
        $query = $this->table()->where('batch', $this->getLastBatchNumber());

        return $query->orderBy('patch', 'desc')->get()->all();
    }

    public function getMigrationBatches(): array
    {
        return $this->table()
            ->orderBy('batch', 'asc')
            ->orderBy('patch', 'asc')
            ->pluck('batch', 'patch')->all();
    }

    public function log($file, $batch): void
    {
        $record = ['patch' => $file, 'batch' => $batch];

        $this->table()->insert($record);
    }

    public function delete($migration): void
    {
        $this->table()->where('patch', $migration->migration)->delete();
    }

    public function createRepository(): void
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table, function ($table) {
            $table->increments('id');
            $table->string('patch');
            $table->integer('batch');
        });
    }
}
