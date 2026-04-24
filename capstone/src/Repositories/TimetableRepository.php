<?php
class TimetableRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getByStudentId(int $studentId): array {
        $sql = "
            SELECT
                ac.day_of_week,
                ac.start_time,
                ac.end_time,
                ac.classroom_num,
                c.course_name,
                s.staff_name
            FROM studentAssignedCourses sac
            INNER JOIN assignedCourses ac ON ac.assigned_id = sac.assigned_id
            INNER JOIN courses c ON c.course_id = ac.course_id
            INNER JOIN staff s ON s.staff_IdNum = ac.staff_IdNum
            WHERE sac.student_IdNum = :sid
            ORDER BY ac.day_of_week, ac.start_time
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":sid" => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}