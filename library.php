<?php
///////////////////////////////////////////////////////////////////////////////
// Project: Event Digest E-mailer
// Author: Ryan Bubinski, ryanbubinski@gmail.com, 2010.
// Purpose: Uses a Google Calendar and e-mail listserv to automagically generate 
//          an periodic events digest e-mail
///////////////////////////////////////////////////////////////////////////////

/*
 * Performs basic input cleanup from event feed XML
 */
function clean( $string )
{
  $string = html_entity_decode( $string );
  $string = strip_tags( $string );

  $string = preg_replace( "/Event Description: /", "", $string );
  $string = preg_replace( "/Event Status:[^\n]+/", "", $string );
  $string = preg_replace( "/Who:[^\n]+\n/", "", $string );
    
  $string = preg_replace( "/&#39;/", "'", $string );
  $string = preg_replace( "/\n([A-Z]+)(\n)+/", "$1\n", $string );

  return $string;
}

function clean_body( $string, $start, $end)
{
  $string = html_entity_decode( $string );
  $string = strip_tags( $string );

  $string = preg_replace( "/Event Description: /", "", $string );
  $string = preg_replace( "/Event Status:[^\n]+/", "", $string );
  $string = preg_replace( "/Who:[^\n]+\n/", "", $string );
  $string = preg_replace( "/When:[^\n]+\n/", "", $string );
  $string = preg_replace( "/Where: /", "", $string );    
  $string = preg_replace( "/&#39;/", "'", $string );
  $string = preg_replace( "/\n([A-Z]+)(\n)+/", "$\n1", $string );
  $string = preg_replace( "/EST\n/", "", $string );
  $string = preg_replace( "/EDT /", "", $string );
  $string = preg_replace( "/\n\n/","\n", $string );
  $ttime = date( "l m/d,  g:i-", $start) . date( "g:i a", $end);
  return $ttime ." in " . $string;
}

/*
 * Retrieves events from Google Calendar feed that fall within the specified interval
 * @param $calendar_url Url of event calendar feed
 * @param $start Beginning of start time interval
 * @param $end End of start time interval
 * @return $events Sorted array of events falling within the specified interval
 */
function getEvents( $calendar_url, $start, $end )
{
  $data = simplexml_load_file( $calendar_url );
  $events = array();

  foreach( $data->entry as $event )
  {
    if( $start_pos = strpos( $event->summary, "First start: " ) ) { continue; }
    else if( strpos( $event->summary, "When: " ) >= 0 )
    {    
      if( strpos( $event->summary, " to " ) )
      {
        $end_pos = strpos( $event->summary, " to " );
      }
      else
      {
        $end_pos = strpos( $event->summary, "<br>" );
      }

      $start_pos = strpos( $event->summary, "When: " );
      $start_time = strtotime( substr( $event->summary, $start_pos+6, $end_pos-$start_pos-6 ) );
      if( $start_time >= $start && $start_time <= $end )
      {
        $event->start_time = $start_time;
        $events[$start_time] = $event;
      }
    }
  }
  
  // sort events by start time (ascending)
  ksort( $events );

  return $events;  
}
