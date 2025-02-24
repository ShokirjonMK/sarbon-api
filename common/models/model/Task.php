<?php

namespace common\models\model;

use api\resources\ResourceTrait;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "building".
 *
 * @property int $id
 * @property int $edu_plan_id
 * @property int $edu_semestr_id
 * @property int $edu_semestr_subject_id
 * @property int $subject_id
 * @property int $exam_type_id
 * @property int $user_id
 * @property int $faculty_id
 * @property int $direction_id
 * @property int $edu_year_id
 * @property int $course_id
 * @property int $semestr_id
 * @property int $start_time
 * @property int $end_time
 * @property int|null $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $is_deleted
 *
 * @property Room[] $rooms
 */
class Task extends \yii\db\ActiveRecord
{
    public static $selected_language = 'uz';

    public $ball;

    use ResourceTrait;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['edu_semestr_subject_id' , 'exam_type_id', 'user_id'], 'required'],
            [[
                'edu_plan_id',
                'edu_semestr_id',
                'edu_semestr_subject_id',
                'subject_id',
                'exam_type_id',
                'user_id',
                'faculty_id',
                'direction_id',
                'edu_year_id',
                'course_id',
                'semestr_id',
                'start_time',
                'end_time',
                'status',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
                'is_deleted'
            ], 'integer'],
            [['ball'], 'number'],
            [['edu_plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduPlan::className(), 'targetAttribute' => ['edu_plan_id' => 'id']],
            [['edu_semestr_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduSemestr::className(), 'targetAttribute' => ['edu_semestr_id' => 'id']],
            [['edu_semestr_subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduSemestrSubject::className(), 'targetAttribute' => ['edu_semestr_subject_id' => 'id']],
            [['subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subject::className(), 'targetAttribute' => ['subject_id' => 'id']],
            [['exam_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExamsType::className(), 'targetAttribute' => ['exam_type_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['faculty_id'], 'exist', 'skipOnError' => true, 'targetClass' => Faculty::className(), 'targetAttribute' => ['faculty_id' => 'id']],
            [['direction_id'], 'exist', 'skipOnError' => true, 'targetClass' => Direction::className(), 'targetAttribute' => ['direction_id' => 'id']],
            [['edu_year_id'], 'exist', 'skipOnError' => true, 'targetClass' => EduYear::className(), 'targetAttribute' => ['edu_year_id' => 'id']],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => Course::className(), 'targetAttribute' => ['course_id' => 'id']],
            [['semestr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Semestr::className(), 'targetAttribute' => ['semestr_id' => 'id']],
        ];
    }

    public function fields()
    {
        $fields =  [
            'id',
            'edu_semestr_id',
            'subject_id',
            'exam_type_id',
            'user_id',
            'faculty_id',
            'direction_id',
            'edu_year_id',
            'course_id',
            'semestr_id',
            'status',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        return $fields;
    }

    public function extraFields()
    {
        $extraFields =  [
            'group',
            'eduPlan',
            'eduSemestr',
            'eduSemestrSubject',
            'subject',
            'examType',
            'user',
            'faculty',
            'direction',
            'eduYear',
            'course',
            'semestr',

            'start_time',
            'startTime' => function () {
                return date('Y-m-d H:i:s' , $this->start_time);
            },
            'end_time',
            'endTime' => function () {
                return date('Y-m-d H:i:s' , $this->end_time);
            },

            'createdBy',
            'updatedBy',
            'createdAt',
            'updatedAt',
        ];

        return $extraFields;
    }

    public function getGroup()
    {
        return $this->hasMany(Group::className(), ['task_id' => 'id'])->where(['is_deleted' => 0]);
    }

    public function getEduPlan()
    {
        return $this->hasOne(EduPlan ::className(), ['id' => 'edu_plan_id']);
    }

    public function getEduSemestr()
    {
        return $this->hasOne(EduSemestr ::className(), ['id' => 'edu_semestr_id']);
    }

    public function getEduSemestrSubject()
    {
        return $this->hasOne(EduSemestrSubject::className(), ['id' => 'edu_semestr_subject_id']);
    }

    public function getSubject()
    {
        return $this->hasOne(Subject::className(), ['id' => 'subject_id']);
    }

    public function getExamType()
    {
        return $this->hasOne(ExamsType::className(), ['id' => 'exam_type_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getFaculty()
    {
        return $this->hasOne(Faculty::className(), ['id' => 'faculty_id']);
    }

    public function getDirection()
    {
        return $this->hasOne(Faculty::className(), ['id' => 'direction_id']);
    }

    public function getEduYear()
    {
        return $this->hasOne(EduYear::className(), ['id' => 'edu_year_id']);
    }

    public function getCourse()
    {
        return $this->hasOne(Course::className(), ['id' => 'course_id']);
    }

    public function getSemestr()
    {
        return $this->hasOne(Semestr::className(), ['id' => 'semestr_id']);
    }

    public static function createItem($model, $post)
    {
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];

        if (isRole('teacher')) {
            $model->user_id = current_user_id();
        }

        if (!$model->validate()) {
            $errors[] = $model->errors;
            $transaction->rollBack();
            return simplify_errors($errors);
        }
        $eduSemestrSubject = $model->eduSemestrSubject;
        if ($eduSemestrSubject) {
            $eduSemestr = $eduSemestrSubject->eduSemestr;
            $model->edu_plan_id = $eduSemestr->edu_plan_id;
            $model->edu_semestr_id = $eduSemestr->id;
            $model->subject_id = $eduSemestrSubject->subject_id;
            $model->faculty_id = $eduSemestr->faculty_id;
            $model->direction_id = $eduSemestr->direction_id;
            $model->edu_year_id = $eduSemestr->edu_year_id;
            $model->course_id = $eduSemestr->course_id;
            $model->semestr_id = $eduSemestr->semestr_id;
            $model->save(false);
            
            $groups = json_decode($post['group'], true);
            dd($groups);
            if (count($groups) > 0) {
                TaskGroup::updateAll(['is_deleted' => 1],['task_id' => $model->id, 'is_deleted' => 0]);
                foreach ($groups as $group => $item) {
                    $group = Group::findOne($group);
                    if ($group) {
                        $taskGroup = TaskGroup::findOne([
                            'task_id' => $model->id,
                            'group_id' => $group,
                        ]);
                        if ($taskGroup) {
                            $taskGroup->edu_plan_id = $model->edu_plan_id;
                            $taskGroup->edu_semestr_id = $model->edu_semestr_id;
                            $taskGroup->subject_id = $model->subject_id;
                            $taskGroup->user_id = $model->user_id;
                            $taskGroup->faculty_id = $model->faculty_id;
                            $taskGroup->direction_id = $model->direction_id;
                            $taskGroup->edu_year_id = $model->edu_year_id;
                            $taskGroup->course_id = $model->course_id;
                            $taskGroup->semestr_id = $model->semestr_id;
                            $taskGroup->ball = $item->ball;
                            $taskGroup->start_time = strtotime($item->start_time);
                            $taskGroup->end_time = strtotime($item->end_time);
                            $taskGroup->status = $item->status;
                            $taskGroup->save(false);
                        } else {
                            $taskGroup = new TaskGroup();
                            $taskGroup->task_id = $model->id;
                            $taskGroup->group_id = $group->id;
                            $taskGroup->edu_plan_id = $model->edu_plan_id;
                            $taskGroup->edu_semestr_id = $model->edu_semestr_id;
                            $taskGroup->edu_semestr_subject_id = $model->edu_semestr_subject_id;
                            $taskGroup->subject_id = $model->subject_id;
                            $taskGroup->exam_type_id = $model->exam_type_id;
                            $taskGroup->user_id = $model->user_id;
                            $taskGroup->faculty_id = $model->faculty_id;
                            $taskGroup->direction_id = $model->direction_id;
                            $taskGroup->edu_year_id = $model->edu_year_id;
                            $taskGroup->course_id = $model->course_id;
                            $taskGroup->semestr_id = $model->semestr_id;
                            $taskGroup->ball = $item->ball;
                            $taskGroup->start_time = strtotime($item->start_time);
                            $taskGroup->end_time = strtotime($item->end_time);
                            $taskGroup->status = $item->status;
                            $taskGroup->save(false);
                        }
                    } else {
                        $errors[] = [_e('Group Id not found')];
                    }
                }
            } else {
                $errors[] = [_e('groups not fount')];
            }

        } else {
            $errors[] = [_e('Edu Semestr Subject not found.')];
        }

        $model->save(false);
        if (count($errors) == 0) {
            $transaction->commit();
            return true;
        }
        $transaction->rollBack();
        return simplify_errors($errors);
    }

    public static function updateItem($model, $post)
    {
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];

        if (isRole('teacher')) {
            $model->user_id = current_user_id();
        }

        if (!($model->validate())) {
            $errors[] = $model->errors;
            $transaction->rollBack();
            return simplify_errors($errors);
        }


        $model->save(false);
        if (count($errors) == 0) {
            $transaction->commit();
            return true;
        }
        $transaction->rollBack();
        return simplify_errors($errors);
    }


    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_by = current_user_id();
        } else {
            $this->updated_by = current_user_id();
        }
        return parent::beforeSave($insert);
    }
}
