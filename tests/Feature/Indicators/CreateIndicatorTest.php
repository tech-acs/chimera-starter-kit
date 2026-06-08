<?php

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\IndicatorAttributes;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\CreateIndicator;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Results\ArtefactCreationResult;
// Permission stub for Spatie Permission (not in dev dependencies)
if (! class_exists('Spatie\Permission\Models\Permission')) {
    class PermissionStub extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'permissions';
        protected $guarded = ['id'];
        public $timestamps = true;
    }
    class_alias(PermissionStub::class, 'Spatie\\Permission\\Models\\Permission');
}

function fakeIndicatorModel(array $attributes = []): Model
{
    $model = new class extends Model {
        protected $table = 'indicators';
        public $timestamps = false;
    };
    $model->forceFill($attributes + ['id' => 1, 'name' => 'TestIndicator']);

    return $model;
}

// ---------------------------------------------------------------------------
// 1. CreateArtefactAction — unit/integration tests

describe('CreateArtefactAction (indicator)', function () {
    beforeEach(function () {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('help')->nullable();
            $table->string('data_source')->nullable();
            $table->string('type');
            $table->json('data')->nullable();
            $table->json('layout')->nullable();
            $table->boolean('published')->default(false);
            $table->string('scope')->nullable();
            $table->timestamps();
        });
    });

    afterEach(function () {
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('permissions');
    });

    it('creates indicator and returns success result', function () {
        $kernelMock = Mockery::mock(Illuminate\Contracts\Console\Kernel::class);
        $kernelMock->shouldReceive('call')
            ->once()
            ->with('chimera:make-artefact', Mockery::on(function (array $params) {
                return $params['name'] === 'TestIndicator'
                    && $params['--namespace'] === '\Livewire';
            }))
            ->andReturn(Command::SUCCESS);
        Artisan::swap($kernelMock);

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: Indicator::class, baseNamespace: '\Livewire', attributes: new IndicatorAttributes(
            name: 'TestIndicator',
            title: 'Test Title',
            dataSource: 'households',
            type: 'bar',
            description: 'Test Description',
            data: [],
            layout: [],
            stub: resource_path('stubs/indicators/default.stub'),
        ));

        expect($result->success)->toBeTrue();
        expect($result->artefact->id)->toBe(1);
        expect($result->filePath)->toContain('TestIndicator.php');
    });

    it('returns failed result when file creation fails', function () {
        $kernelMock = Mockery::mock(Illuminate\Contracts\Console\Kernel::class);
        $kernelMock->shouldReceive('call')
            ->once()
            ->andReturn(Command::FAILURE);
        Artisan::swap($kernelMock);

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: Indicator::class, baseNamespace: '\Livewire', attributes: new IndicatorAttributes(
            name: 'FailingIndicator',
            title: 'Test Title',
            dataSource: 'households',
            type: 'bar',
            description: 'Test Description',
            data: [],
            layout: [],
            stub: resource_path('stubs/indicators/default.stub'),
        ));

        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toContain('problem creating the class file');
    });

    it('returns failed result on exception', function () {
        Schema::dropIfExists('indicators');

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: Indicator::class, baseNamespace: '\Livewire', attributes: new IndicatorAttributes(
            name: 'FailingIndicator',
            title: 'Test Title',
            dataSource: 'households',
            type: 'bar',
            description: 'Test Description',
            data: [],
            layout: [],
            stub: resource_path('stubs/indicators/default.stub'),
        ));

        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toBeString();
    });
});

// ---------------------------------------------------------------------------
// 2. Web Path — controller integration tests
// ---------------------------------------------------------------------------

describe('Web Path', function () {
    beforeEach(function () {
        Schema::create('chart_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('chart_templates');
    });

    it('creates indicator via http post and redirects with success', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeIndicatorModel(),
                app_path('Livewire/TestIndicator.php'),
            ));

        $response = $this
            ->withoutMiddleware()
            ->post(route('developer.indicator.store'), [
                'name' => 'TestIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
                'chosen_chart_type' => 'default',
                'includeSampleCode' => false,
            ]);

        $response->assertRedirect(route('indicator.index'));
        $response->assertSessionHas('message', 'Indicator created');
    });

    it('returns validation errors for invalid input', function () {
        $response = $this
            ->withoutMiddleware()
            ->post(route('developer.indicator.store'), [
                'name' => '123invalid',
                'data_source' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'data_source']);
    });

    it('redirects with error when action fails', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::failed('Something went wrong'));

        $response = $this
            ->withoutMiddleware()
            ->post(route('developer.indicator.store'), [
                'name' => 'TestIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
                'chosen_chart_type' => 'default',
                'includeSampleCode' => false,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('errors');
    });
});

// ---------------------------------------------------------------------------
// 3. CLI Path — command integration tests
// ---------------------------------------------------------------------------

describe('CLI Path', function () {
    beforeEach(function () {
        Schema::create('chart_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->timestamps();
        });
        DB::table('chart_templates')->insert([
            'name' => 'Basic Bar',
            'category' => 'Standard',
        ]);

        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->timestamps();
        });
        DB::table('data_sources')->insert([
            'name' => 'households',
            'title' => json_encode(['en' => 'Households']),
        ]);

        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('chart_templates');
    });

    it('creates indicator and returns success exit code', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeIndicatorModel(),
                app_path('Livewire/TestIndicator.php'),
            ));

        $this->artisan('chimera:make-indicator')
            ->expectsChoice(
                'Which data source will this indicator be using?',
                'households',
                ['households' => 'Households'],
            )
            ->expectsQuestion('Indicator name', 'TestIndicator')
            ->expectsConfirmation('Do you want to create the indicator from a template?', 'no')
            ->expectsConfirmation('Do you want the generated file to include functioning sample code?', 'no')
            ->expectsQuestion('Please enter a reader friendly title for the indicator', 'Test Title')
            ->expectsQuestion('Please enter a description for the indicator', 'Test Description')
            ->assertSuccessful();
    });

    it('returns failure exit code when action fails', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::failed('File creation failed'));

        $this->artisan('chimera:make-indicator')
            ->expectsChoice(
                'Which data source will this indicator be using?',
                'households',
                ['households' => 'Households'],
            )
            ->expectsQuestion('Indicator name', 'TestIndicator')
            ->expectsConfirmation('Do you want to create the indicator from a template?', 'no')
            ->expectsConfirmation('Do you want the generated file to include functioning sample code?', 'no')
            ->expectsQuestion('Please enter a reader friendly title for the indicator', 'Test Title')
            ->expectsQuestion('Please enter a description for the indicator', 'Test Description')
            ->assertFailed();
    });
});

// ---------------------------------------------------------------------------
// 4. MCP Path — tool integration tests
// ---------------------------------------------------------------------------

describe('MCP Path', function () {
    beforeEach(function () {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('indicators');
    });

    it('creates indicator and returns success response', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeIndicatorModel(),
                app_path('Livewire/TestIndicator.php'),
            ));

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(CreateIndicator::class, [
                'name' => 'TestIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
            ]);

        $response->assertOk();
        $response->assertSee('Indicator created successfully');
    });

    it('rejects invalid parameters with error response', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(CreateIndicator::class, [
                'title' => 'Test Title',
                'data_source' => 'households',
            ]);

        $response->assertHasErrors(['name']);
    });

    it('returns error response when action fails', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::failed('File creation failed'));

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(CreateIndicator::class, [
                'name' => 'TestIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
            ]);

        $response->assertHasErrors(['Failed to create']);
    });
});
