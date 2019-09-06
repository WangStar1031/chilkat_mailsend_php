<?php
include("chilkat_9_5_0.php");

exec('hostname mycingular'-'.$orderid');



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
}
setSock();
function sendEmail($recipient){
    global $mailman;
    $smtpHostname = $mailman->mxLookup($recipient);
    if ($mailman->get_LastMethodSuccess() != 1) {
        print $mailman->lastErrorText() . "\r\n";
    }

    #  Set the SMTP server.
    $mailman->put_SmtpHost($smtpHostname);

    // Create randomize message contents

    $random = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 9));
    // $url = rand(1,999);
    $order = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 8));
    $docu = rand(100000000,999999999);
    $docu1 = rand(10000,99999);
    $docu2 = rand(10000,99999);
    $ordid = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 17));
    $ordid1 = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 12));
    $ordid2 = strtoupper(substr(str_shuffle(sha1(microtime())), 0, 12));
    $date = gmdate("j M, Y");

    $message1 = file_get_contents("message.txt");
    $message2 = $message1;
    // $message2 = str_replace("#LINK#", $url, $message1);
    $message3 = str_replace("#TOKEN#", $random, $message2);
    $message4 = str_replace("#ORDER#", $order, $message3);
    $message5 = str_replace("#DOCU#", $docu, $message4);
    $message6 = str_replace("#ORDERID#", $ordid, $message5);
    $message7 = str_replace("#DATE#", $date, $message6);
    $scr = str_replace("#CLIENT#", $recipient, $message7);

    // Create a new email object
    $email = new CkEmail();

    $email->put_Subject('');
    $email->put_Body($scr);
    $email->put_FromName('RASTA');
    $email->put_FromAddress('ahsdhsadhsah@alpha.com');
    $email->AddTo("",$recipient);
    $email->put_Charset('UTF-8');
    $email->put_Mailer('Mailer');
    $email->AddHeaderField('Content-Transfer-Encoding','base64');
    $success = $mailman->SendEmail($email);

    $smtpHostname = $mailman->mxLookup($recipient);
    if ($mailman->get_LastMethodSuccess() != 1) {
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
    $nLoop = 0;
    while( !sendEmail($recipient)){
        $nLoop++;
        sleep(1);
        setSock();
        if( $nLoop > count($arrSocks)){
            break;
        }
    }
}

?>
