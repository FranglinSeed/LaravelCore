<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommentMaster;
use App\Models\CompanyMaster;
use App\Models\SurveyMaster;
use App\Models\SurveyQuestionMaster;
use App\Models\SurveyQuestionMasterTemp;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stevebauman\Location\Facades\Location;

class ApiController extends Controller
{
  public function company_load(Request $request)
  {
    $companies = CompanyMaster::where('delete_flag', 0)->get();

    return $companies;
  }

  public function survey_select(Request $request)
  {
    $companyId = $request->input('companyId');
    $category = $request->input('category');

    $survey = SurveyMaster::where('company_id', $companyId)
      ->where('survey_user_type', $category)
      ->where('status', 1)
      ->where('delete_flag', 0)
      ->first();

    return $survey;
  }

  public function survey_load(Request $request, $id = "")
  {
    if (strpos($id, '@@~') === false) {
      $id_num = (int)$id;
      $surveys = DB::select("SELECT 
        company_master.company_id,
        company_master.company_name,
        company_master.logo,
        company_master.user_id,
        survey_question_master.question_id,
        survey_question_master.survey_id, 
        survey_question_master.kind_of_question, 
        survey_question_master.question,
        survey_question_master.answer, 
        survey_master.survey_user_type, 
        survey_master.survey_name 
        FROM survey_question_master JOIN survey_master 
        ON (survey_master.survey_id = survey_question_master.survey_id) JOIN company_master 
        ON (survey_master.company_id = company_master.company_id) WHERE
        survey_question_master.survey_id = ${id_num} AND 
        survey_master.status = 1 AND 
        survey_master.delete_flag = 0 AND 
        company_master.delete_flag = 0 AND 
        survey_question_master.delete_flag = 0");

      return $surveys;
    }

    $category = substr($id, 3);

    // $surveys = SurveyQuestionMasterTemp::where('survey_id', $category)->get();

    $surveys = DB::select("SELECT
      survey_question_master_temp.question_id,
      survey_question_master_temp.survey_id,
      survey_question_master_temp.kind_of_question,
      survey_question_master_temp.question,
      survey_question_master_temp.answer
      FROM qubu.survey_question_master_temp JOIN survey_master 
      ON survey_question_master_temp.survey_id = survey_master.survey_id 
      WHERE survey_master.survey_user_type = ${category}
    ");

    return $surveys;
  }

  public function comment_add(Request $request)
  {
    $companyName = $request->input('companyName');
    $companyId = $request->input('companyId');
    $email = $request->input('email');
    $ownerId = $request->input('ownerId');
    $userType = $request->input('userType');
    $question = $request->input('questions');
    $answer = $request->input('answers');
    $comment = $request->input('comment');
    $questionIds = $request->input('questionIds');
    $questionScores = $request->input('questionScores');
    $score = $request->input('score');
    $status = $request->input('status');

    $user = User::where('email', $email)->first();

    $commentMaster = new CommentMaster;
    $commentMaster->user_id = $user->user_id;
    $commentMaster->other_user_id = $ownerId == 0 ? 20 : $ownerId;
    $commentMaster->user_type = $userType;
    $commentMaster->company_id = $companyId;
    $commentMaster->type = 0;
    $commentMaster->type_of_id = rand() % 80 + 1;
    $commentMaster->question = $question;
    $commentMaster->answer = $answer;
    $commentMaster->comment = $comment;
    $commentMaster->comment_score = $score;
    $commentMaster->question_id = $questionIds;
    $commentMaster->question_score = $questionScores;
    $commentMaster->status = $status;
    $commentMaster->read_status = 1;
    $commentMaster->delete_flag = 0;

    if (!$companyId) {
      $ip = request()->ip();
      $data = Location::get($ip);
      $location = "Colombia";
      if ($data && $data->countryName && $data->regionName && $data->cityName)
        $location = $data->countryName . " " . $data->regionName . " " . $data->cityName;

      $question1 = "";

      $company = new CompanyMaster;
      $company->company_name = $companyName;
      // $company->user_id = $user->user_id;
      $company->user_id = 20;
      $company->service_description = 'Creado por usuario';
      $company->location = $location;
      if ($data && $data->latitude)
        $company->latitude = $data->latitude;
      if ($data && $data->longitude)
        $company->longitude = $data->longitude;
      $company->logo = 'DvP0JuNMrehWfzG1793520589.png';
      $company->background_image = '0EwDMTbp6koIy9K452469774.png';
      $company->profile_visible = 1;
      $company->delete_flag = 0;
      $company->save();
      $companyId = $company->company_id;

      $survey = new SurveyMaster;
      // $survey->user_id = $user->user_id;
      $survey->user_id = 20;
      $survey->company_id = $companyId;
      $survey->survey_user_type = 1;
      $survey->survey_name = 'Empleados';
      $survey->status = 1;
      $survey->survey_time_value = 1;
      $survey->survey_time = 'week';
      $survey->save();

      $questions = SurveyQuestionMasterTemp::where('survey_id', 3)->get();
      foreach ($questions as $questionTemp) {
        echo $questionTemp->name;
        $question = new SurveyQuestionMaster;
        $question->survey_id = $survey->survey_id;
        $question->kind_of_question = $questionTemp->kind_of_question;
        $question->question = $questionTemp->question;
        $question->answer = $questionTemp->answer;
        $question->save();

        if ($userType == 1) {
          $question1 .= $question->question_id;
          $question1 .= ", ";
        }
      }

      $survey = new SurveyMaster;
      // $survey->user_id = $user->user_id;
      $survey->user_id = 20;
      $survey->company_id = $companyId;
      $survey->survey_user_type = 2;
      $survey->survey_name = 'Proveedores';
      $survey->status = 1;
      $survey->survey_time_value = 1;
      $survey->survey_time = 'week';
      $survey->save();

      $questions = SurveyQuestionMasterTemp::where('survey_id', 2)->get();
      foreach ($questions as $questionTemp) {
        echo $questionTemp->name;
        $question = new SurveyQuestionMaster;
        $question->survey_id = $survey->survey_id;
        $question->kind_of_question = $questionTemp->kind_of_question;
        $question->question = $questionTemp->question;
        $question->answer = $questionTemp->answer;
        $question->save();

        if ($userType == 2) {
          $question1 .= $question->question_id;
          $question1 .= ", ";
        }
      }

      $survey = new SurveyMaster;
      // $survey->user_id = $user->user_id;
      $survey->user_id = 20;
      $survey->company_id = $companyId;
      $survey->survey_user_type = 3;
      $survey->survey_name = 'Clientes';
      $survey->status = 1;
      $survey->survey_time_value = 1;
      $survey->survey_time = 'week';
      $survey->save();

      $questions = SurveyQuestionMasterTemp::where('survey_id', 1)->get();
      foreach ($questions as $questionTemp) {
        echo $questionTemp->name;
        $question = new SurveyQuestionMaster;
        $question->survey_id = $survey->survey_id;
        $question->kind_of_question = $questionTemp->kind_of_question;
        $question->question = $questionTemp->question;
        $question->answer = $questionTemp->answer;
        $question->save();

        if ($userType == 3) {
          $question1 .= $question->question_id;
          $question1 .= ", ";
        }
      }

      $question1 = substr($question1, 0, strlen($question1) - 2);
      $commentMaster->company_id = $companyId;
      $commentMaster->question_id = $question1;
    }

    $commentMaster->save();
  }
}
