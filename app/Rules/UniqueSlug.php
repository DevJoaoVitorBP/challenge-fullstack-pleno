<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueSlug implements ValidationRule
{
    private string $table;
    private ?int $ignoreId;

    public function __construct(string $table, ?int $ignoreId = null)
    {
        $this->table = $table;
        $this->ignoreId = $ignoreId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)->where('slug', $value);

        // Se estamos editando, ignorar o ID atual
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('O slug "' . $value . '" já existe. Deve ser único.');
        }
    }
}
