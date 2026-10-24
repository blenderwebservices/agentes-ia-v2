<?php

namespace App\Filament\Resources\TaskRunResource\Pages;

use App\Filament\Resources\TaskRunResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaskRuns extends ListRecords
{
    protected static string $resource = TaskRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
