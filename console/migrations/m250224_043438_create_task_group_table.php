<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_group}}`.
 */
class m250224_043438_create_task_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            // https://stackoverflow.com/questions/51278467/mysql-collation-utf8mb4-unicode-ci-vs-utf8mb4-default-collation
            // https://www.eversql.com/mysql-utf8-vs-utf8mb4-whats-the-difference-between-utf8-and-utf8mb4/
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
        }

        $tableName = Yii::$app->db->tablePrefix . 'task_group';
        if (!(Yii::$app->db->getTableSchema($tableName, true) === null)) {
            $this->dropTable('task_group');
        }

        $this->createTable('{{%task_group}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->null(),
            'group_id' => $this->integer()->null(),
            'ball' => $this->float()->null(),

            'edu_plan_id' => $this->integer()->null(),
            'edu_semestr_id' => $this->integer()->null(),
            'edu_semestr_subject_id' => $this->integer()->null(),
            'subject_id' => $this->integer()->null(),
            'exam_type_id' => $this->integer()->null(),
            'user_id' => $this->integer()->null(),
            'faculty_id' => $this->integer()->null(),
            'direction_id' => $this->integer()->null(),
            'edu_year_id' => $this->integer()->null(),
            'course_id' => $this->integer()->null(),
            'semestr_id' => $this->integer()->null(),

            'start_time' => $this->integer()->null(),
            'end_time' => $this->integer()->null(),

            'status' => $this->tinyInteger(1)->defaultValue(1),
            'created_at'=>$this->integer()->null(),
            'updated_at'=>$this->integer()->null(),
            'created_by' => $this->integer()->defaultValue(0),
            'updated_by' => $this->integer()->defaultValue(0),
            'is_deleted' => $this->tinyInteger()->defaultValue(0),
        ], $tableOptions);

        $this->addForeignKey('mk_task_group_table_task_table', 'task_group', 'task_id', 'task', 'id');
        $this->addForeignKey('mk_task_group_table_group_table', 'task_group', 'group_id', 'group', 'id');
        $this->addForeignKey('mk_task_group_table_edu_plan_table', 'task_group', 'edu_plan_id', 'edu_plan', 'id');
        $this->addForeignKey('mk_task_group_table_edu_semestr_table', 'task_group', 'edu_semestr_id', 'edu_semestr', 'id');
        $this->addForeignKey('mk_task_group_table_edu_semestr_subject_table', 'task_group', 'edu_semestr_subject_id', 'edu_semestr_subject', 'id');
        $this->addForeignKey('mk_task_group_table_subject_table', 'task_group', 'subject_id', 'subject', 'id');
        $this->addForeignKey('mk_task_group_table_exam_type_table', 'task_group', 'exam_type_id', 'exams_type', 'id');
        $this->addForeignKey('mk_task_group_table_user_table', 'task_group', 'user_id', 'users', 'id');
        $this->addForeignKey('mk_task_group_table_faculty_table', 'task_group', 'faculty_id', 'faculty', 'id');
        $this->addForeignKey('mk_task_group_table_direction_table', 'task_group', 'direction_id', 'direction', 'id');
        $this->addForeignKey('mk_task_group_table_edu_year_table', 'task_group', 'edu_year_id', 'edu_year', 'id');
        $this->addForeignKey('mk_task_group_table_course_table', 'task_group', 'course_id', 'course', 'id');
        $this->addForeignKey('mk_task_group_table_semestr_table', 'task_group', 'semestr_id', 'semestr', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_group}}');
    }
}
