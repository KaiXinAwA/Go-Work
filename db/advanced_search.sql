-- 工作分类表
CREATE TABLE IF NOT EXISTS work_classifications (
    classification_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 工作类型表
CREATE TABLE IF NOT EXISTS work_types (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 地点表
CREATE TABLE IF NOT EXISTS locations (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    region VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 技能要求表
CREATE TABLE IF NOT EXISTS skills (
    skill_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 工作-技能关联表
CREATE TABLE IF NOT EXISTS job_skills (
    job_id INT,
    skill_id INT,
    required_level ENUM('basic', 'intermediate', 'advanced') DEFAULT 'basic',
    PRIMARY KEY (job_id, skill_id),
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE
);

-- 修改现有的jobs表，添加新的字段
ALTER TABLE jobs
ADD COLUMN work_classification_id INT,
ADD COLUMN work_type_id INT,
ADD COLUMN location_id INT,
ADD COLUMN salary_min DECIMAL(10,2),
ADD COLUMN salary_max DECIMAL(10,2),
ADD FOREIGN KEY (work_classification_id) REFERENCES work_classifications(classification_id),
ADD FOREIGN KEY (work_type_id) REFERENCES work_types(type_id),
ADD FOREIGN KEY (location_id) REFERENCES locations(location_id);

-- 插入工作分类数据
INSERT INTO work_classifications (name, description) VALUES
('Engineering & Technology', 'Engineering and technology related jobs'),
('Finance & Accounting', 'Finance and accounting related jobs'),
('Sports & Recreation', 'Sports and recreation related jobs'),
('Security & Defense', 'Security and defense related jobs'),
('Real Estate & Property', 'Real estate and property related jobs'),
('Pet & Animal Care', 'Pet and animal care related jobs'),
('Education', 'Education related jobs'),
('Sales & Marketing', 'Sales and marketing related jobs'),
('Retail & Customer Service', 'Retail and customer service related jobs'),
('Telecommunications', 'Telecommunications related jobs'),
('Transportation & Logistics', 'Transportation and logistics related jobs'),
('Food & Beverage Industry', 'Food and beverage industry related jobs'),
('Business & Management', 'Business and management related jobs'),
('Arts & Media', 'Arts and media related jobs'),
('Agriculture & Farming', 'Agriculture and farming related jobs'),
('Entertainment & Performing Arts', 'Entertainment and performing arts related jobs'),
('Fashion & Beauty', 'Fashion and beauty related jobs'),
('Translation & Language Services', 'Translation and language services related jobs');

-- 插入工作类型数据
INSERT INTO work_types (name, description) VALUES
('Full-Time', 'Full-time employment'),
('Part-Time', 'Part-time employment'),
('Internship', 'Internship positions'),
('Temporary', 'Temporary employment'),
('Apprenticeship', 'Apprenticeship positions'),
('Freelance', 'Freelance or independent contractor work');

-- 插入地点数据
INSERT INTO locations (name, region) VALUES
('Kuching', 'Sarawak'),
('Miri', 'Sarawak'),
('Kanowit', 'Sarawak'),
('Bau', 'Sarawak'),
('Mukah', 'Sarawak'),
('Sibu', 'Sarawak'),
('Samarahan', 'Sarawak'),
('Serian', 'Sarawak'),
('Limbang', 'Sarawak'),
('Tanjung Manis', 'Sarawak'),
('Simunjan', 'Sarawak'),
('Lawas', 'Sarawak');

-- 插入技能数据
INSERT INTO skills (name, category) VALUES
('Programming & coding (Python, Java, C++)', 'Technical Skills'),
('IT security & cyber awareness', 'Technical Skills'),
('Supply chain & inventory management', 'Business Skills'),
('Public relations & advertising', 'Communication Skills'),
('Makeup application & hairstyling', 'Beauty Skills'),
('Sustainable farming practices', 'Agricultural Skills'),
('Digital marketing (SEO, social media)', 'Marketing Skills'),
('Hygiene & food safety standards', 'Food Industry Skills'),
('Communication & public speaking', 'Communication Skills'),
('Market research & property valuation', 'Business Skills'),
('Acting, dancing, or stage performance skills', 'Entertainment Skills'),
('Public speaking & audience engagement', 'Communication Skills'),
('Communication & negotiation', 'Communication Skills'),
('Physical fitness & self-defense', 'Physical Skills'),
('Medical & veterinary knowledge', 'Medical Skills'),
('Physical fitness & stamina', 'Physical Skills'),
('Problem-solving & adaptability', 'Soft Skills'); 