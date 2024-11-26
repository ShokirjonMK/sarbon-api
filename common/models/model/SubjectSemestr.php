<?php

namespace common\models\model;

use api\resources\ResourceTrait;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "subject_category".
 *
 * @property int $id
 * @property string $name
 * @property int|null $order
 * @property int|null $type // for auditory time
 * @property int|null $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $is_deleted
 *
 * @property EduSemestrSubjectCategoryTime[] $eduSemestrSubjectCategoryTimes
 */
class SubjectSemestr extends \yii\db\ActiveRecord
{

    public static $selected_language = 'uz';
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
        return 'subject_semestr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subject_id','semestr_id', 'edu_year_id', 'subject_type_id' , 'edu_form_id' , 'credit'], 'required'],
            [['name' , 'description'] , 'string' , 'max' => 255],
            [['subject_id','semestr_id', 'kafedra_id','edu_year_id', 'subject_type_id' , 'edu_form_id' , 'credit' , 'kafedra_id', 'status',  'created_at', 'updated_at', 'created_by', 'updated_by', 'is_deleted'], 'integer'],
        ];
    }


    public function fields()
    {
        $fields =  [
            'id',
            'name',
            'description',
            'subject_id',
            'semestr_id',
            'edu_year_id',
            'subject_type_id' ,
            'edu_form_id' ,
            'credit' ,
            'kafedra_id',

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
            'subject',
            'semestr',
            'kafedra',
            'eduYear',
            'subjectType',
            'eduForm',
            'description',
            'createdBy',
            'updatedBy',
            'createdAt',
            'updatedAt',
        ];

        return $extraFields;
    }

    public function getTranslate()
    {
        if (Yii::$app->request->get('self') == 1) {
            return $this->infoRelation[0];
        }

        return $this->infoRelation[0] ?? $this->infoRelationDefaultLanguage[0];
    }

    public function getDescription()
    {
        return $this->translate->description ?? '';
    }

    public function getInfoRelation()
    {
        // self::$selected_language = array_value(admin_current_lang(), 'lang_code', 'en');
        return $this->hasMany(Translate::class, ['model_id' => 'id'])
            ->andOnCondition(['language' => Yii::$app->request->get('lang'), 'table_name' => $this->tableName()]);
    }

    public function getInfoRelationDefaultLanguage()
    {
        // self::$selected_language = array_value(admin_current_lang(), 'lang_code', 'en');
        return $this->hasMany(Translate::class, ['model_id' => 'id'])
            ->andOnCondition(['language' => self::$selected_language, 'table_name' => $this->tableName()]);
    }

    /**
     * Gets query for [[EduSemestrSubjectCategoryTimes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::className(), ['id' => 'subject_id']);
    }

    public function getSemestr()
    {
        return $this->hasOne(Semestr::className(), ['id' => 'semestr_id']);
    }

    public function getKafedra()
    {
        return $this->hasOne(Kafedra::className(), ['id' => 'kafedra_id']);
    }

    public function getEduYear()
    {
        return $this->hasOne(EduYear::className(), ['id' => 'edu_year_id']);
    }
    public function getSubjectType()
    {
        return $this->hasOne(SubjectType::className(), ['id' => 'subject_type_id']);
    }

    public function getEduForm()
    {
        return $this->hasOne(EduForm::className(), ['id' => 'edu_form_id']);
    }


    public static function createItem($model, $post)
    {
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];

        if (!($model->validate())) {
            $errors[] = $model->errors;
        } else {
            $model->kafedra_id = $model->subject->kafedra_id;
            $model->save(false);
        }

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

        if (!($model->validate())) {
            $errors[] = $model->errors;
        } else {
            $model->kafedra_id = $model->subject->kafedra_id;
            $model->update(false);
        }

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
            $this->created_by = Current_user_id();
        } else {
            $this->updated_by = Current_user_id();
        }
        return parent::beforeSave($insert);
    }
}
