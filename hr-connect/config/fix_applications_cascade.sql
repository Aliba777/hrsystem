-- Исправление внешнего ключа для каскадного удаления откликов при удалении вакансии

USE hr_connect;

-- Удаляем старый внешний ключ
ALTER TABLE applications 
DROP FOREIGN KEY applications_ibfk_1;

-- Добавляем новый внешний ключ с каскадным удалением
ALTER TABLE applications 
ADD CONSTRAINT applications_ibfk_1 
FOREIGN KEY (vacancy_id) 
REFERENCES vacancies(id) 
ON DELETE CASCADE;

-- Проверяем результат
SHOW CREATE TABLE applications;
