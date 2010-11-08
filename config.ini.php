<?php
///////////////////////////////////////////////////////////////////////////////
// Project: Event Digest E-mailer
// Author: Ryan Bubinski, ryanbubinski@gmail.com, 2010.
// Purpose: Uses a Google Calendar and e-mail listserv to automagically generate 
//          an periodic events digest e-mail
///////////////////////////////////////////////////////////////////////////////

date_default_timezone_set( 'America/New_York' );

/* Public XML feed of Google calendar describing events */ 
define( 'CALENDAR_FEED_URL', "" );

/* Number of days to include from time script runs */
define( 'DAYS_FROM_NOW', 7 ); 

/* E-mail sender information */
define( 'EMAIL_GMAIL_ACCOUNT', "email@gmail.com" );
define( 'EMAIL_GMAIL_PASSWORD', "password" );
define( 'EMAIL_SENDER_NAME', "John Smith" );

/* E-mail recipient */
define( 'EMAIL_RECIPIENT', "listserv@email.com" );

/* E-mail contents */
define( 'EMAIL_SUBJECT', "Events Digest" );
define( 'EMAIL_FIRST_LINE',  "Events this week: " );
define( 'EMAIL_FOOTER', "This e-mail was automagically generated just for you on " . date( "l, F j, Y" ) . " at " . date( "g:ia T" ) . "." );