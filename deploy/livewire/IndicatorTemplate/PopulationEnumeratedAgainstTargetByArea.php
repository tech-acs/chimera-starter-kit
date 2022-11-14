<?php 
namespace App\Http\Livewire\IndicatorTemplate;
use App\Http\Livewire\Chart;
use App\Services\Interfaces\BarChart;
use Carbon\Carbon;


/**
 * Population enumerated by area
 * A BarChart showing the population enumerated by area, with a line chart showing the target population to be enumerated.
 *
 * @category BarChart
 */
abstract class PopulationEnumeratedAgainstTargetByArea extends Chart implements BarChart
{
    
    const POPULATION_DATA_KEY =[ 'area_code', 'population'];
    /**
     * @return \Illuminate\Support\Collection 
     * $data = [
     *     [
     *          'area_name' => 'EAS 1',
     *          'area_code' => 'EAS 1',
     * 
     *          'population' => 100,
     *    ],
     *   [
     * 
     *         'area_name' => 'EAS 2',
     *        'population' => 200,
     *   ],
     * ];
     */
    protected abstract function getPopulationCountedData(array $filter = []);

    protected abstract function getExpectedValues(array $filter = []);

    protected function mergeAreasWithResults($areas, $result, $target = null)
    {
        $areas  = $areas->map(function ($row) use ($result, $target) {
            $resultRow = $result->first(function($item) use ($row){      
                return $item['area_code'] == $row['area_code'];
            });
            if(isset($resultRow)){
                $row =array_merge((array)$row,  (array)$resultRow);
                //remove area_code and area_name b/c they are duplicates and create confusion while data exporting
                $row = (object) array_diff_key((array)$row, ['area_code'=>1, 'area_name'=>1]);
            } 

            if($target != null){
                $resultRow = $target->first(function($item) use ($row){
                    return $item['area_code'] == $row['area_code'];
                });

                if(isset($resultRow)){
                    
                    $row =(object) array_merge((array) $row, ['target'=>$resultRow->target_value]);
                } else {
                    $row =(object) array_merge((array) $row, ['target'=>0]);
                }
            }

            return $row;
        });
        return $areas;
    }
    
    protected function getExercisePeriod()
    {
        return [
            'start_date' => Carbon::parse('2020-01-01'),
            'end_date' => Carbon::parse('2020-01-31'),
        ];
    }

    protected function getData(array $filter = []): array
    {
        $result = $this->getPopulationCountedData($filter);
      
        $areas = $this->getExpectedValues($filter);

        $result = $this->mergeAreasWithResults($areas, $result);
      
        $this->threshold=collect([
            ['green' => 100,'amber' => 90]
        ]);
        
        $now = Carbon::now();
        $dates = $this->getExercisePeriod();
        $start_date= Carbon::parse($dates['start_date'])->subDays(1);
        $end_date= Carbon::parse($dates['end_date']);
      
        $total_Enum_days=$start_date->diffInDays($end_date,false); //4 days        
        $days_Since_enum_start=$start_date->diffInDays($now,false);
        
        if($days_Since_enum_start > $total_Enum_days) {
            $days_Since_enum_start=$total_Enum_days;            
        }
        
        $result = $result->map(function ($row) use ($days_Since_enum_start,$total_Enum_days) {
            $row->bar_width=0.7;
            if(!isset($row->total)){
                $row->total = 0;
            } 
            if(!isset($row->target)){
                $row->target = 0;
            }


           if(!$row->target == null && $days_Since_enum_start > 0) {
                $row->expected = ($row->target *$days_Since_enum_start/$total_Enum_days);
           }
           else{
                $row->expected = 0;
           }

            return $row;
        });
  
        $grandTotal_target  =0;
        $grandTotal_population=0;

        $aggregate_label = 'Total';
        foreach($result as $row)
         {
            $grandTotal_population += $row->total;             
            $grandTotal_target += $row->target;

         }

        $aggregated=collect([
            [   'aggregate' => $grandTotal_population , 
                'expected' => ($grandTotal_target *$days_Since_enum_start/$total_Enum_days),
                'bar_width' => 0.7,
                'code' => '',
                'name' =>  $aggregate_label]
        ]);
        
        $traceTodayTargetAgrg = array_merge(
            $this::BarTraceTemplate,
            [
                'x' => $aggregated->pluck('name')->all(),
                'y' => $aggregated->pluck('expected')->all(),
                'name' => __( "Aggregate expected"),
                'marker' => ['color' => '#c5c5c5','opacity'=>0.7],
                'showlegend' => false,

            ]
        );

        $traceActualAgrg = array_merge(
            $this::BarTraceTemplate,
            [
                'x' => $aggregated->pluck('name')->all(),
                'y' => $aggregated->pluck('aggregate')->all(),
                'width' => $aggregated->pluck('bar_width')->all(),
                'name' => __("Aggregate"),
                'marker' => ['color' => '#c99c25'],
            ]
        );
        
        $traceTodayTarget = array_merge(
            $this::BarTraceTemplate,
            [
                'x' =>$result->pluck('name')->all(),
                'y' => $result->pluck('expected')->all(),
                'name' => __("Today's target"),
                'marker' => ['color' => '#c5c5c5','opacity'=>0.4],
            ]
        );

        $traceActual = array_merge(
            $this::BarTraceTemplate,
            [
                'x' => $result->pluck('name')->all(),
                'y' => $result->pluck('total')->all(),
                'width' => $result->pluck('bar_width')->all(),
                'name' => __('Actual'),
                'marker' => ['color' => '#1e3b87'],
            ]
        );

        return [$traceTodayTarget, $traceActual,$traceTodayTargetAgrg, $traceActualAgrg];
    }

    protected function getLayout(array $filter = []): array
    {
        $layout = parent::getLayout($filter);
        $layout['xaxis']['title']['text'] = "Area Name";
        $layout['xaxis']['type'] = 'text';
        $layout['yaxis']['title']['text'] = "Population";
        return $layout;
    }
}