<?php

// The version number (9_5_0) should match version of the Chilkat extension used, omitting the micro-version number.
// For example, if using Chilkat v9.5.0.48, then include as shown here:
include("chilkat_9_5_0.php");

//  Starting in v9.5.0.49, all Chilkat classes can be unlocked at once at the beginning of a program
//  by calling UnlockBundle.  It requires a Bundle unlock code.
$chilkatGlob = new CkGlobal();
$success = $chilkatGlob->UnlockBundle('Anything for 30-day trial.');
if ($success != true) {
    print $chilkatGlob->lastErrorText() . "\n";
    exit;
}

//  This example requires Chilkat version 9.5.0.50 or greater.
$tunnel = new CkSshTunnel();

$sshHostname = 'locojefe.ro';
$sshPort = 22;

//  Connect to an SSH server and establish the SSH tunnel:
$success = $tunnel->Connect($sshHostname,$sshPort);
if ($success != true) {
    print $tunnel->lastErrorText() . "\n";
    exit;
}

//  Authenticate with the SSH server via a login/password
//  or with a public key.
//  This example demonstrates SSH password authentication.
$success = $tunnel->AuthenticatePw('home','iRouter123e!');
if ($success != true) {
    print $tunnel->lastErrorText() . "\n";
    exit;
}

//  Indicate that the background SSH tunnel thread will behave as a SOCKS proxy server
//  with dynamic port forwarding:
$tunnel->put_DynamicPortForwarding(true);

//  Start the listen/accept thread to begin accepting SOCKS proxy client connections.
//  Listen on port 1080.
$success = $tunnel->BeginAccepting(1080);
if ($success != true) {
    print $tunnel->lastErrorText() . "\n";
    exit;
}

// The mailman object is used for sending and receiving email.
$mailman = new CkMailMan();
$mailman->put_SocksHostname('127.0.0.1');
$mailman->put_SocksPort(1080);
$mailman->put_SocksVersion(5);

$mailman->UnlockComponent("30-day trial");

//$recipient = '7602241960@txt.att.net';
//$recipient = 'locojefe1337@gmail.com';
$recipient = 'locojefe1337@gmail.com';

#  Do a DNS MX lookup for the recipient's mail server.

$smtpHostname = $mailman->mxLookup('$recipient');
if ($mailman->get_LastMethodSuccess() != 1) {
print $mailman->lastErrorText() . "\r\n";
print $pass;
}

#  Set the SMTP server.
$mailman->put_SmtpHost($smtpHostname);


// Create a new email object
$email = new CkEmail();

$email->put_Subject('This is a test');
$email->put_Body('This is a test');
$email->put_FromName('RASTA');
$email->put_FromAddress('ahsdhsadhsah@alpha.com');
$email->AddTo("",$recipient);
$email->put_Charset('UTF-8');
$email->put_Mailer('Mailer');
$email->get_VerboseLogging();
$email->put_VerboseLogging(true);
$email->AddHeaderField('Content-Transfer-Encoding','base64');
$success = $mailman->SendEmail($email);

$smtpHostname = $mailman->mxLookup($recipient);
if ($mailman->get_LastMethodSuccess() != 1) {
print $mailman->lastErrorText() . "\r\n";
print $pass;
}

if ($success != true) {
    print $mailman->lastErrorText() . "\n";
    print 'ConnectFailReason = ' . $mailman->get_ConnectFailReason() . "\n";
    exit;
}

$success = $mailman->SmtpAuthenticate();
if ($success != true) {
    print $mailman->lastErrorText() . "\n";
    exit;
}

$success = $mailman->SendEmail($email);
if ($success != true) {
    print $mailman->lastErrorText() . "\n";
    exit;
}

$mailman->CloseSmtpConnection();

//  Stop the background listen/accept thread:
$waitForThreadExit = true;
$success = $tunnel->StopAccepting($waitForThreadExit);
if ($success != true) {
    print $tunnel->lastErrorText() . "\n";
    exit;
}

//  Close the SSH tunnel (would also kick any remaining connected clients).
$success = $tunnel->CloseTunnel($waitForThreadExit);
if ($success != true) {
    print $tunnel->lastErrorText() . "\n";
    exit;
}


print 'Email Sent.' . "\n";

?>
