<?php

namespace api\controllers;

use common\models\model\EduSemestrSubject;
use common\models\model\Subject;
use common\models\model\SubjectSemestr;
use Yii;
use base\ResponseStatus;
use common\models\model\Faculty;
use common\models\model\Kafedra;
use common\models\model\TeacherAccess;

class SubjectSemestrController extends ApiActiveController
{
    public $modelClass = 'api\resources\Subject';

    public function actions()
    {
        return [];
    }

    public $table_name = 'subject_semestr';
    public $controller_name = 'SubjectSemestr';

    public function actionIndex($lang)
    {
        $model = new SubjectSemestr();

        $query = $model->find()
            ->with(['infoRelation'])
            ->andWhere([$this->table_name . '.is_deleted' => 0])
            ->leftJoin("translate tr", "tr.model_id = $this->table_name.id and tr.table_name = '$this->table_name'")
            ->groupBy($this->table_name . '.id')
            ->andFilterWhere(['like', 'tr.name', Yii::$app->request->get('query')]);

        if (isRole("dean")) {
            $k = $this->isSelf(Faculty::USER_ACCESS_TYPE_ID);
            if ($k['status'] == 1) {
                $query->andFilterWhere(['in', 'kafedra_id', Kafedra::find()->where(['faculty_id' => $k['UserAccess']])->select('id')]);
            }
        } elseif (isRole('mudir')) {
            $k = $this->isSelf(Kafedra::USER_ACCESS_TYPE_ID);
            if ($k['status'] == 1) {
                $query->andFilterWhere([
                    'kafedra_id' => $k['UserAccess']
                ]);
            } elseif ($k['status'] == 2) {
                $query->andFilterWhere([
                    'kafedra_id' => -1
                ]);
            }
        } elseif (isRole("teacher")) {
            $teacherAccessSubjectIds = TeacherAccess::find()
                ->select('subject_semestr_id')
                ->where(['user_id' => current_user_id(), 'is_deleted' => 0])
                ->groupBy('subject_id');

            if ($teacherAccessSubjectIds) {
                $query->andFilterWhere(['in', $this->table_name . '.id', $teacherAccessSubjectIds]);
            } else {
                $query->andFilterWhere(['kafedra_id' => -1]);
            }
        } else {
            /*  is Self  */
            $k = $this->isSelf(Faculty::USER_ACCESS_TYPE_ID);
            if ($k['status'] == 1) {
                $query->andFilterWhere(['in', 'kafedra_id', Kafedra::find()->where(['faculty_id' => $k['UserAccess']])->select('id')]);
                // $query->andFilterWhere([
                //     'kafedra_id' => $k['UserAccess']->table_id
                // ]);
            } elseif ($k['status'] == 2) {
                $query->andFilterWhere([
                    'kafedra_id' => -1
                ]);
            }
            /*  is Self  */
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
        $model = new SubjectSemestr();
        $post = Yii::$app->request->post();
        $this->load($model, $post);

        $result = SubjectSemestr::createItem($model , $post);
        if (!is_array($result)) {
            return $this->response(1, _e($this->controller_name . ' successfully created.'), null, null, ResponseStatus::CREATED);
        } else {
            return $this->response(0, _e('There is an error occurred while processing.'), null, $result, ResponseStatus::UPROCESSABLE_ENTITY);
        }
    }

    public function actionUpdate($lang, $id)
    {
        $model = SubjectSemestr::findOne($id);
        if (!$model) {
            return $this->response(0, _e('Data not found.'), null, null, ResponseStatus::NOT_FOUND);
        }
        $oldModel = $model;
        $post = Yii::$app->request->post();
        $this->load($model, $post);
        $model->subject_id = $oldModel->subject_id;
        $result = SubjectSemestr::updateItem($model, $post);
        if (!is_array($result)) {
            return $this->response(1, _e($this->controller_name . ' successfully updated.'), $model, null, ResponseStatus::OK);
        } else {
            return $this->response(0, _e('There is an error occurred while processing.'), null, $result, ResponseStatus::UPROCESSABLE_ENTITY);
        }
    }

    public function actionView($lang, $id)
    {
        $model = SubjectSemestr::find()
            ->andWhere(['id' => $id, 'is_deleted' => 0])
            ->one();
        if (!$model) {
            return $this->response(0, _e('Data not found.'), null, null, ResponseStatus::NOT_FOUND);
        }
        return $this->response(1, _e('Success.'), $model, null, ResponseStatus::OK);
    }

    public function actionDelete($lang, $id)
    {
        $model = SubjectSemestr::find()
            ->andWhere(['id' => $id, 'is_deleted' => 0])
            ->one();

        if (!$model) {
            return $this->response(0, _e('Data not found.'), null, null, ResponseStatus::NOT_FOUND);
        }

        $eduSemestr = EduSemestrSubject::findOne([
            'subject_semestr_id' => $model->id,
            'is_deleted' => 0,
        ]);

        if (isset($eduSemestr)) {
            return $this->response(0, _e('This subject is linked to the EduSemestrSubject table.'), null, null, ResponseStatus::BAD_REQUEST);
        }

        // remove model
        if ($model) {
            TeacherAccess::updateAll(['is_deleted' => 1 , 'status' => 0], ['subject_semestr_id' => $model->id, 'is_deleted' => 0]);
            $model->is_deleted = 1;
            $model->status = 0;
            $model->update(false);
            return $this->response(1, _e($this->controller_name . ' succesfully removed.'), null, null, ResponseStatus::OK);
        }
        return $this->response(0, _e('There is an error occurred while processing.'), null, null, ResponseStatus::BAD_REQUEST);
    }
}