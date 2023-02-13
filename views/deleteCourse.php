<?php
$courseID = Router::$ROUTE['URL_PARAMS']['id'];
if(Course::doesCourseBelongToUser($courseID)){
    Course::deleteCourseById($courseID);
}
header('Location: /dashboard');
