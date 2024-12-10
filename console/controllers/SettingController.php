<?php

namespace console\controllers;

use common\models\model\Group;

use common\models\model\PasswordEncrypts;
use common\models\model\Profile;
use common\models\model\Student;
use common\models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

use yii\console\Controller;


class SettingController extends Controller
{
    public function actionDel()
    {
        $students = Student::find()
            ->all();

        foreach ($students as $student) {
            Profile::deleteAll(['user_id' => $student->user_id]);
            PasswordEncrypts::deleteAll(['user_id' => $student->user_id]);
            $user = $student->user;
            $student->delete();
            $user->delete();
        }
    }
    public function actionStudentsImport()
    {
        $errors = [];
        $inputFileName = __DIR__ . '/excels/talabalar1.xlsx';
        $spreadsheet = IOFactory::load($inputFileName);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $i = 0;
        foreach ($data as $dataOne) {
            if (isset($dataOne[1])) {

                $group = Group::findOne(['unical_name' =>  $dataOne[1]]);
                $role = 'student';
                if (isset($group)) {

                    $model = new \api\resources\User();
                    $user = self::studentLogin();
                    $model->username = $user['username'];
                    $model->email= $user['email'];
                    $password = _passwordMK();
                    $model->password_hash = \Yii::$app->security->generatePasswordHash($password);
                    $model->auth_key = \Yii::$app->security->generateRandomString(20);
                    $model->password_reset_token = null;
                    $model->access_token = \Yii::$app->security->generateRandomString();
                    $model->access_token_time = time();
                    $model->status = 10;
                    $model->save(false);
//
                    $model->savePassword($password, $model->id);

                    $profile = new Profile();
                    $profile->user_id = $model->id;
                    $profile->passport_pin = $dataOne[0];
                    $profile->save(false);

                    $student = new Student();
                    $student->group_id = $group->id;
                    $student->user_id = $model->id;
                    $student->type = 1;
                    $student->status = 10;
                    $student->save(false);

                    $auth = \Yii::$app->authManager;
                    $authorRole = $auth->getRole($role);
                    $auth->assign($authorRole, $model->id);

                } else {
                    $errors[] = $dataOne[1];
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
            $result['email'] = 'sarbon-std-' . $count . '@sarbon.uz';
        }
        return $result;
    }
}