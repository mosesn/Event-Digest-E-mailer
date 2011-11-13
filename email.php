<?php
///////////////////////////////////////////////////////////////////////////////
// Project: Event Digest E-mailer
// Author: Ryan Bubinski, ryanbubinski@gmail.com, 2010.
// Purpose: Uses a Google Calendar and e-mail listserv to automagically generate 
//          an periodic events digest e-mail
///////////////////////////////////////////////////////////////////////////////

/* Load dependencies */
require_once( "./config.ini.php" );
require_once( "./library.php" );
require_once( "./classes/class.phpmailer.php" );

/* Get upcoming events from Google Calendar XML feed */
$start = mktime( 0, 0, 0 );
$end = $start + ( DAYS_FROM_NOW*24*60*60 );
$events = getEvents( CALENDAR_FEED_URL, $start, $end );

if ( empty( $events ) ) {
  die( "There are no upcoming events\n" );
  exit();
}

/* Generate e-mail body */
$email_body .= "\nXXX UPDATE HEADER XXX\n\n\n";
$count = 1;

$email_body .= "ADI Events\n";
foreach( $events as $time => $event )
{
  $email_body .= strval($count++) . ". ";
  $email_body .= html_entity_decode( $event->title ). ", ";
  $email_body .= date( "l m/d", $time );
}

$email_body .= "\n\n";
$email_body .= "ADI EVENTS\n";

$count = 1;
foreach( $events as $time => $event )
{ 
  $email_body .= strval($count++);
  $email_body .=  ". " . clean( $event->title ) . "\n";
  //$email_body .= $event->content;
  $email_body .= clean_body( $event->content, $time, $end);
  $email_body .= "\n\n";
  //$email_body .= clean( $event->content ) . "\n\n\n";
  //$email_body .= strval(strpos($event->content, "\n\n"));
  
}

$email_body .= "XXX UPDATE OTHER EVENTS XXX\n";
echo $email_body;

/* send e-mail */
/*$email_subject = EMAIL_SUBJECT . " ( Week of " . date( "n/j", $start ) .  " - " . date( "n/j", $end ) . " )";

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
else echo "Message sent!\n";*/
