<?php
    echo "Access Denied.";
    $dateTime = new DateTime();
    echo $dateTime->format(DateTime::ATOM);
    echo $dateTime->add(new DateInterval($interval_spec))->format(DateTime::ATOM);
?>