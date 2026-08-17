<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WithSorting
{
    public string $sortDir = 'asc';

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Apply orderBy with a whitelist — only real columns or aggregate aliases.
     */
    protected function applySorting(Builder $query, array $sortableColumns): Builder
    {
        $column = in_array($this->sortBy, $sortableColumns, true) ? $this->sortBy : $sortableColumns[0];

        return $query->orderBy($column, $this->sortDir === 'desc' ? 'desc' : 'asc');
    }
}