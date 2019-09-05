<?php
include("chilkat_9_5_0.php");

exec('hostname mycingular'-'.$orderid');


$random = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 9));
$url = rand(1,999);
$order = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 8));
$docu = rand(100000000,999999999);
$docu1 = rand(10000,99999);
$docu2 = rand(10000,99999);
$ordid = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 17));
$ordid1 = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 12));
$ordid2 = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 12));
$date = gmdate("j M, Y");


$glob = new CkGlobal();
$success = $glob->UnlockBundle('Anything for 30-day trial');
if ($success != true) {
    print $glob->lastErrorText() . "\n";
    exit;
}

$status = $glob->get_UnlockStatus();
if ($status == 2) {
    print 'Unlocked using purchased unlock code.' . "\n";
}
else {
    print 'Unlocked in trial mode.' . "\n";
}

// The LastErrorText can be examined in the success case to see if it was unlocked in
// trial more, or with a purchased unlock code.
print $glob->lastErrorText() . "\n";


// The mailman object is used for sending and receiving email.
$mailman = new CkMailMan();
$mailman->UnlockComponent("30-day trial");

$strRecipient = @file_get_contents("emails.txt");
$arrBuff = explode(PHP_EOL, $strRecipient);
$arrRecipients = [];
foreach ($arrBuff as $value) {
    if( $value != ""){
        $arrRecipients[] = $value;
    }
}
$strSocks = @file_get_contents("proxies.txt");
$arrBuff = explode(PHP_EOL, $strSocks);
$arrSocks = [];
foreach ($arrBuff as $value) {
    if( $value != ""){
        $arrSocks[] = $value;
    }
}
$sockIndex = 0;
function setSock(){
    global $mailman;
    global $arrSocks;
    global $sockIndex;
    $curSock = $arrSocks[$sockIndex++];
    $sockIndex = $sockIndex >= count($arrSocks) ? $sockIndex - count($arrSocks) : $sockIndex;
    $arrBuff = explode(" ", $curSock);
    $mailman->put_SocksHostname($arrBuff[0]);
    $mailman->put_SocksPort($arrBuff[1]);
    $mailman->put_SocksVersion($arrBuff[2]);
    // $mailman->put_SocksHostname('190.85.19.146');
    // $mailman->put_SocksPort(8008);
    // $mailman->put_SocksVersion(4);
}

setSock();
//$recipient = '7602241960@txt.att.net';
// $recipient = 'locojefe1337@gmail.com';
function sendEmail($recipient){
    global $mailman;
    $smtpHostname = $mailman->mxLookup($recipient);
    if ($mailman->get_LastMethodSuccess() != 1) {
        print $mailman->lastErrorText() . "\r\n";
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
    $email->AddHeaderField('Content-Transfer-Encoding','base64');
    $success = $mailman->SendEmail($email);

    $smtpHostname = $mailman->mxLookup($recipient);
    if ($mailman->get_LastMethodSuccess() != 1) {
        print $mailman->lastErrorText() . "\r\n";
    }

    if ($success != true) {
        print $mailman->lastErrorText() . "\n";
        print 'ConnectFailReason = ' . $mailman->get_ConnectFailReason() . "\n";
        return false;
    }

    $success = $mailman->SmtpAuthenticate();
    if ($success != true) {
        print $mailman->lastErrorText() . "\n";
        return false;
    }

    $success = $mailman->SendEmail($email);
    if ($success != true) {
        print $mailman->lastErrorText() . "\n";
        return false;
    }

    $mailman->CloseSmtpConnection();

    print 'Email Sent.' . "\n";
    return true;
}
foreach ($arrRecipients as $recipient) {
    print $recipient . "\n";
    while( !sendEmail($recipient)){
        setSock();
    }
}

?>
