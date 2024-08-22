<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditProfile extends BaseEditProfile
{
    public function getTitle(): string|Htmlable
    {
        $name = auth()->user()->name;
        $email = auth()->user()->email;

        return new HtmlString(<<<HTML
            <div>$name</div>
            <div class="text-sm">$email</div>
        HTML);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
