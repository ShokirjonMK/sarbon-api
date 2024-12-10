<?php

namespace console\controllers;

use api\resources\BaseGet;
use api\resources\SemestrUpdate;
use common\models\model\AttendReason;
use common\models\model\EduPlan;
use common\models\model\EduSemestr;
use common\models\model\EduSemestrExamsType;
use common\models\model\EduSemestrSubject;
use common\models\model\Faculty;
use common\models\model\FinalExam;
use common\models\model\FinalExamConfirm;
use common\models\model\FinalExamGroup;
use common\models\model\FinalExamTest;
use common\models\model\FinalExamTestQuestion;
use common\models\model\FinalExamTestStart;
use common\models\model\Group;
use common\models\model\Kafedra;
use common\models\model\LoginHistory;
use common\models\model\MarkHistory;
use common\models\model\Option;
use common\models\model\PasswordEncrypts;
use common\models\model\Student;
use common\models\model\StudentAttend;
use common\models\model\StudentGroup;
use common\models\model\StudentMark;
use common\models\model\StudentMarkHistory;
use common\models\model\StudentSemestrSubject;
use common\models\model\StudentSemestrSubjectVedomst;
use common\models\model\StudentTopicPermission;
use common\models\model\SubjectVedomst;
use common\models\model\TeacherAccess;
use common\models\model\Test;
use common\models\model\TestBody;
use common\models\model\Timetable;
use common\models\model\TimetableAttend;
use common\models\model\TimetableDate;
use common\models\model\TimetableReason;
use common\models\model\TimetableStudent;
use common\models\model\Translate;
use common\models\model\UserAccess;
use common\models\model\UserAccessType;
use common\models\Profile;
use common\models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Console;
use yii\helpers\Inflector;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\web\Response;

class SettingController extends Controller
{
    public function actionStudentsImport()
    {
        $errors = [];
        $inputFileName = __DIR__ . '/excels/talabalar.xlsx';

        dd($inputFileName);
        $spreadsheet = IOFactory::load($inputFileName);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $i = 0;
        foreach ($data as $key => $row) {

            if ($key != "") {
                $guruh = $row[1];
                $group = Group::findOne(['unical_name' => $guruh]);
                $role = 'student';
                if (isset($group)) {

//                    $model = new User();
//                    $user = self::studentLogin();
//                    $model->username = $user['username'];
//                    $model->email= $user['email'];
//                    $password = _passwordMK();
//                    $model->password_hash = \Yii::$app->security->generatePasswordHash($password);
//                    $model->auth_key = \Yii::$app->security->generateRandomString(20);
//                    $model->password_reset_token = null;
//                    $model->access_token = \Yii::$app->security->generateRandomString();
//                    $model->access_token_time = time();
//                    $model->status = 10;
//                    $model->save(false);
//
//                    $model->savePassword($password, $model->id);
//
//                    $profile = new Profile();
//                    $profile->user_id = $model->id;
//                    $profile->save(false);
//
//                    $student = new Student();
//                    $student->group_id = $group->id;
//                    $student->user_id = $model->id;
//                    $student->type = 1;
//                    $student->status = 10;
//                    $student->save(false);
//
//                    $auth = \Yii::$app->authManager;
//                    $authorRole = $auth->getRole($role);
//                    $auth->assign($authorRole, $model->id);

                } else {
                    $errors[] = $guruh;
                }
                $i++;
                echo $i."\n";
            } else {
                break;
            }
        }

        dd($errors);

    }

    public static function studentLogin() {
        $result = [];
        $std = \api\resources\User::find()->orderBy(['id' => SORT_DESC])->one();
        if ($std) {
            $count = $std->id + 100 + 1;
        } else {
            $count = 100 + 1;
        }

        $result['username'] = 'sarbon-std-' . $count;
        if (!(isset($post['email']))) {
            $result['email'] = 'sarbon-std' . $count . '@sarbon.uz';
        }
        return $result;
    }
}