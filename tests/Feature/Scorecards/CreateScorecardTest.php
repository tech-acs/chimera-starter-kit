<?php

use App\Actions\Maker\CreateArtefactAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Symfony\Component\Console\Command\Command;
use Uneca\Chimera\DTOs\ScorecardAttributes;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\CreateScorecard;
use Uneca\Chimera\Models\Scorecard;
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

function fakeScorecardModel(array $attributes = []): Model
{
    $model = new class extends Model {
        protected $table = 'scorecards';
        public $timestamps = false;
    };
    $model->forceFill($attributes + ['id' => 1, 'name' => 'TestScorecard']);

    return $model;
}

// ---------------------------------------------------------------------------
// 1. CreateArtefactAction — unit/integration tests

describe('CreateArtefactAction (scorecard)', function () {
    beforeEach(function () {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug');
            $table->text('title')->nullable();
            $table->string('data_source')->nullable();
            $table->boolean('published')->default(false);
            $table->string('scope')->nullable();
            $table->timestamps();
        });
    });

    afterEach(function () {
        Schema::dropIfExists('scorecards');
        Schema::dropIfExists('permissions');
    });

    it('creates scorecard and returns success result', function () {
        $kernelMock = Mockery::mock(Illuminate\Contracts\Console\Kernel::class);
        $kernelMock->shouldReceive('call')
            ->once()
            ->with('chimera:make-artefact', Mockery::on(function (array $params) {
                return $params['name'] === 'TestScorecard'
                    && $params['--namespace'] === 'Livewire\Scorecard';
            }))
            ->andReturn(Command::SUCCESS);
        Artisan::swap($kernelMock);

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: Scorecard::class, baseNamespace: 'Livewire\Scorecard', attributes: new ScorecardAttributes(
            name: 'TestScorecard',
            title: 'Test Title',
            dataSource: 'households',
            stub: resource_path('stubs/scorecards/default.stub'),
        ));

        expect($result->success)->toBeTrue();
        expect($result->artefact->id)->toBe(1);
        expect($result->filePath)->toContain('TestScorecard.php');
    });

    it('returns failed result when file creation fails', function () {
        $kernelMock = Mockery::mock(Illuminate\Contracts\Console\Kernel::class);
        $kernelMock->shouldReceive('call')
            ->once()
            ->andReturn(Command::FAILURE);
        Artisan::swap($kernelMock);

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: Scorecard::class, baseNamespace: 'Livewire\Scorecard', attributes: new ScorecardAttributes(
            name: 'FailingScorecard',
            title: 'Test Title',
            dataSource: 'households',
            stub: resource_path('stubs/scorecards/default.stub'),
        ));

        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toContain('problem creating the class file');
    });

    it('returns failed result on exception', function () {
        Schema::dropIfExists('scorecards');

        $action = app(CreateArtefactAction::class);
        $result = $action->execute(modelClass: Scorecard::class, baseNamespace: 'Livewire\Scorecard', attributes: new ScorecardAttributes(
            name: 'FailingScorecard',
            title: 'Test Title',
            dataSource: 'households',
            stub: resource_path('stubs/scorecards/default.stub'),
        ));

        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toBeString();
    });
});

// ---------------------------------------------------------------------------
// 4. Web Path — controller integration tests
// ---------------------------------------------------------------------------

describe('Web Path', function () {
    beforeEach(function () {
        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('scorecards');
    });

    it('creates scorecard via http post and redirects with success', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeScorecardModel(),
                app_path('Livewire/Scorecard/TestScorecard.php'),
            ));

        $response = $this
            ->withoutMiddleware()
            ->post(route('developer.scorecard.store'), [
                'name' => 'TestScorecard',
                'title' => 'Test Title',
                'data_source' => 'households',
            ]);

        $response->assertRedirect(route('scorecard.index'));
        $response->assertSessionHas('message', 'Scorecard created');
    });

    it('returns validation errors for invalid input', function () {
        $response = $this
            ->withoutMiddleware()
            ->post(route('developer.scorecard.store'), [
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
            ->post(route('developer.scorecard.store'), [
                'name' => 'TestScorecard',
                'title' => 'Test Title',
                'data_source' => 'households',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('errors');
    });
});

// ---------------------------------------------------------------------------
// 5. CLI Path — command integration tests
// ---------------------------------------------------------------------------

describe('CLI Path', function () {
    beforeEach(function () {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

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

        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('scorecards');
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('permissions');
    });

    it('creates scorecard and returns success exit code', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeScorecardModel(),
                app_path('Livewire/Scorecard/TestScorecard.php'),
            ));

        $this->artisan('chimera:make-scorecard')
            ->expectsChoice(
                'Which data source will this scorecard be using?',
                'households',
                ['households' => 'Households'],
            )
            ->expectsQuestion('Scorecard name', 'TestScorecard')
            ->expectsQuestion('Please enter a reader friendly title for the scorecard', 'Test Title')
            ->assertSuccessful();
    });

    it('returns failure exit code when action fails', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::failed('File creation failed'));

        $this->artisan('chimera:make-scorecard')
            ->expectsChoice(
                'Which data source will this scorecard be using?',
                'households',
                ['households' => 'Households'],
            )
            ->expectsQuestion('Scorecard name', 'TestScorecard')
            ->expectsQuestion('Please enter a reader friendly title for the scorecard', 'Test Title')
            ->assertFailed();
    });
});

// ---------------------------------------------------------------------------
// 6. MCP Path — tool integration tests
// ---------------------------------------------------------------------------

describe('MCP Path', function () {
    beforeEach(function () {
        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $this->mockAction = Mockery::mock(CreateArtefactAction::class);
        $this->app->instance(CreateArtefactAction::class, $this->mockAction);
    });

    afterEach(function () {
        Schema::dropIfExists('scorecards');
    });

    it('creates scorecard and returns success response', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(ArtefactCreationResult::success(
                fakeScorecardModel(),
                app_path('Livewire/Scorecard/TestScorecard.php'),
            ));

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(CreateScorecard::class, [
                'name' => 'TestScorecard',
                'title' => 'Test Title',
                'data_source' => 'households',
            ]);

        $response->assertOk();
        $response->assertSee('Scorecard created successfully');
    });

    it('rejects invalid parameters with error response', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(CreateScorecard::class, [
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
            ->tool(CreateScorecard::class, [
                'name' => 'TestScorecard',
                'title' => 'Test Title',
                'data_source' => 'households',
            ]);

        $response->assertHasErrors(['Failed to create']);
    });
});
