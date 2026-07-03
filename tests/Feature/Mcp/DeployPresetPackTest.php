<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Actions\Maker\CreateArtefactAction;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\DeployPresetPack;
use Uneca\Chimera\Results\ArtefactCreationResult;

function fakeArtefactModel(string $name): \Illuminate\Database\Eloquent\Model
{
    $model = new class extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'scorecards';
        public $timestamps = false;
    };
    $model->forceFill([
        'id' => 1,
        'name' => 'Households/' . $name,
    ]);

    return $model;
}

describe('DeployPresetPack tool', function () {
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

        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('indicators', function (Blueprint $table) {
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
        Schema::dropIfExists('data_sources');
        Schema::dropIfExists('scorecards');
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('permissions');
    });

    it('deploys all artefacts in a pack', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->andReturn(ArtefactCreationResult::success(
                fakeArtefactModel('Artefact'),
                '/app/Livewire/Indicator/Households/Artefact.php',
            ));

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(DeployPresetPack::class, [
                'pack' => 'census-enumeration',
                'data_source' => 'households',
            ]);

        $response->assertOk();
        $response->assertSee('Deployment Report');
        $response->assertSee('census-enumeration');
        $response->assertSee('households');
        $response->assertSee('deployed');
        $response->assertSee('0 failed');
    });

    it('deploys only included artefacts when specified', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->andReturn(ArtefactCreationResult::success(
                fakeArtefactModel('EstimatedDaysToCompleteEnumeration'),
                '/app/Livewire/Scorecard/Households/EstimatedDaysToCompleteEnumeration.php',
            ));

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(DeployPresetPack::class, [
                'pack' => 'census-enumeration',
                'data_source' => 'households',
                'include' => ['EstimatedDaysToCompleteEnumeration'],
            ]);

        $response->assertOk();
        $response->assertSee('deployed');
        $response->assertSee('0 failed');
    });

    it('returns error for unknown pack', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(DeployPresetPack::class, [
                'pack' => 'nonexistent',
                'data_source' => 'households',
            ]);

        $response->assertHasErrors(['not found']);
    });

    it('returns error when data_source is missing', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(DeployPresetPack::class, [
                'pack' => 'census-enumeration',
            ]);

        $response->assertHasErrors(["'data_source' parameter is required"]);
    });

    it('returns error for unknown data source', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(DeployPresetPack::class, [
                'pack' => 'census-enumeration',
                'data_source' => 'nonexistent',
            ]);

        $response->assertHasErrors(["Data source 'nonexistent' not found"]);
    });

    it('reports failures when action fails', function () {
        $this->mockAction
            ->shouldReceive('execute')
            ->andReturnUsing(function () {
                static $count = 0;
                $count++;
                return match ($count) {
                    1 => ArtefactCreationResult::success(
                        fakeArtefactModel('Artefact'),
                        '/app/Livewire/Indicator/Households/Artefact.php',
                    ),
                    2 => ArtefactCreationResult::failed('Name already exists'),
                    default => ArtefactCreationResult::success(
                        fakeArtefactModel('Artefact'),
                        '/app/Livewire/Indicator/Households/Artefact.php',
                    ),
                };
            });

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(DeployPresetPack::class, [
                'pack' => 'census-enumeration',
                'data_source' => 'households',
            ]);

        $response->assertOk();
        $response->assertSee('failed');
        $response->assertSee('Name already exists');
    });
});
