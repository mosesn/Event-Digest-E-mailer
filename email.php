<?php
///////////////////////////////////////////////////////////////////////////////
// Automated Event Digest e-mail Generator
// Author: Ryan Bubinski, ryanbubinski@gmail.com, 2010.
// Purpose: Automatically generate event digest from Google Calendar XML
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Dependencies
///////////////////////////////////////////////////////////////////////////////

/* Load dependencies */
require_once( "classes/class.phpmailer.php" );


///////////////////////////////////////////////////////////////////////////////
// Configuration
///////////////////////////////////////////////////////////////////////////////

date_default_timezone_set( 'America/New_York' );

/* XML feed  of Google calendar describing events */ 
define( 'CALENDAR_FEED_URL', "http://www.google.com/calendar/feeds/contact%25adicu.com%40gtempaccount.com/public/basic" );
define( 'PUBLIC_CALENDAR_URL', "http://www.google.com/calendar/embed?src=contact%25adicu.com%40gtempaccount.com&ctz=America/New_York" );

/* Number of days to include from time script runs */
define( 'DAYS_FROM_NOW', 7 ); 

/* E-mail sender information */
define( 'EMAIL_GMAIL_ACCOUNT', "events@adicu.com" );
define( 'EMAIL_GMAIL_PASSWORD', "hackcu10" );
define( 'EMAIL_SENDER_NAME', "Application Development Initiative" );

/* E-mail recipient */
define( 'EMAIL_RECIPIENT', "application-development-initiative@googlegroups.com" );

/* E-mail contents */
define( 'EMAIL_SUBJECT', "ADI Events Digest" );
define( 'EMAIL_FIRST_LINE',  "ADI events this week: " );
define( 'EMAIL_FOOTER', "This e-mail was automagically generated just for you on " . date( "l, F j, Y" ) . " at " . date( "g:ia T" ) . ".\nYou can build your own custom tech events mashup at events.adicu.com." );


///////////////////////////////////////////////////////////////////////////////
// Script
///////////////////////////////////////////////////////////////////////////////

/* Get upcoming events from Google Calendar XML feed */
$start = mktime( 0, 0, 0 );
$end = $start + ( DAYS_FROM_NOW*24*60*60 );
$events = getEvents( CALENDAR_FEED_URL, $start, $end );

if ( empty( $events ) ) die( "There are no upcoming events" );

/* Generate e-mail body */
$email_body = EMAIL_FIRST_LINE;
$email_body .= "\n\n";

foreach( $events as $time => $event )
  {
  $email_body .= ( date( "g", $time ) < 10 ) ? date( "D,  g:ia: ", $time ) : date( "D, g:ia: ", $time );
  $email_body .= html_entity_decode( $event->title ). "\n";
  }

$email_body .= "\n";

foreach( $events as $time => $event )
  {
  $email_body .=  "**********************************************************************";
  $email_body .=  "\n" . clean( $event->title ) . "\n";
  $email_body .=  "**********************************************************************";
  $email_body .= "\n\n";
  $email_body .=  clean( $event->content ) . "\n\n\n";
  }

$email_body .=  "\n\n" . EMAIL_FOOTER;

/* send e-mail */
$email_subject = EMAIL_SUBJECT . " ( Week of " . date( "n/j", $start ) .  " - " . date( "n/j", $end ) . " )";

$mail = new PHPMailer();
$mail->IsSMTP();                           // telling the class to use SMTP
$mail->SMTPDebug = 0;                      // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
$mail->Username   = EMAIL_GMAIL_ACCOUNT;   // GMAIL username
$mail->Password   = EMAIL_GMAIL_PASSWORD;  // GMAIL password

$mail->SetFrom( EMAIL_GMAIL_ACCOUNT, EMAIL_SENDER_NAME );
$mail->AddReplyTo( EMAIL_GMAIL_ACCOUNT, EMAIL_SENDER_NAME );
$mail->Subject = $email_subject;
$mail->Body = $email_body;

$mail->AddAddress( EMAIL_RECIPIENT );

if( !$mail->Send() ) echo "Mailer Error: " . $mail->ErrorInfo . "\n";
else echo "Message sent!\n";


///////////////////////////////////////////////////////////////////////////////
// Functions
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