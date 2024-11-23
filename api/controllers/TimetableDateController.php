<?php

namespace api\controllers;

use common\models\model\Group;
use common\models\model\TeacherAccess;
use common\models\model\Timetable;
use common\models\model\TimeTable1;
use common\models\model\TimetableAttend;
use common\models\model\TimetableDate;
use common\models\model\TimeTableGroup;
use Yii;
use base\ResponseStatus;
use common\models\model\EduSemestr;
use common\models\model\Kafedra;
use common\models\model\Student;
use common\models\model\Subject;
use yii\db\Expression;

class TimetableDateController extends ApiActiveController
{
    public $modelClass = 'common\models\model\TimetableDate';

    public $table_name = 'timetable_date';

    public $controller_name = 'TimetableDate';

    public function actions()
    {
        return [];
    }

    public function actionIndex($lang)
    {
        $model = new TimetableDate();

        $query = $model->find()->where(['is_deleted' => 0, 'group_type' => 1]);

        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');

        $query->andWhere(['between', 'date', new Expression('DATE(:start_date)'), new Expression('DATE(:end_date)')])
            ->params([':start_date' => $startDate, ':end_date' => $endDate]);

        if (isRole('student')) {
            $query->andWhere(['group_id' => current_student()->group_id]);
        }

        $query = $this->filter($query, $model);

        // filter
        $query = $this->filterAll($query, $model);

        // sort
        $query = $this->sort($query);

//        $query->groupBy('ids_id');

        // data
        $data =  $this->getData($query);

        return $this->response(1, _e('Success'), $data);
    }

    public function actionGet($lang)
    {
        $model = new TimetableDate();

        if (isRole('teacher')) {
            $userId = current_user_id();
        } else {
            $userId = Yii::$app->request->get('user_id');
        }

        $query = $model->find()->where(['is_deleted' => 0, 'user_id' => $userId]);

        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');

//        $startDate = ($startDate !== null) ? date('Y-m-d', strtotime("$startDate -1 day")) : null;
//        $endDate = ($endDate !== null) ? date('Y-m-d', strtotime("$endDate +1 day")) : null;

        $query->andWhere(['between', 'date', new Expression('DATE(:start_date)'), new Expression('DATE(:end_date)')])
            ->params([':start_date' => $startDate, ':end_date' => $endDate]);

        $query = $this->filter($query, $model);

        // filter
        $query = $this->filterAll($query, $model);

        // sort
        $query = $this->sort($query);

        // data
        $data =  $this->getData($query);

        return $this->response(1, _e('Success'), $data);
    }

    public function actionTeacher($lang)
    {
        $model = new TimetableDate();

        $query = $model->find()->where(['is_deleted' => 0, 'user_id' => current_user_id()]);

        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');

        $query->andWhere(['between', 'date', new Expression('DATE(:start_date)'), new Expression('DATE(:end_date)')])
            ->params([':start_date' => $startDate, ':end_date' => $endDate]);

        $query->all();

        // data
        $data =  [];
        if (count($query) == 0) {
            return $this->response(1, _e('Success'), $data);
        }

        foreach ($query as $item) {
            for ($i = 1; $i < 7; $i++) {
                $status = 0;
                if ($item->week_id == $i) {
                    switch ($item->para_id) {
                        case 1:

                            $data[$i][1] = [
                                ''
                            ];
                            break;
                    }
                }
            }
        }

        return $this->response(1, _e('Success'), $data);
    }



    public function actionFilter($lang)
    {
        $model = new TimetableDate();

        $query = $model->find()->where(['is_deleted' => 0]);

        if (isRole('student')) {
            $query->andWhere(['group_id' => current_student()->group_id]);
        }

        // filter
        $query = $this->filterAll($query, $model);

        // sort
        $query = $this->sort($query);

        // data
        $data =  $this->getData($query);

        return $this->response(1, _e('Success'), $data);
    }


    public function actionCreate($lang)
    {
        $post = Yii::$app->request->post();
        $result = Timetable::createItem($post);
        if (!is_array($result)) {
            return $this->response(1, _e($this->controller_name . ' successfully created.'), null, null, ResponseStatus::CREATED);
        } else {
            return $this->response(0, _e('There is an error occurred while processing.'), null, $result, ResponseStatus::UPROCESSABLE_ENTITY);
        }
    }

    public function actionDelete($lang, $id)
    {
        $post = Yii::$app->request->post();
        $models = TimetableDate::findOne(['id' => $id, 'status' => 1, 'is_deleted' => 0]);

        if (!$models) {
            return $this->response(0, _e('Data not found.'), null, null, ResponseStatus::NOT_FOUND);
        }

        $result = TimetableDate::removeDay($models , $post);
        if (!is_array($result)) {
            return $this->response(1, _e($this->controller_name . ' successfully removed.'), null, null, ResponseStatus::OK);
        } else {
            return $this->response(0, _e('There is an error occurred while processing.'), null, $result, ResponseStatus::UPROCESSABLE_ENTITY);
        }
    }

    public function actionAttend($lang, $id)
    {
        $model = TimetableDate::findOne([
            'id' => $id,
            'status' => 1,
            'is_deleted' => 0,
            'user_id' => current_user_id(),
        ]);

        if (!$model) {
            return $this->response(0, _e('Data not found.'), null, null, ResponseStatus::NOT_FOUND);
        }
        $data = [];
        $data['ids'] = $model->ids_id;
        $data['group_type'] = $model->group_type;
        $data['now'] = date("Y-m-d");
        $data['subject'] = $model->subject;
        $dates = TimetableDate::find()
            ->where([
                'ids_id' => $model->ids_id,
                'user_id' => $model->user_id,
                'group_id' => $model->group_id,
                //'para_id' => $model->para_id,
                'status' => 1,
                'is_deleted' => 0
            ])
            ->orderBy('date asc')
            ->all();
        foreach ($dates as $date) {
            $status = 0;
            if ($date->date == date("Y-m-d")) {
                $status = 1;
            }

            $timeTableAttend = TimetableAttend::find()
                ->where([
                    'ids_id' => $model->ids_id,
//                    'timetable_date_id' => $date->id,
//                    'date' => $date->date,
//                    'timetable_date_id' => $date->id,
                    'group_type' => $model->group_type,
//                    'para_id' => $model->para_id, // yangi qoshildi
                    'status' => 1,
                    'is_deleted' => 0
                ])
                ->andWhere(['in' , 'timetable_date_id' , TimetableDate::find()
                    ->select('id')
                    ->where([
                        'ids_id' => $model->ids_id,
                        'user_id' => $model->user_id,
                        'date' => $date->date,
                        'status' => 1,
                        'is_deleted' => 0,
                        'para_id' => $date->para_id
                    ])
                ])
                ->all();

            $data['dates'][] = [
                'status' => $status,
                'date' => $date->date,
                'room' => $date->room,
                'week' => $date->week,
                'para' => $date->para,
                'attend_status' => $date->attend_status,
                'attend' => $timeTableAttend
            ];
        }

        $timeTables = Timetable::find()
            ->where([
                'ids' => $model->ids_id,
                'group_type' => $model->group_type,
                'status' => 1,
                'is_deleted' => 0
            ])->all();

        foreach ($timeTables as $timeTable) {
            $data['patok'][] = [
                'timetable_id' => $timeTable->id,
                'group' => $timeTable->group,
                'student' => $timeTable->timeTableStudent,
            ];
        }

        return $this->response(1, _e('Success'), $data);
    }


    public function actionGetDate($lang)
    {
        $model = new TimetableDate();

        $teacherAccessId = Yii::$app->request->get('teacher_access_id');
        $eduYearId = Yii::$app->request->get('edu_year_id');
        $date = Yii::$app->request->get('date');
        if ($date == null) {
            $date = date("Y-m-d");
        } else {
            $date = date("Y-m-d" , strtotime($date));
        }

        $query = $model->find()->where([
            'is_deleted' => 0,
            'teacher_access_id' => $teacherAccessId,
            'date' => $date,
            'edu_year_id' => $eduYearId ?? activeYearId()
        ]);

        $query = $this->filter($query, $model);

        // filter
        $query = $this->filterAll($query, $model);

        // sort
        $query = $this->sort($query);

        // data
        $data =  $this->getData($query);

        return $this->response(1, _e('Success'), $data);
    }

}
