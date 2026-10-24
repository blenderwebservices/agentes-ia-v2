<?php

namespace App\Filament\Resources\TaskRunResource\Pages;

use App\Filament\Resources\TaskRunResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaskRun extends EditRecord
{
    protected static string $resource = TaskRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
