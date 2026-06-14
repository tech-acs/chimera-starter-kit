<?php

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\MapIndicatorAttributes;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\CreateMapIndicator;
use Uneca\Chimera\Models\MapIndicator;
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

function fakeMapIndicatorModel(array $attributes = []): Model
{
    $model = new class extends Model {
        protected $table = 'map_indicators';
        public $timestamps = false;
    };
    $model->forceFill($attributes + ['id' => 1, 'name' => 'TestMapIndicator']);

    return $model;
}

// ---------------------------------------------------------------------------
// 1. CreateArtefactAction — unit/integration tests

describe('CreateArtefactAction (map indicator)', function () {
    beforeEach(function () {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('map_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->string('data_source')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    });

    afterEach(function () {
        Schema::dropIfExists('map_indicators');
        Schema::dropIfExists('permissions');
    });

    it('creates map indicator and returns success result', function () {
        $kernelMock = Mockery::mock(Illuminate\Contracts\Console\Kernel::class);
        $kernelMock->shouldReceive('call')
            ->once()
            ->with('chimera:make-artefact', Mockery::on(function (array $params) {
                return $params['name'] === 'TestMapIndicator'
                    && $params['--namespace'] === '\MapIndicators';
            }))
            ->andReturn(Command::SUCCESS);
        Artisan::swap($kernelMock);

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: MapIndicator::class, baseNamespace: '\MapIndicators', attributes: new MapIndicatorAttributes(
            name: 'TestMapIndicator',
            title: 'Test Title',
            description: 'Test Description',
            dataSource: 'households',
            stub: resource_path('stubs/map_indicators/default.stub'),
        ));

        expect($result->success)->toBeTrue();
        expect($result->artefact->id)->toBe(1);
        expect($result->filePath)->toContain('TestMapIndicator.php');
    });

    it('returns failed result when file creation fails', function () {
        $kernelMock = Mockery::mock(Illuminate\Contracts\Console\Kernel::class);
        $kernelMock->shouldReceive('call')
            ->once()
            ->andReturn(Command::FAILURE);
        Artisan::swap($kernelMock);

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: MapIndicator::class, baseNamespace: '\MapIndicators', attributes: new MapIndicatorAttributes(
            name: 'FailingMapIndicator',
            title: 'Test Title',
            description: 'Test Description',
            dataSource: 'households',
            stub: resource_path('stubs/map_indicators/default.stub'),
        ));

        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toContain('problem creating the class file');
    });

    it('returns failed result on exception', function () {
        Schema::dropIfExists('map_indicators');

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: MapIndicator::class, baseNamespace: '\MapIndicators', attributes: new MapIndicatorAttributes(
            name: 'FailingMapIndicator',
            title: 'Test Title',
            description: 'Test Description',
            dataSource: 'households',
            stub: resource_path('stubs/map_indicators/default.stub'),
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
        Schema::create('map_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('map_indicators');
    });

    it('creates map indicator via http post and redirects with success', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeMapIndicatorModel(),
                app_path('MapIndicators/TestMapIndicator.php'),
            ));

        $response = $this
            ->withoutMiddleware()
            ->post(route('developer.map_indicator.store'), [
                'name' => 'TestMapIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
            ]);

        $response->assertRedirect(route('manage.map_indicator.index'));
        $response->assertSessionHas('message', 'Map indicator created');
    });

    it('returns validation errors for invalid input', function () {
        $response = $this
            ->withoutMiddleware()
            ->post(route('developer.map_indicator.store'), [
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
            ->post(route('developer.map_indicator.store'), [
                'name' => 'TestMapIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
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

        Schema::create('map_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('map_indicators');
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('permissions');
    });

    it('creates map indicator and returns success exit code', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeMapIndicatorModel(),
                app_path('MapIndicators/TestMapIndicator.php'),
            ));

        $this->artisan('chimera:make-map-indicator')
            ->expectsChoice(
                'Which data source will this map indicator be using?',
                'households',
                ['households' => 'Households'],
            )
            ->expectsQuestion('Map indicator name', 'TestMapIndicator')
            ->expectsQuestion('Please enter a reader friendly title for the map indicator', 'Test Title')
            ->expectsQuestion('Please enter a description for the map indicator', 'Test Description')
            ->assertSuccessful();
    });

    it('returns failure exit code when action fails', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::failed('File creation failed'));

        $this->artisan('chimera:make-map-indicator')
            ->expectsChoice(
                'Which data source will this map indicator be using?',
                'households',
                ['households' => 'Households'],
            )
            ->expectsQuestion('Map indicator name', 'TestMapIndicator')
            ->expectsQuestion('Please enter a reader friendly title for the map indicator', 'Test Title')
            ->expectsQuestion('Please enter a description for the map indicator', 'Test Description')
            ->assertFailed();
    });
});

// ---------------------------------------------------------------------------
// 4. MCP Path — tool integration tests
// ---------------------------------------------------------------------------

describe('MCP Path', function () {
    beforeEach(function () {
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        \DB::table('data_sources')->insert([
            'name' => 'households',
            'title' => json_encode(['en' => 'Households']),
            'active' => true,
        ]);

        Schema::create('map_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('map_indicators');
    });

    it('creates map indicator and returns success response', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeMapIndicatorModel(),
                app_path('MapIndicators/TestMapIndicator.php'),
            ));

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(CreateMapIndicator::class, [
                'name' => 'TestMapIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
            ]);

        $response->assertOk();
        $response->assertSee('Map indicator created successfully');
    });

    it('rejects invalid parameters with error response', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(CreateMapIndicator::class, [
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
            ->tool(CreateMapIndicator::class, [
                'name' => 'TestMapIndicator',
                'title' => 'Test Title',
                'description' => 'Test Description',
                'data_source' => 'households',
            ]);

        $response->assertHasErrors(['Failed to create']);
    });
});
