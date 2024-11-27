<?php

namespace api\resources;

use common\models\model\StudentGroup;
use common\models\model\StudentMark;
use common\models\model\StudentSemestrSubject;
use common\models\model\StudentSemestrSubject as CommonStudentSemestrSubject;
use common\models\model\StudentSemestrSubjectVedomst;
use Yii;

class StudentSubject extends CommonStudentSemestrSubject
{
    use ResourceTrait;

    public static function merge($eduSemestr)
    {
        $errors = [];

        $eduSemestrSubjects = $eduSemestr->eduSemestrSubjects;
        if (count($eduSemestrSubjects) == 0) {
            $errors[] = [_e('Edu Semestr Subject not found.')];
            return ['is_ok' => false , 'errors' => $errors];
        }

        $studentGroups = StudentGroup::find()
            ->where([
                'edu_semestr_id' => $eduSemestr->id,
                'status' => 1,
                'is_deleted' => 0
            ])->all();
        if (count($studentGroups) > 0) {
            foreach ($studentGroups as $studentGroup) {
                $result = self::isCheck($eduSemestr , $studentGroup, $eduSemestrSubjects);
                if (!$result['is_ok']) {
                    return ['is_ok' => false , 'errors' => $result['errors']];
                }
            }
        } else {
            $errors[] = [_e('Student Groups not found.')];
            return ['is_ok' => false , 'errors' => $errors];
        }

        if (count($errors) == 0) {
            return ['is_ok' => true];
        }
        return ['is_ok' => false , 'errors' => $errors];
    }

    public static function isCheck($eduSemestr , $studentGroup , $eduSemestrSubjects)
    {
        $errors = [];

        foreach ($eduSemestrSubjects as $eduSemestrSubject) {
            $isStudentSubject = StudentSemestrSubject::findOne([
                'edu_semestr_subject_id' => $eduSemestrSubject->id,
                'student_id' => $studentGroup->student_id,
                'is_deleted' => 0
            ]);

            $eduSemestrSubjectExamTypes = $eduSemestrSubject->eduSemestrExamsTypes;

            if (!$isStudentSubject) {
                $studentSemestrSubject = new StudentSemestrSubject();
                $studentSemestrSubject->edu_plan_id = $eduSemestr->edu_plan_id;
                $studentSemestrSubject->edu_semestr_id = $eduSemestr->id;
                $studentSemestrSubject->edu_semestr_subject_id = $eduSemestrSubject->id;
                $studentSemestrSubject->student_id = $studentGroup->student_id;
                $studentSemestrSubject->student_user_id = $studentGroup->student->user_id;
                $studentSemestrSubject->faculty_id = $eduSemestr->faculty_id;
                $studentSemestrSubject->direction_id = $eduSemestr->direction_id;
                $studentSemestrSubject->edu_form_id = $eduSemestr->edu_form_id;
                $studentSemestrSubject->edu_year_id = $eduSemestr->edu_year_id;
                $studentSemestrSubject->course_id = $eduSemestr->course_id;
                $studentSemestrSubject->semestr_id = $eduSemestr->semestr_id;
                if (!$studentSemestrSubject->validate()) {
                    $errors[] = [ 'studentSemestrSubject'];
                } else {
                    $studentSemestrSubject->save(false);
                    $studentVedomst = new StudentSemestrSubjectVedomst();
                    $studentVedomst->student_semestr_subject_id = $studentSemestrSubject->id;
                    $studentVedomst->edu_semestr_subject_id = $eduSemestrSubject->id;
                    $studentVedomst->subject_id = $eduSemestrSubject->subject_id;
                    $studentVedomst->edu_year_id = $eduSemestr->edu_year_id;
                    $studentVedomst->semestr_id = $eduSemestr->semestr_id;
                    $studentVedomst->student_id = $studentGroup->student_id;
                    $studentVedomst->student_user_id = $studentSemestrSubject->student_user_id;
                    $studentVedomst->group_id = $studentGroup->group_id;
                    $studentVedomst->vedomst = 1;
                    if (!$studentVedomst->validate()) {
                        $errors[] = ['student Vedomst validate'];
                    } else {
                        $studentVedomst->save(false);
                        foreach ($eduSemestrSubjectExamTypes as $eduSemestrSubjectExamType) {
                            $studentMark = new StudentMark();
                            $studentMark->edu_semestr_exams_type_id = $eduSemestrSubjectExamType->id;
                            $studentMark->exam_type_id = $eduSemestrSubjectExamType->exams_type_id;
                            $studentMark->group_id = $studentGroup->group_id;
                            $studentMark->student_id = $studentGroup->student_id;
                            $studentMark->student_user_id = $studentVedomst->student_user_id;
                            $studentMark->max_ball = $eduSemestrSubjectExamType->max_ball;
                            $studentMark->edu_semestr_subject_id = $eduSemestrSubject->id;
                            $studentMark->subject_id = $eduSemestrSubject->subject_id;
                            $studentMark->edu_plan_id = $eduSemestr->edu_plan_id;
                            $studentMark->edu_semestr_id = $eduSemestr->id;
                            $studentMark->faculty_id = $eduSemestr->faculty_id;
                            $studentMark->direction_id = $eduSemestr->direction_id;
                            $studentMark->semestr_id = $eduSemestr->semestr_id;
                            $studentMark->course_id = $eduSemestr->course_id;
                            $studentMark->vedomst = 1;
                            $studentMark->student_semestr_subject_vedomst_id = $studentVedomst->id;
                            $studentMark->save(false);
                        }
                    }
                }
            } else {
                $studentVedomsts = $isStudentSubject->studentVedomst;
                if (count($studentVedomsts) > 0) {
                    foreach ($studentVedomsts as $studentVedomst) {
                        $studentVedomst->group_id = $studentGroup->group_id;
                        $studentVedomst->update(false);
                        foreach ($eduSemestrSubjectExamTypes as $eduSemestrSubjectExamType) {
                            $isStudentMark = StudentMark::findOne([
                                'student_semestr_subject_vedomst_id' => $studentVedomst->id,
                                'edu_semestr_exams_type_id' => $eduSemestrSubjectExamType->id,
                                'is_deleted' => 0,
                                'vedomst' => $studentVedomst->vedomst
                            ]);
                            if ($isStudentMark) {
                                $isStudentMark->group_id = $studentGroup->group_id;
                                $isStudentMark->max_ball = $eduSemestrSubjectExamType->max_ball;
                                $isStudentMark->update(false);
                            } else {
                                $studentMark = new StudentMark();
                                $studentMark->edu_semestr_exams_type_id = $eduSemestrSubjectExamType->id;
                                $studentMark->exam_type_id = $eduSemestrSubjectExamType->exams_type_id;
                                $studentMark->group_id = $studentGroup->group_id;
                                $studentMark->student_id = $studentGroup->student_id;
                                $studentMark->student_user_id = $studentVedomst->student_user_id;
                                $studentMark->max_ball = $eduSemestrSubjectExamType->max_ball;
                                $studentMark->edu_semestr_subject_id = $eduSemestrSubject->id;
                                $studentMark->subject_id = $eduSemestrSubject->subject_id;
                                $studentMark->edu_plan_id = $eduSemestr->edu_plan_id;
                                $studentMark->edu_semestr_id = $eduSemestr->id;
                                $studentMark->faculty_id = $eduSemestr->faculty_id;
                                $studentMark->direction_id = $eduSemestr->direction_id;
                                $studentMark->semestr_id = $eduSemestr->semestr_id;
                                $studentMark->course_id = $eduSemestr->course_id;
                                $studentMark->vedomst = $studentVedomst->vedomst;
                                $studentMark->student_semestr_subject_vedomst_id = $studentVedomst->id;
                                $studentMark->save(false);
                            }
                        }
                    }
                } else {
                    $studentVedomst = new StudentSemestrSubjectVedomst();
                    $studentVedomst->student_semestr_subject_id = $isStudentSubject->id;
                    $studentVedomst->edu_semestr_subject_id = $eduSemestrSubject->id;
                    $studentVedomst->subject_id = $eduSemestrSubject->subject_id;
                    $studentVedomst->edu_year_id = $eduSemestr->edu_year_id;
                    $studentVedomst->semestr_id = $eduSemestr->semestr_id;
                    $studentVedomst->student_id = $studentGroup->student_id;
                    $studentVedomst->student_user_id = $isStudentSubject->student_user_id;
                    $studentVedomst->group_id = $studentGroup->group_id;
                    $studentVedomst->vedomst = 1;
                    if (!$studentVedomst->validate()) {
                        $errors[] = ['student Vedomst validate'];
                    } else {
                        $studentVedomst->save(false);
                        foreach ($eduSemestrSubjectExamTypes as $eduSemestrSubjectExamType) {
                            $studentMark = new StudentMark();
                            $studentMark->edu_semestr_exams_type_id = $eduSemestrSubjectExamType->id;
                            $studentMark->exam_type_id = $eduSemestrSubjectExamType->exams_type_id;
                            $studentMark->group_id = $studentGroup->group_id;
                            $studentMark->student_id = $studentGroup->student_id;
                            $studentMark->student_user_id = $studentVedomst->student_user_id;
                            $studentMark->max_ball = $eduSemestrSubjectExamType->max_ball;
                            $studentMark->edu_semestr_subject_id = $eduSemestrSubject->id;
                            $studentMark->subject_id = $eduSemestrSubject->subject_id;
                            $studentMark->edu_plan_id = $eduSemestr->edu_plan_id;
                            $studentMark->edu_semestr_id = $eduSemestr->id;
                            $studentMark->faculty_id = $eduSemestr->faculty_id;
                            $studentMark->direction_id = $eduSemestr->direction_id;
                            $studentMark->semestr_id = $eduSemestr->semestr_id;
                            $studentMark->course_id = $eduSemestr->course_id;
                            $studentMark->vedomst = 1;
                            $studentMark->student_semestr_subject_vedomst_id = $studentVedomst->id;
                            $studentMark->save(false);
                        }
                    }
                }
            }
        }

        if (count($errors) == 0) {
            return ['is_ok' => true];
        }
        return ['is_ok' => false , 'errors' => $errors];
    }
}
