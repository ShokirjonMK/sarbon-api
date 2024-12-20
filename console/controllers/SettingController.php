<?php

namespace console\controllers;

use common\models\model\EduSemestr;
use common\models\model\Group;

use common\models\model\PasswordEncrypts;
use common\models\model\Profile;
use common\models\model\Student;
use common\models\model\StudentGroup;
use common\models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

use yii\console\Controller;


class SettingController extends Controller
{
    public function actionDel1()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $errors = [];

        $students = Student::find()
            ->where(['is_deleted' => 0 , 'status' => 10])
            ->all();
        foreach ($students as $student) {
            $group = Group::findOne($student->group_id);
            if ($group) {
                $eduSemestrs = EduSemestr::find()
                    ->where(['edu_plan_id' => $group->edu_plan_id , 'is_deleted' => 0])
                    ->orderBy('semestr_id asc')
                    ->all();
                foreach ($eduSemestrs as $eduSemestr) {
                    $new = new StudentGroup();
                    $new->student_id = $student->id;
                    $new->group_id = $group->id;
                    $new->edu_year_id = $eduSemestr->edu_year_id;
                    $new->edu_plan_id = $eduSemestr->edu_plan_id;
                    $new->edu_semestr_id = $eduSemestr->id;
                    $new->edu_form_id = $eduSemestr->edu_form_id;
                    $new->semestr_id = $eduSemestr->semestr_id;
                    $new->course_id = $eduSemestr->course_id;
                    $new->faculty_id = $eduSemestr->faculty_id;
                    $new->direction_id = $eduSemestr->direction_id;
                    $new->save(false);
                    if ($eduSemestr->status == 1) {
                        $student->faculty_id = $eduSemestr->faculty_id;
                        $student->direction_id = $eduSemestr->direction_id;
                        $student->course_id = $eduSemestr->course_id;
                        $student->edu_year_id = $eduSemestr->edu_year_id;
                        $student->edu_type_id = $eduSemestr->edu_type_id;
                        $student->edu_form_id = $eduSemestr->edu_form_id;
                        $student->edu_lang_id = $group->language_id;
                        $student->edu_plan_id = $group->edu_plan_id;
                        $student->is_contract = 1;
                        $student->save(false);
                        break;
                    }
                }
            }
        }

        $transaction->commit();
        dd(232323);
    }

    public function actionStudentsImport()
    {
        $errors = [];
        $inputFileName = __DIR__ . '/excels/std.xlsx';
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
            $count = $std->id + 10000 + 1;
        } else {
            $count = 10000 + 1;
        }

        $result['username'] = 'sarbon-std-' . $count;
        if (!(isset($post['email']))) {
            $result['email'] = 'sarbon-std-' . $count . '@sarbon.uz';
        }
        return $result;
    }

    public function actionGetData()
    {
        $url = 'https://subsidiya.idm.uz/api/applicant/get-photo';

        $b = 0;
        $profiles = Profile::find()->all();
        foreach ($profiles as $profile) {

            if ($profile->passport_pin != null) {
                $data = json_encode([
                    'pinfl' => $profile->passport_pin
                ]);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Basic ' . base64_encode('ikbol:ikbol123321')
                ]);

                $response = curl_exec($ch);
                $response = json_decode($response, true);
                curl_close($ch);
//                $photoBase64 = $response['data']['photo'] ?? null;
                $image = null;

//                if ($photoBase64) {
//                    // Rasmni dekodlash
//                    $photoData = base64_decode($photoBase64);
//
//                    if (!file_exists(\Yii::getAlias('@api/web/storage/std_image'))) {
//                        mkdir(\Yii::getAlias('@api/web/storage/std_image'), 0777, true);
//                    }
//
//                    // Saqlash uchun fayl nomini va yo‘lini aniqlash
//                    $fileName = $profile->passport_pin.'_ik.jpg'; // Fayl nomini kerakli tarzda o'zgartirishingiz mumkin
//                    $filePath = \Yii::getAlias('@api/web/storage/std_image/') . $fileName;
//                    $image = 'storage/std_image/'.$fileName;
//
//                    // Faylni papkaga saqlash
//                    file_put_contents($filePath, $photoData);
//
//                    echo $b++."\n";
//                }

                $pin = $response['data']['pinfl'];
                $seria = $response['data']['docSeria'];
                $number = $response['data']['docNumber'];
                $last_name = $response['data']['surnameLatin'];
                $first_name = $response['data']['nameLatin'];
                $middle_name = $response['data']['patronymLatin'];
                $birthday = $response['data']['birthDate'];
                $b_date = $response['data']['docDateBegin'];
                $e_date = $response['data']['docDateEnd'];
                $given_by = $response['data']['docGivePlace'];
                $jins = $response['data']['sex'];

                $profile->first_name = $first_name;
                $profile->last_name = $last_name;
                $profile->middle_name = $middle_name;
                $profile->birthday = $birthday;
                $profile->passport_given_date = $b_date;
                $profile->passport_issued_date = $e_date;
                $profile->passport_given_by = $given_by;
                $profile->gender = $jins;
                $profile->passport_serial = $seria;
                $profile->passport_number = $number;
                $profile->passport_pin = $pin;
                $profile->image = $image;
                $profile->update(false);
            }
        }
    }


    public function actionIk()
    {
        $url = 'https://subsidiya.idm.uz/api/applicant/get-photo';

        $data = json_encode([
            'pinfl' => 52111045840018
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode('ikbol:ikbol123321')
        ]);

        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);

        $photoBase64 = $response['data']['photo'] ?? null;

        if ($photoBase64) {
            // Rasmni dekodlash
            $photoData = base64_decode($photoBase64);

            if (!file_exists(\Yii::getAlias('@api/web/storage/std_image'))) {
                mkdir(\Yii::getAlias('@api/web/storage/std_image'), 0777, true);
            }

            // Saqlash uchun fayl nomini va yo‘lini aniqlash
            $fileName =  '60210056530017' . '__ik.jpg'; // Fayl nomini kerakli tarzda o'zgartirishingiz mumkin
            $filePath = \Yii::getAlias('@api/web/storage/std_image/') . $fileName;

            // Faylni papkaga saqlash
            file_put_contents($filePath, $photoData);
        }

        $pin = $photoBase64 = $response['data']['pinfl'];
        $seria = $photoBase64 = $response['data']['docSeria'];
        $number = $photoBase64 = $response['data']['docNumber'];
        $last_name = $photoBase64 = $response['data']['surnameLatin'];
        $first_name = $photoBase64 = $response['data']['nameLatin'];
        $middle_name = $photoBase64 = $response['data']['patronymLatin'];
        $birthday = $photoBase64 = $response['data']['birthDate'];
        $b_date = $photoBase64 = $response['data']['docDateBegin'];
        $e_date = $photoBase64 = $response['data']['docDateEnd'];
        $given_by = $photoBase64 = $response['data']['docGivePlace'];
        $jins = $photoBase64 = $response['data']['sex'];
    }

}