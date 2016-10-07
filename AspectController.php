<?php

namespace Intranet\Http\Controllers\API\Aspect;

use Illuminate\Http\Request;
use Intranet\Models\Criterion;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller as BaseController;

class AspectController extends BaseController
{
    use Helpers;

    public function getCriterions($aspectId, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $criterions = Criterion::lastUpdated($date)->where('IdAspecto' ,$aspectId)->get();

        return $this->response->array($criterions->toArray());
    }
}
