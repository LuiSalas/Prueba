<?php

namespace Intranet\Http\Controllers\API\Faculty;

use JWTAuth;
use Intranet\Models\Course;
use Intranet\Models\Cicle;
use Intranet\Models\Rubric;
use Intranet\Models\Aspect;
use Intranet\Models\Teacher;
use Illuminate\Http\Request;
use Intranet\Models\Faculty;
use Dingo\Api\Routing\Helpers;
use Intranet\Models\Suggestion;
use Intranet\Models\StudentsResult;
use Intranet\Models\ImprovementPlan;
use Intranet\Models\EvaluatedCourses;
use Intranet\Models\EducationalObjetive;
use Intranet\Http\Services\Course\CourseService;
use Intranet\Http\Services\Period\PeriodService;
use Intranet\Http\Services\Faculty\FacultyService;
use Illuminate\Routing\Controller as BaseController;
use Intranet\Http\Services\TimeTable\TimeTableService;
use Intranet\Models\Wrappers\EvaluatedPerformanceMatrixLine;
use Intranet\Http\Services\StudentsResult\StudentsResultService;
use Intranet\Models\Wrappers\EvaluatedPerformanceMatrixLineDetail;

class FacultyController extends BaseController
{
    use Helpers;

    public function __construct()
    {
        $this->facultyService= new FacultyService;
        $this->course_service = new CourseService;
        $this->period_service = new PeriodService;
        $this->studentResultsService = new StudentsResultService();
        $this->timeTableService = new TimeTableService();
    }

    public function get(Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $user = JWTAuth::parseToken()->authenticate();

        $faculties = collect();

        if ($user->isAdmin() || $user->isGeneralAcreditor())
          $faculties = Faculty::lastUpdated($date)->get();

        if ($user->isAcreditor())
          $faculties = Faculty::lastUpdated($date)->where('IdEspecialidad', $user->accreditor->IdEspecialidad)->get();

        $faculties->load('coordinator');
        return $this->response->array($faculties->toArray());
    }

    public function getEducationalObjectives($faculty_id, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $objectives = EducationalObjetive::lastUpdated($date)
                                           ->with('studentsResults')
                                           ->where('idEspecialidad', $faculty_id)
                                           ->get();

        return $this->response->array($objectives->toArray());
    }

    public function getStudentsResult($faculty_id, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $results = StudentsResult::lastUpdated($date)
                                   ->with('aspects')
                                   ->with(['educationalObjectives' => function($q) {
                                        return $q->whereNotNull('ResultadoxObjetivo.deleted_at');
                                   }])
                                   ->where('idEspecialidad', $faculty_id)
                                   ->get();

        return $this->response->array($results->toArray());
    }

    public function getEvaluatedCourses($faculty_id, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));

        $academic_semester = Cicle::where('Vigente', 1)->first();

        $courses = Course::lastUpdated($date)
                           ->where('IdEspecialidad', $faculty_id)
                           ->orderBy('NivelAcademico', 'asc')
                           ->orderBy('IdCurso', 'asc')
                           ->with('semesters')
                           ->whereHas('semesters', function($query) use ($academic_semester) {
                              $query->where('CursoxCiclo.IdCicloAcademico', $academic_semester->IdCicloAcademico);
                              $query->whereNull('CursoxCiclo.deleted_at');
                           })
                           ->get();

        return $this->response->array($courses->toArray());
    }

    public function getAspects($faculty_id, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $aspects = Aspect::lastUpdated($date)
                           ->whereHas('studentsResult', function($query) use ($faculty_id) {
                              $query->where('IdEspecialidad', $faculty_id);
                           })->with('criterion')
                           ->get();

        return $this->response->array($aspects->toArray());
    }

    public function getTeachers($faculty_id, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $teachers = Teacher::lastUpdated($date)->where('IdEspecialidad', $faculty_id)->get();

        return $this->response->array($teachers->toArray());
    }

    public function getSuggestions($faculty_id, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $suggestions = Suggestion::lastUpdated($date)
                                 ->with('teacher', 'improvementPlan')
                                 ->where('IdEspecialidad', $faculty_id)
                                 ->get();

        return $this->response->array($suggestions->toArray());
    }

    public function getImprovementsPlans($faculty_id, Request $request)
    {
        $date = date('Y-m-d H:i:s', $request->get('since', 0));
        $improvement_plans = ImprovementPlan::lastUpdated($date)
                                            ->where('IdEspecialidad', $faculty_id)
                                            ->with('typeImprovementPlan', 'teacher', 'actions', 'file')
                                            ->get();

        return $this->response->array($improvement_plans->toArray());
    }

    public function getCourseReport($faculty_id, $course_id, $semester_id)
    {
        $data['course'] = $this->course_service->findCourseById($course_id);
        $data['schedules'] = $schedules = $this->course_service
                          ->findSchedules($course_id, $semester_id)
                          ->map(function($schedule){
                                $schedule->numEvaluated = $this->course_service
                                                               ->numEvaluatedBySchedule($schedule->IdHorario);
                            return $schedule;
                          });

        $conf_period = $this->period_service->getBySemester($semester_id);
        $data['studentResults'] = $this->course_service
                                       ->findStudentResultsInputByCourse($course_id, $semester_id)
                                       ->map(function($sr) use($schedules, $conf_period) {
                                            $sr->performanceMatrix = $this->course_service->makePerformanceMatrix($sr->studentsResult->criterions, $schedules, $conf_period->NivelEsperado);
                                            return $sr;
                                       });

        return view('studyPlan.api', $data);
    }

    public function getMeasureReport($faculty_id)
    {
        $cycle = $this->facultyService->findCycle($faculty_id);
        if (is_null($cycle)) return '';

        $studentResults = $this->studentResultsService->findResultEvaluated($cycle);
        $conf = $this->facultyService->findConfFaculty($faculty_id, $cycle->IdPeriodo);
        $data['inYoRush'] = $cyclesInBetween = $this->period_service->findAllCyclesInBetweenPeriod(
        $conf->cycleAcademicStart->Descripcion, $conf->cycleAcademicEnd->Descripcion);

        $data['matrixLines'] = [];

        if($studentResults!=null){
            foreach($studentResults as $studentResult){
                $evaluatedPerformanceMatrixLine = new EvaluatedPerformanceMatrixLine();
                $evaluatedPerformanceMatrixLine ->studentResult = $studentResult;

                $acumulatedAspectGrade = 0;
                $aspectIterations = 0;
                //sacamos los aspectos de los resultados estudiantiles
                if($studentResult->aspects !=null){
                    foreach ($studentResult->aspects as $aspect){
                        $acumulatedCriterionGrade = 0;
                        $iterations = 0;
                        $evaluatedPerformanceMatrixLineDetail = []; //For the details of such aspects

                        foreach ($aspect->criterion as $criterion){
                            $scores = $this->timeTableService->findScoresByCriterion($criterion->IdCriterio);
                            $countPassed = 0;
                            $total = 0;
                            //luego sacamos los alumnos x criterio
                            foreach ($scores as $score){
                                //finalmente vemos cuales pasaron y cuales no y calculamos el porcentaje de cada criterio
                                if($conf->NivelEsperado <= $score->Nota){
                                    $countPassed++;
                                }
                                $total++;
                            }
                            $criteria = new EvaluatedPerformanceMatrixLineDetail();
                            array_push($criteria->criterions, $criterion);
                            if($total>0){array_push($criteria->criterionsRating, $countPassed/ $total);}
                            array_push($evaluatedPerformanceMatrixLineDetail, $criteria);

                            if($total>0){$acumulatedCriterionGrade += $countPassed/ $total;}
                            $iterations++;
                        }

                        $acumulatedAspectGrade = $acumulatedCriterionGrade / $iterations;
                        $aspectIterations ++;
                        array_push($evaluatedPerformanceMatrixLine->aspects, $aspect);
                        if($iterations>0){array_push($evaluatedPerformanceMatrixLine->aspectsRating, $acumulatedCriterionGrade / $iterations);}
                        array_push($evaluatedPerformanceMatrixLine->aspectsDetail, $evaluatedPerformanceMatrixLineDetail);
                    }
                }
                $evaluatedPerformanceMatrixLine->studentResult =$studentResult;
                if($aspectIterations>0){$evaluatedPerformanceMatrixLine->studentResultRating = $acumulatedAspectGrade /$aspectIterations;}
                //Aplicamos regla de 3 simple para los resultados estudiantiles
                array_push($data['matrixLines'], $evaluatedPerformanceMatrixLine );
            }
        }

      return view('consolidated.results.api', $data);
    }

}
