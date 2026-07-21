<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class SetPassword extends Page
{
    protected static ?string $title = 'Definir Password';

    protected static ?string $slug = 'set-password';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.set-password';

    public ?string $password = null;

    public ?string $passwordConfirmation = null;

    protected function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);

        Notification::make()
            ->title('Password definida com sucesso!')
            ->success()
            ->send();

        $this->redirect('/manage-backoffice');
    }
}
