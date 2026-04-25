<?php

namespace App\Filament\Pages;

use App\Exceptions\TenantProvisioningException;
use App\Services\Tenancy\TenantProvisioningService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ProvisionTenant extends Page
{
    public ?array $data = [];

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'Platform';

    protected static ?string $navigationLabel = 'Provision Tenant';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'tenants/provision';

    protected static ?string $title = 'Provision Tenant';

    protected ?string $subheading = 'Create a fully isolated tenant with its own database, baseline workflow data, organization profile, and initial admin user.';

    public function mount(): void
    {
        $this->form->fill($this->getDefaultFormState());
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::FourExtraLarge;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make('Tenant Details')
                            ->description('These values define the tenant record, domain, and tenant database name.')
                            ->columns(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->label('Tenant Slug')
                                    ->required()
                                    ->maxLength(40)
                                    ->placeholder('ourbpo')
                                    ->helperText('Lowercase letters, numbers, and hyphens only. This becomes the subdomain and database slug.')
                                    ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                                    ->dehydrateStateUsing(fn (?string $state): ?string => $state ? Str::lower(trim($state)) : null),
                                TextInput::make('display_name')
                                    ->label('Display Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Our BPO'),
                                TextInput::make('legal_name')
                                    ->label('Legal Name')
                                    ->maxLength(255)
                                    ->placeholder('Our BPO Private Limited'),
                                TextInput::make('domain')
                                    ->label('Primary Domain')
                                    ->maxLength(255)
                                    ->placeholder('ourbpo.crm.local')
                                    ->helperText('Leave blank to use <slug>.crm.local automatically.'),
                                TextInput::make('timezone')
                                    ->required()
                                    ->maxLength(64)
                                    ->placeholder('Asia/Kolkata'),
                                TextInput::make('locale')
                                    ->maxLength(35)
                                    ->placeholder('en-IN'),
                                TextInput::make('region')
                                    ->required()
                                    ->maxLength(32)
                                    ->placeholder('primary'),
                                TextInput::make('currency_code')
                                    ->label('Currency Code')
                                    ->required()
                                    ->maxLength(3)
                                    ->minLength(3)
                                    ->placeholder('INR')
                                    ->dehydrateStateUsing(fn (?string $state): ?string => $state ? Str::upper(trim($state)) : null),
                                TextInput::make('country_code')
                                    ->label('Country Code')
                                    ->maxLength(2)
                                    ->minLength(2)
                                    ->placeholder('IN')
                                    ->dehydrateStateUsing(fn (?string $state): ?string => $state ? Str::upper(trim($state)) : null),
                            ]),
                        Section::make('Initial Admin')
                            ->description('This user is created inside the tenant database and receives the tenant_admin role.')
                            ->columns(2)
                            ->schema([
                                TextInput::make('admin_name')
                                    ->label('Admin Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Amit Sharma'),
                                TextInput::make('admin_email')
                                    ->label('Admin Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('admin@ourbpo.com'),
                                TextInput::make('admin_job_title')
                                    ->label('Admin Job Title')
                                    ->maxLength(255)
                                    ->placeholder('Operations Manager'),
                                TextInput::make('admin_password')
                                    ->label('Admin Password')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->minLength(8)
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }

    public function provision(TenantProvisioningService $service): void
    {
        $data = $this->form->getState();

        try {
            $tenant = $service->provision(
                slug: $data['slug'],
                displayName: $data['display_name'],
                adminName: $data['admin_name'],
                adminEmail: $data['admin_email'],
                adminPassword: $data['admin_password'],
                domain: filled($data['domain'] ?? null) ? $data['domain'] : null,
                legalName: filled($data['legal_name'] ?? null) ? $data['legal_name'] : null,
                timezone: $data['timezone'],
                locale: filled($data['locale'] ?? null) ? $data['locale'] : null,
                region: $data['region'],
                currencyCode: $data['currency_code'],
                countryCode: filled($data['country_code'] ?? null) ? $data['country_code'] : null,
                adminJobTitle: filled($data['admin_job_title'] ?? null) ? $data['admin_job_title'] : null,
                triggeredByUserId: auth()->id(),
            );
        } catch (InvalidArgumentException $exception) {
            Notification::make()
                ->danger()
                ->title('Could not provision tenant')
                ->body($exception->getMessage())
                ->send();

            return;
        } catch (TenantProvisioningException $exception) {
            Notification::make()
                ->danger()
                ->title('Tenant provisioning failed')
                ->body($exception->getMessage())
                ->persistent()
                ->send();

            return;
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('Unexpected provisioning error')
                ->body('The tenant was not activated. Review the latest provisioning run and application logs.')
                ->persistent()
                ->send();

            return;
        }

        $primaryDomain = $tenant->domains()->where('is_primary', true)->value('domain');

        Notification::make()
            ->success()
            ->title('Tenant provisioned successfully')
            ->body("{$tenant->display_name} is now active at {$primaryDomain} using database {$tenant->database_name}.")
            ->send();

        $this->form->fill($this->getDefaultFormState());
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('provision')
                ->label('Provision Tenant')
                ->color('primary')
                ->submit('provision'),
        ];
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('provision')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(Alignment::End)
                    ->fullWidth(false)
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function getDefaultFormState(): array
    {
        return [
            'timezone' => 'Asia/Kolkata',
            'locale' => 'en-IN',
            'region' => 'primary',
            'currency_code' => 'INR',
            'country_code' => 'IN',
        ];
    }
}
