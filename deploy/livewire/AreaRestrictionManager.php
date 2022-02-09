<?php

namespace App\Http\Livewire;

use App\Models\AreaRestriction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AreaRestrictionManager extends Component
{
    public User $user;
    public array $regions;
    public string $selectedRegion = '';
    public array$districts;
    public string $selectedDistrict = '';
    public array$sas;
    public string $selectedSa = '';
    public string $connection;

    private function addChecksumSafety($value) : string
    {
        return empty($value) ? '' : "*$value";
    }

    private function removeChecksumSafety($value) : string
    {
        return ltrim($value, '*');
    }

    private function getAreaList($connection, $areaType, $parentArea)
    {
        if (config('chimera.cache.enabled')) {
            return Cache::tags([$connection, 'area-list'])
                ->remember("$areaType-$parentArea", config('chimera.cache.ttl'), function () use ($connection, $areaType, $parentArea) {
                    //return DataSource::getAreaList($connection, $areaType, $parentArea);
                });
        }
        //return DataSource::getAreaList($connection, $areaType, $parentArea);
    }

    private function getRegions()
    {
        $areaList = $this->getAreaList($this->connection, 'region', null);
        $areaList = collect($areaList)->mapWithKeys(function ($name, $code) {
            return ["*$code" => $name];
        })->all();
        return $areaList;
    }

    private function getDistricts()
    {
        $areaList = $this->getAreaList($this->connection, 'district', $this->removeChecksumSafety($this->selectedRegion));
        $areaList = collect($areaList)->mapWithKeys(function ($name, $code) {
            return ["*$code" => $name];
        })->all();
        return $areaList;
    }

    private function getSas()
    {
        $areaList = $this->getAreaList($this->connection, 'sa', $this->removeChecksumSafety($this->selectedDistrict));
        $areaList = collect($areaList)->mapWithKeys(function ($name, $code) {
            return ["*$code" => $name];
        })->all();
        return $areaList;
    }

    public function apply()
    {
        AreaRestriction::updateOrCreate(
            [
                'user_id' => $this->user->id,
                'connection' => $this->connection,
            ],
            [
                'region_code' => $this->removeChecksumSafety($this->selectedRegion),
                'region_name' => $this->regions[$this->selectedRegion] ?? null,
                'district_code' => $this->removeChecksumSafety($this->selectedDistrict),
                'district_name' => $this->districts[$this->selectedDistrict] ?? null,
                'sa_code' => $this->removeChecksumSafety($this->selectedSa),
                'sa_name' => $this->sas[$this->selectedSa] ?? null,
            ]
        );
    }

    public function regionSelected($selected)
    {
        $this->selectedRegion = $selected;
        $this->selectedDistrict = '';
        $this->selectedSa = '';
        $this->districts = $this->getDistricts();
    }

    public function districtSelected($selected)
    {
        $this->selectedDistrict = $selected;
        $this->selectedSa = '';
    }

    public function saSelected($selected)
    {
        $this->selectedSa = $selected;
    }

    public function mount()
    {
        $areaRestriction = $this->user->areaRestrictions()->where('connection', $this->connection)->first();
        $this->selectedRegion = $this->addChecksumSafety($areaRestriction->region_code ?? '');
        $this->selectedDistrict = $this->addChecksumSafety($areaRestriction->district_code ?? '');
        $this->selectedSa = $this->addChecksumSafety($areaRestriction->sa_code ?? '');
    }

    public function render()
    {
        $this->regions = $this->getRegions();
        $this->districts = $this->getDistricts();
        $this->sas = $this->getSas();
        return view('livewire.area-restriction-manager');
    }
}
