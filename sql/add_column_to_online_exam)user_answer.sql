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
ALTER TABLE assesment.online_exam_user_answer_option ADD total_actual_score double NULL; -- nilai jawaban admin
ALTER TABLE assesment.online_exam_user_answer_option ADD is_verif BOOL DEFAULT false NULL;
ALTER TABLE newassessment.online_exam_user_answer_option ADD s_perkara VARCHAR(255) NULL; --Surat perkara
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


-- Laporan Bulanan --
CREATE TABLE IF NOT EXISTS `laporan_bulanan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file` text NOT NULL,
  `original_name` text NOT NULL,
  `parent_id` int NOT NULL,
  `date` date NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_userID` int NOT NULL DEFAULT '0',
  `create_usertypeID` int NOT NULL DEFAULT '0',
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)
INSERT INTO permissions (description, name)
VALUES ('Laporan Bulanan', 'laporan_bulanan');


-- add new menu lapbul --
UPDATE menu 
SET parentID = 0, status = 1, icon = 'fa-certificate', menuName = 'Laporan Bulanan'
WHERE menuID = 30;

UPDATE `permission_relationships`
SET usertype_id = 3
where permission_id = 565;

