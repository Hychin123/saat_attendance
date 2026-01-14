<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AttendanceChatbotWidget extends Widget
{
    protected static string $view = 'filament.widgets.attendance-chatbot-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 5;

    public function getViewData(): array
    {
        return [
            'user' => auth()->user(),
        ];
    }
}
