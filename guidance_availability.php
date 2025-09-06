<?php
function guidance_is_within_business_hours(DateTime $start): bool {
    // Business hours: Monday–Friday, 08:00 to 17:00. Appointments are 1 hour.
    // Require start time between 08:00 and 16:00 inclusive so it ends by 17:00.
    $day = (int)$start->format('N'); // 1 = Monday, 7 = Sunday
    if ($day < 1 || $day > 5) {
        return false;
    }
    $minutes = ((int)$start->format('H')) * 60 + (int)$start->format('i');
    $minStart = 8 * 60;   // 08:00
    $maxStart = 16 * 60;  // 16:00, so +1h ends at 17:00
    return $minutes >= $minStart && $minutes <= $maxStart;
}
?>

