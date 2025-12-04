-- Таблица для резюме соискателей
CREATE TABLE resumes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_seeker_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    desired_position VARCHAR(255) NOT NULL,
    desired_salary DECIMAL(10,2),
    work_experience_years INT,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_seeker_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица для офферов от HR к соискателям
CREATE TABLE offers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT NOT NULL,
    hr_id INT NOT NULL,
    job_seeker_id INT NOT NULL,
    message TEXT NOT NULL,
    salary_offer DECIMAL(10,2),
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    FOREIGN KEY (hr_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_seeker_id) REFERENCES users(id) ON DELETE CASCADE
);
