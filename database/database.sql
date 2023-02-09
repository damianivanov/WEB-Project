CREATE
    DATABASE gradeview;

USE gradeview;

CREATE TABLE teachers
(
    id        INT          NOT NULL AUTO_INCREMENT,
    name      VARCHAR(255) NOT NULL,
    email     VARCHAR(255) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    expertise VARCHAR(255),
    PRIMARY KEY (id)
);

CREATE TABLE courses
(
    id         INT          NOT NULL AUTO_INCREMENT,
    name       VARCHAR(255) NOT NULL,
    year       YEAR         NOT NULL,
    teacher_id INT,
    PRIMARY KEY (id),
    FOREIGN KEY (teacher_id) REFERENCES teachers (id)
);

CREATE TABLE students
(
    id             INT          NOT NULL AUTO_INCREMENT,
    faculty_number VARCHAR(255) NOT NULL,
    name           VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE students_courses_pivot
(
    id         INT NOT NULL AUTO_INCREMENT,
    student_id INT,
    course_id  INT,

    PRIMARY KEY (id),
    FOREIGN KEY (course_id) REFERENCES courses (id),
    FOREIGN KEY (student_id) REFERENCES students (id)
);

CREATE TABLE papers
(
    id           INT          NOT NULL AUTO_INCREMENT,
    name         VARCHAR(255) NOT NULL,
    student_course_pivot_id   INT,
    is_presented BOOLEAN,
    PRIMARY KEY (id),
    FOREIGN KEY (student_course_pivot_id) REFERENCES students_courses_pivot (id)
);

CREATE TABLE presences
(
    id            INT          NOT NULL AUTO_INCREMENT,
    presence_time DATETIME    NOT NULL,
    student_course_pivot_id INT,
    PRIMARY KEY (id),
    FOREIGN KEY (student_course_pivot_id) REFERENCES students_courses_pivot (id)
);

CREATE TABLE time_tables
(
    id                INT       NOT NULL AUTO_INCREMENT,
    paper_id          INT       NOT NULL,
    from_time_planned DATETIME NOT NULL,
    to_time_planned   DATETIME NOT NULL,
    from_time_real    DATETIME,
    to_time_real      DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (paper_id) REFERENCES papers (id)
);

CREATE TABLE students_presence_pivot
(
    id          INT NOT NULL AUTO_INCREMENT,
    student_id  INT NOT NULL,
    presence_id INT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES students (id),
    FOREIGN KEY (presence_id) REFERENCES presences (id)
);
