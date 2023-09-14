<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Policies\BoardPolicy;
use App\Policies\CardPolicy;
use App\Policies\ColumnPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Card::class => CardPolicy::class,
        Column::class => ColumnPolicy::class,
        Board::class => BoardPolicy::class
    ];

    public function boot(): void
    {
        //
    }
}
