<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorAuthentication extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = '2FA Settings';
    protected static ?string $title = 'Two-Factor Authentication';
    protected static string $view = 'filament.pages.two-factor-authentication';
    protected static ?int $navigationSort = 99;

    public ?string $verificationCode = null;
    public ?string $qrCodeUrl = null;
    public ?string $secret = null;

    public function mount(): void
    {
        $user = Auth::user();
        
        if (!$user->google2fa_enabled) {
            $this->generateNewSecret();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Two-Factor Authentication Status')
                    ->description('Secure your account with two-factor authentication using Google Authenticator.')
                    ->schema([
                        Forms\Components\Placeholder::make('status')
                            ->label('Current Status')
                            ->content(fn () => Auth::user()->google2fa_enabled 
                                ? '✅ Two-Factor Authentication is ENABLED' 
                                : '❌ Two-Factor Authentication is DISABLED'
                            ),
                    ])
                    ->visible(fn () => true),

                Forms\Components\Section::make('Enable Two-Factor Authentication')
                    ->description('Scan the QR code with Google Authenticator app, then enter the 6-digit code to verify.')
                    ->schema([
                        Forms\Components\Placeholder::make('qr_code')
                            ->label('QR Code')
                            ->content(function () {
                                if ($this->qrCodeUrl) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div style="text-align: center; padding: 20px;">' . $this->qrCodeUrl . '</div>'
                                    );
                                }
                                return 'Loading...';
                            }),
                        
                        Forms\Components\Placeholder::make('secret_key')
                            ->label('Secret Key (Manual Entry)')
                            ->content(fn () => $this->secret ?? 'Loading...')
                            ->helperText('Use this key if you cannot scan the QR code'),

                        Forms\Components\TextInput::make('verificationCode')
                            ->label('Verification Code')
                            ->placeholder('Enter 6-digit code')
                            ->required()
                            ->maxLength(6)
                            ->minLength(6)
                            ->numeric()
                            ->helperText('Enter the 6-digit code from Google Authenticator'),
                    ])
                    ->visible(fn () => !Auth::user()->google2fa_enabled),

                Forms\Components\Section::make('Disable Two-Factor Authentication')
                    ->description('Enter your verification code to disable 2FA.')
                    ->schema([
                        Forms\Components\TextInput::make('verificationCode')
                            ->label('Verification Code')
                            ->placeholder('Enter 6-digit code')
                            ->required()
                            ->maxLength(6)
                            ->minLength(6)
                            ->numeric()
                            ->helperText('Enter the 6-digit code from Google Authenticator to disable 2FA'),
                    ])
                    ->visible(fn () => Auth::user()->google2fa_enabled),
            ]);
    }

    protected function generateNewSecret(): void
    {
        $google2fa = new Google2FA();
        $this->secret = $google2fa->generateSecretKey();
        
        $user = Auth::user();
        
        // Generate the OTP Auth URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $this->secret
        );

        // Generate QR code as SVG
        $qrCode = QrCode::size(200)
            ->margin(0)
            ->generate($qrCodeUrl);

        $this->qrCodeUrl = $qrCode;
        
        // Store the secret temporarily (it will be confirmed when user verifies)
        $user->update(['google2fa_secret' => encrypt($this->secret)]);
    }

    public function enableTwoFactor(): void
    {
        $this->validate([
            'verificationCode' => 'required|digits:6',
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA();
        
        $secret = decrypt($user->google2fa_secret);
        
        $valid = $google2fa->verifyKey($secret, $this->verificationCode);

        if ($valid) {
            $user->update([
                'google2fa_enabled' => true,
                'google2fa_enabled_at' => now(),
            ]);

            Notification::make()
                ->title('Two-Factor Authentication Enabled')
                ->success()
                ->body('Your account is now protected with 2FA.')
                ->send();

            $this->redirect(static::getUrl());
        } else {
            Notification::make()
                ->title('Invalid Code')
                ->danger()
                ->body('The verification code you entered is incorrect.')
                ->send();
        }
    }

    public function disableTwoFactor(): void
    {
        $this->validate([
            'verificationCode' => 'required|digits:6',
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA();
        
        $secret = decrypt($user->google2fa_secret);
        
        $valid = $google2fa->verifyKey($secret, $this->verificationCode);

        if ($valid) {
            $user->update([
                'google2fa_enabled' => false,
                'google2fa_secret' => null,
                'google2fa_enabled_at' => null,
            ]);

            Notification::make()
                ->title('Two-Factor Authentication Disabled')
                ->warning()
                ->body('2FA has been disabled for your account.')
                ->send();

            $this->redirect(static::getUrl());
        } else {
            Notification::make()
                ->title('Invalid Code')
                ->danger()
                ->body('The verification code you entered is incorrect.')
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        $user = Auth::user();
        
        if ($user->google2fa_enabled) {
            return [
                Forms\Components\Actions\Action::make('disable')
                    ->label('Disable 2FA')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action('disableTwoFactor'),
            ];
        }

        return [
            Forms\Components\Actions\Action::make('enable')
                ->label('Enable 2FA')
                ->color('success')
                ->action('enableTwoFactor'),
        ];
    }
}
