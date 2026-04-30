-- Добавление каскадного удаления для всех связанных таблиц

USE hr_connect;

-- applications
ALTER TABLE applications DROP FOREIGN KEY applications_ibfk_2;
ALTER TABLE applications ADD CONSTRAINT applications_ibfk_2 
FOREIGN KEY (job_seeker_id) REFERENCES users(id) ON DELETE CASCADE;

-- vacancies
ALTER TABLE vacancies DROP FOREIGN KEY vacancies_ibfk_1;
ALTER TABLE vacancies ADD CONSTRAINT vacancies_ibfk_1 
FOREIGN KEY (hr_id) REFERENCES users(id) ON DELETE CASCADE;

-- job_preferences
ALTER TABLE job_preferences DROP FOREIGN KEY job_preferences_ibfk_1;
ALTER TABLE job_preferences ADD CONSTRAINT job_preferences_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- user_certifications
ALTER TABLE user_certifications DROP FOREIGN KEY user_certifications_ibfk_1;
ALTER TABLE user_certifications ADD CONSTRAINT user_certifications_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- user_education
ALTER TABLE user_education DROP FOREIGN KEY user_education_ibfk_1;
ALTER TABLE user_education ADD CONSTRAINT user_education_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- user_experience
ALTER TABLE user_experience DROP FOREIGN KEY user_experience_ibfk_1;
ALTER TABLE user_experience ADD CONSTRAINT user_experience_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- user_images
ALTER TABLE user_images DROP FOREIGN KEY user_images_ibfk_1;
ALTER TABLE user_images ADD CONSTRAINT user_images_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- user_languages
ALTER TABLE user_languages DROP FOREIGN KEY user_languages_ibfk_1;
ALTER TABLE user_languages ADD CONSTRAINT user_languages_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- user_other_contacts
ALTER TABLE user_other_contacts DROP FOREIGN KEY user_other_contacts_ibfk_1;
ALTER TABLE user_other_contacts ADD CONSTRAINT user_other_contacts_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- user_skills
ALTER TABLE user_skills DROP FOREIGN KEY user_skills_ibfk_1;
ALTER TABLE user_skills ADD CONSTRAINT user_skills_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

SELECT 'Каскадное удаление настроено!' as status;
