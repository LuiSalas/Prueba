<?php

namespace Intranet\Http\Controllers\API\Period;

use Intranet\Models\Period;
use Dingo\Api\Routing\Helpers;
use Intranet\Models\AcademicCycle;
use Illuminate\Routing\Controller as BaseController;

class PeriodController extends BaseController
{
    use Helpers;

    public function getSemesters($faculty_id)
    {
        $period = Period::where('Vigente', 1)->where('IdEspecialidad', $faculty_id)->with('configuration')->first();

        if(is_null($period)) return response()->json($period);

        $init_semester = $period->configuration->cycleAcademicStart->Numero;
        $end_semester = $period->configuration->cycleAcademicEnd->Numero;
        $period->semesters = AcademicCycle::where('Numero', '>=', $init_semester)
                                          ->where('Numero', '<=', $end_semester)
                                          ->orderBy('Numero', 'asc')
                                          ->get();

        return response()->json($period);
    }
}