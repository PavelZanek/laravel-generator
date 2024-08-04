<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Console\Commands;

use PavelZanek\LaravelGenerator\Enums\FrameworkEnum;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PavelZanek\LaravelGenerator\Enums\TestingFrameworkEnum;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

final class GenerateAppSectionCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-generator:app {class} {--framework=Laravel} {--compact} {--jetstream} {--factory} {--seeder} {--tests=PHPunit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate App files for given model';

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'class' => 'What model do you want to generate App files for?',
        ];
    }

    /**
     * Perform actions after the user was prompted for missing arguments.
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        $input->setOption('framework', select(
            label: 'What framework do you want to use?',
            options: [FrameworkEnum::LARAVEL->value, FrameworkEnum::LIVEWIRE->value],
            default: $this->option('framework') // @phpstan-ignore-line
        ));

        if($this->option('framework') == FrameworkEnum::LIVEWIRE->value) {
            $input->setOption('compact', confirm(
                label: 'Want to generate compact views (modals)?',
                default: $this->option('compact') // @phpstan-ignore-line
            ));
        }

        $input->setOption('jetstream', confirm(
            label: 'Want to generate files that assume you have Laravel Jetstream installed? If not, then an App Layout similar to Laravel Jetstream\'s will be created.',
            default: $this->option('jetstream') // @phpstan-ignore-line
        ));

        $input->setOption('factory', confirm(
            label: 'Want to create a Factory?',
            default: $this->option('factory') // @phpstan-ignore-line
        ));

        $input->setOption('seeder', confirm(
            label: 'Want to create a Seeder?',
            default: $this->option('seeder') // @phpstan-ignore-line
        ));

        $input->setOption('tests', select(
            label: 'What testing framework do you want to use?',
            options: [TestingFrameworkEnum::PHPUNIT->value, TestingFrameworkEnum::PEST->value, 'Don\'t generate tests'],
            default: $this->option('tests') // @phpstan-ignore-line
        ));
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle(): int
    {
        $className = $this->argument('class');
        $className = 'App\Models\FirstNamespace\SecondNamespace\ExampleItem'; // TODO
        if (!str_contains($className, '\\')) {
            $className = 'App\\Models\\' . $className;
        }
        if (!class_exists($className)) {
            $this->fail('Class does not exist: "' . $className . '". Enter the name of the class (for example "Post") in the full form using double backslashes (for example "App\\\\Models\\\\Post")');
        }

        /**
         * @var Model $model
         */
        $model = app($className);
        $modelName = Str::afterLast($className, '\\');
        $modelPluralName = Str::camel($model->getTable());

        $classNamespaceFull = Str::beforeLast($className, '\\');
        $classNamespace = Str::afterLast($classNamespaceFull, 'App\\Models') . '\\';
        if ($classNamespaceFull === 'App\\Models') {
            $classNamespace = '\\';
        }
        $classNamespaceSlash = \str_replace('\\', '/', $classNamespace);
        $namespaceSegments = \explode('/', $classNamespaceSlash);
        $sluggedNamespaceSegments = \array_map(fn ($segment) => Str::slug(Str::snake($segment)), $namespaceSegments);

        /** @var array<string, string|bool|array<string, string>> $data */
        $data = [
            'framework' => $this->option('framework'),
            'isCompact' => $this->option('compact'), // it only works with Livewire framework,
            'jetstreamCompatibility' => $this->option('jetstream'),
            'factory' => $this->option('factory'),
            'seeder' => $this->option('seeder'),
            'tests' => $this->option('tests'),
            'replacement' => [
                '#dbTableName#' => $model->getTable(),
                '#className#' => $replacementClassName = Str::afterLast($className, '\\'),
                '#classNamespace#' => $classNamespace,
                '#classNamespaceWithoutEndingBackslash#' => Str::beforeLast($classNamespace, '\\'),
                '#classNamespaceSlash#' => $classNamespaceSlash,
                '#sluggedClassNamespace#' => \implode('/', $sluggedNamespaceSegments),
                '#dottedClassNamespace#' => \implode('.', $sluggedNamespaceSegments),

                '#camelModelNamePlural#' => $modelPluralName,
                '#ucfirstCamelModelNamePlural#' => \ucfirst($modelPluralName),
                '#uppercaseModelNamePlural#' => \mb_strtoupper($model->getTable()),
                '#sluggedModelNamePlural#' => Str::slug(Str::snake($modelPluralName)),

                '#camelModelNameSingular#' => \lcfirst($modelName),
                '#uppercaseModelNameSingular#' => \mb_strtoupper(Str::snake($modelName)),
                '#sluggedModelNameSingular#' => Str::slug(Str::snake($modelName)),

//                '#validationRules#' => $this->rulesArrayToString(
//                    $this->generateValidationRules($model, $replacementClassName)
//                ),
            ],
        ];

        //        dd($data);
        //        "framework" => "Laravel"
        //        "isCompact" => false
        //        "jetstreamCompatibility" => false
        //        "factory" => false
        //        "seeder" => false
        //        "tests" => "PHPUnit
        //        "replacement" => array:13 [
        //            "#dbTableName#" => "example_items"
        //            "#classNamespace#" => "\FirstNamespace\SecondNamespace\"
        //            "#classNamespaceWithoutEndingBackslash#" => "\FirstNamespace\SecondNamespace"
        //            "#classNamespaceSlash#" => "/FirstNamespace/SecondNamespace/"
        //            "#sluggedClassNamespace#" => "/first-namespace/second-namespace/"
        //            "#dottedClassNamespace#" => ".first-namespace.second-namespace."
        //            "#camelModelNamePlural#" => "exampleItems"
        //            "#ucfirstCamelModelNamePlural#" => "ExampleItems"
        //            "#uppercaseModelNamePlural#" => "EXAMPLE_ITEMS"
        //            "#sluggedModelNamePlural#" => "example-items"
        //            "#camelModelNameSingular#" => "exampleItem"
        //            "#uppercaseModelNameSingular#" => "EXAMPLE_ITEM"
        //            "#sluggedModelNameSingular#" => "example-item"

        //        "framework" => "Laravel"
        //        "isCompact" => false
        //        "jetstreamCompatibility" => false
        //        "factory" => false
        //        "seeder" => false
        //        "tests" => "PHPUnit
        //        "replacement" => array:14 [
        //            "#dbTableName#" => "users"
        //            "#className#" => "User"
        //            "#classNamespace#" => "\"
        //            "#classNamespaceWithoutEndingBackslash#" => ""
        //            "#classNamespaceSlash#" => "/"
        //            "#sluggedClassNamespace#" => "/"
        //            "#dottedClassNamespace#" => "."
        //            "#camelModelNamePlural#" => "users"
        //            "#ucfirstCamelModelNamePlural#" => "Users"
        //            "#uppercaseModelNamePlural#" => "USERS"
        //            "#sluggedModelNamePlural#" => "users"
        //            "#camelModelNameSingular#" => "user"
        //            "#uppercaseModelNameSingular#" => "USER"
        //            "#sluggedModelNameSingular#" => "user"


        // Generate translation file
        $this->line('Generating translations');
        $translationPath = $this->generateTranslationFile($data);
        $this->newLine();

        // Generate action files
        $this->line('Generating actions');
        $this->generateActionFiles($data);
        $this->newLine();

        // Generate request files
        if ($data['framework'] === FrameworkEnum::LARAVEL->value) {
            $this->line('Generating requests (validation)');
            $this->generateRequestFiles($data, $translationPath);
            $this->newLine();
        }

        // Generate tables
        //        $this->line('Generating table helper');
        //        $this->generateTableHelperFiles($data);
        //        $this->newLine();

        // Generate table settings
        $this->line('Generating table settings');
        $this->generateTableSettingsFile($data, $translationPath);
        $this->newLine();

        // Generate controller file
        $this->line('Generating controller');
        $this->generateControllerFile($data, $translationPath);
        $this->newLine();

        // Generate view files
        $this->line('Generating views');
        $this->generateViewFiles($data, $translationPath);
        $this->newLine();


        // Generate Livewire component & view files
        if($data['framework'] === FrameworkEnum::LIVEWIRE->value) {
            $this->line('Generating Livewire component files');
            $this->generateLivewireFiles($data, $translationPath);
            $this->newLine();

            $this->line('Generating Livewire view files');
            $this->generateLivewireViewFiles($data, $translationPath);
            $this->newLine();
        }

        // Generate factory file
        if($data['factory']) {
            $this->line('Generating factory');
            $this->generateFactoryFile($data);
            $this->newLine();
        }

        // Generate seeder file
        if($data['seeder']) {
            $this->line('Generating seeder');
            $this->generateSeederFile($data);
            $this->newLine();
        }

        // Generate test files
        if(\in_array($data['tests'], [TestingFrameworkEnum::PHPUNIT->value, TestingFrameworkEnum::PEST->value])) {
            $this->line('Generating tests');
            $this->generateTestFiles($data);
            $this->newLine();
        }

        // Generate resource route
        $this->line('Generating resource route');
        $this->generateResourceRoute($data);
        $this->newLine();

        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @return string
     */
    protected function generateTranslationFile(array $data): string
    {
        /** @var array<array-key, string> $languages */
        $languages = config('laravel-generator.locales');
        $sluggedClassNamespace = $data['replacement']['#sluggedClassNamespace#']; // @phpstan-ignore-line
        $sluggedModelNameSingular = $data['replacement']['#sluggedModelNameSingular#']; // @phpstan-ignore-line

        $translationPath = "app{$sluggedClassNamespace}{$sluggedModelNameSingular}-translations";

        foreach($languages as $language) {
            if(! \in_array($language, ['en', 'cs'])) {
                $this->warn("Invalid translation language - $language, skipping.");
                continue;
            }

            $this->generateFile(
                filePath: lang_path(path: "{$language}/{$translationPath}.php"),
                stubPath: $this->getStubPath(stub: "translations/{$language}.php.stub"),
                data: $data,
                message: 'Generated translation file',
            );
        }

        return $translationPath;
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @return void
     */
    protected function generateActionFiles(array $data): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line
        $ucfirstCamelModelNamePlural = $data['replacement']['#ucfirstCamelModelNamePlural#']; // @phpstan-ignore-line

        foreach (['Create', 'Update', 'Delete'] as $action) {
            $this->generateFile(
                filePath: app_path(path: "Actions/App{$classNamespace}{$ucfirstCamelModelNamePlural}/{$action}{$className}Action.php"),
                stubPath: $this->getStubPath(stub: "actions/{$action}Action.php.stub"),
                data: $data,
                message: 'Generated request file',
                arrayKeys: ['#actionType#'],
                arrayValues: [$action],
            );
        }
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @param string $translationPath
     * @return void
     */
    protected function generateRequestFiles(array $data, string $translationPath): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line
        $ucfirstCamelModelNamePlural = $data['replacement']['#ucfirstCamelModelNamePlural#']; // @phpstan-ignore-line

        foreach (['Store', 'Update'] as $requestType) {
            $this->generateFile(
                filePath: app_path(path: "Http/Requests/App{$classNamespace}{$ucfirstCamelModelNamePlural}/{$requestType}{$className}Request.php"),
                stubPath: $this->getStubPath(stub: "requests/{$requestType}Request.php.stub"),
                data: $data,
                message: 'Generated request file',
                arrayKeys: ['#requestType#', '#translationPath#'],
                arrayValues: [$requestType, $translationPath],
            );
        }
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @return void
     */
    protected function generateTableHelperFiles(array $data): void
    {
        $this->generateFile(
            filePath: app_path(path: "Tables/Helpers/TableColumn.php"),
            stubPath: $this->getStubPath(stub: "tables/TableColumn.php.stub"),
            data: $data,
            message: 'Generated table helper file',
        );

        $this->generateFile(
            filePath: app_path(path: "Enums/TableFilters/FilterInputTypeEnum.php"),
            stubPath: $this->getStubPath(stub: "enums/FilterInputTypeEnum.php.stub"),
            data: $data,
            message: 'Generated enum file',
        );

        $this->generateFile(
            filePath: app_path(path: "Enums/TableFilters/FilterOperatorEnum.php"),
            stubPath: $this->getStubPath(stub: "enums/FilterOperatorEnum.php.stub"),
            data: $data,
            message: 'Generated enum file',
        );

        $this->generateFile(
            filePath: app_path(path: "Enums/TableFilters/FilterTypeEnum.php"),
            stubPath: $this->getStubPath(stub: "enums/FilterTypeEnum.php.stub"),
            data: $data,
            message: 'Generated enum file',
        );
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @param string $translationPath
     * @return void
     */
    protected function generateTableSettingsFile(array $data, string $translationPath): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line

        $this->generateFile(
            filePath: app_path(path: "Tables/App{$classNamespace}{$className}TableSettings.php"),
            stubPath: $this->getStubPath(stub: "tables/TableSettings.php.stub"),
            data: $data,
            message: 'Generated table settings file',
            arrayKeys: ['#translationPath#'],
            arrayValues: [$translationPath],
        );
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @param string $translationPath
     * @return void
     */
    protected function generateControllerFile(array $data, string $translationPath): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line

        if($data['framework'] === FrameworkEnum::LARAVEL->value) {
            $this->generateFile(
                filePath: app_path(path: "Http/Controllers/App{$classNamespace}{$className}Controller.php"),
                stubPath: $this->getStubPath(stub: "controllers/LaravelCrudController.php.stub"),
                data: $data,
                message: 'Generated controller file',
                arrayKeys: ['#translationPath#'],
                arrayValues: [$translationPath],
            );
        } elseif($data['framework'] === FrameworkEnum::LIVEWIRE->value) {
            $this->generateFile(
                filePath: app_path(path: "Http/Controllers/App{$classNamespace}{$className}Controller.php"),
                stubPath: $this->getStubPath(stub: "controllers/LivewireCrudController.php.stub"),
                data: $data,
                message: 'Generated controller file',
                arrayKeys: ['#translationPath#'],
                arrayValues: [$translationPath],
            );
        }
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @param string $translationPath
     * @return void
     */
    protected function generateViewFiles(array $data, string $translationPath): void
    {
        $sluggedClassNamespace = $data['replacement']['#sluggedClassNamespace#']; // @phpstan-ignore-line
        $sluggedModelNamePlural = $data['replacement']['#sluggedModelNamePlural#']; // @phpstan-ignore-line

        if($data['jetstreamCompatibility'] === false) {
            $this->generateFile(
                filePath: app_path(path: "View/Components/AppLayout.php"),
                stubPath: $this->getStubPath(stub: "views/layouts/AppLayout.php.stub"),
                data: $data,
                message: 'Generated app layout component file',
            );
            $this->generateFile(
                filePath: resource_path(path: "views/layouts/app.blade.php"),
                stubPath: $this->getStubPath(stub: "views/layouts/app.blade.php.stub"),
                data: $data,
                message: 'Generated app layout blade file',
            );
        }

        foreach (['index.blade.php', 'create.blade.php', 'edit.blade.php'] as $template) {
            if($data['framework'] === FrameworkEnum::LARAVEL->value) { // Laravel
                $this->generateFile(
                    filePath: resource_path(path: "views/app{$sluggedClassNamespace}{$sluggedModelNamePlural}/{$template}"),
                    stubPath: $this->getStubPath(stub: "views/laravel/{$template}.stub"),
                    data: $data,
                    message: 'Generated view file',
                    arrayKeys: ['#translationPath#'],
                    arrayValues: [$translationPath],
                );
            } elseif($data['framework'] === FrameworkEnum::LIVEWIRE->value) { // Livewire
                $this->generateFile(
                    filePath: resource_path(path: "views/app{$sluggedClassNamespace}{$sluggedModelNamePlural}/{$template}"),
                    stubPath: $this->getStubPath(stub: "views/livewire/{$template}.stub"),
                    data: $data,
                    message: 'Generated view file',
                    arrayKeys: ['#translationPath#'],
                    arrayValues: [$translationPath],
                );
            }
        }
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @param string $translationPath
     * @return void
     */
    protected function generateLivewireFiles(array $data, string $translationPath): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line
        $ucfirstCamelModelNamePlural = $data['replacement']['#ucfirstCamelModelNamePlural#']; // @phpstan-ignore-line

        foreach (['Create', 'Edit', 'Manage'] as $componentType) {
            $this->generateFile(
                filePath: app_path(path: "Livewire/App{$classNamespace}{$ucfirstCamelModelNamePlural}/{$componentType}{$className}Component.php"),
                stubPath: $this->getStubPath(stub: "livewire/{$componentType}Component.php.stub"),
                data: $data,
                message: 'Generated Livewire component file',
                arrayKeys: ['#translationPath#'],
                arrayValues: [$translationPath],
            );
        }

        foreach (['Create', 'Edit'] as $formType) {
            $this->generateFile(
                filePath: app_path(path: "Livewire/Forms/App{$classNamespace}{$ucfirstCamelModelNamePlural}/{$formType}{$className}Form.php"),
                stubPath: $this->getStubPath(stub: "livewire/forms/{$formType}Form.php.stub"),
                data: $data,
                message: 'Generated Livewire form file',
                arrayKeys: ['#translationPath#'],
                arrayValues: [$translationPath],
            );
        }
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @param string $translationPath
     * @return void
     */
    protected function generateLivewireViewFiles(array $data, string $translationPath): void
    {
        $sluggedClassNamespace = $data['replacement']['#sluggedClassNamespace#']; // @phpstan-ignore-line
        $sluggedModelNamePlural = $data['replacement']['#sluggedModelNamePlural#']; // @phpstan-ignore-line
        $sluggedModelNameSingular = $data['replacement']['#sluggedModelNameSingular#']; // @phpstan-ignore-line

        $this->generateFile(
            filePath: resource_path(path: "views/livewire/app{$sluggedClassNamespace}{$sluggedModelNamePlural}/create-{$sluggedModelNameSingular}-component.blade.php"),
            stubPath: $this->getStubPath(stub: "views/livewire/components/create-component.blade.php.stub"),
            data: $data,
            message: 'Generated Livewire view file',
            arrayKeys: ['#translationPath#'],
            arrayValues: [$translationPath],
        );

        $this->generateFile(
            filePath: resource_path(path: "views/livewire/app{$sluggedClassNamespace}{$sluggedModelNamePlural}/edit-{$sluggedModelNameSingular}-component.blade.php"),
            stubPath: $this->getStubPath(stub: "views/livewire/components/edit-component.blade.php.stub"),
            data: $data,
            message: 'Generated Livewire view file',
            arrayKeys: ['#translationPath#'],
            arrayValues: [$translationPath],
        );

        $this->generateFile(
            filePath: resource_path(path: "views/livewire/app{$sluggedClassNamespace}{$sluggedModelNamePlural}/manage-{$sluggedModelNameSingular}-component.blade.php"),
            stubPath: $this->getStubPath(stub: "views/livewire/components/manage-component.blade.php.stub"),
            data: $data,
            message: 'Generated Livewire view file',
            arrayKeys: ['#translationPath#'],
            arrayValues: [$translationPath],
        );
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @return void
     */
    protected function generateFactoryFile(array $data): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line

        $this->generateFile(
            filePath: database_path(path: "factories{$classNamespace}{$className}Factory.php"),
            stubPath: $this->getStubPath(stub: "factories/ModelFactory.php.stub"),
            data: $data,
            message: 'Generated factory file',
        );
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @return void
     */
    protected function generateSeederFile(array $data): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line

        $this->generateFile(
            filePath: database_path(path: "seeders{$classNamespace}{$className}Seeder.php"),
            stubPath: $this->getStubPath("seeders/ModelSeeder.php.stub"),
            data: $data,
            message: 'Generated seeder file',
        );
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @return void
     */
    protected function generateTestFiles(array $data): void
    {
        $classNamespace = $data['replacement']['#classNamespaceSlash#']; // @phpstan-ignore-line
        $className = $data['replacement']['#className#']; // @phpstan-ignore-line
        $ucfirstCamelModelNamePlural = $data['replacement']['#ucfirstCamelModelNamePlural#']; // @phpstan-ignore-line
        $pest = $data['tests'] === TestingFrameworkEnum::PEST->value ? TestingFrameworkEnum::PEST->value : '';

        $this->generateFile(
            filePath: base_path(path: "tests/Feature/Actions/App{$classNamespace}{$ucfirstCamelModelNamePlural}/Create{$className}ActionTest.php"),
            stubPath: $this->getStubPath(stub: "tests/actions/CreateActionTest{$pest}.php.stub"),
            data: $data,
            message: 'Generated test file',
        );

        $this->generateFile(
            filePath: base_path(path: "tests/Feature/Actions/App{$classNamespace}{$ucfirstCamelModelNamePlural}/Delete{$className}ActionTest.php"),
            stubPath: $this->getStubPath(stub: "tests/actions/DeleteActionTest{$pest}.php.stub"),
            data: $data,
            message: 'Generated test file',
        );

        $this->generateFile(
            filePath: base_path(path: "tests/Feature/Actions/App{$classNamespace}{$ucfirstCamelModelNamePlural}/Update{$className}ActionTest.php"),
            stubPath: $this->getStubPath(stub: "tests/actions/UpdateActionTest{$pest}.php.stub"),
            data: $data,
            message: 'Generated test file',
        );

        if($data['framework'] === FrameworkEnum::LARAVEL->value) {
            $this->generateFile(
                filePath: base_path(path: "tests/Feature/Controllers/App{$classNamespace}{$className}ControllerTest.php"),
                stubPath: $this->getStubPath(stub: "tests/controllers/LaravelCrudControllerTest{$pest}.php.stub"),
                data: $data,
                message: 'Generated test file',
            );
        } elseif ($data['framework'] === FrameworkEnum::LIVEWIRE->value) {
            $this->generateFile(
                filePath: base_path(path: "tests/Feature/Controllers/App{$classNamespace}{$className}ControllerTest.php"),
                stubPath: $this->getStubPath(stub: "tests/controllers/LivewireCrudControllerTest{$pest}.php.stub"),
                data: $data,
                message: 'Generated test file',
            );

            $this->generateFile(
                filePath: base_path(path: "tests/Feature/Livewire/App{$classNamespace}{$ucfirstCamelModelNamePlural}/Manage{$className}ComponentTest.php"),
                stubPath: $this->getStubPath(stub: "tests/livewire/ManageComponentTest{$pest}.php.stub"),
                data: $data,
                message: 'Generated test file',
            );

            $this->generateFile(
                filePath: base_path(path: "tests/Feature/Livewire/App{$classNamespace}{$ucfirstCamelModelNamePlural}/Create{$className}ComponentTest.php"),
                stubPath: $this->getStubPath(stub: "tests/livewire/CreateComponentTest{$pest}.php.stub"),
                data: $data,
                message: 'Generated test file',
            );

            $this->generateFile(
                filePath: base_path(path: "tests/Feature/Livewire/App{$classNamespace}{$ucfirstCamelModelNamePlural}/Edit{$className}ComponentTest.php"),
                stubPath: $this->getStubPath(stub: "tests/livewire/EditComponentTest{$pest}.php.stub"),
                data: $data,
                message: 'Generated test file',
            );
        }
    }

    /**
     * @param array<string, string|bool|array<string, string>> $data
     * @return void
     */
    protected function generateResourceRoute(array $data): void
    {
        $sluggedModelNamePlural = $data['replacement']['#sluggedModelNamePlural#']; // @phpstan-ignore-line
        if(Route::has($sluggedModelNamePlural . '.index')) {
            $this->warn("Routes already exist, skipping.");
        } else {
            if($data['framework'] === FrameworkEnum::LARAVEL->value) {
                \file_put_contents(
                    base_path("routes/web.php"),
                    Str::replace(
                        \array_keys($data['replacement']), // @phpstan-ignore-line
                        \array_values($data['replacement']), // @phpstan-ignore-line
                        \file_get_contents($this->getStubPath("routes/routes.php.stub")) // @phpstan-ignore-line
                    ),
                    FILE_APPEND,
                );
            } elseif($data['framework'] === FrameworkEnum::LIVEWIRE->value) {
                \file_put_contents(
                    base_path("routes/web.php"),
                    Str::replace(
                        \array_keys($data['replacement']), // @phpstan-ignore-line
                        \array_values($data['replacement']), // @phpstan-ignore-line
                        \file_get_contents($this->getStubPath("routes/routes-livewire.php.stub")) // @phpstan-ignore-line
                    ),
                    FILE_APPEND,
                );
            }
            $this->info("Added resource route to web.php.");
        }
    }

    /**
     * @param string $filePath
     * @param string $stubPath
     * @param array<string, string|bool|array<string, string>> $data
     * @param string $message
     * @param array<array-key, string> $arrayKeys
     * @param array<array-key, string> $arrayValues
     * @return void
     */
    private function generateFile(
        string $filePath,
        string $stubPath,
        array $data,
        string $message,
        array $arrayKeys = [],
        array $arrayValues = [],
    ): void {
        @mkdir(dirname($filePath), 0755, true);

        if(! $this->checkAndInformIfFileExists($filePath)) {
            \file_put_contents(
                $filePath,
                Str::replace(
                    \array_merge(\array_keys($data['replacement']), $arrayKeys), // @phpstan-ignore-line
                    \array_merge(\array_values($data['replacement']), $arrayValues), // @phpstan-ignore-line
                    \file_get_contents($stubPath) // @phpstan-ignore-line
                )
            );
            $this->info("$message - $filePath");
        }
    }

    /**
     * Check if a file exists and inform if it does.
     *
     * @param string $filePath
     * @return bool
     */
    private function checkAndInformIfFileExists(string $filePath): bool
    {
        if (\file_exists($filePath)) {
            $this->warn("File already exists - $filePath, skipping.");
            return true;
        }

        return false;
    }

    /**
     * @param string $stub
     * @return string
     */
    private function getStubPath(string $stub): string
    {
        $customStubPath = resource_path(path: "stubs/vendor/laravel-generator/{$stub}");
        if (file_exists($customStubPath)) {
            return $customStubPath;
        }

        return __DIR__ . "/../../../stubs/{$stub}"; // development
        //        return base_path("vendor/pavelzanek/laravel-generator/stubs/{$stub}"); // production
    }

    //    /**
    //     * @param \Illuminate\Database\Eloquent\Model $model
    //     * @param string $replacementClassName
    //     * @return array<string, array<int, mixed>>
    //     */
    //    private function generateValidationRules(Model $model, string $replacementClassName): array
    //    {
    //        $table = $model->getTable();
    //        $columns = Schema::getColumnListing($table);
    //        $rules = [];
    //
    //        foreach ($columns as $column) {
    //            if (\in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'])) continue;
    //            $columnDetails = $this->getColumnDetails($table, $column);
    ////            $rules[$column] = $this->getRulesForColumn($column, $columnDetails, $model);
    //            $rules[$replacementClassName . '::ATTR_' . \strtoupper($column)] = $this->getRulesForColumn($column, $columnDetails, $model);
    //        }
    //
    //        return $rules;
    //    }
    //
    //    /**
    //     * @param string $table
    //     * @param string $column
    //     * @return array
    //     */
    //    private function getColumnDetails(string $table, string $column): array
    //    {
    //        $connection = Schema::getConnection();
    //        $database = $connection->getDatabaseName();
    //        $query = /** @lang mysql */
    //            "SELECT COLUMN_TYPE as type, IS_NULLABLE as nullable, COLUMN_DEFAULT as default_value
    //                  FROM INFORMATION_SCHEMA.COLUMNS
    //                  WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    //
    //        $details = DB::selectOne($query, [$database, $table, $column]);
    //
    //        return [
    //            'type' => $details->type,
    //            'notnull' => $details->nullable === 'NO',
    //            'default' => $details->default_value,
    //        ];
    //    }
    //
    //    /**
    //     * @param string $column
    //     * @param array<array-key, mixed> $columnDetails
    //     * @param \Illuminate\Database\Eloquent\Model $model
    //     * @return array
    //     */
    //    private function getRulesForColumn(string $column, array $columnDetails, Model $model): array
    //    {
    //        $rules = [];
    //
    //        // Determine if the column should be required or nullable
    //        if ($columnDetails['default'] !== null) {
    //            $rules[] = 'nullable';
    //        } else {
    //            $rules[] = $columnDetails['notnull'] ? 'required' : 'nullable';
    //        }
    //
    //        // Add rules based on the column type
    //        $rules = array_merge($rules, $this->getTypeRules($columnDetails['type']));
    //
    //        // Handle enums dynamically using Laravel's Enum validation rule
    //        if ($this->isEnumColumn($model, $column)) {
    //            $rules[] = new Enum($this->getEnumClass($model, $column));
    //        }
    //
    //        return $rules;
    //    }
    //
    //    /**
    //     * @param string $type
    //     * @return array|string[]
    //     */
    //    private function getTypeRules(string $type): array
    //    {
    //        return match (true) {
    //            str_contains($type, 'varchar') || str_contains($type, 'char') => ['string', 'max:' . $this->getLength($type)],
    //            str_contains($type, 'text') => ['string'],
    //            str_contains($type, 'int') => ['integer'],
    //            str_contains($type, 'tinyint(1)') => ['boolean'],
    //            str_contains($type, 'datetime'), str_contains($type, 'timestamp') => ['date'],
    //            default => [],
    //        };
    //    }
    //
    //    /**
    //     * @param string $type
    //     * @return int
    //     */
    //    private function getLength(string $type): int
    //    {
    //        preg_match('/\((\d+)\)/', $type, $matches);
    //        return isset($matches[1]) ? (int) $matches[1] : 255;
    //    }
    //
    //    /**
    //     * @param \Illuminate\Database\Eloquent\Model $model
    //     * @param string $column
    //     * @return bool
    //     */
    //    private function isEnumColumn(Model $model, string $column): bool
    //    {
    //        $casts = $model->getCasts();
    //        return isset($casts[$column]) && enum_exists($casts[$column]);
    //    }
    //
    //    /**
    //     * @param \Illuminate\Database\Eloquent\Model $model
    //     * @param string $column
    //     * @return string
    //     */
    //    private function getEnumClass(Model $model, string $column): string
    //    {
    //        $casts = $model->getCasts();
    //        return $casts[$column];
    //    }
    //
    //    /**
    //     * @param array $rules
    //     * @return string
    //     */
    //    private function rulesArrayToString(array $rules): string
    //    {
    //        $rulesString = "[\n";
    //        foreach ($rules as $key => $ruleSet) {
    //            $rulesString .= "    $key => [\n";
    //            foreach ($ruleSet as $rule) {
    //                if ($rule instanceof Enum) {
    //                    $reflector = new \ReflectionClass($rule);
    //                    $property = $reflector->getProperty('type');
    //                    // $property->setAccessible(true);
    //                    $enumClass = $property->getValue($rule);
    //                    $rulesString .= "        new \\Illuminate\\Validation\\Rules\\Enum(\\$enumClass::class),\n";
    //                } else {
    //                    $rulesString .= "        " . var_export($rule, true) . ",\n";
    //                }
    //            }
    //            $rulesString .= "    ],\n";
    //        }
    //        $rulesString .= "]";
    //
    //        return $rulesString;
    //    }
}
