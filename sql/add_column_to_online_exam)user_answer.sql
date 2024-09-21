-- online_exam_user_answer_option
ALTER TABLE assesment.online_exam_user_answer_option ADD fileAnswer varchar(200) NULL;
-- 

-- question_level_report
ALTER TABLE assesment.question_level_report ADD questionID int NULL;
ALTER TABLE assesment.question_level_report ADD onlineExamUserAnswerID int NULL;
ALTER TABLE assesment.question_level_report ADD onlineExamUserAnswerOptionID int NULL;
