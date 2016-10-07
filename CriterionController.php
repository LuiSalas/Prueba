<?php

namespace Intranet\Http\Controllers\API\Criterion;

use View;
use Dingo\Api\Routing\Helpers;
use Intranet\Models\CriterionLevel;

use Illuminate\Routing\Controller as BaseController;
use Intranet\Http\Services\Criterion\CriterionService;

class CriterionController extends BaseController
{
    use Helpers;

    public function getLevels($idCriterion)
    {
        $levels = CriterionLevel::where('IdCriterio', $idCriterion)->get();

        return $this->response->array($levels->toArray());
    }


    public function getById($criterionCode){
        $crit = [];
        try{
            $crit = $this->criterionService->getCriterionById($criterionCode);
        } catch(\Exception $e) {
            dd($e);
        }
        return json_encode($crit);
    }
}
