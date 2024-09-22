-- online_exam_user_answer_option
ALTER TABLE assesment.online_exam_user_answer_option ADD fileAnswer varchar(200) NULL;
-- 

-- question_level_report
ALTER TABLE assesment.question_level_report ADD questionID int NULL;
ALTER TABLE assesment.question_level_report ADD onlineExamUserAnswerID int NULL;
ALTER TABLE assesment.question_level_report ADD onlineExamUserAnswerOptionID int NULL;
ALTER TABLE assesment.question_level_report ADD ref varchar(100) NULL;


-- online_exam_user_answer_option
ALTER TABLE assesment.online_exam_user_answer_option ADD catatan text NULL;
ALTER TABLE assesment.online_exam_user_answer_option ADD score double NULL;
ALTER TABLE assesment.online_exam_user_answer_option ADD actual_value double NULL; -- jawaban admin
ALTER TABLE assesment.online_exam_user_answer_option ADD actual_score double NULL; -- nilai jawaban admin
ALTER TABLE assesment.online_exam_user_answer_option ADD is_verif BOOL DEFAULT false NULL;
-- 

-- question_level_report
ALTER TABLE assesment.question_level_report DROP COLUMN ref;
ALTER TABLE assesment.question_level_report MODIFY questionID varchar(50) NULL;
ALTER TABLE assesment.question_level_report MODIFY onlineExamUserAnswerID varchar(50) NULL;
ALTER TABLE assesment.question_level_report MODIFY onlineExamUserAnswerOptionID varchar(50) NULL;
ALTER TABLE assesment.question_level_report ADD onlineExamUserStatus int NULL;



-- online_exam_user_status
ALTER TABLE assesment.online_exam_user_status ADD score_verifikasi DOUBLE NULL;
ALTER TABLE assesment.online_exam_user_status MODIFY COLUMN score double NOT NULL;
