<?php

function check_if_open($opening, $special, $closed)
{
   $status = false;

   // Get hours for yesterday and today
   $yesterday         = time() - 60 * 60 * 24;
   $today             = time();

   $yesterday_date    = date('Y-m-d', $yesterday);
   $today_date        = date('Y-m-d', $today);

   $yesterday_weekday = strtolower(date('l', $yesterday));
   $today_weekday     = strtolower(date('l', $today));

   $yesterday_start  = (int) strtotime($yesterday_date . ' ' . $opening[$yesterday_weekday]['opening_hour']);
   $yesterday_end    = (int) strtotime($yesterday_date . ' ' . $opening[$yesterday_weekday]['closing_hour']);
   $yesterday_closed = $opening[$yesterday_weekday]['is_closed'];

   $today_start     = (int) strtotime($today_date . ' ' . $opening[$today_weekday]['opening_hour']);
   $today_end       = (int) strtotime($today_date . ' ' . $opening[$today_weekday]['closing_hour']);
   $today_closed    = $opening[$today_weekday]['is_closed'];

   $current = time();

   // Find out if yesterday and/or today are operating under special hours
   foreach ($special as $i)
   {
      if ($i['date'] == $yesterday_date)
      {
         $yesterday_start  = (int) strtotime($yesterday_date . ' ' . $i['opening_hour']);
         $yesterday_end    = (int) strtotime($yesterday_date . ' ' . $i['closing_hour']);
         $yesterday_closed = false;
      }
      else if ($i['date'] == $today_date)
      {
         $today_start  = (int) strtotime($today_date . ' ' . $i['opening_hour']);
         $today_end    = (int) strtotime($today_date . ' ' . $i['closing_hour']);
         $today_closed = false;
      }
   }

   // Make adjustments to end times
   // for times where the outlet is open later than midnight
   if ($yesterday_start >= $yesterday_end)
   {
      $yesterday_end += 60 * 60 * 24;
   }

   if ($today_start >= $today_end)
   {
      $today_end += 60 * 60 * 24;
   }

   // Check if we are still before close from yesterday's hours
   // or if we are in the range for today's hours.
   if (   (   $current < $yesterday_end
           && !in_array($yesterday_date, $closed)
           && !$yesterday_closed)
       || (   $current >= $today_start
           && $current < $today_end
           && !in_array($today_date, $closed)
           && !$today_closed)) {
          $status = true;
    }

    return $status;
}

?>
