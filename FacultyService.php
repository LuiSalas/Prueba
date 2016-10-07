<?php namespace Intranet\Http\Services\Faculty;

use DB;
use Session;
use Intranet\Models\Cicle;
use Intranet\Models\Period;
use Intranet\Models\Faculty;
use Intranet\Models\Teacher;
use Intranet\Models\CycleAcademic;
use Intranet\Models\ConfFaculty;
use Intranet\Models\StudentsResult;
use Intranet\Models\EducationalObjetive;
use Intranet\Models\Aspect;
use Intranet\Models\Criterion;
use Intranet\Models\CyclexResult;
use Intranet\Models\PeriodxResult;
use Intranet\Models\User;
use Intranet\Models\FacultyxCycle;

class FacultyService {

	public function retrieveAll() {
		return Faculty::get();
	}

	public function getAllCycleAcademics() {
		return CycleAcademic::get();
	}

	public function allResults() {
		return StudentsResult::where('IdEspecialidad', Session::get('faculty-code'))->get();
	}

	public function allResultsInPeriod() {
		return PeriodxResult::where('IdPeriodo', Session::get('period-code'))->get();
	}

	public function findResultsxCycle($id) {
		return CyclexResult::where('IdCicloAcademico', $id)->orderby('IdResultadoEstudiantil','ASC')->get();
	}

	public function AllCycleAcademic() {
		//Vigente = 0: Es cuando el ciclo se creó, pero no ha sido iniciado
		//Vigente = 1: Es cuando el ciclo está activo
		//Vigente = 2: Es cuando el ciclo ha estado activo y se desactivó
		/*
		$ciclesAcademics = DB::table('CicloAcademico')
            				->join('CicloxEspecialidad', 'CicloxEspecialidad.IdCiclo', '<>', 'CicloAcademico.IdCicloAcademico')
            				->select('CicloAcademico.*')
            				->where('CicloAcademico.IdCicloAcademico','<>','CicloxEspecialidad.IdCiclo')
            				->where('CicloxEspecialidad.Vigente','=', 2)
            				->get();

        $cyclesxperiod = DB::table('ConfEspecialidad')->where('IdPeriodo', Session::get('period-code'))->first();
        $cycleIni = DB::table('CicloAcademico')->where('IdCicloAcademico', $cyclesxperiod->IdCicloInicio)->first();
        $cycleFin = DB::table('CicloAcademico')->where('IdCicloAcademico', $cyclesxperiod->IdCicloFin)->first();
*/
        $ciclesAcademics =  DB::table('CicloAcademico')
        					->orderby('Descripcion','ASC')
							->whereNotIn('IdCicloAcademico', DB::table('CicloxEspecialidad')->select('CicloxEspecialidad.IdCiclo')->where('CicloxEspecialidad.Vigente','=', 2)->where('CicloxEspecialidad.IdEspecialidad','=',Session::get('faculty-code')))
							//->whereBetween('Descripcion',array($cycleIni->Descripcion,$cycleFin->Descripcion))
							/*
							->whereBetween('Descripcion', array(DB::table('ConfEspecialidad')->select('ConfEspecialidad.IdCicloInicio')->where('ConfEspecialidad.IdPeriodo', Session::get('period-code')), DB::table('ConfEspecialidad')->select('ConfEspecialidad.IdCicloFin')->where('ConfEspecialidad.IdPeriodo',Session::get('period-code'))))*/
							->get();


		return $ciclesAcademics;

	}

	public function AllCyclesAcademics() {
		if(Session::get('period-code')!=null){
			$cyclesxperiod = DB::table('ConfEspecialidad')->where('IdPeriodo', Session::get('period-code'))->first();
	        $cycleIni = DB::table('CicloAcademico')->where('IdCicloAcademico', $cyclesxperiod->IdCicloInicio)->first();
	        $cycleFin = DB::table('CicloAcademico')->where('IdCicloAcademico', $cyclesxperiod->IdCicloFin)->first();

	        $ciclesAcademics =  DB::table('CicloAcademico')
	        					->orderby('Descripcion','ASC')
								->whereNotIn('IdCicloAcademico', DB::table('CicloxEspecialidad')->select('CicloxEspecialidad.IdCiclo')->where('CicloxEspecialidad.Vigente','=', 2)->where('CicloxEspecialidad.IdEspecialidad','=',Session::get('faculty-code')))
								->whereBetween('Descripcion',array($cycleIni->Descripcion,$cycleFin->Descripcion))
								->get();

			return $ciclesAcademics;
		}else{
			return null;
		}
	}

	public function create($request) {
		$faculty = Faculty::create([
			'Nombre' => $request['name']	,
			'Descripcion' => $request['description'],
			'Codigo' => $request['code']
		]);

	}

	public function getCode($codigo) {
		$faculty = Faculty::where('Codigo', $codigo)->first();
		$retVal = (is_null($faculty)) ? true : false ;
		$data['faculty'] = $retVal;
		if($retVal!=true){
			$data['info'] = $faculty;
		}else{
			$data['info'] = null;
		}

		return $data;
	}


	public function getId($codigo) {
		$faculty = Faculty::where('IdEspecialidad', $codigo)->first();
		return $faculty;
	}

	public function getName($nombre) {
		$faculty = Faculty::where('Nombre', $nombre)->first();
		$retVal = (is_null($faculty)) ? True : False ;
		$data['faculty'] = $retVal;
		if($retVal!=true){
			$data['info'] = $faculty;
		}else{
			$data['info'] = null;
		}
		return $data;
	}

	public function updateCoordinator($request) {

		if($request['coordinator'] == 0){

			$specialty = Faculty::where('IdEspecialidad',$request['facultyId'])->first();
			if($specialty->teacher!=null){

				$coordinatorC = $specialty->teacher;
				$userC = User::where('IdUsuario', $coordinatorC->IdUsuario)
						->update(array('IdPerfil' => 2
				));
			}

			$faculty = Faculty::where('IdEspecialidad', $request['facultyId'])
						->update(array(	'Descripcion' => $request['description'],
										'IdDocente' => null
			));



		}else{
			$specialty = Faculty::where('IdEspecialidad',$request['facultyId'])->first();
			//Perfil Profesor
			if($specialty->teacher!=null){
				$coordinatorA = $specialty->teacher;
				$userA = User::where('IdUsuario', $coordinatorA->IdUsuario)
						->update(array('IdPerfil' => 2
				));
			}

			$coordinatorN = Teacher::where('IdDocente',$request['coordinator'])->first();

			$faculty = Faculty::where('IdEspecialidad', $request['facultyId'])
						->update(array(	'Descripcion' => $request['description'],
										'IdDocente' => $request['coordinator']
			));
			//Perfil Coordinador
			$userA = User::where('IdUsuario', $coordinatorN->IdUsuario)
						 ->update(array('IdPerfil' => 1
			));
		}
	}

	public function update($request) {
		if($request['coordinator'] == 0){

			$specialty = Faculty::where('IdEspecialidad',$request['facultyId'])->first();
			if($specialty->teacher!=null){

				$coordinatorC = $specialty->teacher;
				$userC = User::where('IdUsuario', $coordinatorC->IdUsuario)
						->update(array('IdPerfil' => 2
				));
			}

			$faculty = Faculty::where('IdEspecialidad', $request['facultyId'])
			->update(array(	'Codigo' => $request['code'],
							'Nombre' => $request['name'],
							'Descripcion' => $request['description'],
							'IdDocente' => null
			));
		}else{
			$specialty = Faculty::where('IdEspecialidad',$request['facultyId'])->first();
			//Perfil Profesor
			if($specialty->teacher!=null){
				$coordinatorA = $specialty->teacher;
				$userA = User::where('IdUsuario', $coordinatorA->IdUsuario)
						->update(array('IdPerfil' => 2
				));
			}

			$coordinatorN = Teacher::where('IdDocente',$request['coordinator'])->first();

			$faculty = Faculty::where('IdEspecialidad', $request['facultyId'])
			//->where('deleted_at', null)
			->update(array(	'Codigo' => $request['code'],
							'Nombre' => $request['name'],
							'Descripcion' => $request['description'],
							'IdDocente' => $request['coordinator']
			));

			//Perfil Coordinador
			$userA = User::where('IdUsuario', $coordinatorN->IdUsuario)
						 ->update(array('IdPerfil' => 1
			));
		}
	}

	public function delete($id) {

		$faculty = Faculty::find($id);

        $activeCycle = $faculty->cycle;
        
        if($activeCycle && $activeCycle->Vigente == 1){
           return null;
        }

		$faculty->delete();

		return 1;
	}

	public function find($id) {
		$faculty = Faculty::where('IdEspecialidad', $id)->first();
		return $faculty;
	}


    public function getTeacher() {
		$teachers = Teacher::get();
		return $teachers;
	}

	public function teachers($request) {
		$teachers = Teacher::where('IdEspecialidad', $request['facultyId'])->get();
		return $teachers;
	}


	public function getTeacherById($id) {
		$teacher = Teacher::where('IdDocente', $id)->first();
		return $teacher;
	}

	public function retrieveAllAcademicCycle() {
		return ConfFaculty::where('IdEspecialidad',Session::get('faculty-code'))->get();
	}

	public function createAcademicCycle($request,$id) {

		$facultyAcademicCycle = ConfFaculty::create([
			'UmbralAceptacion' => $request['facultyAgreement']	,
			'IdCicloAcademico' =>  1	,
			'NivelEsperado' => $request['facultyAgreementLevel']	,
			'CantNivelCriterio' => $request['criteriaLevel']	,
			'IdDocente' => $_POST['facultyCoordinator']	,
			'IdEspecialidad' =>  $id
		]);

	}

	public function createCycle($request) {
		$s = $request['cycleDateI'];
		$submitdate = str_replace( "/" , "-" , $s);
		$time = strtotime($submitdate);
		$dateI = date('Y-m-d',$time);



		$s1 = $request['cycleDateF'];
		$submitdate1 = str_replace( "/" , "-" , $s1);
		$time1 = strtotime($submitdate1);

		$dateF = date('Y-m-d',$time1);

		$periodo = Period::where('Vigente',1)->first();
		$confEspecialidad = ConfFaculty::where('IdPeriodo', $periodo->IdPeriodo)->first();

		if($request['cycleId'] >= $confEspecialidad->IdCicloInicio && $request['cycleId']<= $confEspecialidad->IdCicloFin ){

			$cycleA = Cicle::create(['IdEspecialidad' =>Session::get('faculty-code'),
					'IdCiclo' => $request['cycleId'],
					'Vigente' =>'0',
					'IdPeriodo' =>$periodo->IdPeriodo,
					'FechaInicio'=>$dateI,
					'FechaFin'=>$dateF

			]);
		}
		else{
			$cycleA = Cicle::create(['IdEspecialidad' =>Session::get('faculty-code'),
					'IdCiclo' => $request['cycleId'],
					'Vigente' =>'0',
					'FechaInicio'=>$dateI,
					'FechaFin'=>$dateF
			]);
		}

		for($i=1;$i<count($request['check']);$i++) {
			$cyclexresults = CyclexResult::create([
						'IdResultadoEstudiantil' => $request['check'][$i],
						'IdCicloAcademico' => $cycleA->IdCicloAcademico

			]);
		}
	}

	public function activateCycle($request) {
		$activate = Cicle::where('IdEspecialidad', Session::get('faculty-code'))->orderby('IdCicloAcademico','DESC')->take(1)
			->update(array(
					'Vigente' =>'1'
		));

	}

	public function desactivateCycle($request) {
		$ciclo = Cicle::where('IdEspecialidad', Session::get('faculty-code'))->where('Vigente', 1)->first();

		$activate = Cicle::where('IdEspecialidad', Session::get('faculty-code'))->where('Vigente', 1)->first()
			->update(array(
					'Vigente' =>'2'
		));

		//Desactivando Periodo,si el ciclo que se cierra es el último del periodo.
		$periodo = Period::where('Vigente',1)->first();
		if($periodo != null){
			$confEspecialidad = ConfFaculty::where('IdPeriodo', $periodo->IdPeriodo)->first();

			if($ciclo->academicCycle->IdCicloAcademico == $confEspecialidad->IdCicloFin){
				$desactivate = Period::where('IdEspecialidad', Session::get('faculty-code'))->where('Vigente', 1)->first()
				->update(array(
					'Vigente' =>'0'
				));
			}
		}
	}

	public function desactivatePeriod($period_id, $faculty_id) {
		$activate = Period::where('IdPeriodo', $period_id)->where('IdEspecialidad', $faculty_id)->where('Vigente', 1)->first()
			->update(array(
					'Vigente' =>'0'
		));

		//Desactivando Ciclo, si es que hay un ciclo vigente
		if(Session::get('academic-cycle')!=null){
			$ciclo = Cicle::where('IdEspecialidad', Session::get('faculty-code'))->where('Vigente', 1)->first();

			$activate = Cicle::where('IdEspecialidad', Session::get('faculty-code'))->where('Vigente', 1)->first()
				->update(array(
						'Vigente' =>'2'
			));
		}


	}

	public function createConfFaculty($request) {

		DB::table('Periodo')->where('IdEspecialidad', Session::get('faculty-code'))
							->where('Vigente', 1)
							->update(['Vigente' => 0]);

		$period = Period::create(['IdEspecialidad' =>Session::get('faculty-code'),
					'Vigente'=>'1'
			]);



	    $confFaculty = ConfFaculty::create(['NivelEsperado' => $request['facultyAgreementLevel'],
					'UmbralAceptacion' => $request['facultyAgreement'],
					'CantNivelCriterio' => $request['criteriaLevel']	,
					'IdCicloFin' => $request['cycleEnd']	,
					'IdCicloInicio' => $request['cycleStart']	,
					'IdEspecialidad' =>  Session::get('faculty-code'),
					'IdPeriodo' => $period->IdPeriodo
			]);

		$measures = (array_key_exists('measures', $request))?$request['measures']: [];
		$period->measures()->sync($measures);

		$educationalObjetives = (array_key_exists('objCheck', $request))?$request['objCheck']: [];
		$studentsResults = (array_key_exists('stRstCheck', $request))?$request['stRstCheck']: [];
		$aspects = (array_key_exists('aspCheck', $request))?$request['aspCheck']: [];
		$criterions = (array_key_exists('crtCheck', $request))?$request['crtCheck']: [];

		$period->educationalObjetives()->sync($educationalObjetives);
		$period->studentsResults()->sync($studentsResults);
		$period->aspects()->sync($aspects);
		$period->criterions()->sync($criterions);

		$educationalObjetivesAll = EducationalObjetive::where('IdEspecialidad', Session::get('faculty-code'))
			->where('deleted_at', null)->get();

		foreach ($educationalObjetivesAll as $obj) {
			$statusObj = 0;
			if (in_array($obj->IdObjetivoEducacional, $educationalObjetives)) {
				$statusObj = 1;
			}
			EducationalObjetive::where('IdObjetivoEducacional', $obj->IdObjetivoEducacional)
				->update(array('Estado' => $statusObj,));
		}

		$studentResultsAll = StudentsResult::where('IdEspecialidad', Session::get('faculty-code'))
			->where('deleted_at', null)->get();

		foreach ($studentResultsAll as $stRst){
			$statusStRst = 0;
			if(in_array($stRst->IdResultadoEstudiantil, $studentsResults)){
				$statusStRst = 1;
			}
			StudentsResult::where('IdResultadoEstudiantil', $stRst->IdResultadoEstudiantil)
				->update(array('Estado' => $statusStRst,));
			foreach ($stRst->aspects as $asp){
				$statusAsp = 0;
				if(in_array($asp->IdAspecto, $aspects)){
					$statusAsp = 1;
				}
				Aspect::where('IdAspecto', $asp->IdAspecto)
					->update(array('Estado' => $statusAsp,));
				foreach ($asp->criterion as $crt){
					$statusCrt = 0;
					if(in_array($crt->IdCriterio, $criterions)){
						$statusCrt = 1;
					}
					Criterion::where('IdCriterio', $crt->IdCriterio)
						->update(array('Estado' => $statusCrt,));
				}
			}
		}

		return $period;
	}

	public function findPeriod($idFaculty) {
		$period = Period::where('IdEspecialidad', $idFaculty)->where('Vigente', 1)->first();
		return $period;
	}
	//Coordinador y Administrador
	public function findCoordinator() {
		if(Session::get('user')->IdDocente != 0){
			if(Session::get('user')->user->IdPerfil == 1){
				$coordinator = Teacher::where('IdUsuario', Session::get('user')->IdUsuario)->first();
			}else{
				$coordinator = null;
			}
		}else{
			$coordinator = null;
		}

		return $coordinator;
	}


	public function findConf($idFaculty, $idPeriod) {
		$confFaculty = ConfFaculty::where('IdEspecialidad', $idFaculty)
			->where('IdPeriodo', $idPeriod)->first();
		return $confFaculty;
	}

	public function findCycle($idFaculty) {
		$cycle = Cicle::where('IdEspecialidad', $idFaculty)->where('Vigente', 1)->first();
		return $cycle;
	}
	//FALTA COMPLETAR IDEA
	public function findCycleLast($idFaculty) {
		$cycle = Cicle::where('IdEspecialidad', $idFaculty)->
						//where('IdPeriodo', null)->
						orderby('IdCicloAcademico','DESC')->take(1)->first();
		//dd($cycle);
		return $cycle;
	}

	public function findAcademicCycle($idFacultyAcademicCycle) {
		$facultyAcademicCycle = ConfFaculty::where('IdConfEspecialidad', $idFacultyAcademicCycle)->first();
		return $facultyAcademicCycle;
	}

	public function findAcademicCycleByFaculty($idFaculty) {
		$facultyAcademicCycle = ConfFaculty::where('IdEspecialidad', $idFaculty)->first();
		return $facultyAcademicCycle;
	}

	public function findConfFaculty($idFaculty, $idPeriod) {
		$confFaculty = ConfFaculty::where('IdEspecialidad', $idFaculty)->where('IdPeriodo', $idPeriod)->first();
		return $confFaculty;
	}

	public function updateConfFaculty($request) {

	    $confFaculty = ConfFaculty::where('IdConfEspecialidad', $request['conf-code'])
			->update(array(
					'NivelEsperado' => $request['facultyAgreementLevel'],
					'UmbralAceptacion' => $request['facultyAgreement'],
					'CantNivelCriterio' => $request['criteriaLevel']	,
					'IdCicloFin' => $request['cycleEnd']	,
					'IdCicloInicio' => $request['cycleStart']	,
			));


		$measures = (array_key_exists('measures', $request))?$request['measures']: [];

		$period = Period::where('IdPeriodo', $request['period-code'])->first();
		$period->measures()->sync($measures);

		$educationalObjetives = (array_key_exists('objCheck', $request))?$request['objCheck']: [];
		$studentsResults = (array_key_exists('stRstCheck', $request))?$request['stRstCheck']: [];
		$aspects = (array_key_exists('aspCheck', $request))?$request['aspCheck']: [];
		$criterions = (array_key_exists('crtCheck', $request))?$request['crtCheck']: [];

		$period->educationalObjetives()->sync($educationalObjetives);
		$period->studentsResults()->sync($studentsResults);
		$period->aspects()->sync($aspects);
		$period->criterions()->sync($criterions);

		$educationalObjetivesAll = EducationalObjetive::where('IdEspecialidad', Session::get('faculty-code'))
			->where('deleted_at', null)->get();

		foreach ($educationalObjetivesAll as $obj) {
			$statusObj = 0;
			if (in_array($obj->IdObjetivoEducacional, $educationalObjetives)) {
				$statusObj = 1;
			}
			EducationalObjetive::where('IdObjetivoEducacional', $obj->IdObjetivoEducacional)
				->update(array('Estado' => $statusObj,));
		}

		$studentResultsAll = StudentsResult::where('IdEspecialidad', Session::get('faculty-code'))
			->where('deleted_at', null)->get();

		foreach ($studentResultsAll as $stRst){
			$statusStRst = 0;
			if(in_array($stRst->IdResultadoEstudiantil, $studentsResults)){
				$statusStRst = 1;
			}
			StudentsResult::where('IdResultadoEstudiantil', $stRst->IdResultadoEstudiantil)
				->update(array('Estado' => $statusStRst,));
			foreach ($stRst->aspects as $asp){
				$statusAsp = 0;
				if(in_array($asp->IdAspecto, $aspects)){
					$statusAsp = 1;
				}
				Aspect::where('IdAspecto', $asp->IdAspecto)
					->update(array('Estado' => $statusAsp,));
				foreach ($asp->criterion as $crt){
					$statusCrt = 0;
					if(in_array($crt->IdCriterio, $criterions)){
						$statusCrt = 1;
					}
					Criterion::where('IdCriterio', $crt->IdCriterio)
						->update(array('Estado' => $statusCrt,));
				}
			}
		}

	}

	public function updateCycle($request) {

		$s = $request['cycleDateI'];
		$submitdate = str_replace( "/" , "-" , $s);
		$time = strtotime($submitdate);
		$dateI = date('Y-m-d',$time);



		$s1 = $request['cycleDateF'];
		$submitdate1 = str_replace( "/" , "-" , $s1);
		$time1 = strtotime($submitdate1);

		$dateF = date('Y-m-d',$time1);


	    Cicle::where('IdCicloAcademico', $request['cycle-code'])
		->update(array(
					'IdCiclo' => $request['cycleId'],
					'FechaInicio'=>$dateI,
					'FechaInicio'=>$dateI,
					'FechaFin'=>$dateF
		));

		//

		if(count($_REQUEST['check']) != 1){

			$cyclexresults = CyclexResult::where('IdCicloAcademico', Session::get('academic-cycle')->IdCicloAcademico)->get();

			for($j=0; $j<count($cyclexresults); $j++){
					$presente = false;
					for($z=1; $z<count($_REQUEST['check']);$z++){
						if($cyclexresults[$j]->IdResultadoEstudiantil == $_REQUEST['check'][$z]){
							$presente = true;
						}
					}
					if($presente == false){
						$cdic = CyclexResult::where('IdResultadoEstudiantil', $cyclexresults[$j]->IdResultadoEstudiantil)
											 ->where('IdCicloAcademico', Session::get('academic-cycle')->IdCicloAcademico)
											 ->first();
        				$cdic->delete();
					}

			}

			for ($i=1; $i<count($_REQUEST['check']); $i++)
			{

				$cyclexresult = CyclexResult::where('IdResultadoEstudiantil',$_REQUEST['check'][$i])
											->where('IdCicloAcademico', Session::get('academic-cycle')->IdCicloAcademico)->first();

				if($cyclexresult == null){

					$cyclexresults = CyclexResult::create([
							'IdResultadoEstudiantil' => $_REQUEST['check'][$i],
							'IdCicloAcademico' => Session::get('academic-cycle')->IdCicloAcademico

					]);
				}

			}

		}else{

			$cdic = CyclexResult::where('IdCicloAcademico', Session::get('academic-cycle')->IdCicloAcademico)->get();
			foreach ($cdic as $borrar) {
				$borrar->delete();
			}

		}
		//
		$cycle = Cicle::where('IdEspecialidad', $request['cycle-faculty'])->where('Vigente', 1)->first();
		return $cycle;
	}

	public function updateAcademicCycle($idconf,$request) {

	    $facultyAcademicCycle = ConfFaculty::where('IdConfEspecialidad', $idconf)
		//->where('deleted_at', null)
		->update(array(
						'NivelEsperado' => $request['facultyAgreementLevel'],
						'UmbralAceptacion' => $request['facultyAgreement'],
						'CantNivelCriterio' => $request['criteriaLevel']	,
						));

	}
/*



*/

}